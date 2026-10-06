<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Denúncia de Conteúdo e Takedown — BAIXOU!!</title>
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="apple-touch-icon" href="apple-touch-icon.png">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background: #070d1e;
            color: #e2e8f0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            line-height: 1.6;
            padding: 2.5rem 1rem;
        }
        .container {
            max-width: 680px;
            margin: 0 auto;
            background: rgba(14, 25, 54, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        }
        h1 { font-size: 2rem; font-weight: 900; margin-bottom: 0.5rem; color: #fff; }
        p { margin-bottom: 1.5rem; color: #94a3b8; font-size: 0.95rem; }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #60a5fa;
            text-decoration: none;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        label {
            display: block;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 0.4rem;
            color: #cbd5e1;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        input, select, textarea {
            width: 100%;
            background: rgba(6, 12, 28, 0.85);
            border: 1.5px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 0.9rem 1.1rem;
            color: #fff;
            font-size: 0.95rem;
            outline: none;
            box-sizing: border-box;
            font-family: inherit;
        }
        input:focus, select:focus, textarea:focus {
            border-color: #ff334b;
        }
        textarea { resize: vertical; min-height: 110px; }
        .btn-submit {
            width: 100%;
            background: #ff334b;
            color: #fff;
            border: none;
            border-radius: 14px;
            padding: 1.1rem;
            font-size: 1rem;
            font-weight: 800;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 1rem;
        }
        .btn-submit:hover { background: #e60023; }
        .alert-box {
            display: none;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }
        .alert-success { background: rgba(16, 185, 129, 0.2); border: 1px solid #10b981; color: #34d399; }
        .alert-error { background: rgba(255, 51, 75, 0.2); border: 1px solid #ff334b; color: #ff8595; }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Voltar ao Início</a>
        <h1>🚨 Denúncia / Notificação de Takedown</h1>
        <p>Utilize este formulário para solicitar a remoção imediata ou o bloqueio preventivo de conteúdos ou domínios protegidos por direitos autorais.</p>

        <div id="alertBox" class="alert-box"></div>

        <form id="reportForm">
            <div class="form-group">
                <label for="targetUrl">URL Objeto da Denúncia *</label>
                <input type="url" id="targetUrl" name="target_url" placeholder="https://exemplo.com/video/..." required>
            </div>

            <div class="form-group">
                <label for="reporterEmail">Seu E-mail de Contato / Titular *</label>
                <input type="email" id="reporterEmail" name="email" placeholder="seu-email@dominio.com" required>
            </div>

            <div class="form-group">
                <label for="reason">Motivo da Notificação *</label>
                <select id="reason" name="reason">
                    <option value="Direitos Autorais (Titular)">Violação de Direitos Autorais (Sou o Titular/Representante)</option>
                    <option value="Uso não autorizado de marca">Uso não autorizado de Marca Registrada</option>
                    <option value="Conteúdo Impróprio ou Ilegal">Conteúdo Impróprio, Abusivo ou Ilegal</option>
                    <option value="Outro">Outro Motivo</option>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Descrição Detalhada / Comprovação</label>
                <textarea id="description" name="description" placeholder="Descreva brevemente a obra original, vínculo de titularidade ou link da obra protegida para verificação rápida..."></textarea>
            </div>

            <button type="submit" class="btn-submit" id="btnReport">
                <i class="fa-solid fa-paper-plane"></i> Enviar Notificação Formal
            </button>
        </form>
    </div>

    <script>
        const reportForm = document.getElementById('reportForm');
        const alertBox = document.getElementById('alertBox');
        const btnReport = document.getElementById('btnReport');

        reportForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            btnReport.disabled = true;
            btnReport.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Registrando notificação...';

            try {
                const formData = new FormData(reportForm);
                const res = await fetch('api.php?action=report', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    alertBox.className = 'alert-box alert-success';
                    alertBox.textContent = data.message;
                    alertBox.style.display = 'block';
                    reportForm.reset();
                } else {
                    alertBox.className = 'alert-box alert-error';
                    alertBox.textContent = data.error || 'Erro ao processar notificação.';
                    alertBox.style.display = 'block';
                }
            } catch (err) {
                alertBox.className = 'alert-box alert-error';
                alertBox.textContent = 'Erro de comunicação com o servidor.';
                alertBox.style.display = 'block';
            } finally {
                btnReport.disabled = false;
                btnReport.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Enviar Notificação Formal';
            }
        });
    </script>
</body>
</html>
