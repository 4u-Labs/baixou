<?php
// Worker de processamento em segundo plano para o BAIXOU!!
// Executa de forma desacoplada do servidor web para não travar o HTTP

if (php_sapi_name() !== 'cli' && empty($_GET['internal_key'])) {
    http_response_code(403);
    die('Acesso negado.');
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/cleaner.php';

// Limpeza preventiva de arquivos com mais de 30 min
purgeExpiredFiles();

$jobId = $argv[1] ?? $_GET['job_id'] ?? '';
if (empty($jobId)) {
    die("Job ID obrigatório.\n");
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM jobs WHERE id = :id");
$stmt->execute([':id' => $jobId]);
$job = $stmt->fetch();

if (!$job) {
    die("Job $jobId não encontrado.\n");
}

// Localizar binários no sistema (Local e VPS)
function findBinary($name) {
    $candidates = [
        "/usr/local/bin/$name",
        "/usr/bin/$name",
        "/home/fabiano/.local/bin/$name",
        "/bin/$name"
    ];
    foreach ($candidates as $c) {
        if (file_exists($c) && is_executable($c)) {
            return $c;
        }
    }
    // Fallback para PATH
    $which = trim(shell_exec("which " . escapeshellarg($name) . " 2>/dev/null") ?? '');
    return !empty($which) ? $which : $name;
}

$binYtDlp = findBinary('yt-dlp');
$binFfmpeg = findBinary('ffmpeg');
$ffmpegDir = dirname($binFfmpeg);

function updateJob($db, $jobId, $fields) {
    $set = [];
    $params = [':id' => $jobId];
    foreach ($fields as $k => $v) {
        $set[] = "$k = :$k";
        $params[":$k"] = $v;
    }
    $sql = "UPDATE jobs SET " . implode(', ', $set) . " WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
}

// 1. Iniciar análise
updateJob($db, $jobId, [
    'status' => 'analyzing',
    'step' => 'Consultando metadados e formatos disponíveis...',
    'progress' => 15,
    'started_at' => time()
]);

$url = $job['url'];
$format = $job['format'] ?? 'mp4';
$quality = $job['quality'] ?? 'auto';

$isYouTube = (stripos($url, 'youtube.com') !== false || stripos($url, 'youtu.be') !== false);
$proxyOpt = '';

// Se for YouTube e houver proxy/túnel residencial ativo (porta 10800), roteia tráfego para contornar bloqueio de ASN de Datacenter
if ($isYouTube) {
    $fp = @fsockopen('127.0.0.1', 10800, $errno, $errstr, 0.3);
    if ($fp) {
        fclose($fp);
        $proxyOpt = ' --proxy "socks5://127.0.0.1:10800"';
    }
}

$cookiesFile = __DIR__ . '/storage/data/cookies.txt';
// Não passar cookies para o YouTube caso use proxy residencial (evita conflito de sessão/rotação)
$cookiesOpt = (file_exists($cookiesFile) && filesize($cookiesFile) > 10 && !$isYouTube) ? ' --cookies ' . escapeshellarg($cookiesFile) : '';

// Obter metadados via yt-dlp dump-json
$cmdInfo = sprintf(
    'PATH=%s:$PATH %s --dump-json --no-warnings --no-playlist%s%s --remote-components ejs:github --extractor-args "youtube:player_client=android,web" %s 2>&1',
    escapeshellarg($ffmpegDir),
    escapeshellarg($binYtDlp),
    $cookiesOpt,
    $proxyOpt,
    escapeshellarg($url)
);

$infoJson = shell_exec($cmdInfo);
$meta = json_decode($infoJson ?? '', true);

$title = 'Vídeo Autorizado';
$thumb = '';
$durationFormatted = '--:--';

if ($meta && !empty($meta['title'])) {
    $title = $meta['title'];
    $thumb = $meta['thumbnail'] ?? '';
    if (!empty($meta['thumbnails']) && is_array($meta['thumbnails'])) {
        $lastThumb = end($meta['thumbnails']);
        if (!empty($lastThumb['url'])) $thumb = $lastThumb['url'];
    }
    $durSec = (int)($meta['duration'] ?? 0);
    if ($durSec > 0) {
        $durationFormatted = $durSec >= 3600
            ? sprintf('%02d:%02d:%02d', floor($durSec / 3600), floor(($durSec % 3600) / 60), $durSec % 60)
            : sprintf('%02d:%02d', floor($durSec / 60), $durSec % 60);
    }
}

// Atualizar metadados no banco
updateJob($db, $jobId, [
    'title' => $title,
    'thumbnail' => $thumb,
    'duration' => $durationFormatted,
    'status' => 'downloading',
    'step' => 'Baixando fluxo de dados autorizado...',
    'progress' => 35
]);

// 2. Preparar pasta de destino
$jobDir = __DIR__ . "/storage/downloads/$jobId";
@mkdir($jobDir, 0777, true);
$outPattern = "$jobDir/media.%(ext)s";

// Montar comando de download e conversão
if ($format === 'mp3') {
    $dlCmd = sprintf(
        'PATH=%s:$PATH %s -x --audio-format mp3 --audio-quality 0 --ffmpeg-location %s --no-playlist%s%s --remote-components ejs:github --extractor-args "youtube:player_client=android,web" -o %s %s 2>&1',
        escapeshellarg($ffmpegDir),
        escapeshellarg($binYtDlp),
        escapeshellarg($binFfmpeg),
        $cookiesOpt,
        $proxyOpt,
        escapeshellarg($outPattern),
        escapeshellarg($url)
    );
    $expectedMime = 'audio/mpeg';
} else {
    // Formato MP4
    $formatSelector = 'bestvideo[ext=mp4]+bestaudio[ext=m4a]/best[ext=mp4]/best';
    if ($quality === '1080p') {
        $formatSelector = 'bestvideo[height<=1080][ext=mp4]+bestaudio[ext=m4a]/best[height<=1080][ext=mp4]/best';
    } elseif ($quality === '720p') {
        $formatSelector = 'bestvideo[height<=720][ext=mp4]+bestaudio[ext=m4a]/best[height<=720][ext=mp4]/best';
    } elseif ($quality === '480p') {
        $formatSelector = 'bestvideo[height<=480][ext=mp4]+bestaudio[ext=m4a]/best[height<=480][ext=mp4]/best';
    }

    $dlCmd = sprintf(
        'PATH=%s:$PATH %s -f %s --merge-output-format mp4 --ffmpeg-location %s --no-playlist%s%s --remote-components ejs:github --extractor-args "youtube:player_client=android,web" -o %s %s 2>&1',
        escapeshellarg($ffmpegDir),
        escapeshellarg($binYtDlp),
        escapeshellarg($formatSelector),
        escapeshellarg($binFfmpeg),
        $cookiesOpt,
        $proxyOpt,
        escapeshellarg($outPattern),
        escapeshellarg($url)
    );
    $expectedMime = 'video/mp4';
}

updateJob($db, $jobId, [
    'status' => 'processing',
    'step' => 'Processando e sincronizando áudio e vídeo com FFmpeg...',
    'progress' => 70
]);

$outputResult = shell_exec($dlCmd);

// 3. Verificar arquivo final gerado
$files = glob("$jobDir/media.*");
$finalFile = null;

foreach ($files as $f) {
    if (is_file($f) && filesize($f) > 1024) {
        $finalFile = $f;
        break;
    }
}

if ($finalFile && file_exists($finalFile)) {
    $fileSize = filesize($finalFile);
    $expiresAt = time() + 1800; // 30 minutos

    updateJob($db, $jobId, [
        'status' => 'completed',
        'step' => 'Conversão concluída com sucesso! Pronto para download.',
        'progress' => 100,
        'file_path' => $finalFile,
        'file_size' => $fileSize,
        'mime_type' => $expectedMime,
        'completed_at' => time(),
        'expires_at' => $expiresAt
    ]);
    echo "Job $jobId finalizado com sucesso. Arquivo: $finalFile ($fileSize bytes)\n";
} else {
    // Erro ao processar
    $errorMsg = 'Não foi possível baixar ou converter este conteúdo. A fonte pode exigir autenticação, estar indisponível ou possuir restrições de acesso.';
    if (strpos($outputResult ?? '', 'Sign in to confirm you’re not a bot') !== false) {
        $errorMsg = 'O YouTube exigiu verificação de robô devido ao IP do servidor em nuvem. Utilize a versão local do BAIXOU (http://localhost:8000/app/baixou/) ou mantenha seu aplicativo local conectado.';
    } elseif (strpos($outputResult ?? '', 'Private video') !== false) {
        $errorMsg = 'O vídeo é privado ou requer permissões especiais de acesso.';
    }

    updateJob($db, $jobId, [
        'status' => 'error',
        'step' => 'Erro no processamento da solicitação',
        'progress' => 0,
        'error_message' => $errorMsg
    ]);
    echo "Job $jobId falhou. Log:\n$outputResult\n";
}
