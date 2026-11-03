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

// Função utilitária para converter e limpar legendas WebVTT do YouTube (elimina repetições de roll-up)
function parseVttToCleanTranscript(string $vttContent): array {
    $cleanVttText = function($text) {
        $text = preg_replace('/<[^>]+>/', '', $text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim($text);
    };

    preg_match_all('/(\d{2}:\d{2}:\d{2}\.\d{3})\s+-->\s+(\d{2}:\d{2}:\d{2}\.\d{3})[^\n]*\n([\s\S]*?)(?=\n\n\d{2}:\d{2}|\n*$)/', $vttContent, $matches, PREG_SET_ORDER);

    $cleanEntries = [];
    $lastLine = '';

    foreach ($matches as $m) {
        $start = $m[1];
        $raw = $cleanVttText($m[3]);
        if (empty($raw)) continue;

        $subLines = explode("\n", $raw);
        foreach ($subLines as $sLine) {
            $sLine = trim($sLine);
            if (empty($sLine) || $sLine === $lastLine) continue;

            $parts = explode(':', substr($start, 0, 8));
            $ts = ($parts[0] === '00')
                ? (ltrim($parts[1], '0') ?: '0') . ':' . $parts[2]
                : ltrim($parts[0], '0') . ':' . $parts[1] . ':' . $parts[2];

            $cleanEntries[] = [
                'raw_start' => $start,
                'ts' => $ts,
                'text' => $sLine
            ];
            $lastLine = $sLine;
        }
    }
    return $cleanEntries;
}

// 1. Iniciar análise
updateJob($db, $jobId, [
    'status' => 'analyzing',
    'step' => 'Consultando metadados e formatos disponíveis...',
    'progress' => 15,
    'started_at' => time()
]);

$url = $job['url'];
$format = strtolower($job['format'] ?? 'mp4');
$quality = strtolower($job['quality'] ?? 'auto');

$isYouTube = (stripos($url, 'youtube.com') !== false || stripos($url, 'youtu.be') !== false);
$proxyOpt = '';

// Se for YouTube e houver proxy residencial ativo (porta 10800), roteia tráfego
if ($isYouTube) {
    $fp = @fsockopen('127.0.0.1', 10800, $errno, $errstr, 0.3);
    if ($fp) {
        fclose($fp);
        $proxyOpt = ' --proxy "socks5://127.0.0.1:10800"';
    }
}

$cookiesFile = __DIR__ . '/storage/data/cookies.txt';
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
    'step' => 'Iniciando processamento do conteúdo solicitado...',
    'progress' => 25
]);

// 2. Preparar pasta de destino
$jobDir = __DIR__ . "/storage/downloads/$jobId";
@mkdir($jobDir, 0777, true);
$outPattern = "$jobDir/media.%(ext)s";

$isTranscript = in_array($format, ['txt', 'md', 'srt'], true);
$finalFile = null;
$expectedMime = 'text/plain; charset=utf-8';
$outputResult = '';

if ($isTranscript) {
    updateJob($db, $jobId, [
        'status' => 'downloading',
        'step' => 'Localizando faixas de transcrição e legendas oficiais...',
        'progress' => 45
    ]);

    // Seleção de idiomas (Português preferencial, Inglês, Espanhol ou todas)
    $subLang = match($quality) {
        'pt' => 'pt.*,pt-BR,pt-orig,en.*,all',
        'en' => 'en.*,en-US,en-orig,pt.*,all',
        'es' => 'es.*,es-419,pt.*,en.*,all',
        default => 'pt.*,pt-BR,pt-orig,en.*,all'
    };

    $rawPattern = "$jobDir/raw.%(ext)s";
    $subCmd = sprintf(
        'PATH=%s:$PATH %s --write-auto-subs --write-subs --sub-lang %s --sub-format "vtt/srt" --skip-download%s%s -o %s %s 2>&1',
        escapeshellarg($ffmpegDir),
        escapeshellarg($binYtDlp),
        escapeshellarg($subLang),
        $cookiesOpt,
        $proxyOpt,
        escapeshellarg($rawPattern),
        escapeshellarg($url)
    );

    $outputResult = shell_exec($subCmd);

    // Encontrar arquivos de legenda baixados
    $subFiles = glob("$jobDir/raw.*.vtt");
    if (empty($subFiles)) {
        $subFiles = glob("$jobDir/raw.*.srt");
    }

    if (!empty($subFiles)) {
        // Escolher o melhor idioma (priorizar português se disponível)
        $selectedSub = $subFiles[0];
        foreach ($subFiles as $sf) {
            if (stripos($sf, '.pt') !== false || stripos($sf, 'pt-BR') !== false || stripos($sf, 'pt-orig') !== false) {
                $selectedSub = $sf;
                break;
            }
        }

        $vttRaw = file_get_contents($selectedSub);
        $entries = parseVttToCleanTranscript($vttRaw);

        if (!empty($entries)) {
            updateJob($db, $jobId, [
                'status' => 'processing',
                'step' => 'Formatando e estruturando transcrição...',
                'progress' => 85
            ]);

            if ($format === 'md') {
                $finalContent = "# " . ($title ?: 'Transcrição') . "\n\n";
                $finalContent .= "- **Origem:** " . $url . "\n";
                $finalContent .= "- **Duração:** " . $durationFormatted . "\n";
                $finalContent .= "- **Extraído por:** BAIXOU!! (https://4u.ia.br/app/baixou/)\n\n---\n\n";
                foreach ($entries as $e) {
                    $finalContent .= "**[" . $e['ts'] . "]** " . $e['text'] . "\n\n";
                }
                $finalFile = "$jobDir/media.md";
                $expectedMime = 'text/markdown; charset=utf-8';
            } elseif ($format === 'srt') {
                $finalContent = "";
                $idx = 1;
                for ($i = 0; $i < count($entries); $i++) {
                    $curr = $entries[$i];
                    $next = $entries[$i + 1] ?? null;
                    $startTs = str_replace('.', ',', $curr['raw_start']);
                    $endTs = $next ? str_replace('.', ',', $next['raw_start']) : $startTs;
                    $finalContent .= "$idx\n$startTs --> $endTs\n" . $curr['text'] . "\n\n";
                    $idx++;
                }
                $finalFile = "$jobDir/media.srt";
                $expectedMime = 'text/plain; charset=utf-8';
            } else {
                // Formato TXT padrão: parágrafos fluidos estruturados
                $finalContent = ($title ?: 'Transcrição') . "\n";
                $finalContent .= str_repeat('=', mb_strlen($title ?: 'Transcrição')) . "\n";
                $finalContent .= "Duração: $durationFormatted | Fonte: $url\n\n";
                $para = [];
                foreach ($entries as $e) {
                    $line = $e['text'];
                    if (strpos($line, '>>') === 0 || strpos($line, '—') === 0) {
                        if (!empty($para)) {
                            $finalContent .= implode(' ', $para) . "\n\n";
                            $para = [];
                        }
                        $line = ltrim($line, '>- ');
                    }
                    $para[] = $line;
                    if (count($para) >= 6) {
                        $finalContent .= implode(' ', $para) . "\n\n";
                        $para = [];
                    }
                }
                if (!empty($para)) {
                    $finalContent .= implode(' ', $para) . "\n\n";
                }
                $finalFile = "$jobDir/media.txt";
                $expectedMime = 'text/plain; charset=utf-8';
            }

            file_put_contents($finalFile, $finalContent);

            // Limpar arquivos brutos raw
            foreach (glob("$jobDir/raw.*") as $rf) {
                @unlink($rf);
            }
        }
    }
} else {
    // Montar comando de download de mídia (MP3 ou MP4)
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

                            $pctFloat = floatval($pctStr);
                            if ($pctFloat > 0 && (time() - $lastUpdate >= 1)) {
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
        $outputResult = shell_exec($dlCmd . " 2>&1");
    }

    $files = glob("$jobDir/media.*");
    foreach ($files as $f) {
        if (is_file($f) && filesize($f) > 1024) {
            $finalFile = $f;
            break;
        }
    }
}

// 3. Verificar arquivo final gerado
if ($finalFile && file_exists($finalFile)) {
    $fileSize = filesize($finalFile);
    $expiresAt = time() + 1800; // 30 minutos

    $completionStep = $isTranscript
        ? 'Transcrição concluída com sucesso! Disponível para leitura e download.'
        : 'Conversão concluída com sucesso! Pronto para download.';

    updateJob($db, $jobId, [
        'status' => 'completed',
        'step' => $completionStep,
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
    $errorMsg = $isTranscript
        ? 'Nenhuma transcrição ou legenda disponível publicamente para este vídeo.'
        : 'Não foi possível baixar ou converter este conteúdo. A fonte pode exigir autenticação, estar indisponível ou possuir restrições de acesso.';

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
