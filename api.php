<?php
// API REST Segura para o BAIXOU!!
// Tratamento assíncrono de jobs, Anti-SSRF, controle de consentimento e entrega de mídia

header('Content-Type: application/json; charset=utf-8');
$_fouruOrigin = $_SERVER['HTTP_ORIGIN'] ?? ''; if ($_fouruOrigin !== '' && (in_array($_fouruOrigin, ['https://4u.ia.br','https://www.4u.ia.br'], true) || str_starts_with($_fouruOrigin, 'chrome-extension://') || str_starts_with($_fouruOrigin, 'http://localhost') || str_starts_with($_fouruOrigin, 'http://127.0.0.1'))) { header('Access-Control-Allow-Origin: ' . $_fouruOrigin); header('Vary: Origin'); }
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/cleaner.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Função rigorosa de proteção Anti-SSRF e validação de URL
function validateSafeUrl($url, $db) {
    if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
        return ['valid' => false, 'error' => 'URL inválida ou em formato incorreto.'];
    }

    $parsed = parse_url($url);
    $scheme = strtolower($parsed['scheme'] ?? '');
    if (!in_array($scheme, ['http', 'https'], true)) {
        return ['valid' => false, 'error' => 'Protocolo não suportado. Apenas HTTP e HTTPS são permitidos.'];
    }

    $host = strtolower($parsed['host'] ?? '');
    if (empty($host)) {
        return ['valid' => false, 'error' => 'Domínio não identificado na URL.'];
    }

    // Bloquear credenciais embutidas na URL
    if (isset($parsed['user']) || isset($parsed['pass'])) {
        return ['valid' => false, 'error' => 'URLs com dados de autenticação não são permitidas.'];
    }

    // Bloquear portas administrativas ou suspeitas
    if (isset($parsed['port']) && !in_array((int)$parsed['port'], [80, 443], true)) {
        return ['valid' => false, 'error' => 'Apenas portas web padrão (80 e 443) são aceitas.'];
    }

    // Verificar se o domínio está na Blacklist
    $stmt = $db->prepare("SELECT domain FROM blocked_sources WHERE domain = :host OR domain = :exact");
    $stmt->execute([':host' => $host, ':exact' => $url]);
    if ($stmt->fetch()) {
        return ['valid' => false, 'error' => 'Esta fonte de conteúdo foi bloqueada preventivamente pela administração.'];
    }

    // Resolver endereço IP e barrar endereços locais / privados / loopback
    $ip = gethostbyname($host);
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        // Se for um IP privado (127.0.0.1, 10.x, 192.168.x, 169.254.x, etc.)
        return ['valid' => false, 'error' => 'Acesso a endereços de rede local, loopback ou infraestrutura interna bloqueado por segurança.'];
    }

    return ['valid' => true, 'host' => $host];
}

// 1. Criar Tarefa de Download (Background Job)
if ($action === 'create_job') {
    $db = getDB();

    $url = trim($_POST['url'] ?? '');
    $format = strtolower(trim($_POST['format'] ?? 'mp4'));
    $quality = strtolower(trim($_POST['quality'] ?? 'auto'));
    $consentRights = !empty($_POST['consent_rights']) && ($_POST['consent_rights'] === '1' || $_POST['consent_rights'] === 'true' || $_POST['consent_rights'] === true);
    $consentTerms = !empty($_POST['consent_terms']) && ($_POST['consent_terms'] === '1' || $_POST['consent_terms'] === 'true' || $_POST['consent_terms'] === true);

    // Validação estrita de consentimento jurídico
    if (!$consentRights || !$consentTerms) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'É obrigatório confirmar que possui a autorização dos direitos sobre o conteúdo e aceitar os Termos de Uso.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Validação de segurança de URL (Anti-SSRF)
    $val = validateSafeUrl($url, $db);
    if (!$val['valid']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $val['error']], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Formatos válidos
    if (!in_array($format, ['mp4', 'mp3', 'txt', 'md', 'srt'], true)) {
        $format = 'mp4';
    }
    if (!in_array($quality, ['auto', '1080p', '720p', '480p', '360p', 'pt', 'en', 'es'], true)) {
        $quality = 'auto';
    }

    $ipAddress = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255);
    $jobId = bin2hex(random_bytes(16)); // UUID seguro de 32 caracteres

    // Inserir registro do job
    $stmt = $db->prepare("INSERT INTO jobs (id, url, format, quality, status, step, progress, ip_address, user_agent, created_at) VALUES (:id, :url, :format, :quality, 'pending', 'Solicitação na fila de processamento...', 5, :ip, :ua, :created)");
    $stmt->execute([
        ':id' => $jobId,
        ':url' => $url,
        ':format' => $format,
        ':quality' => $quality,
        ':ip' => $ipAddress,
        ':ua' => $userAgent,
        ':created' => time()
    ]);

    // Registrar Aceite dos Termos (Audit Trail)
    $logStmt = $db->prepare("INSERT INTO consent_logs (job_id, url, terms_version, rights_declared, ip_address, user_agent, accepted_at) VALUES (:job_id, :url, '1.0', 1, :ip, :ua, :accepted)");
    $logStmt->execute([
        ':job_id' => $jobId,
        ':url' => $url,
        ':ip' => $ipAddress,
        ':ua' => $userAgent,
        ':accepted' => time()
    ]);

    // Disparar worker em segundo plano de forma totalmente assíncrona e desanexada
    $workerPath = __DIR__ . '/worker.php';
    function findPhpCli() {
        foreach (['/usr/bin/php', '/usr/bin/php8.4', '/usr/bin/php8.3', '/usr/local/bin/php'] as $c) {
            if (file_exists($c) && is_executable($c)) return $c;
        }
        return 'php';
    }
    $phpBin = findPhpCli();
    $cmd = sprintf("nohup %s %s %s > /dev/null 2>&1 &", escapeshellarg($phpBin), escapeshellarg($workerPath), escapeshellarg($jobId));
    exec($cmd);

    echo json_encode([
        'success' => true,
        'job_id' => $jobId,
        'message' => 'Tarefa criada com sucesso e em processamento em segundo plano.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 2. Consultar Status do Job (Polling / SSE)
if ($action === 'status') {
    $jobId = trim($_GET['job_id'] ?? $_POST['job_id'] ?? '');
    if (empty($jobId)) {
        echo json_encode(['success' => false, 'error' => 'ID de tarefa não informado.']);
        exit;
    }

    $db = getDB();
    $stmt = $db->prepare("SELECT id, url, format, quality, status, step, progress, title, thumbnail, duration, file_path, file_size, error_message, created_at, expires_at FROM jobs WHERE id = :id");
    $stmt->execute([':id' => $jobId]);
    $job = $stmt->fetch();

    if (!$job) {
        echo json_encode(['success' => false, 'error' => 'Tarefa não encontrada.']);
        exit;
    }

    $isReady = ($job['status'] === 'completed');
    $downloadUrl = $isReady ? "api.php?action=download&job_id={$job['id']}" : null;

    $transcriptContent = null;
    if ($isReady && in_array(strtolower($job['format']), ['txt', 'md', 'srt'], true) && !empty($job['file_path']) && file_exists($job['file_path'])) {
        // Retornar conteúdo da transcrição diretamente para exibição e cópia na interface
        $transcriptContent = file_get_contents($job['file_path']);
    }

    echo json_encode([
        'success' => true,
        'job' => [
            'id' => $job['id'],
            'status' => $job['status'],
            'step' => $job['step'],
            'progress' => (int)$job['progress'],
            'title' => $job['title'] ?: 'Processando...',
            'thumbnail' => $job['thumbnail'] ?: '',
            'duration' => $job['duration'] ?: '--:--',
            'format' => strtoupper($job['format']),
            'file_size' => (int)$job['file_size'],
            'file_size_formatted' => $job['file_size'] > 0 ? ($job['file_size'] < 1048576 ? round($job['file_size'] / 1024, 1) . ' KB' : round($job['file_size'] / (1024 * 1024), 2) . ' MB') : '',
            'transcript_content' => $transcriptContent,
            'error_message' => $job['error_message'],
            'download_url' => $downloadUrl,
            'expires_in' => $job['expires_at'] ? max(0, $job['expires_at'] - time()) : null
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 3. Download do Arquivo Gerado (Stream de alta velocidade, chunked, sem limite de tempo e com suporte a Range)
if ($action === 'download') {
    $jobId = trim($_GET['job_id'] ?? '');
    if (empty($jobId)) {
        header('Content-Type: text/plain; charset=utf-8');
        die('ID da tarefa inválido.');
    }

    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM jobs WHERE id = :id");
    $stmt->execute([':id' => $jobId]);
    $job = $stmt->fetch();

    if (!$job || empty($job['file_path']) || !file_exists($job['file_path'])) {
        header('Content-Type: text/plain; charset=utf-8');
        die('Arquivo não encontrado ou já expirado pelo limite de retenção de 30 minutos.');
    }

    if ($job['expires_at'] && time() > $job['expires_at']) {
        @unlink($job['file_path']);
        header('Content-Type: text/plain; charset=utf-8');
        die('Este link expirou. Por segurança e privacidade, os arquivos são excluídos após 30 minutos.');
    }

    $filePath = $job['file_path'];
    $fileSize = filesize($filePath);
    $ext = pathinfo($filePath, PATHINFO_EXTENSION);
    $safeTitle = preg_replace('/[^\p{L}\p{N}\s\-_.]/u', '', $job['title'] ?: 'midia_baixou');
    $safeTitle = trim(preg_replace('/\s+/', ' ', $safeTitle));
    if (empty($safeTitle)) {
        $safeTitle = 'midia_baixou';
    }
    $filename = $safeTitle . '.' . $ext;

    $mime = $job['mime_type'];
    if (empty($mime)) {
        $mime = match($ext) {
            'mp3' => 'audio/mpeg',
            'txt' => 'text/plain; charset=utf-8',
            'md' => 'text/markdown; charset=utf-8',
            'srt' => 'text/plain; charset=utf-8',
            default => 'video/mp4'
        };
    }

    // Desativar limites de tempo para downloads pesados (ex: podcasts de 4h / gigabytes)
    @set_time_limit(0);
    @ini_set('max_execution_time', '0');
    @ini_set('memory_limit', '512M');
    ignore_user_abort(true);

    // Suporte a HTTP Range (para downloads pausáveis / gerenciadores / reconexão)
    $start = 0;
    $end = $fileSize - 1;
    $isRange = false;

    if (isset($_SERVER['HTTP_RANGE'])) {
        if (preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $_SERVER['HTTP_RANGE'], $matches)) {
            $start = intval($matches[1]);
            if (!empty($matches[2])) {
                $end = intval($matches[2]);
            }
            if ($start > $end || $start >= $fileSize) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes */$fileSize");
                exit;
            }
            $isRange = true;
        }
    }

    $length = $end - $start + 1;

    // Limpar buffers ativos para liberar memória
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    // Headers de transferência otimizados (X-Accel-Buffering: no para streaming direto no Nginx)
    header('X-Accel-Buffering: no');
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
    header('Accept-Ranges: bytes');
    header('Expires: 0');
    header('Cache-Control: public, must-revalidate, max-age=0');
    header('Pragma: public');

    if ($isRange) {
        header('HTTP/1.1 206 Partial Content');
        header("Content-Range: bytes $start-$end/$fileSize");
    } else {
        header('HTTP/1.1 200 OK');
    }
    header('Content-Length: ' . $length);

    $fp = fopen($filePath, 'rb');
    if ($fp === false) {
        exit;
    }

    if ($start > 0) {
        fseek($fp, $start);
    }

    $chunkSize = 1024 * 1024; // 1 MB por bloco
    $bytesRemaining = $length;

    while (!feof($fp) && $bytesRemaining > 0 && connection_status() === CONNECTION_NORMAL) {
        $readAmount = ($bytesRemaining > $chunkSize) ? $chunkSize : $bytesRemaining;
        $buffer = fread($fp, $readAmount);
        echo $buffer;
        flush();
        $bytesRemaining -= strlen($buffer);
    }

    fclose($fp);
    exit;
}

// 4. Registro de Denúncia / Takedown (DMCA & Marco Civil)
if ($action === 'report') {
    $db = getDB();
    $targetUrl = trim($_POST['target_url'] ?? '');
    $reporterEmail = trim($_POST['email'] ?? '');
    $reason = trim($_POST['reason'] ?? 'Direitos autorais');
    $description = trim($_POST['description'] ?? '');

    if (empty($targetUrl) || empty($reporterEmail)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Informe a URL e seu e-mail de contato para denúncia.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO reports (target_url, reporter_email, reason, description, status, created_at) VALUES (:url, :email, :reason, :desc, 'pending', :created)");
    $stmt->execute([
        ':url' => $targetUrl,
        ':email' => $reporterEmail,
        ':reason' => $reason,
        ':desc' => $description,
        ':created' => time()
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Denúncia registrada com sucesso. O conteúdo ou fonte será analisado pela nossa equipe de moderação.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Ação não informada ou inválida.'], JSON_UNESCAPED_UNICODE);
