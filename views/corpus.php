<?php if (!defined('ABSPATH') || !defined('ELAIA_PLUGIN_DIR')) exit;

$flags_path = plugins_url('assets', dirname(__FILE__));
$lang = substr(get_locale(), 0, 2) ?: 'fr';
$pc = esc_attr($primary_color);
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
/* ═══════════════════════════════════════════
   ELAIA CORPUS WEBAPP — v7.1 (inline)
   ═══════════════════════════════════════════ */
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap');

#elaia-corpus-app {
    --ec-bg-color: #ffffff;
    --ec-accent-color: <?php echo $pc; ?>;
    --ec-text-color: #212121;
    --ec-accent-light: <?php echo $pc; ?>1A;
    --ec-accent-medium: <?php echo $pc; ?>33;
    --ec-surface: #ffffff;
    --ec-text-secondary: #757575;
    --ec-text-hint: #9e9e9e;
    --ec-border: #e0e0e0;
    --ec-shadow: 0 2px 8px rgba(0,0,0,0.08);
    --ec-shadow-lg: 0 4px 20px rgba(0,0,0,0.12);
    --ec-radius: 16px;
    font-family: 'Nunito', -apple-system, sans-serif;
    color: var(--ec-text-color);
    max-width: 480px;
    margin: 0 auto;
    background: #f5f5f5;
    min-height: 100vh;
    min-height: 100dvh;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

/* ─── Bottom Navbar ─── */
.ec-navbar {
    display: flex; align-items: stretch; background: var(--ec-bg-color);
    border-radius: 16px 16px 0 0; box-shadow: 0 -4px 20px rgba(0,0,0,0.12);
    padding: 6px 0; padding-bottom: max(6px, env(safe-area-inset-bottom));
    flex-shrink: 0; z-index: 100;
}
.ec-nav-item {
    flex: 1; display: flex; flex-direction: column; align-items: center; gap: 3px;
    padding: 8px 4px; background: none; border: none; cursor: pointer;
    font-family: inherit; transition: all 0.15s;
    color: var(--ec-text-color); opacity: 0.5; font-size: 11px; font-weight: 600;
}
.ec-nav-item:hover { opacity: 0.75; }
.ec-nav-item.active { opacity: 1; }
.ec-nav-icon { font-size: 22px; line-height: 1; }
.ec-nav-label { line-height: 1; }

/* ─── Scroll ─── */
.ec-scroll { flex: 1; overflow-y: auto; overflow-x: hidden; -webkit-overflow-scrolling: touch; }

/* ─── Header ─── */
.ec-header { padding: 32px 24px 56px; text-align: center; position: relative; }
.ec-header-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
.ec-header-status { display: flex; align-items: center; gap: 6px; font-size: 12px; }
.ec-header-status-dot { width: 6px; height: 6px; border-radius: 50%; background: #4ade80; animation: ec-pulse 2s infinite; }
@keyframes ec-pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }

/* ─── Language switcher ─── */
.ec-lang-switcher { position: relative; }
.ec-lang-btn {
    background: rgba(255,255,255,0.25); border: 1px solid rgba(255,255,255,0.3);
    border-radius: 10px; padding: 6px 10px; cursor: pointer; font-size: 20px; line-height: 1;
    transition: all 0.15s; min-width: 40px; min-height: 36px;
    display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px);
}
.ec-lang-btn:hover { background: rgba(255,255,255,0.4); }
.ec-lang-dropdown {
    position: absolute; top: calc(100% + 6px); right: 0;
    background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    padding: 6px; min-width: 120px; z-index: 200;
    opacity: 0; visibility: hidden; transform: translateY(-8px); transition: all 0.15s ease;
}
.ec-lang-dropdown.open { opacity: 1; visibility: visible; transform: translateY(0); }
.ec-lang-option {
    display: flex; align-items: center; gap: 10px; width: 100%;
    padding: 8px 12px; border: none; background: none; border-radius: 8px;
    cursor: pointer; font-family: inherit; font-size: 13px; font-weight: 600;
    color: #424242; transition: background 0.1s;
}
.ec-lang-option:hover { background: #f5f5f5; }
.ec-lang-option.active { background: var(--ec-accent-light); color: var(--ec-accent-color); }

/* ─── Flag images ─── */
.ec-flag-img {
    width: 22px; height: 16px; border-radius: 3px; display: inline-block;
    vertical-align: middle; object-fit: cover; box-shadow: 0 0 0 1px rgba(0,0,0,0.1);
}
.ec-lang-option .ec-flag-img { width: 20px; height: 14px; }

/* ─── Avatar ─── */
.ec-header-avatar {
    width: 72px; height: 72px; border-radius: 50%; object-fit: cover;
    border: 3px solid rgba(128,128,128,0.2); margin: 0 auto 12px; display: block;
    background: rgba(128,128,128,0.1);
}
.ec-header-avatar-placeholder {
    width: 72px; height: 72px; border-radius: 50%; background: rgba(128,128,128,0.15);
    margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; font-size: 32px;
}
.ec-header-title { font-size: 22px; font-weight: 800; line-height: 1.3; margin: 0 0 12px; }
.ec-header-badge {
    display: inline-block; padding: 6px 16px; background: var(--ec-accent-color);
    color: white; border-radius: 12px; font-size: 13px; font-weight: 600;
}

/* ─── CTA Button ─── */
.ec-cta-wrap { position: relative; margin: -36px 20px 0; z-index: 10; }
.ec-start-chat-btn {
    position: relative; width: 100%; padding: 18px 20px;
    background: #ffffff; color: var(--ec-accent-color);
    border: 1.5px solid var(--ec-accent-color); border-radius: 16px;
    font-size: 16px; font-weight: 700; font-family: inherit; cursor: pointer;
    box-shadow: 0 6px 24px rgba(0,0,0,0.12); transition: all 0.2s ease;
    display: flex; align-items: center; justify-content: center; gap: 10px; overflow: hidden;
}
.ec-start-chat-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 32px rgba(0,0,0,0.18); background: var(--ec-accent-light); }
.ec-start-chat-btn:active { transform: translateY(0) scale(0.98); }
.ec-start-chat-icon { font-size: 22px; }
.ec-start-chat-text { flex: 1; text-align: left; }
.ec-start-chat-arrow { font-size: 24px; font-weight: 300; opacity: 0.5; transition: all 0.2s; }
.ec-start-chat-btn:hover .ec-start-chat-arrow { transform: translateX(4px); opacity: 1; }
.ec-start-chat-pulse {
    position: absolute; inset: 0; border-radius: 16px;
    animation: ec-cta-pulse 2.5s ease-out infinite; pointer-events: none;
}
@keyframes ec-cta-pulse {
    0% { box-shadow: 0 0 0 0 var(--ec-accent-medium); }
    70% { box-shadow: 0 0 0 10px transparent; }
    100% { box-shadow: 0 0 0 0 transparent; }
}

/* ─── Suggests ─── */
.ec-suggests { margin-bottom: 20px; }
.ec-suggests-label { font-size: 14px; font-weight: 700; color: var(--ec-text-color); margin: 0 0 10px; }
.ec-suggests-scroll {
    display: flex; gap: 8px; overflow-x: auto; padding-bottom: 4px;
    -webkit-overflow-scrolling: touch; scrollbar-width: none;
}
.ec-suggests-scroll::-webkit-scrollbar { display: none; }
.ec-suggest-chip {
    flex-shrink: 0; padding: 10px 16px;
    background: var(--ec-accent-light); color: var(--ec-accent-color);
    border: 1px solid var(--ec-accent-medium); border-radius: 24px;
    font-size: 13px; font-weight: 600; font-family: inherit;
    cursor: pointer; transition: all 0.15s; white-space: nowrap;
    max-width: 240px; overflow: hidden; text-overflow: ellipsis;
}
.ec-suggest-chip:hover { background: var(--ec-accent-medium); transform: translateY(-1px); box-shadow: var(--ec-shadow); }
.ec-suggest-chip:active { transform: scale(0.97); }

/* ─── Content ─── */
.ec-content { padding: 24px 20px 20px; }
.ec-breadcrumb { display: flex; align-items: center; gap: 6px; margin-bottom: 16px; font-size: 13px; flex-wrap: wrap; }
.ec-crumb {
    padding: 4px 10px; border-radius: 16px; cursor: pointer;
    color: var(--ec-text-secondary); border: none; background: none;
    font-family: inherit; font-size: 13px; font-weight: 600; transition: all 0.15s;
}
.ec-crumb:hover { background: white; color: var(--ec-text-color); }
.ec-crumb.active { background: var(--ec-accent-light); color: var(--ec-accent-color); cursor: default; }
.ec-crumb-sep { color: #ccc; font-size: 11px; }

/* ─── Categories ─── */
.ec-category-list { display: flex; flex-direction: column; gap: 12px; }
.ec-category-row {
    display: flex; align-items: center; gap: 16px; padding: 20px;
    background: white; border-radius: var(--ec-radius); box-shadow: var(--ec-shadow);
    cursor: pointer; transition: all 0.2s;
}
.ec-category-row:hover { box-shadow: var(--ec-shadow-lg); transform: translateY(-1px); }
.ec-category-row:active { transform: scale(0.98); }
.ec-cat-icon {
    width: 48px; height: 48px; border-radius: 50%; background: var(--ec-accent-light);
    display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;
}
.ec-cat-info { flex: 1; min-width: 0; }
.ec-cat-title { font-size: 16px; font-weight: 700; color: var(--ec-text-color); margin: 0 0 3px; }
.ec-cat-subtitle { font-size: 13px; color: var(--ec-text-hint); margin: 0; }
.ec-cat-chevron { color: #bdbdbd; font-size: 22px; flex-shrink: 0; }

/* ─── Subcategories ─── */
.ec-subcat-list { display: flex; flex-direction: column; gap: 10px; }
.ec-subcat-row {
    display: flex; align-items: center; gap: 16px; padding: 20px;
    background: white; border-radius: var(--ec-radius); box-shadow: var(--ec-shadow);
    cursor: pointer; transition: all 0.2s;
}
.ec-subcat-row:hover { box-shadow: var(--ec-shadow-lg); }
.ec-subcat-icon {
    width: 56px; height: 56px; border-radius: 50%; background: var(--ec-accent-light);
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; flex-shrink: 0; color: var(--ec-accent-color);
}
.ec-subcat-info { flex: 1; min-width: 0; }
.ec-subcat-name { font-size: 16px; font-weight: 700; color: var(--ec-text-color); margin: 0 0 4px; }
.ec-subcat-desc { font-size: 13px; color: var(--ec-text-hint); margin: 0; }
.ec-subcat-chevron { color: #bdbdbd; font-size: 24px; flex-shrink: 0; transition: transform 0.15s; }
.ec-subcat-row:hover .ec-subcat-chevron { color: var(--ec-accent-color); transform: translateX(3px); }

/* ─── Cards ─── */
.ec-cards-grid { display: flex; flex-direction: column; gap: 12px; }
.ec-card {
    background: white; border-radius: var(--ec-radius); overflow: hidden;
    box-shadow: var(--ec-shadow); cursor: pointer; transition: all 0.2s;
}
.ec-card:hover { box-shadow: var(--ec-shadow-lg); transform: translateY(-1px); }
.ec-card-img { width: 100%; height: 180px; object-fit: cover; display: block; background: #eee; }
.ec-card-img-empty {
    width: 100%; height: 180px; background: #eee;
    display: flex; align-items: center; justify-content: center; color: #bdbdbd; font-size: 40px;
}
.ec-card-body { padding: 16px; }
.ec-card-title { font-size: 18px; font-weight: 700; color: var(--ec-text-color); margin: 0 0 6px; }
.ec-card-desc {
    font-size: 13px; color: var(--ec-text-secondary); margin: 0 0 12px; line-height: 1.5;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.ec-card-action {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 13px; font-weight: 600; color: var(--ec-accent-color);
    background: none; border: none; cursor: pointer; font-family: inherit; padding: 0; float: right;
}
.ec-card-action:hover { text-decoration: underline; }

/* ─── Bottom Sheet ─── */
.ec-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.4);
    z-index: 99999; display: flex; align-items: flex-end;
    justify-content: center; animation: ec-fadeIn 0.2s ease;
}
@keyframes ec-fadeIn { from{opacity:0} to{opacity:1} }
.ec-sheet {
    background: white; border-radius: var(--ec-radius) var(--ec-radius) 0 0;
    max-width: 600px; width: 100%; max-height: 80vh;
    overflow-y: auto; animation: ec-slideUp 0.3s cubic-bezier(0.22,1,0.36,1);
}
@keyframes ec-slideUp { from{transform:translateY(100%)} to{transform:translateY(0)} }
.ec-sheet-handle { width: 40px; height: 4px; border-radius: 2px; background: #e0e0e0; margin: 12px auto 8px; }
.ec-sheet-img { width: 100%; height: 150px; object-fit: cover; border-radius: 12px; display: block; margin-bottom: 12px; }
.ec-sheet-body { padding: 0 16px 16px; }
.ec-sheet-title { font-size: 20px; font-weight: 800; color: var(--ec-text-color); margin: 0 0 8px; }
.ec-sheet-badge {
    display: inline-block; padding: 4px 8px; background: #eee; border-radius: 8px;
    font-size: 12px; font-weight: 500; color: var(--ec-text-secondary); margin-bottom: 12px;
}
.ec-sheet-desc { font-size: 14px; color: #616161; line-height: 1.6; margin: 0 0 20px; }
.ec-sheet-section-title { font-size: 16px; font-weight: 700; color: var(--ec-text-color); margin: 0 0 12px; }
.ec-detail-row { display: flex; gap: 12px; margin-bottom: 12px; align-items: flex-start; }
.ec-detail-icon { font-size: 18px; color: var(--ec-text-secondary); margin-top: 2px; flex-shrink: 0; }
.ec-detail-content { flex: 1; }
.ec-detail-label { font-size: 12px; color: #9e9e9e; font-weight: 500; margin: 0 0 2px; }
.ec-detail-value { font-size: 14px; color: var(--ec-text-color); margin: 0; word-break: break-word; }
.ec-sheet-actions { display: flex; gap: 12px; margin-top: 18px; }
.ec-btn {
    flex: 1; display: flex; align-items: center; justify-content: center;
    gap: 8px; padding: 13px 16px; border-radius: 12px; font-size: 14px;
    font-weight: 600; border: none; cursor: pointer; font-family: inherit;
    transition: all 0.15s; text-decoration: none;
}
.ec-btn-primary { background: var(--ec-text-color); color: white; }
.ec-btn-primary:hover { opacity: 0.9; }
.ec-btn-secondary { background: #e0e0e0; color: var(--ec-text-color); }
.ec-btn-secondary:hover { background: #d5d5d5; }
.ec-btn-full { width: 100%; background: var(--ec-text-color); color: white; }
.ec-sheet-map { width: 100%; height: 180px; border-radius: 12px; overflow: hidden; margin-top: 16px; border: 1px solid var(--ec-border); }

/* ─── Map ─── */
.ec-map-page { flex: 1; position: relative; }
.ec-map-page .ec-map-full { width: 100%; height: 100%; position: absolute; inset: 0; }
.ec-map-btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 18px; border-radius: 24px; border: none;
    background: white; color: var(--ec-text-secondary);
    font-size: 13px; font-weight: 600; cursor: pointer;
    box-shadow: var(--ec-shadow); transition: all 0.15s; font-family: inherit; margin-bottom: 16px;
}
.ec-map-btn:hover { box-shadow: var(--ec-shadow-lg); color: var(--ec-accent-color); }
.ec-map-btn.active { background: var(--ec-accent-color); color: white; }

/* ─── Iframe ─── */
.ec-iframe-page { flex: 1; position: relative; overflow: hidden; }
.ec-iframe { width: 100%; height: 100%; position: absolute; inset: 0; border: none; background: white; }

/* ─── Placeholder ─── */
.ec-placeholder-page {
    flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 40px 24px; text-align: center;
}
.ec-placeholder-icon {
    width: 80px; height: 80px; border-radius: 50%; background: var(--ec-accent-light);
    display: flex; align-items: center; justify-content: center; font-size: 36px; margin-bottom: 20px;
}
.ec-placeholder-title { font-size: 20px; font-weight: 800; color: var(--ec-text-color); margin: 0 0 8px; }
.ec-placeholder-desc { font-size: 14px; color: var(--ec-text-secondary); margin: 0; line-height: 1.5; }

/* ─── Loading ─── */
.ec-loading {
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; padding: 80px 20px; gap: 16px; flex: 1;
}
.ec-spinner {
    width: 32px; height: 32px; border: 3px solid #e0e0e0;
    border-top-color: var(--ec-accent-color); border-radius: 50%;
    animation: ec-spin 0.6s linear infinite;
}
@keyframes ec-spin { to{transform:rotate(360deg)} }
.ec-loading-text { font-size: 14px; color: var(--ec-text-hint); }
.ec-error { text-align: center; padding: 48px 20px; color: #c62828; font-size: 14px; }

/* ─── Responsive ─── */
@media (min-width: 640px) { #elaia-corpus-app { max-width: 520px; } }
@media (min-width: 1024px) { #elaia-corpus-app { max-width: 480px; } }
</style>

<?php if (!$api_ok): ?>
  <div style="max-width:480px;margin:20px auto;padding:20px;background:#fef2f2;border:1px solid #fecaca;border-radius:12px;color:#b91c1c;">
    <strong>Erreur API :</strong> <?php echo esc_html($api_err); ?>
  </div>
<?php elseif ($api_code < 200 || $api_code >= 300): ?>
  <div style="max-width:480px;margin:20px auto;padding:20px;background:#fef2f2;border:1px solid #fecaca;border-radius:12px;color:#b91c1c;">
    <strong>Réponse API inattendue (code <?php echo (int)$api_code; ?>)</strong>
  </div>
<?php endif; ?>


<div id="elaia-corpus-app"
     data-config='<?php echo esc_attr(wp_json_encode([
         'domain'       => $domain,
         'lang'         => $lang,
         'flagsPath'    => $flags_path,
         'chatHost'     => $chat_host,
         'appHost'      => $app_host,
         'chatbotKey'   => $chatbot_key,
         'agentName'    => $agent_name,
         'agentPicture' => $agent_picture,
         'hookText'     => $hook_text,
         'primaryColor' => $primary_color,
         'corpus'       => $corpus,
         'suggests'     => $suggests,
     ])); ?>'>
</div>

<script>
(function () {
    'use strict';

    const container = document.getElementById('elaia-corpus-app');
    if (!container) return;

    const config = JSON.parse(container.dataset.config || '{}');
    const CHAT_HOST = config.chatHost || 'https://chatbot.ela-ia.com';
    const APP_HOST = config.appHost || 'https://app.ela-ia.com';
    const FLAGS_PATH = config.flagsPath || '';
    let LANG = config.lang || 'fr';

    let COLORS = { bg: '#ffffff', accent: '#16a34a', text: '#212121' };

    let corpusData = config.corpus || [];
    let chatbotKey = config.chatbotKey || null;
    let suggests = config.suggests || [];
    let campingName = config.agentName || '';
    let agentPicture = config.agentPicture || null;
    let hookText = config.hookText || '';

    if (config.primaryColor) {
        COLORS.bg = config.primaryColor;
        COLORS.accent = config.primaryColor;
    }

    let activeTab = 'home';
    let currentView = 'categories';
    let selectedCategory = null;
    let selectedSubCategory = null;
    let mapInstance = null;
    let showingMap = false;

    const ICONS = {
        'Icons.info': 'ℹ️', 'Icons.store': '🏢', 'Icons.shopping_basket': '🛒',
        'Icons.restaurant': '🍽️', 'Icons.directions_bus': '🗺️', 'Icons.hotel': '🏨',
        'Icons.pool': '🏊', 'Icons.sports': '⚽', 'Icons.local_activity': '🎯',
    };

    const LOCALES = [
        { code: 'fr', flag: '🇫🇷', label: 'FR' },
        { code: 'en', flag: '🇬🇧', label: 'EN' },
        { code: 'es', flag: '🇪🇸', label: 'ES' },
        { code: 'de', flag: '🇩🇪', label: 'DE' },
        { code: 'nl', flag: '🇳🇱', label: 'NL' },
        { code: 'it', flag: '🇮🇹', label: 'IT' },
        { code: 'pt', flag: '🇵🇹', label: 'PT' },
        { code: 'eus', flag: '<img class="ec-flag-img" src="' + FLAGS_PATH + '/eus.svg" alt="EUS">', label: 'EUS' },
        { code: 'cat', flag: '<img class="ec-flag-img" src="' + FLAGS_PATH + '/cat.svg" alt="CAT">', label: 'CAT' },
    ];

    // ═══════════════════════════════════════
    // UI TRANSLATIONS
    // ═══════════════════════════════════════

    const UI = {
        fr: { welcome:'Bienvenue !',online:'En ligne',frequent_questions:'💡 Questions fréquentes',home:'Accueil',map:'Carte',conversations:'Conversations',contact:'Contact',start_chat:'Commencer la discussion',see_cards:'Voir les fiches',see_on_map:'Voir sur la carte',see_more:'👁 En voir +',detailed_info:'Informations détaillées',directions:'🧭 Itinéraires',close:'Fermer',loading:'Chargement...',no_section:'Aucune rubrique.',no_card:'Aucune fiche.',card:'fiche',cards:'fiches',chatbot_unavailable:'Chatbot non disponible',chatbot_not_configured:'Le chatbot n\'est pas configuré pour ce site.',contact_unavailable:'Contact non disponible',contact_not_configured:'Les informations de contact ne sont pas configurées.',load_error:'Impossible de charger les données.' },
        en: { welcome:'Welcome!',online:'Online',frequent_questions:'💡 Frequently asked questions',home:'Home',map:'Map',conversations:'Conversations',contact:'Contact',start_chat:'Start a conversation',see_cards:'View cards',see_on_map:'View on map',see_more:'👁 See more',detailed_info:'Detailed information',directions:'🧭 Directions',close:'Close',loading:'Loading...',no_section:'No section.',no_card:'No card.',card:'card',cards:'cards',chatbot_unavailable:'Chatbot unavailable',chatbot_not_configured:'The chatbot is not configured for this site.',contact_unavailable:'Contact unavailable',contact_not_configured:'Contact information is not configured.',load_error:'Unable to load data.' },
        es: { welcome:'¡Bienvenido!',online:'En línea',frequent_questions:'💡 Preguntas frecuentes',home:'Inicio',map:'Mapa',conversations:'Conversaciones',contact:'Contacto',start_chat:'Iniciar conversación',see_cards:'Ver fichas',see_on_map:'Ver en el mapa',see_more:'👁 Ver más',detailed_info:'Información detallada',directions:'🧭 Cómo llegar',close:'Cerrar',loading:'Cargando...',no_section:'Sin sección.',no_card:'Sin ficha.',card:'ficha',cards:'fichas',chatbot_unavailable:'Chatbot no disponible',chatbot_not_configured:'El chatbot no está configurado para este sitio.',contact_unavailable:'Contacto no disponible',contact_not_configured:'La información de contacto no está configurada.',load_error:'No se pueden cargar los datos.' },
        de: { welcome:'Willkommen!',online:'Online',frequent_questions:'💡 Häufige Fragen',home:'Startseite',map:'Karte',conversations:'Gespräche',contact:'Kontakt',start_chat:'Gespräch starten',see_cards:'Karten anzeigen',see_on_map:'Auf der Karte',see_more:'👁 Mehr sehen',detailed_info:'Detaillierte Informationen',directions:'🧭 Wegbeschreibung',close:'Schließen',loading:'Laden...',no_section:'Kein Bereich.',no_card:'Keine Karte.',card:'Karte',cards:'Karten',chatbot_unavailable:'Chatbot nicht verfügbar',chatbot_not_configured:'Der Chatbot ist für diese Seite nicht konfiguriert.',contact_unavailable:'Kontakt nicht verfügbar',contact_not_configured:'Kontaktinformationen sind nicht konfiguriert.',load_error:'Daten konnten nicht geladen werden.' },
        nl: { welcome:'Welkom!',online:'Online',frequent_questions:'💡 Veelgestelde vragen',home:'Home',map:'Kaart',conversations:'Gesprekken',contact:'Contact',start_chat:'Start een gesprek',see_cards:'Bekijk kaarten',see_on_map:'Op de kaart',see_more:'👁 Meer zien',detailed_info:'Gedetailleerde informatie',directions:'🧭 Routebeschrijving',close:'Sluiten',loading:'Laden...',no_section:'Geen sectie.',no_card:'Geen kaart.',card:'kaart',cards:'kaarten',chatbot_unavailable:'Chatbot niet beschikbaar',chatbot_not_configured:'De chatbot is niet geconfigureerd voor deze site.',contact_unavailable:'Contact niet beschikbaar',contact_not_configured:'Contactinformatie is niet geconfigureerd.',load_error:'Kan gegevens niet laden.' },
        it: { welcome:'Benvenuto!',online:'Online',frequent_questions:'💡 Domande frequenti',home:'Home',map:'Mappa',conversations:'Conversazioni',contact:'Contatto',start_chat:'Inizia una conversazione',see_cards:'Vedi schede',see_on_map:'Vedi sulla mappa',see_more:'👁 Vedi di più',detailed_info:'Informazioni dettagliate',directions:'🧭 Indicazioni',close:'Chiudi',loading:'Caricamento...',no_section:'Nessuna sezione.',no_card:'Nessuna scheda.',card:'scheda',cards:'schede',chatbot_unavailable:'Chatbot non disponibile',chatbot_not_configured:'Il chatbot non è configurato per questo sito.',contact_unavailable:'Contatto non disponibile',contact_not_configured:'Le informazioni di contatto non sono configurate.',load_error:'Impossibile caricare i dati.' },
        pt: { welcome:'Bem-vindo!',online:'Online',frequent_questions:'💡 Perguntas frequentes',home:'Início',map:'Mapa',conversations:'Conversas',contact:'Contacto',start_chat:'Iniciar conversa',see_cards:'Ver fichas',see_on_map:'Ver no mapa',see_more:'👁 Ver mais',detailed_info:'Informações detalhadas',directions:'🧭 Direções',close:'Fechar',loading:'A carregar...',no_section:'Sem secção.',no_card:'Sem ficha.',card:'ficha',cards:'fichas',chatbot_unavailable:'Chatbot indisponível',chatbot_not_configured:'O chatbot não está configurado para este site.',contact_unavailable:'Contacto indisponível',contact_not_configured:'As informações de contacto não estão configuradas.',load_error:'Não foi possível carregar os dados.' },
        eus: { welcome:'Ongi etorri!',online:'Linean',frequent_questions:'💡 Maiz egiten diren galderak',home:'Hasiera',map:'Mapa',conversations:'Elkarrizketak',contact:'Kontaktua',start_chat:'Elkarrizketa hasi',see_cards:'Fitxak ikusi',see_on_map:'Mapan ikusi',see_more:'👁 Gehiago ikusi',detailed_info:'Informazio xehatua',directions:'🧭 Nola iritsi',close:'Itxi',loading:'Kargatzen...',no_section:'Atalik ez.',no_card:'Fitxarik ez.',card:'fitxa',cards:'fitxa',chatbot_unavailable:'Chatbot-a ez dago eskuragarri',chatbot_not_configured:'Chatbot-a ez dago gune honetarako konfiguratuta.',contact_unavailable:'Kontaktua ez dago eskuragarri',contact_not_configured:'Kontaktu informazioa ez dago konfiguratuta.',load_error:'Ezin izan dira datuak kargatu.' },
        cat: { welcome:'Benvingut!',online:'En línia',frequent_questions:'💡 Preguntes freqüents',home:'Inici',map:'Mapa',conversations:'Converses',contact:'Contacte',start_chat:'Iniciar conversa',see_cards:'Veure fitxes',see_on_map:'Veure al mapa',see_more:'👁 Veure més',detailed_info:'Informació detallada',directions:'🧭 Com arribar-hi',close:'Tancar',loading:'Carregant...',no_section:'Cap secció.',no_card:'Cap fitxa.',card:'fitxa',cards:'fitxes',chatbot_unavailable:'Chatbot no disponible',chatbot_not_configured:'El chatbot no està configurat per a aquest lloc.',contact_unavailable:'Contacte no disponible',contact_not_configured:'La informació de contacte no està configurada.',load_error:'No s\'han pogut carregar les dades.' },
    };
    function t(key) { return UI[LANG]?.[key] || UI.fr[key] || key; }

    // ═══════════════════════════════════════
    // TRANSLATION HELPERS
    // ═══════════════════════════════════════

    function tCatName(cat) { return (LANG !== 'fr' && cat.translations?.[LANG]?.name) || cat.category; }
    function tCatDesc(cat) { return (LANG !== 'fr' && cat.translations?.[LANG]?.description) || cat.description || ''; }
    function tSubName(sub) { return (LANG !== 'fr' && sub.translations?.[LANG]?.name) || sub['sub-category']; }
    function tSubDesc(sub) { return (LANG !== 'fr' && sub.translations?.[LANG]?.description) || sub.description || ''; }
    function tCardName(card) {
        if (LANG === 'fr') return card.name;
        return card.name_translations?.[LANG] || card.translations?.[LANG]?.name || card.name;
    }
    function tCardDesc(card) { return (LANG !== 'fr' && card.translations?.[LANG]?.description) || card.description || ''; }
    function tCardValues(card) {
        if (!card.values || typeof card.values !== 'object') return {};
        if (LANG === 'fr' || !card.translations?.[LANG]) return card.values;
        var ld = card.translations[LANG], r = {};
        for (var k in card.values) {
            var tr = ld[k];
            if (tr && typeof tr === 'object' && tr.question) r[tr.question] = tr.answer || card.values[k];
            else if (typeof tr === 'string') r[k] = tr;
            else r[k] = card.values[k];
        }
        return r;
    }
    function tSuggestTitle(s) { return (LANG !== 'fr' && s.translations?.[LANG]?.title) || s.title || ''; }

    // ═══════════════════════════════════════
    // COLOR
    // ═══════════════════════════════════════

    function applyColors() {
        container.style.setProperty('--ec-bg-color', COLORS.bg);
        container.style.setProperty('--ec-accent-color', COLORS.accent);
        container.style.setProperty('--ec-text-color', COLORS.text);
        container.style.setProperty('--ec-accent-light', COLORS.accent + '1A');
        container.style.setProperty('--ec-accent-medium', COLORS.accent + '33');
    }
    function contrastText(hex) {
        var h = hex.replace('#',''), r = parseInt(h.substring(0,2),16), g = parseInt(h.substring(2,4),16), b = parseInt(h.substring(4,6),16);
        return (0.299*r + 0.587*g + 0.114*b) / 255 > 0.6 ? '#212121' : '#ffffff';
    }

    // ═══════════════════════════════════════
    // RENDER
    // ═══════════════════════════════════════

    function render() {
        applyColors();
        if (corpusData === null) {
            container.innerHTML = '<div class="ec-loading"><div class="ec-spinner"></div><span class="ec-loading-text">' + t('loading') + '</span></div>';
            return;
        }
        var html = '';
        switch (activeTab) {
            case 'home': html += '<div class="ec-scroll">' + renderHome() + '</div>'; break;
            case 'map': html += '<div class="ec-map-page"><div id="ec-map-full" class="ec-map-full"></div></div>'; break;
            case 'chatbot': html += renderChatbot(); break;
            case 'contact': html += renderContact(); break;
        }
        html += renderNavbar();
        container.innerHTML = html;
        bindEvents();
        if (activeTab === 'map') initFullMap();
    }

    function renderNavbar() {
        var tabs = [
            { id:'home', icon:'🏠', label:t('home') },
            { id:'map', icon:'🗺️', label:t('map') },
            { id:'chatbot', icon:'💬', label:t('conversations') },
            { id:'contact', icon:'📞', label:t('contact') },
        ];
        return '<nav class="ec-navbar">' + tabs.map(function(tb) {
            return '<button class="ec-nav-item ' + (activeTab === tb.id ? 'active' : '') + '" data-tab="' + tb.id + '"><span class="ec-nav-icon">' + tb.icon + '</span><span class="ec-nav-label">' + tb.label + '</span></button>';
        }).join('') + '</nav>';
    }

    // ─── Home ───
    function renderHome() {
        var html = renderHeader();
        html += '<div class="ec-content">';
        if (suggests.length > 0) html += renderSuggests();
        html += renderBreadcrumb();
        if (currentView === 'cards' && getSubCatPoints(selectedSubCategory).length > 0) {
            html += '<button class="ec-map-btn ' + (showingMap ? 'active' : '') + '" data-action="toggle-map">📍 ' + (showingMap ? t('see_cards') : t('see_on_map')) + '</button>';
        }
        if (showingMap) {
            html += '<div style="border-radius:16px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:16px;"><div id="ec-map" style="width:100%;height:350px;"></div></div>';
        } else {
            switch (currentView) {
                case 'categories': html += renderCategories(); break;
                case 'subcategories': html += renderSubCategories(); break;
                case 'cards': html += renderCards(); break;
            }
        }
        html += '</div>';
        return html;
    }

    function renderHeader() {
        var hBg = COLORS.bg, hText = contrastText(hBg);
        var hMuted = hText === '#ffffff' ? 'rgba(255,255,255,0.7)' : 'rgba(0,0,0,0.5)';
        var avatar = agentPicture
            ? '<img class="ec-header-avatar" src="' + APP_HOST + '/' + esc(agentPicture) + '" alt="" onerror="this.className=\'ec-header-avatar-placeholder\';this.textContent=\'👤\';">'
            : '<div class="ec-header-avatar-placeholder">👤</div>';
        var loc = LOCALES.find(function(l){ return l.code === LANG; }) || LOCALES[0];
        var title = campingName || t('welcome');

        return '<div class="ec-header" style="background:' + hBg + ';color:' + hText + '">'
            + '<div class="ec-header-top">'
            + '<div class="ec-header-status" style="color:' + hMuted + '"><span class="ec-header-status-dot"></span> ' + t('online') + '</div>'
            + '<div class="ec-lang-switcher"><button class="ec-lang-btn" id="ec-lang-toggle">' + loc.flag + '</button>'
            + '<div class="ec-lang-dropdown" id="ec-lang-dropdown">'
            + LOCALES.map(function(l){ return '<button class="ec-lang-option ' + (l.code === LANG ? 'active' : '') + '" data-lang="' + l.code + '"><span>' + l.flag + '</span><span>' + l.label + '</span></button>'; }).join('')
            + '</div></div></div>'
            + avatar
            + '<h1 class="ec-header-title" style="color:' + hText + '">' + esc(title) + '</h1>'
            + (hookText ? '<span class="ec-header-badge">' + esc(hookText) + '</span>' : '')
            + '</div>'
            + '<div class="ec-cta-wrap"><button class="ec-start-chat-btn" id="ec-start-chat">'
            + '<span class="ec-start-chat-pulse"></span>'
            + '<span class="ec-start-chat-icon">💬</span>'
            + '<span class="ec-start-chat-text">' + t('start_chat') + '</span>'
            + '<span class="ec-start-chat-arrow">›</span>'
            + '</button></div>';
    }

    function renderSuggests() {
        return '<div class="ec-suggests"><p class="ec-suggests-label">' + t('frequent_questions') + '</p><div class="ec-suggests-scroll">'
            + suggests.map(function(s, i){ return '<button class="ec-suggest-chip" data-suggest="' + i + '">' + esc(tSuggestTitle(s)) + '</button>'; }).join('')
            + '</div></div>';
    }

    function renderBreadcrumb() {
        if (currentView === 'categories') return '';
        var items = ['<button class="ec-crumb" data-nav="categories">' + t('home') + '</button>'];
        if (selectedCategory) {
            items.push('<span class="ec-crumb-sep">›</span>');
            items.push('<button class="ec-crumb ' + (currentView === 'subcategories' ? 'active' : '') + '" data-nav="subcategories">' + esc(tCatName(selectedCategory)) + '</button>');
        }
        if (selectedSubCategory && currentView === 'cards') {
            items.push('<span class="ec-crumb-sep">›</span>');
            items.push('<span class="ec-crumb active">' + esc(tSubName(selectedSubCategory)) + '</span>');
        }
        return '<nav class="ec-breadcrumb">' + items.join('') + '</nav>';
    }

    function renderCategories() {
        if (!corpusData.length) {
        return '<div class="ec-placeholder-page" style="padding:40px 20px;text-align:center;">'
            + '<div class="ec-placeholder-icon">📋</div>'
            + '<p style="color:var(--ec-text-hint);font-size:14px;">' + t('no_section') + '</p>'
            + '</div>';
    }
    
        return '<div class="ec-category-list">' + corpusData.map(function(cat, i) {
            return '<div class="ec-category-row" data-category="' + i + '"><div class="ec-cat-icon">' + (ICONS[cat.icon] || '📁') + '</div><div class="ec-cat-info"><p class="ec-cat-title">' + esc(tCatName(cat)) + '</p><p class="ec-cat-subtitle">' + esc(tCatDesc(cat)) + '</p></div><span class="ec-cat-chevron">›</span></div>';
        }).join('') + '</div>';
    }

    function renderSubCategories() {
        if (!selectedCategory || !selectedCategory['sub-categories']) return '<p class="ec-error">' + t('no_section') + '</p>';
        return '<div class="ec-subcat-list">' + selectedCategory['sub-categories'].map(function(sub, i) {
            var c = (sub.cards || []).length;
            return '<div class="ec-subcat-row" data-subcategory="' + i + '"><div class="ec-subcat-icon">📋</div><div class="ec-subcat-info"><p class="ec-subcat-name">' + esc(tSubName(sub)) + '</p><p class="ec-subcat-desc">' + esc(tSubDesc(sub)) + ' · ' + c + ' ' + (c > 1 ? t('cards') : t('card')) + '</p></div><span class="ec-subcat-chevron">›</span></div>';
        }).join('') + '</div>';
    }

    function renderCards() {
        if (!selectedSubCategory || !selectedSubCategory.cards) return '<p class="ec-error">' + t('no_card') + '</p>';
        return '<div class="ec-cards-grid">' + selectedSubCategory.cards.map(function(card, i) {
            var img = card.image
                ? '<img class="ec-card-img" src="' + esc(card.image) + '" alt="' + esc(tCardName(card)) + '" loading="lazy" onerror="this.outerHTML=\'<div class=\\\'ec-card-img-empty\\\'>🏞️</div>\'">'
                : '<div class="ec-card-img-empty">🏞️</div>';
            return '<div class="ec-card" data-card="' + i + '">' + img + '<div class="ec-card-body"><h4 class="ec-card-title">' + esc(tCardName(card)) + '</h4><p class="ec-card-desc">' + esc(tCardDesc(card)) + '</p><button class="ec-card-action">' + t('see_more') + '</button></div></div>';
        }).join('') + '</div>';
    }

    // ─── Map ───
    function initFullMap() {
        setTimeout(function() {
            var el = document.getElementById('ec-map-full');
            if (!el || typeof L === 'undefined') return;
            if (mapInstance) { mapInstance.remove(); mapInstance = null; }
            var points = getAllPoints();
            mapInstance = L.map(el).setView([46.6, 1.9], 6);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OSM' }).addTo(mapInstance);
            if (!points.length) return;
            var bounds = [];
            points.forEach(function(p) {
                var m = L.marker([p.latitude, p.longitude]).addTo(mapInstance);
                m.bindPopup('<strong>' + esc(tCardName(p)) + '</strong><br>' + esc(tCardDesc(p)));
                m.on('click', function(){ showDetail(p); });
                bounds.push([p.latitude, p.longitude]);
            });
            bounds.length > 1 ? mapInstance.fitBounds(bounds, { padding: [30, 30] }) : mapInstance.setView(bounds[0], 13);
            setTimeout(function(){ mapInstance && mapInstance.invalidateSize(); }, 100);
        }, 50);
    }

    // ─── Chatbot & Contact ───
    function renderChatbot() {
        if (!chatbotKey) return '<div class="ec-placeholder-page"><div class="ec-placeholder-icon">💬</div><h2 class="ec-placeholder-title">' + t('chatbot_unavailable') + '</h2><p class="ec-placeholder-desc">' + t('chatbot_not_configured') + '</p></div>';
        return '<div class="ec-iframe-page"><iframe class="ec-iframe" src="' + CHAT_HOST + '/chat-plugin?k=' + encodeURIComponent(chatbotKey) + '&lang=' + LANG + '" allow="microphone"></iframe></div>';
    }
    function renderContact() {
        if (!chatbotKey) return '<div class="ec-placeholder-page"><div class="ec-placeholder-icon">📞</div><h2 class="ec-placeholder-title">' + t('contact_unavailable') + '</h2><p class="ec-placeholder-desc">' + t('contact_not_configured') + '</p></div>';
        return '<div class="ec-iframe-page"><iframe class="ec-iframe" src="' + CHAT_HOST + '/contact-plugin?k=' + encodeURIComponent(chatbotKey) + '&lang=' + LANG + '" allow="microphone"></iframe></div>';
    }

    // ─── Detail Sheet ───
    function showDetail(card) {
        var name = tCardName(card), desc = tCardDesc(card), values = tCardValues(card);
        var imgH = card.image ? '<img class="ec-sheet-img" src="' + esc(card.image) + '" alt="' + esc(name) + '" onerror="this.style.display=\'none\'">' : '';
        var valH = '';
        if (values && typeof values === 'object' && Object.keys(values).length > 0) {
            valH = '<p class="ec-sheet-section-title">' + t('detailed_info') + '</p>';
            Object.entries(values).forEach(function(e) {
                valH += '<div class="ec-detail-row"><span class="ec-detail-icon">ℹ️</span><div class="ec-detail-content"><p class="ec-detail-label">' + esc(e[0]) + '</p><p class="ec-detail-value">' + esc(String(e[1])) + '</p></div></div>';
            });
        }
        var hasGps = card.latitude && card.longitude;
        var mapH = hasGps ? '<div id="ec-detail-map" class="ec-sheet-map"></div>' : '';
        var subN = selectedSubCategory ? tSubName(selectedSubCategory) : '';
        var actH = hasGps
            ? '<div class="ec-sheet-actions"><a class="ec-btn ec-btn-primary" href="https://www.google.com/maps/dir/?api=1&destination=' + card.latitude + ',' + card.longitude + '" target="_blank" rel="noopener">' + t('directions') + '</a><button class="ec-btn ec-btn-secondary" data-close-sheet>' + t('close') + '</button></div>'
            : '<div class="ec-sheet-actions"><button class="ec-btn ec-btn-full" data-close-sheet>' + t('close') + '</button></div>';

        var o = document.createElement('div');
        o.className = 'ec-overlay';
        o.innerHTML = '<div class="ec-sheet"><div class="ec-sheet-handle"></div><div class="ec-sheet-body">' + imgH + '<h2 class="ec-sheet-title">' + esc(name) + '</h2>' + (subN ? '<span class="ec-sheet-badge">' + esc(subN) + '</span>' : '') + '<p class="ec-sheet-desc">' + esc(desc) + '</p>' + valH + mapH + actH + '</div></div>';
        document.body.appendChild(o);
        o.querySelectorAll('[data-close-sheet]').forEach(function(b){ b.addEventListener('click', function(){ o.remove(); }); });
        o.addEventListener('click', function(e){ if (e.target === o) o.remove(); });
        document.addEventListener('keydown', function escH(e){ if (e.key === 'Escape') { o.remove(); document.removeEventListener('keydown', escH); } });
        if (hasGps) {
            setTimeout(function() {
                var el = document.getElementById('ec-detail-map');
                if (!el || typeof L === 'undefined') return;
                var m = L.map(el, { zoomControl: false }).setView([card.latitude, card.longitude], 14);
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OSM' }).addTo(m);
                L.marker([card.latitude, card.longitude]).addTo(m);
                setTimeout(function(){ m.invalidateSize(); }, 100);
            }, 50);
        }
    }

    // ─── Helpers ───
    function getSubCatPoints(sub) { return (sub && sub.cards || []).filter(function(c){ return c.latitude && c.longitude; }); }
    function getAllPoints() {
        if (!corpusData) return [];
        var pts = [];
        corpusData.forEach(function(cat) { (cat['sub-categories'] || []).forEach(function(sub) { (sub.cards || []).forEach(function(c) { if (c.latitude && c.longitude) pts.push(c); }); }); });
        return pts;
    }
    function esc(s) { if (!s) return ''; var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

    // ─── Events ───
    function bindEvents() {
        var langToggle = document.getElementById('ec-lang-toggle');
        var langDropdown = document.getElementById('ec-lang-dropdown');
        if (langToggle && langDropdown) {
            langToggle.addEventListener('click', function(e) { e.stopPropagation(); langDropdown.classList.toggle('open'); });
            document.addEventListener('click', function() { langDropdown.classList.remove('open'); });
            langDropdown.querySelectorAll('[data-lang]').forEach(function(el) {
                el.addEventListener('click', function(e) { e.stopPropagation(); LANG = el.dataset.lang; langDropdown.classList.remove('open'); render(); });
            });
        }

        var startChat = document.getElementById('ec-start-chat');
        if (startChat) startChat.addEventListener('click', function() { activeTab = 'chatbot'; render(); });

        container.querySelectorAll('[data-suggest]').forEach(function(el) {
            el.addEventListener('click', function() { if (!suggests[+el.dataset.suggest]) return; activeTab = 'chatbot'; render(); });
        });

        container.querySelectorAll('[data-tab]').forEach(function(el) {
            el.addEventListener('click', function() {
                var tab = el.dataset.tab;
                if (tab === activeTab) return;
                activeTab = tab;
                if (tab === 'home') { currentView = 'categories'; selectedCategory = null; selectedSubCategory = null; showingMap = false; }
                if (tab !== 'map' && mapInstance) { mapInstance.remove(); mapInstance = null; }
                render();
            });
        });

        container.querySelectorAll('[data-category]').forEach(function(el) {
            el.addEventListener('click', function() { selectedCategory = corpusData[+el.dataset.category]; selectedSubCategory = null; currentView = 'subcategories'; showingMap = false; render(); });
        });
        container.querySelectorAll('[data-subcategory]').forEach(function(el) {
            el.addEventListener('click', function() { selectedSubCategory = selectedCategory['sub-categories'][+el.dataset.subcategory]; currentView = 'cards'; showingMap = false; render(); });
        });
        container.querySelectorAll('[data-card]').forEach(function(el) {
            el.addEventListener('click', function() { showDetail(selectedSubCategory.cards[+el.dataset.card]); });
        });
        container.querySelectorAll('[data-nav]').forEach(function(el) {
            el.addEventListener('click', function() {
                if (el.dataset.nav === 'categories') { currentView = 'categories'; selectedCategory = null; selectedSubCategory = null; }
                else if (el.dataset.nav === 'subcategories') { currentView = 'subcategories'; selectedSubCategory = null; }
                showingMap = false; render();
            });
        });
        container.querySelectorAll('[data-action="toggle-map"]').forEach(function(el) {
            el.addEventListener('click', function() {
                showingMap = !showingMap; render();
                if (showingMap) {
                    setTimeout(function() {
                        var el2 = document.getElementById('ec-map');
                        if (!el2 || typeof L === 'undefined') return;
                        var pts = getSubCatPoints(selectedSubCategory);
                        if (!pts.length) return;
                        var m = L.map(el2).setView([46.6, 1.9], 6);
                        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OSM' }).addTo(m);
                        var bounds = [];
                        pts.forEach(function(p) {
                            var mk = L.marker([p.latitude, p.longitude]).addTo(m);
                            mk.bindPopup('<strong>' + esc(tCardName(p)) + '</strong>');
                            mk.on('click', function(){ showDetail(p); });
                            bounds.push([p.latitude, p.longitude]);
                        });
                        bounds.length > 1 ? m.fitBounds(bounds, { padding: [30, 30] }) : m.setView(bounds[0], 13);
                        setTimeout(function(){ m.invalidateSize(); }, 100);
                    }, 50);
                }
            });
        });
    }

    // ─── Boot ───
    render();
})();
</script>