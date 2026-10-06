<?php
$pageTitle = "Termos de Uso — BAIXOU!!";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="apple-touch-icon" href="apple-touch-icon.png">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background: #070d1e;
            color: #e2e8f0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            line-height: 1.7;
            padding: 2rem 1rem;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(14, 25, 54, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        }
        h1 { font-size: 2.2rem; font-weight: 900; margin-bottom: 0.5rem; color: #fff; }
        .version-badge {
            display: inline-block;
            background: rgba(0, 102, 255, 0.2);
            color: #60a5fa;
            border: 1px solid rgba(0, 102, 255, 0.3);
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 700;
            margin-bottom: 2rem;
        }
        h2 { font-size: 1.3rem; font-weight: 700; margin-top: 1.8rem; margin-bottom: 0.75rem; color: #fff; border-left: 4px solid #0066ff; padding-left: 0.75rem; }
        p { margin-bottom: 1rem; font-size: 0.95rem; color: #cbd5e1; }
        ul { margin-left: 1.5rem; margin-bottom: 1.25rem; font-size: 0.95rem; color: #cbd5e1; }
        li { margin-bottom: 0.5rem; }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #60a5fa;
            text-decoration: none;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        .back-link:hover { color: #93c5fd; }
        .highlight-box {
            background: rgba(255, 193, 7, 0.08);
            border: 1px solid rgba(255, 193, 7, 0.25);
            border-radius: 14px;
            padding: 1.25rem;
            margin: 1.5rem 0;
            font-size: 0.9rem;
            color: #fde047;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Voltar ao BAIXOU!!</a>
        <h1>Termos de Uso</h1>
        <div class="version-badge">Versão 1.0 — Atualizado em Outubro de 2026</div>

        <div class="highlight-box">
            <i class="fa-solid fa-triangle-exclamation"></i> <strong>Aviso Fundamental:</strong> O BAIXOU!! fornece exclusivamente tecnologia de processamento e conversão técnica. Nós NÃO concedemos direitos, licenças ou autorizações de uso sobre conteúdos de terceiros.
        </div>

        <h2>1. Finalidade do Serviço</h2>
        <p>O <strong>BAIXOU!!</strong> é uma ferramenta web de utilidade técnica destinada à conversão e ao download de mídias para uso pessoal, educativo ou de cópia de segurança de conteúdos que o próprio usuário produziu, possui os direitos ou obteve autorização legal expressa para baixar.</p>

        <h2>2. Responsabilidade Exclusiva do Usuário</h2>
        <p>Ao utilizar o serviço e marcar a declaração obrigatória antes da conversão, você declara e garante que:</p>
        <ul>
            <li>É o titular dos direitos autorais sobre a mídia informada ou possui autorização do respectivo titular para realizar o download e conversão;</li>
            <li>Não utilizará a ferramenta para violar direitos de propriedade intelectual, marcas registradas ou contratos de terceiros;</li>
            <li>Assume integral responsabilidade civil e criminal pelo uso do conteúdo obtido através desta plataforma.</li>
        </ul>

        <h2>3. Conteúdos Proibidos</h2>
        <p>É estritamente proibido submeter URLs que contenham:</p>
        <ul>
            <li>Materiais protegidos por direitos autorais sem a devida autorização do titular;</li>
            <li>Conteúdos que promovam violência, ódio, discriminação, pornografia infantil ou qualquer ato ilegal;</li>
            <li>Links com códigos maliciosos, exploits ou tentativas de ataque de negação de serviço.</li>
        </ul>

        <h2>4. Processamento Técnico e Retenção Zero</h2>
        <p>Para viabilizar a conversão solicitada, o servidor realiza um processamento temporário em segundo plano. Os arquivos gerados são mantidos em área restrita e temporária e <strong>são automaticamente e definitivamente excluídos em no máximo 30 minutos</strong>. O BAIXOU!! não mantém acervo, catálogo ou hospedagem permanente de arquivos.</p>

        <h2>5. Registro de Consentimento e Logs Técnicos</h2>
        <p>Para resguardo jurídico e segurança do sistema, cada solicitação registra no banco de dados a data/hora, endereço IP do solicitante, User-Agent, a URL informada e a confirmação positiva dos termos e declaração de direitos.</p>

        <h2>6. Bloqueio Preventivo de Fontes e Denúncias</h2>
        <p>A administração do serviço reserva-se o direito de bloquear domínios (blacklist), padrões de URL ou endereços IP a qualquer momento, sem aviso prévio, caso detecte abusos ou receba notificações de titulares de direitos através do nosso canal de <a href="denunciar.php" style="color: #60a5fa;">Denúncias / Takedown</a>.</p>

        <h2>7. Isenção de Garantias</h2>
        <p>O serviço é fornecido "como está" (as is), sem garantias de disponibilidade ininterrupta ou de compatibilidade com todas as fontes da web. Plataformas de terceiros podem alterar seus protocolos técnicos a qualquer momento.</p>
    </div>
</body>
</html>
