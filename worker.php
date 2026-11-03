<?php
// Worker de processamento em segundo plano para o BAIXOU!!
// Executa de forma desacoplada do servidor web para não travar o HTTP

// Desativar qualquer limite de tempo de execução para downloads gigantescos
@set_time_limit(0);
@ini_set('max_execution_time', '0');
@ini_set('memory_limit', '1024M');
ignore_user_abort(true);

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

// Obter metadados via yt-dlp dump-json (redirecionando stderr para não poluir JSON)
$cmdInfo = sprintf(
    'PATH=%s:$PATH %s --dump-json --no-warnings --no-playlist%s%s %s 2>/dev/null',
    escapeshellarg($ffmpegDir),
    escapeshellarg($binYtDlp),
    $cookiesOpt,
    $proxyOpt,
    escapeshellarg($url)
);

$infoJson = trim(shell_exec($cmdInfo) ?? '');
$meta = null;

if (!empty($infoJson)) {
    $firstBrace = strpos($infoJson, '{');
    $lastBrace = strrpos($infoJson, '}');
    if ($firstBrace !== false && $lastBrace !== false) {
        $cleanJson = substr($infoJson, $firstBrace, $lastBrace - $firstBrace + 1);
        $meta = json_decode($cleanJson, true);
    }
}

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
    'step' => 'Iniciando download do fluxo de dados autorizado...',
    'progress' => 25
]);

// 2. Preparar pasta de destino
$jobDir = __DIR__ . "/storage/downloads/$jobId";
@mkdir($jobDir, 0777, true);
$outPattern = "$jobDir/media.%(ext)s";

// Montar comando de download e conversão com suporte a formatos amplos e merging automático
if ($format === 'mp3') {
    $dlCmd = sprintf(
        'PATH=%s:$PATH %s -x --audio-format mp3 --audio-quality 0 --ffmpeg-location %s --no-playlist%s%s --newline --progress-template "%%(progress._percent_str)s|%%(progress._speed_str)s|%%(progress._eta_str)s|%%(progress._total_bytes_estimate_str)s" -o %s %s',
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
    // Formato MP4 com seleção dinâmica de qualidade
    $formatSelector = 'bestvideo+bestaudio/best';
    if ($quality === '1080p') {
        $formatSelector = 'bestvideo[height<=1080]+bestaudio/best[height<=1080]/best';
    } elseif ($quality === '720p') {
        $formatSelector = 'bestvideo[height<=720]+bestaudio/best[height<=720]/best';
    } elseif ($quality === '480p') {
        $formatSelector = 'bestvideo[height<=480]+bestaudio/best[height<=480]/best';
    } elseif ($quality === '360p') {
        $formatSelector = 'bestvideo[height<=360]+bestaudio/best[height<=360]/best';
    }

    $dlCmd = sprintf(
        'PATH=%s:$PATH %s -f %s --merge-output-format mp4 --ffmpeg-location %s --no-playlist%s%s --newline --progress-template "%%(progress._percent_str)s|%%(progress._speed_str)s|%%(progress._eta_str)s|%%(progress._total_bytes_estimate_str)s" -o %s %s',
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
    'status' => 'downloading',
    'step' => 'Baixando fluxo de dados...',
    'progress' => 30
]);

// Executar via proc_open para atualizar progresso em tempo real na interface
$descriptors = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w']
];

$proc = proc_open($dlCmd, $descriptors, $pipes, dirname($jobDir), ['PATH' => "$ffmpegDir:" . getenv('PATH')]);
$outputResult = '';
$lastUpdate = time();

if (is_resource($proc)) {
    fclose($pipes[0]);
    stream_set_blocking($pipes[1], 0);
    stream_set_blocking($pipes[2], 0);

    while (true) {
        $pstatus = proc_get_status($proc);
        $read = [$pipes[1], $pipes[2]];
        $write = null;
        $except = null;
        $numChanged = @stream_select($read, $write, $except, 0, 200000); // 200ms

        if ($numChanged > 0) {
            foreach ($read as $r) {
                while (($line = fgets($r)) !== false) {
                    $outputResult .= $line;

                    // Exemplo da template: " 45.2%| 12.3MiB/s| 02:15| 1.82GiB"
                    if (strpos($line, '%|') !== false) {
                        $parts = explode('|', trim($line));
                        $pctStr = trim(str_replace('%', '', $parts[0] ?? ''));
                        $speed = trim($parts[1] ?? '');
                        $eta = trim($parts[2] ?? '');
                        $size = trim($parts[3] ?? '');

                        $pctFloat = floatval($pctStr);
                        if ($pctFloat > 0 && (time() - $lastUpdate >= 1)) {
                            // Mapeia 0..100% do download para 30..90% da barra global
                            $globalProgress = 30 + (int)($pctFloat * 0.60);
                            $stepMsg = "Baixando mídia: " . round($pctFloat, 1) . "%";
                            if (!empty($speed) && $speed !== 'NA' && $speed !== 'N/A') $stepMsg .= " ($speed)";
                            if (!empty($eta) && $eta !== 'NA' && $eta !== 'N/A') $stepMsg .= " • Resta: $eta";

                            updateJob($db, $jobId, [
                                'progress' => min(90, $globalProgress),
                                'step' => $stepMsg
                            ]);
                            $lastUpdate = time();
                        }
                    } elseif (stripos($line, '[Merger]') !== false || stripos($line, '[ExtractAudio]') !== false || stripos($line, 'ffmpeg') !== false) {
                        updateJob($db, $jobId, [
                            'status' => 'processing',
                            'progress' => 93,
                            'step' => 'Sincronizando áudio e vídeo com FFmpeg...'
                        ]);
                    }
                }
            }
        }

        if (!$pstatus['running']) {
            break;
        }
    }

    $outputResult .= stream_get_contents($pipes[1]);
    $outputResult .= stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($proc);
} else {
    // Fallback para shell_exec caso proc_open falhe
    $outputResult = shell_exec($dlCmd . " 2>&1");
}

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
