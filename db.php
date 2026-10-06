<?php
// Banco de dados SQLite centralizado para o BAIXOU!!
// Armazena Jobs, Logs de Consentimento (Compliance Jurídico), Blacklist e Denúncias

define('DB_PATH', __DIR__ . '/storage/data/baixou.sqlite');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dir = dirname(DB_PATH);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA journal_mode = WAL;');
        initDBTables($pdo);
    }
    return $pdo;
}

function initDBTables(PDO $pdo): void {
    // Tabela de tarefas de conversão
    $pdo->exec("CREATE TABLE IF NOT EXISTS jobs (
        id TEXT PRIMARY KEY,
        url TEXT NOT NULL,
        format TEXT NOT NULL,
        quality TEXT DEFAULT 'auto',
        status TEXT NOT NULL, -- pending, analyzing, downloading, processing, completed, error, expired
        step TEXT DEFAULT 'Aguardando na fila...',
        progress INTEGER DEFAULT 0,
        title TEXT,
        thumbnail TEXT,
        duration TEXT,
        file_path TEXT,
        file_size INTEGER DEFAULT 0,
        mime_type TEXT,
        error_message TEXT,
        ip_address TEXT,
        user_agent TEXT,
        created_at INTEGER NOT NULL,
        started_at INTEGER,
        completed_at INTEGER,
        expires_at INTEGER
    )");

    // Tabela de consentimento e aceite dos termos (Audit Trail jurídico)
    $pdo->exec("CREATE TABLE IF NOT EXISTS consent_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        job_id TEXT NOT NULL,
        url TEXT NOT NULL,
        terms_version TEXT NOT NULL,
        rights_declared INTEGER NOT NULL,
        ip_address TEXT NOT NULL,
        user_agent TEXT,
        accepted_at INTEGER NOT NULL
    )");

    // Tabela de fontes e domínios bloqueados (Blacklist)
    $pdo->exec("CREATE TABLE IF NOT EXISTS blocked_sources (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        domain TEXT UNIQUE NOT NULL,
        reason TEXT,
        created_at INTEGER NOT NULL
    )");

    // Tabela de denúncias de conteúdo (Takedowns / DMCA)
    $pdo->exec("CREATE TABLE IF NOT EXISTS reports (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        job_id TEXT,
        target_url TEXT NOT NULL,
        reporter_email TEXT NOT NULL,
        reason TEXT NOT NULL,
        description TEXT,
        status TEXT DEFAULT 'pending', -- pending, reviewed, blocked, resolved
        created_at INTEGER NOT NULL,
        resolved_at INTEGER
    )");

    // Logs gerais do sistema
    $pdo->exec("CREATE TABLE IF NOT EXISTS system_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        level TEXT DEFAULT 'INFO',
        message TEXT NOT NULL,
        context TEXT,
        created_at INTEGER NOT NULL
    )");
}
