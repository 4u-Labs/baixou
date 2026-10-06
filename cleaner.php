<?php
// Script de limpeza e expiração automática de arquivos (Retenção Zero / Máx 30 minutos)
require_once __DIR__ . '/db.php';

function purgeExpiredFiles(): int {
    $db = getDB();
    $downloadsDir = __DIR__ . '/storage/downloads';
    $now = time();
    $deletedCount = 0;

    // 1. Marcar jobs expirados no banco
    $stmt = $db->prepare("SELECT id, file_path FROM jobs WHERE expires_at IS NOT NULL AND expires_at < :now AND status = 'completed'");
    $stmt->execute([':now' => $now]);
    $expiredJobs = $stmt->fetchAll();

    foreach ($expiredJobs as $job) {
        if (!empty($job['file_path']) && file_exists($job['file_path'])) {
            @unlink($job['file_path']);
            $dir = dirname($job['file_path']);
            // Se o diretório do job estiver vazio, exclui também
            if ($dir !== $downloadsDir && is_dir($dir) && count(scandir($dir)) <= 2) {
                @rmdir($dir);
            }
            $deletedCount++;
        }
        $update = $db->prepare("UPDATE jobs SET status = 'expired', step = 'Arquivo expirado e excluído automaticamente', file_path = NULL WHERE id = :id");
        $update->execute([':id' => $job['id']]);
    }

    // 2. Limpeza física no diretório caso haja arquivos órfãos com mais de 30 minutos (1800s)
    if (is_dir($downloadsDir)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($downloadsDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            $path = $item->getRealPath();
            if ($item->isFile()) {
                if ($now - $item->getMTime() > 1800) {
                    @unlink($path);
                    $deletedCount++;
                }
            } elseif ($item->isDir()) {
                if (count(scandir($path)) <= 2) {
                    @rmdir($path);
                }
            }
        }
    }

    return $deletedCount;
}

if (php_sapi_name() === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $count = purgeExpiredFiles();
    echo "[" . date('Y-m-d H:i:s') . "] Limpeza executada. Arquivos expirados removidos: $count\n";
}
