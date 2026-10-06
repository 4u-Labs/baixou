<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BAIXOU!! — Baixe e converta vídeos autorizados em MP4 e MP3</title>
    <meta name="description" content="Baixe e converta vídeos e áudios que você possui ou tem autorização para utilizar. Processamento em segundo plano de alta velocidade, seguro e com exclusão automática.">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="apple-touch-icon" href="apple-touch-icon.png">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#0d47a1">

    <!-- Google Fonts & FontAwesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --primary: #0066ff;
            --primary-glow: rgba(0, 102, 255, 0.4);
            --accent-red: #ff334b;
            --accent-green: #10b981;
            --accent-yellow: #ffc107;
            --bg-dark: #070d1e;
            --card-bg: rgba(14, 25, 54, 0.75);
            --card-border: rgba(255, 255, 255, 0.12);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background: radial-gradient(circle at 50% 0%, #152960 0%, #070d1e 70%, #03060f 100%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Navbar */
        .navbar {
            padding: 1.25rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(12px);
            background: rgba(7, 13, 30, 0.6);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            text-decoration: none;
            color: #fff;
        }

        .nav-logo {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 102, 255, 0.35);
        }

        .nav-title {
            font-size: 1.45rem;
            font-weight: 900;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #ffffff 40%, #ffd000 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .nav-link {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .nav-link:hover {
            color: #fff;
        }

        .nav-btn-report {
            background: rgba(255, 51, 75, 0.15);
            color: #ff6b7d;
            border: 1px solid rgba(255, 51, 75, 0.3);
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .nav-btn-report:hover {
            background: rgba(255, 51, 75, 0.25);
            color: #fff;
            border-color: #ff334b;
        }

        /* Container & Main Card */
        .main-container {
            flex: 1;
            max-width: 820px;
            width: 100%;
            margin: 2.5rem auto;
            padding: 0 1.25rem;
        }

        .app-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .hero-logo {
            width: 110px;
            height: 110px;
            border-radius: 28px;
            margin-bottom: 1.25rem;
            box-shadow: 0 12px 35px rgba(0, 102, 255, 0.45), 0 0 50px rgba(255, 51, 75, 0.25);
            animation: float 4s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        .hero-title {
            font-size: 2.8rem;
            font-weight: 900;
            letter-spacing: -1px;
            margin-bottom: 0.6rem;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        }

        .hero-subtitle {
            font-size: 1.15rem;
            color: var(--text-muted);
            max-width: 580px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Glass Card */
        .glass-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 28px;
            padding: 2.5rem;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            margin-bottom: 2.5rem;
        }

        /* Input Form */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .url-input {
            width: 100%;
            background: rgba(6, 12, 28, 0.85);
            border: 2px solid rgba(255, 255, 255, 0.12);
            border-radius: 18px;
            padding: 1.25rem 7.5rem 1.25rem 3.25rem;
            color: #fff;
            font-size: 1.05rem;
            font-weight: 500;
            transition: all 0.25s ease;
            outline: none;
        }

        .url-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 5px var(--primary-glow);
            background: rgba(6, 12, 28, 0.98);
        }

        .input-icon {
            position: absolute;
            left: 1.25rem;
            color: var(--text-muted);
            font-size: 1.2rem;
        }

        .paste-btn {
            position: absolute;
            right: 0.85rem;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #cbd5e1;
            padding: 0.6rem 1rem;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s ease;
        }

        .paste-btn:hover {
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
        }

        /* Options Row */
        .options-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
            margin-bottom: 1.75rem;
        }

        .option-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .select-custom {
            width: 100%;
            background: rgba(6, 12, 28, 0.85);
            border: 1.5px solid rgba(255, 255, 255, 0.12);
            border-radius: 14px;
            padding: 0.95rem 1rem;
            color: #fff;
            font-size: 0.95rem;
            font-weight: 600;
            outline: none;
            cursor: pointer;
            transition: border-color 0.2s ease;
        }

        .select-custom:focus {
            border-color: var(--primary);
        }

        /* Checkboxes Container */
        .legal-check-box {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 1.25rem;
            margin-bottom: 2rem;
            display: flex;
            flex-direction: column;
            gap: 0.9rem;
        }

        .custom-check-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            cursor: pointer;
            user-select: none;
        }

        .custom-check-item input[type="checkbox"] {
            appearance: none;
            -webkit-appearance: none;
            width: 22px;
            height: 22px;
            background: rgba(0, 0, 0, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.25);
            border-radius: 6px;
            cursor: pointer;
            margin-top: 2px;
            flex-shrink: 0;
            display: grid;
            place-content: center;
            transition: all 0.2s ease;
        }

        .custom-check-item input[type="checkbox"]:checked {
            background: var(--accent-green);
            border-color: var(--accent-green);
        }

        .custom-check-item input[type="checkbox"]:checked::before {
            content: "\f00c";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            color: #fff;
            font-size: 13px;
        }

        .check-text {
            font-size: 0.88rem;
            line-height: 1.5;
            color: #cbd5e1;
        }

        .check-text a {
            color: #60a5fa;
            text-decoration: underline;
            font-weight: 600;
        }

        /* Convert Button */
        .btn-action {
            width: 100%;
            background: linear-gradient(135deg, #0066ff 0%, #0052cc 50%, #ff334b 100%);
            background-size: 200% 200%;
            border: none;
            border-radius: 18px;
            padding: 1.35rem 2rem;
            color: #fff;
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            cursor: pointer;
            box-shadow: 0 10px 30px rgba(0, 102, 255, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
        }

        .btn-action:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(0, 102, 255, 0.6);
            background-position: right center;
        }

        .btn-action:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        /* Progress Card */
        #progressCard {
            display: none;
            background: rgba(10, 19, 44, 0.95);
            border: 1px solid rgba(0, 102, 255, 0.3);
            border-radius: 24px;
            padding: 2rem;
            margin-top: 2rem;
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .progress-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
        }

        .progress-status-badge {
            background: rgba(0, 102, 255, 0.2);
            color: #60a5fa;
            border: 1px solid rgba(0, 102, 255, 0.4);
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .progress-bar-container {
            width: 100%;
            height: 12px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 9999px;
            overflow: hidden;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .progress-bar-fill {
            height: 100%;
            width: 5%;
            background: linear-gradient(90deg, #0066ff, #10b981);
            border-radius: 9999px;
            transition: width 0.4s ease;
        }

        .progress-steps-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.92rem;
            color: var(--text-muted);
            transition: color 0.3s ease;
        }

        .step-item.active {
            color: #fff;
            font-weight: 700;
        }

        .step-item.completed {
            color: var(--accent-green);
        }

        .step-icon {
            width: 20px;
            text-align: center;
        }

        /* Result Preview Box */
        .result-preview {
            display: none;
            background: rgba(0, 0, 0, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 18px;
            padding: 1.25rem;
            margin-top: 1.5rem;
            align-items: center;
            gap: 1.25rem;
        }

        .preview-thumb {
            width: 120px;
            height: 75px;
            border-radius: 10px;
            object-fit: cover;
            background: #1e293b;
            flex-shrink: 0;
        }

        .preview-info {
            flex: 1;
            min-width: 0;
        }

        .preview-title {
            font-size: 1rem;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 0.35rem;
        }

        .preview-meta {
            font-size: 0.82rem;
            color: var(--text-muted);
            display: flex;
            gap: 1rem;
        }

        .btn-download-ready {
            display: none;
            width: 100%;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            border-radius: 18px;
            padding: 1.35rem;
            color: #fff;
            font-size: 1.2rem;
            font-weight: 900;
            text-align: center;
            text-decoration: none;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
            margin-top: 1.5rem;
            transition: all 0.25s ease;
        }

        .btn-download-ready:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(16, 185, 129, 0.6);
        }

        .retention-notice {
            display: none;
            text-align: center;
            margin-top: 1rem;
            font-size: 0.8rem;
            color: #facc15;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
        }

        /* Error Box */
        .error-box {
            display: none;
            background: rgba(255, 51, 75, 0.15);
            border: 1px solid rgba(255, 51, 75, 0.3);
            border-radius: 16px;
            padding: 1rem 1.25rem;
            color: #ff8595;
            font-size: 0.9rem;
            margin-top: 1.5rem;
            align-items: center;
            gap: 0.75rem;
        }

        /* Badges list */
        .platforms-badge-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
        }

        .platform-badge {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 9999px;
            padding: 0.4rem 0.9rem;
            font-size: 0.82rem;
            font-weight: 600;
            color: #cbd5e1;
            display: flex;
            align-items: center;
            gap: 0.45rem;
        }

        /* Footer */
        .footer {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding: 2.5rem 1.5rem;
            background: rgba(3, 6, 15, 0.8);
            text-align: center;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 1.75rem;
            margin-bottom: 1.5rem;
        }

        .footer-link {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .footer-link:hover {
            color: #fff;
        }

        .footer-legal-notice {
            max-width: 700px;
            margin: 0 auto;
            font-size: 0.78rem;
            color: #64748b;
            line-height: 1.6;
        }

        @media (max-width: 640px) {
            .options-grid {
                grid-template-columns: 1fr;
            }
            .hero-title {
                font-size: 2.2rem;
            }
            .glass-card {
                padding: 1.5rem;
            }
            .url-input {
                padding-right: 5.5rem;
            }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <a href="index.php" class="nav-brand">
            <img src="logo.png" alt="Logo BAIXOU!!" class="nav-logo">
            <span class="nav-title">BAIXOU!!</span>
        </a>
        <div class="nav-links">
            <a href="termos.php" class="nav-link">Termos</a>
            <a href="direitos-autorais.php" class="nav-link">Direitos Autorais</a>
            <a href="denunciar.php" class="nav-btn-report"><i class="fa-solid fa-flag"></i> Denunciar</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-container">

        <!-- Hero Header -->
        <header class="app-header">
            <img src="logo.png" alt="BAIXOU!!" class="hero-logo">
            <h1 class="hero-title">BAIXOU!!</h1>
            <p class="hero-subtitle">Baixe e converta vídeos e áudios autorizados com processamento em alta velocidade e segurança máxima.</p>
        </header>

        <!-- Supported Platforms -->
        <div class="platforms-badge-list">
            <span class="platform-badge"><i class="fa-brands fa-youtube" style="color: #ff0000;"></i> YouTube</span>
            <span class="platform-badge"><i class="fa-brands fa-x-twitter"></i> X (Twitter)</span>
            <span class="platform-badge"><i class="fa-brands fa-instagram" style="color: #e1306c;"></i> Instagram</span>
            <span class="platform-badge"><i class="fa-brands fa-tiktok"></i> TikTok</span>
            <span class="platform-badge"><i class="fa-brands fa-facebook" style="color: #1877f2;"></i> Facebook</span>
            <span class="platform-badge"><i class="fa-brands fa-vimeo-v" style="color: #1ab7ea;"></i> Vimeo</span>
            <span class="platform-badge"><i class="fa-brands fa-twitch" style="color: #9146ff;"></i> Twitch</span>
        </div>

        <!-- Conversion Card -->
        <div class="glass-card">
            <form id="convertForm">
                <!-- URL Input -->
                <div class="form-group">
                    <label class="option-label" for="urlInput">Cole a URL do Vídeo Autorizado</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-link input-icon"></i>
                        <input type="url" id="urlInput" class="url-input" placeholder="https://www.youtube.com/watch?v=... ou link do X" required autocomplete="off">
                        <button type="button" class="paste-btn" id="btnPaste">
                            <i class="fa-regular fa-clipboard"></i> Colar
                        </button>
                    </div>
                </div>

                <!-- Format and Quality Options -->
                <div class="options-grid">
                    <div>
                        <label class="option-label" for="selectFormat">Formato de Saída</label>
                        <select id="selectFormat" class="select-custom">
                            <option value="mp4" selected>🎬 MP4 (Vídeo com Áudio)</option>
                            <option value="mp3">🎵 MP3 (Apenas Áudio de Alta Qualidade)</option>
                        </select>
                    </div>
                    <div>
                        <label class="option-label" for="selectQuality">Resolução / Qualidade</label>
                        <select id="selectQuality" class="select-custom">
                            <option value="auto" selected>🌟 Máxima Resolução (Automático)</option>
                            <option value="1080p">📺 1080p (Full HD)</option>
                            <option value="720p">📱 720p (HD)</option>
                            <option value="480p">⚡ 480p (Rápido / Leve)</option>
                        </select>
                    </div>
                </div>

                <!-- Legal Checkboxes -->
                <div class="legal-check-box">
                    <label class="custom-check-item">
                        <input type="checkbox" id="checkRights" required>
                        <span class="check-text">
                            <strong>Declaração de Direitos:</strong> Confirmo que possuo os direitos autorais, licença de uso ou autorização legítima do titular para baixar e utilizar este conteúdo.
                        </span>
                    </label>
                    <label class="custom-check-item">
                        <input type="checkbox" id="checkTerms" required>
                        <span class="check-text">
                            Li e aceito expressamente os <a href="termos.php" target="_blank">Termos de Uso</a> e a <a href="direitos-autorais.php" target="_blank">Política de Direitos Autorais</a>.
                        </span>
                    </label>
                </div>

                <!-- Action Button -->
                <button type="submit" class="btn-action" id="btnSubmit">
                    <i class="fa-solid fa-bolt"></i> <span id="btnSubmitText">CONVERTER PARA MP4</span>
                </button>
            </form>

            <!-- Realtime Progress Box -->
            <div id="progressCard">
                <div class="progress-header">
                    <span class="progress-status-badge" id="badgeStatus">
                        <i class="fa-solid fa-spinner fa-spin"></i> <span id="textStatus">Processando...</span>
                    </span>
                    <span id="textPercent" style="font-weight: 800; font-size: 1.1rem; color: #60a5fa;">0%</span>
                </div>

                <div class="progress-bar-container">
                    <div class="progress-bar-fill" id="progressBar"></div>
                </div>

                <!-- Detailed Steps -->
                <div class="progress-steps-list">
                    <div class="step-item active" id="step1">
                        <span class="step-icon"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <span>1. Analisando URL e consultando metadados...</span>
                    </div>
                    <div class="step-item" id="step2">
                        <span class="step-icon"><i class="fa-solid fa-cloud-arrow-down"></i></span>
                        <span>2. Obtendo conteúdo autorizado da plataforma...</span>
                    </div>
                    <div class="step-item" id="step3">
                        <span class="step-icon"><i class="fa-solid fa-gears"></i></span>
                        <span>3. Processando e sincronizando vídeo e áudio com FFmpeg...</span>
                    </div>
                    <div class="step-item" id="step4">
                        <span class="step-icon"><i class="fa-solid fa-circle-check"></i></span>
                        <span>4. Finalizando arquivo de mídia para entrega...</span>
                    </div>
                </div>

                <!-- Video Preview -->
                <div class="result-preview" id="resultPreview">
                    <img src="" alt="Thumbnail" class="preview-thumb" id="previewThumb">
                    <div class="preview-info">
                        <div class="preview-title" id="previewTitle">Título do Vídeo</div>
                        <div class="preview-meta">
                            <span><i class="fa-regular fa-clock"></i> <span id="previewDuration">--:--</span></span>
                            <span><i class="fa-regular fa-hard-drive"></i> <span id="previewSize">-- MB</span></span>
                            <span><i class="fa-solid fa-file-video"></i> <span id="previewFormat">MP4</span></span>
                        </div>
                    </div>
                </div>

                <!-- Download Ready Button -->
                <a href="#" id="btnDownload" class="btn-download-ready">
                    <i class="fa-solid fa-download"></i> BAIXAR ARQUIVO AGORA
                </a>

                <div class="retention-notice" id="retentionNotice" style="display: none;">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Retenção Zero: O arquivo será excluído permanentemente em <strong id="retentionTimer">30:00</strong> por segurança e privacidade.</span>
                </div>
            </div>

            <!-- Error Box -->
            <div class="error-box" id="errorBox">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.4rem;"></i>
                <div id="errorText">Não foi possível processar o vídeo.</div>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-links">
            <a href="termos.php" class="footer-link">Termos de Uso</a>
            <a href="direitos-autorais.php" class="footer-link">Direitos Autorais & DMCA</a>
            <a href="privacidade.php" class="footer-link">Política de Privacidade</a>
            <a href="denunciar.php" class="footer-link">🚨 Denunciar Conteúdo</a>
            <a href="admin.php" class="footer-link">Administração</a>
        </div>
        <p class="footer-legal-notice">
            Aviso de Responsabilidade: O BAIXOU!! fornece tecnologia para conversão e processamento temporário de mídias para uso pessoal de conteúdos próprios ou autorizados pelo titular dos direitos. O serviço não armazena arquivos permanentemente e não apoia a violação de direitos autorais.
        </p>
    </footer>

    <!-- Frontend Script -->
    <script>
        const form = document.getElementById('convertForm');
        const urlInput = document.getElementById('urlInput');
        const selectFormat = document.getElementById('selectFormat');
        const selectQuality = document.getElementById('selectQuality');
        const checkRights = document.getElementById('checkRights');
        const checkTerms = document.getElementById('checkTerms');
        const btnSubmit = document.getElementById('btnSubmit');
        const btnSubmitText = document.getElementById('btnSubmitText');
        const btnPaste = document.getElementById('btnPaste');

        const progressCard = document.getElementById('progressCard');
        const progressBar = document.getElementById('progressBar');
        const textPercent = document.getElementById('textPercent');
        const badgeStatus = document.getElementById('badgeStatus');
        const textStatus = document.getElementById('textStatus');
        const errorBox = document.getElementById('errorBox');
        const errorText = document.getElementById('errorText');

        const step1 = document.getElementById('step1');
        const step2 = document.getElementById('step2');
        const step3 = document.getElementById('step3');
        const step4 = document.getElementById('step4');

        const resultPreview = document.getElementById('resultPreview');
        const previewThumb = document.getElementById('previewThumb');
        const previewTitle = document.getElementById('previewTitle');
        const previewDuration = document.getElementById('previewDuration');
        const previewSize = document.getElementById('previewSize');
        const previewFormat = document.getElementById('previewFormat');
        const btnDownload = document.getElementById('btnDownload');
        const retentionNotice = document.getElementById('retentionNotice');
        const retentionTimer = document.getElementById('retentionTimer');

        let activeJobId = null;
        let pollInterval = null;
        let countdownInterval = null;

        // Atualizar texto do botão dinamicamente
        selectFormat.addEventListener('change', () => {
            const fmt = selectFormat.value.toUpperCase();
            btnSubmitText.textContent = `CONVERTER PARA ${fmt}`;
        });

        // Botão Colar da Área de Transferência
        btnPaste.addEventListener('click', async () => {
            try {
                const text = await navigator.clipboard.readText();
                if (text) {
                    urlInput.value = text.trim();
                    urlInput.focus();
                }
            } catch (err) {
                urlInput.focus();
            }
        });

        // Envio do formulário
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (!checkRights.checked || !checkTerms.checked) {
                alert('Você precisa confirmar a declaração de direitos e aceitar os termos.');
                return;
            }

            const url = urlInput.value.trim();
            if (!url) return;

            // Resetar UI
            errorBox.style.display = 'none';
            resultPreview.style.display = 'none';
            btnDownload.style.display = 'none';
            retentionNotice.style.display = 'none';
            if (countdownInterval) clearInterval(countdownInterval);

            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Criando Tarefa...';

            progressCard.style.display = 'block';
            updateSteps(1, 10, 'Iniciando...');

            try {
                const formData = new FormData();
                formData.append('url', url);
                formData.append('format', selectFormat.value);
                formData.append('quality', selectQuality.value);
                formData.append('consent_rights', '1');
                formData.append('consent_terms', '1');

                const res = await fetch('api.php?action=create_job', {
                    method: 'POST',
                    body: formData
                });

                const data = await res.json();

                if (!data.success) {
                    showError(data.error || 'Erro ao registrar solicitação.');
                    return;
                }

                activeJobId = data.job_id;
                startPolling(activeJobId);

            } catch (err) {
                showError('Erro de conexão com o servidor. Verifique sua rede e tente novamente.');
            }
        });

        function startPolling(jobId) {
            if (pollInterval) clearInterval(pollInterval);

            pollInterval = setInterval(async () => {
                try {
                    const res = await fetch(`api.php?action=status&job_id=${jobId}`);
                    const data = await res.json();

                    if (!data.success) {
                        clearInterval(pollInterval);
                        showError(data.error || 'Falha ao consultar progresso.');
                        return;
                    }

                    const job = data.job;

                    if (job.status === 'analyzing') {
                        updateSteps(1, job.progress || 20, job.step);
                    } else if (job.status === 'downloading') {
                        updateSteps(2, job.progress || 40, job.step);
                    } else if (job.status === 'processing') {
                        updateSteps(3, job.progress || 75, job.step);
                    } else if (job.status === 'completed') {
                        clearInterval(pollInterval);
                        updateSteps(4, 100, 'Conversão concluída!');
                        showCompleted(job);
                    } else if (job.status === 'error') {
                        clearInterval(pollInterval);
                        showError(job.error_message || 'Erro durante a conversão.');
                    }
                } catch (e) {
                    // Ignora falhas pontuais de rede no polling
                }
            }, 1800);
        }

        function updateSteps(stepNumber, percent, stepText) {
            progressBar.style.width = `${percent}%`;
            textPercent.textContent = `${percent}%`;
            textStatus.textContent = stepText;

            [step1, step2, step3, step4].forEach((el, idx) => {
                const num = idx + 1;
                el.classList.remove('active', 'completed');
                if (num < stepNumber) {
                    el.classList.add('completed');
                    el.querySelector('.step-icon').innerHTML = '<i class="fa-solid fa-circle-check"></i>';
                } else if (num === stepNumber) {
                    el.classList.add('active');
                    el.querySelector('.step-icon').innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
                }
            });
        }

        function showCompleted(job) {
            btnSubmit.disabled = false;
            const fmt = selectFormat.value.toUpperCase();
            btnSubmitText.textContent = `CONVERTER PARA ${fmt}`;
            btnSubmit.innerHTML = `<i class="fa-solid fa-bolt"></i> <span>CONVERTER PARA ${fmt}</span>`;

            badgeStatus.style.background = 'rgba(16, 185, 129, 0.2)';
            badgeStatus.style.borderColor = 'rgba(16, 185, 129, 0.4)';
            badgeStatus.style.color = '#34d399';
            badgeStatus.innerHTML = '<i class="fa-solid fa-check"></i> Concluído com sucesso!';

            if (job.thumbnail) {
                previewThumb.src = job.thumbnail;
                previewThumb.style.display = 'block';
            } else {
                previewThumb.style.display = 'none';
            }

            previewTitle.textContent = job.title || 'Mídia Pronta';
            previewDuration.textContent = job.duration || '--:--';
            previewSize.textContent = job.file_size_formatted || 'Pronto';
            previewFormat.textContent = job.format;

            resultPreview.style.display = 'flex';
            btnDownload.href = job.download_url;
            btnDownload.style.display = 'block';
            retentionNotice.style.display = 'flex';

            // Contador regressivo de 30 minutos
            let remainingSec = job.expires_in || 1800;
            countdownInterval = setInterval(() => {
                remainingSec--;
                if (remainingSec <= 0) {
                    clearInterval(countdownInterval);
                    retentionTimer.textContent = 'Expirado';
                    btnDownload.style.display = 'none';
                } else {
                    const m = Math.floor(remainingSec / 60);
                    const s = remainingSec % 60;
                    retentionTimer.textContent = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
                }
            }, 1000);
        }

        function showError(msg) {
            btnSubmit.disabled = false;
            const fmt = selectFormat.value.toUpperCase();
            btnSubmit.innerHTML = `<i class="fa-solid fa-bolt"></i> <span>CONVERTER PARA ${fmt}</span>`;

            progressCard.style.display = 'none';
            errorBox.style.display = 'flex';
            errorText.textContent = msg;
        }
    </script>
</body>
</html>
