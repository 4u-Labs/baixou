<?php
session_start();
require_once __DIR__ . '/db.php';

// Senha padrão do administrador (pode ser alterada)
define('ADMIN_PASSWORD', '4u@baixou2026');

// Tratamento de Login/Logout
if (isset($_GET['logout'])) {
    unset($_SESSION['baixou_admin']);
    header('Location: admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_pass'])) {
    if ($_POST['login_pass'] === ADMIN_PASSWORD) {
        $_SESSION['baixou_admin'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $loginError = "Senha incorreta.";
    }
}

$isLoggedIn = !empty($_SESSION['baixou_admin']);

if (!$isLoggedIn) {
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo — BAIXOU!!</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background: #070d1e; color: #fff; font-family: 'Plus Jakarta Sans', sans-serif; display: grid; place-content: center; height: 100vh; margin: 0; }
        .card { background: rgba(14, 25, 54, 0.95); border: 1px solid rgba(255,255,255,0.12); padding: 2.5rem; border-radius: 20px; width: 340px; text-align: center; box-shadow: 0 10px 40px rgba(0,0,0,0.5); }
        input { width: 100%; padding: 0.9rem; margin: 1.2rem 0; border-radius: 12px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.4); color: #fff; box-sizing: border-box; }
        button { width: 100%; padding: 1rem; border-radius: 12px; background: #0066ff; color: #fff; border: none; font-weight: 800; cursor: pointer; }
        .error { color: #ff334b; font-size: 0.85rem; margin-top: 0.5rem; }
    </style>
</head>
<body>
    <div class="card">
        <i class="fa-solid fa-shield-halved" style="font-size: 2.5rem; color: #0066ff; margin-bottom: 1rem;"></i>
        <h2>Acesso Admin</h2>
        <?php if (!empty($loginError)): ?><div class="error"><?= $loginError ?></div><?php endif; ?>
        <form method="POST">
            <input type="password" name="login_pass" placeholder="Digite a senha admin" required autofocus>
            <button type="submit">Entrar no Painel</button>
        </form>
    </div>
</body>
</html>
<?php
    exit;
}

$db = getDB();

// Ações administrativas: Adicionar Blacklist, Remover Blacklist, Resolver Report
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_blacklist') {
        $domain = strtolower(trim($_POST['domain']));
        $reason = trim($_POST['reason']);
        if ($domain) {
            $stmt = $db->prepare("INSERT OR IGNORE INTO blocked_sources (domain, reason, created_at) VALUES (:d, :r, :c)");
            $stmt->execute([':d' => $domain, ':r' => $reason, ':c' => time()]);
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'del_blacklist') {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("DELETE FROM blocked_sources WHERE id = :id");
        $stmt->execute([':id' => $id]);
    } elseif (isset($_POST['action']) && $_POST['action'] === 'resolve_report') {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("UPDATE reports SET status = 'resolved', resolved_at = :now WHERE id = :id");
        $stmt->execute([':now' => time(), ':id' => $id]);
    } elseif (isset($_POST['action']) && $_POST['action'] === 'save_cookies') {
        $cookieContent = trim($_POST['cookies_text'] ?? '');
        $cookiePath = __DIR__ . '/storage/data/cookies.txt';
        if (!empty($cookieContent)) {
            file_put_contents($cookiePath, $cookieContent);
        } else {
            @unlink($cookiePath);
        }
    }
    header('Location: admin.php');
    exit;
}

// Estatísticas
$startOfDay = strtotime('today midnight');
$totalToday = $db->query("SELECT count(*) FROM jobs WHERE created_at >= $startOfDay")->fetchColumn();
$successToday = $db->query("SELECT count(*) FROM jobs WHERE created_at >= $startOfDay AND status = 'completed'")->fetchColumn();
$errorsToday = $db->query("SELECT count(*) FROM jobs WHERE created_at >= $startOfDay AND status = 'error'")->fetchColumn();
$pendingReports = $db->query("SELECT count(*) FROM reports WHERE status = 'pending'")->fetchColumn();

// Tamanho de disco usado
$downloadsDir = __DIR__ . '/storage/downloads';
$diskUsageBytes = 0;
if (is_dir($downloadsDir)) {
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($downloadsDir, FilesystemIterator::SKIP_DOTS)) as $file) {
        $diskUsageBytes += $file->getSize();
    }
}
$diskUsageMB = round($diskUsageBytes / (1024 * 1024), 2);

// Últimos jobs
$latestJobs = $db->query("SELECT * FROM jobs ORDER BY created_at DESC LIMIT 15")->fetchAll();
$blacklist = $db->query("SELECT * FROM blocked_sources ORDER BY created_at DESC")->fetchAll();
$reports = $db->query("SELECT * FROM reports ORDER BY created_at DESC LIMIT 10")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel de Controle — BAIXOU!!</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background: #070d1e; color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; padding: 2rem; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 1rem; }
        .grid-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem; }
        .stat-card { background: rgba(14, 25, 54, 0.8); border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; padding: 1.5rem; }
        .stat-val { font-size: 2rem; font-weight: 900; margin-top: 0.5rem; }
        .stat-card.blue .stat-val { color: #60a5fa; }
        .stat-card.green .stat-val { color: #34d399; }
        .stat-card.red .stat-val { color: #ff6b7d; }
        .stat-card.yellow .stat-val { color: #facc15; }
        .section { background: rgba(14, 25, 54, 0.7); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 1.75rem; margin-bottom: 2rem; }
        h2 { margin-top: 0; font-size: 1.25rem; }
        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; margin-top: 1rem; }
        th, td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.08); }
        th { color: #94a3b8; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; }
        .badge { padding: 0.25rem 0.6rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 800; }
        .badge-completed { background: rgba(16, 185, 129, 0.2); color: #34d399; }
        .badge-error { background: rgba(255, 51, 75, 0.2); color: #ff8595; }
        .badge-pending { background: rgba(250, 204, 21, 0.2); color: #facc15; }
        .badge-analyzing, .badge-downloading, .badge-processing { background: rgba(0, 102, 255, 0.2); color: #60a5fa; }
        .btn-sm { padding: 0.4rem 0.8rem; border-radius: 8px; border: none; font-size: 0.8rem; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 0.3rem; }
        .btn-red { background: #ff334b; color: #fff; }
        .btn-green { background: #10b981; color: #fff; }
        .input-text { background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.2); color: #fff; padding: 0.6rem 0.8rem; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>⚙️ Painel de Governança — BAIXOU!!</h1>
        <div>
            <a href="index.php" style="color: #60a5fa; margin-right: 1.5rem; text-decoration: none; font-weight: 700;"><i class="fa-solid fa-arrow-left"></i> Ir para o App</a>
            <a href="admin.php?logout=1" class="btn-sm btn-red"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid-stats">
        <div class="stat-card blue">
            <div>Solicitações Hoje</div>
            <div class="stat-val"><?= $totalToday ?></div>
        </div>
        <div class="stat-card green">
            <div>Conversões Concluídas</div>
            <div class="stat-val"><?= $successToday ?></div>
        </div>
        <div class="stat-card red">
            <div>Erros / Falhas</div>
            <div class="stat-val"><?= $errorsToday ?></div>
        </div>
        <div class="stat-card yellow">
            <div>Denúncias Pendentes</div>
            <div class="stat-val"><?= $pendingReports ?></div>
        </div>
        <div class="stat-card">
            <div>Disco Usado (Temp)</div>
            <div class="stat-val"><?= $diskUsageMB ?> MB</div>
        </div>
    </div>

    <!-- Reports (Denúncias DMCA) -->
    <div class="section">
        <h2>🚨 Notificações de Takedown / Denúncias Recentes</h2>
        <?php if (empty($reports)): ?>
            <p style="color: #94a3b8;">Nenhuma notificação registrada no momento.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>URL</th>
                        <th>Notificante</th>
                        <th>Motivo</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $r): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', $r['created_at']) ?></td>
                            <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= htmlspecialchars($r['target_url']) ?></td>
                            <td><?= htmlspecialchars($r['reporter_email']) ?></td>
                            <td><?= htmlspecialchars($r['reason']) ?></td>
                            <td><span class="badge badge-<?= $r['status'] === 'pending' ? 'pending' : 'completed' ?>"><?= strtoupper($r['status']) ?></span></td>
                            <td>
                                <?php if ($r['status'] === 'pending'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="resolve_report">
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                        <button type="submit" class="btn-sm btn-green"><i class="fa-solid fa-check"></i> Resolver</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: #34d399;"><i class="fa-solid fa-check-double"></i> Resolvido</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Blacklist de Domínios -->
    <div class="section">
        <h2>🛡️ Fontes Bloqueadas (Blacklist Preventiva)</h2>
        <form method="POST" style="display: flex; gap: 0.75rem; margin-bottom: 1.5rem;">
            <input type="hidden" name="action" value="add_blacklist">
            <input type="text" name="domain" class="input-text" placeholder="ex: dominio-proibido.com" required style="width: 240px;">
            <input type="text" name="reason" class="input-text" placeholder="Motivo do bloqueio (ex: Notificação de Takedown)" style="flex: 1;">
            <button type="submit" class="btn-sm btn-red"><i class="fa-solid fa-ban"></i> Bloquear Domínio</button>
        </form>

        <?php if (empty($blacklist)): ?>
            <p style="color: #94a3b8;">Nenhum domínio bloqueado manualmente na lista negra.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Domínio</th>
                        <th>Motivo</th>
                        <th>Data de Bloqueio</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($blacklist as $b): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($b['domain']) ?></strong></td>
                            <td><?= htmlspecialchars($b['reason']) ?></td>
                            <td><?= date('d/m/Y H:i', $b['created_at']) ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="del_blacklist">
                                    <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                    <button type="submit" class="btn-sm" style="background: #475569; color: #fff;"><i class="fa-solid fa-trash"></i> Desbloquear</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Gerenciador de Cookies para Bypass de Bloqueios -->
    <?php
    $cookiePath = __DIR__ . '/storage/data/cookies.txt';
    $currentCookies = file_exists($cookiePath) ? file_get_contents($cookiePath) : '';
    ?>
    <div class="section">
        <h2>🍪 Bypass de Proteções (Cookies do YouTube & Plataformas)</h2>
        <p style="color: #94a3b8; font-size: 0.88rem; margin-bottom: 1rem;">
            Se o YouTube ou outra plataforma exigir login ("Sign in to confirm you're not a bot"), cole aqui os cookies exportados do seu navegador (formato padrão Netscape/cookies.txt). O worker utilizará automaticamente para autenticar as requisições no servidor.
        </p>
        <form method="POST">
            <input type="hidden" name="action" value="save_cookies">
            <textarea name="cookies_text" rows="5" class="input-text" style="width: 100%; box-sizing: border-box; font-family: monospace; font-size: 0.8rem;" placeholder="# Netscape HTTP Cookie File..."><?= htmlspecialchars($currentCookies) ?></textarea>
            <div style="margin-top: 0.75rem; display: flex; gap: 0.75rem;">
                <button type="submit" class="btn-sm btn-green"><i class="fa-solid fa-floppy-disk"></i> Salvar Cookies</button>
                <?php if (!empty($currentCookies)): ?>
                    <span style="color: #34d399; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                        <i class="fa-solid fa-circle-check"></i> Cookies ativos configurados (<?= strlen($currentCookies) ?> bytes)
                    </span>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Últimas Tarefas -->
    <div class="section">
        <h2>📋 Últimas Solicitações de Conversão</h2>
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Título / URL</th>
                    <th>Formato</th>
                    <th>Status</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($latestJobs as $j): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', $j['created_at']) ?></td>
                        <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            <?= htmlspecialchars($j['title'] ?: $j['url']) ?>
                        </td>
                        <td><?= strtoupper($j['format']) ?></td>
                        <td><span class="badge badge-<?= $j['status'] ?>"><?= strtoupper($j['status']) ?></span></td>
                        <td><?= preg_replace('/\.\d+$/', '.***', $j['ip_address'] ?? '127.0.0.1') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
