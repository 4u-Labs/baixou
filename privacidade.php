<?php
$pageTitle = "Política de Privacidade — BAIXOU!!";
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
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Voltar ao BAIXOU!!</a>
        <h1>Política de Privacidade</h1>
        <p>A sua privacidade e a segurança dos seus dados são prioridades absolutas no <strong>BAIXOU!!</strong>. Operamos sob uma estrita política de <strong>Retenção Zero de Arquivos</strong> e minimização de coleta de dados pessoais.</p>

        <h2>1. Política de Retenção Zero</h2>
        <p>Nós não armazenamos os vídeos processados permanentemente. Todos os arquivos convertidos são mantidos exclusivamente em diretórios temporários e <strong>são irrevogavelmente destruídos após 30 minutos</strong> do término da conversão.</p>

        <h2>2. Dados Coletados</h2>
        <p>Para prevenção de abusos técnicos (ataques DDoS, scraping agressivo, SSRF) e conformidade jurídica com o Marco Civil da Internet (Lei nº 12.965/2014), coletamos temporariamente:</p>
        <ul>
            <li>Endereço IP e User-Agent da requisição;</li>
            <li>Data, hora e URL do conteúdo solicitado;</li>
            <li>Registro do aceite dos termos e da declaração de titularidade/autorização.</li>
        </ul>

        <h2>3. Compartilhamento de Informações</h2>
        <p>O BAIXOU!! não vende, não aluga e não comercializa informações de navegação com terceiros ou redes de publicidade. Informações de log podem ser compartilhadas exclusivamente mediante ordem judicial formal emitida por autoridade competente.</p>

        <h2>4. Cookies</h2>
        <p>Utilizamos apenas cookies estritamente necessários para a sessão técnica e funcionamento da interface do aplicativo. Não rastreamos sua navegação em sites externos.</p>
    </div>
</body>
</html>
