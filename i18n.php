<?php
// Sistema de Internacionalização (i18n) Bilíngue (PT / EN) para o BAIXOU!!

function getAppLanguage() {
    // 1. Prioridade: Parâmetro explícito na URL (?lang=pt ou ?lang=en)
    if (!empty($_GET['lang'])) {
        $l = strtolower(trim($_GET['lang']));
        if (in_array($l, ['pt', 'en'], true)) {
            setcookie('baixou_lang', $l, time() + (86400 * 365), '/');
            return $l;
        }
    }

    // 2. Cookie salvo de preferência anterior do usuário
    if (!empty($_COOKIE['baixou_lang'])) {
        $l = strtolower(trim($_COOKIE['baixou_lang']));
        if (in_array($l, ['pt', 'en'], true)) {
            return $l;
        }
    }

    // 3. Detecção automática do navegador:
    // Se o idioma primário começar com 'pt' (ex: pt-BR, pt-PT, pt), abre em PORTUGUÊS.
    // Qualquer outro idioma (en, es, fr, de, ja, zh, etc.), abre em INGLÊS.
    $acceptLang = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');
    if (!empty($acceptLang)) {
        $firstPart = explode(',', $acceptLang)[0];
        $primaryLang = explode(';', $firstPart)[0];
        if (strpos(trim($primaryLang), 'pt') === 0) {
            return 'pt';
        }
    }

    // Fallback padrão para qualquer outro idioma do mundo: Inglês
    return 'en';
}

$currentLang = getAppLanguage();

$i18nData = [
    'pt' => [
        'page_title' => 'BAIXOU!! — Baixe e converta vídeos autorizados em MP4 e MP3',
        'meta_desc' => 'Baixe e converta vídeos e áudios que você possui ou tem autorização para utilizar. Processamento em segundo plano de alta velocidade, seguro e com exclusão automática.',
        'nav_terms' => 'Termos',
        'nav_copyright' => 'Direitos Autorais',
        'nav_report' => 'Denunciar',
        'hero_title' => 'BAIXOU!!',
        'hero_subtitle' => 'Baixe e converta vídeos e áudios autorizados com processamento em alta velocidade e segurança máxima.',
        'input_label' => 'Cole a URL do Vídeo Autorizado',
        'input_placeholder' => 'https://www.youtube.com/watch?v=... ou link do X',
        'btn_paste' => 'Colar',
        'label_format' => 'Formato de Saída',
        'format_mp4' => '🎬 MP4 (Vídeo com Áudio)',
        'format_mp3' => '🎵 MP3 (Apenas Áudio de Alta Qualidade)',
        'label_quality' => 'Resolução / Qualidade',
        'quality_auto' => '🌟 Máxima Resolução (Automático)',
        'quality_1080p' => '📺 1080p (Full HD)',
        'quality_720p' => '📱 720p (HD)',
        'quality_480p' => '⚡ 480p (Rápido / Leve)',
        'check_rights_title' => 'Declaração de Direitos:',
        'check_rights_text' => 'Confirmo que possuo os direitos autorais, licença de uso ou autorização legítima do titular para baixar e utilizar este conteúdo.',
        'check_terms_prefix' => 'Li e aceito expressamente os',
        'terms_link_text' => 'Termos de Uso',
        'check_terms_and' => 'e a',
        'copyright_link_text' => 'Política de Direitos Autorais',
        'btn_convert_prefix' => 'CONVERTER PARA',
        'btn_creating_job' => 'Criando Tarefa...',
        'btn_processing' => 'Processando Mídia...',
        'step_1' => '1. Analisando URL e consultando metadados...',
        'step_2' => '2. Obtendo conteúdo autorizado da plataforma...',
        'step_3' => '3. Processando e sincronizando vídeo e áudio com FFmpeg...',
        'step_4' => '4. Finalizando arquivo de mídia para entrega...',
        'status_processing' => 'Processando...',
        'status_completed' => 'Concluído com sucesso!',
        'preview_title_default' => 'Mídia Pronta',
        'preview_ready' => 'Pronto',
        'btn_download' => 'BAIXAR ARQUIVO AGORA',
        'retention_prefix' => 'Retenção Zero: O arquivo será excluído permanentemente em',
        'retention_suffix' => 'por segurança e privacidade.',
        'retention_expired' => 'Expirado',
        'error_default' => 'Não foi possível processar o vídeo.',
        'error_check_boxes' => 'Você precisa confirmar a declaração de direitos e aceitar os termos.',
        'error_network' => 'Erro de conexão com o servidor. Verifique sua rede e tente novamente.',
        'footer_terms' => 'Termos de Uso',
        'footer_copyright' => 'Direitos Autorais & DMCA',
        'footer_privacy' => 'Política de Privacidade',
        'footer_report' => '🚨 Denunciar Conteúdo',
        'footer_admin' => 'Administração',
        'footer_disclaimer' => 'Aviso de Responsabilidade: O BAIXOU!! fornece tecnologia para conversão e processamento temporário de mídias para uso pessoal de conteúdos próprios ou autorizados pelo titular dos direitos. O serviço não armazena arquivos permanentemente e não apoia a violação de direitos autorais.'
    ],
    'en' => [
        'page_title' => 'BAIXOU!! — Download & convert authorized videos to MP4 and MP3',
        'meta_desc' => 'Download and convert videos and audios you own or are authorized to use. High-speed background processing, secure with automatic zero-retention deletion.',
        'nav_terms' => 'Terms',
        'nav_copyright' => 'Copyright',
        'nav_report' => 'Report Content',
        'hero_title' => 'BAIXOU!!',
        'hero_subtitle' => 'Download and convert authorized videos and audios with high-speed processing and maximum privacy.',
        'input_label' => 'Paste Authorized Video URL',
        'input_placeholder' => 'https://www.youtube.com/watch?v=... or X link',
        'btn_paste' => 'Paste',
        'label_format' => 'Output Format',
        'format_mp4' => '🎬 MP4 (Video with Audio)',
        'format_mp3' => '🎵 MP3 (High Quality Audio Only)',
        'label_quality' => 'Resolution / Quality',
        'quality_auto' => '🌟 Maximum Quality (Auto)',
        'quality_1080p' => '📺 1080p (Full HD)',
        'quality_720p' => '📱 720p (HD)',
        'quality_480p' => '⚡ 480p (Fast / Lightweight)',
        'check_rights_title' => 'Rights Declaration:',
        'check_rights_text' => 'I confirm that I hold the copyright, usage license, or legitimate authorization from the rights holder to download and use this content.',
        'check_terms_prefix' => 'I have read and expressly accept the',
        'terms_link_text' => 'Terms of Use',
        'check_terms_and' => 'and the',
        'copyright_link_text' => 'Copyright Policy',
        'btn_convert_prefix' => 'CONVERT TO',
        'btn_creating_job' => 'Creating Job...',
        'btn_processing' => 'Processing Media...',
        'step_1' => '1. Analyzing URL and fetching metadata...',
        'step_2' => '2. Downloading authorized stream from platform...',
        'step_3' => '3. Processing & merging video/audio with FFmpeg...',
        'step_4' => '4. Finalizing media file for secure delivery...',
        'status_processing' => 'Processing...',
        'status_completed' => 'Completed successfully!',
        'preview_title_default' => 'Ready Media',
        'preview_ready' => 'Ready',
        'btn_download' => 'DOWNLOAD FILE NOW',
        'retention_prefix' => 'Zero Retention: File will be permanently purged in',
        'retention_suffix' => 'for security and privacy.',
        'retention_expired' => 'Expired',
        'error_default' => 'Unable to process this video.',
        'error_check_boxes' => 'You must confirm the rights declaration and accept the terms of service.',
        'error_network' => 'Server connection error. Please check your network and try again.',
        'footer_terms' => 'Terms of Use',
        'footer_copyright' => 'Copyright & DMCA',
        'footer_privacy' => 'Privacy Policy',
        'footer_report' => '🚨 Report Content',
        'footer_admin' => 'Administration',
        'footer_disclaimer' => 'Disclaimer: BAIXOU!! provides technology for temporary media conversion and processing for personal use of own or authorized content from rights holders. The service does not store files permanently and strictly opposes copyright infringement.'
    ]
];

function t($key) {
    global $i18nData, $currentLang;
    return $i18nData[$currentLang][$key] ?? $i18nData['en'][$key] ?? $key;
}
