<?php
/**
 * ═══════════════════════════════════════════════════════════════
 * ELAIA CORPUS — Vue principale (app mobile-like)
 * ═══════════════════════════════════════════════════════════════
 *
 * Cette vue affiche le corpus du chatbot sous forme d'application
 * mobile avec navigation par onglets (Accueil, Carte, Conversations, Contact).
 *
 * Variables attendues (injectées par Corpus.php → elaia_prepare_corpus_payload()) :
 *   - $domain        : Domaine du chatbot (ex: "www.campasun.eu/international/le-camping")
 *   - $corpus        : Tableau des catégories/sous-catégories/cards du corpus
 *   - $chatbot_key   : UUID du chatbot (pour l'iframe conversationnel)
 *   - $agent_name    : Nom de l'agent affiché dans le header
 *   - $agent_picture : Chemin relatif de l'avatar de l'agent
 *   - $default_picture : Image par défaut pour les cards sans image
 *   - $hook_text     : Texte du badge d'accroche (sous le nom de l'agent)
 *   - $primary_color : Couleur principale du thème (#hex)
 *   - $suggests      : Tableau des suggestions de questions fréquentes
 *   - $chat_host     : URL du serveur chatbot (iframe)
 *   - $app_host      : URL du serveur API Elaia (images, assets)
 *   - $api_ok        : Bool — l'appel API a-t-il réussi ?
 *   - $api_code      : Int — code HTTP de la réponse API
 *   - $api_err       : String — message d'erreur si échec
 *
 * Préfixe CSS : ec- (Elaia Corpus)
 */

if (!defined('ABSPATH') || !defined('ELAIA_PLUGIN_DIR')) exit;

// ═══════════════════════════════════════════════════════════════
// VARIABLES PHP — Préparation pour le template
// ═══════════════════════════════════════════════════════════════

$flagsPath    = plugins_url('assets', dirname(__FILE__));   // Chemin vers les drapeaux SVG (basque, catalan)
$currentLang  = substr(get_locale(), 0, 2) ?: 'fr';         // Langue courante du site WP (2 lettres)
$accentColor  = esc_attr($primary_color);                    // Couleur d'accent échappée pour injection CSS
?>

<!-- ═══════════════════════════════════════════════════════════════
     DÉPENDANCES EXTERNES
     ═══════════════════════════════════════════════════════════════ -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
/* ═══════════════════════════════════════════════════════════════
   ELAIA CORPUS WEBAPP — Styles inline
   ═══════════════════════════════════════════════════════════════
   L'app est contrainte à 480px max (mobile-first).
   Toutes les couleurs dynamiques utilisent des CSS custom properties
   settées par le JS au runtime via applyThemeColors().
   ═══════════════════════════════════════════════════════════════ */
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap');

/* ─── Container principal ─── */
#elaia-corpus-app {
    --ec-bg-color: #ffffff;
    --ec-accent-color: <?php echo $accentColor; ?>;
    --ec-text-color: #212121;
    --ec-accent-light: <?php echo $accentColor; ?>1A;
    --ec-accent-medium: <?php echo $accentColor; ?>33;
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

/* ─── Barre de navigation inférieure (4 onglets) ─── */
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

/* ─── Zone scrollable (contenu principal) ─── */
.ec-scroll { flex: 1; overflow-y: auto; overflow-x: hidden; -webkit-overflow-scrolling: touch; }

/* ─── Header — Avatar, nom de l'agent, badge d'accroche ─── */
.ec-header { padding: 32px 24px 56px; text-align: center; position: relative; }
.ec-header-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
.ec-header-status { display: flex; align-items: center; gap: 6px; font-size: 12px; }
.ec-header-status-dot { width: 6px; height: 6px; border-radius: 50%; background: #4ade80; animation: ec-pulse 2s infinite; }
@keyframes ec-pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }

/* ─── Sélecteur de langue (dropdown) ─── */
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

/* ─── Drapeaux (emoji + images SVG pour basque/catalan) ─── */
.ec-flag-img {
    width: 22px; height: 16px; border-radius: 3px; display: inline-block;
    vertical-align: middle; object-fit: cover; box-shadow: 0 0 0 1px rgba(0,0,0,0.1);
}
.ec-lang-option .ec-flag-img { width: 20px; height: 14px; }

/* ─── Avatar de l'agent ─── */
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

/* ─── Bouton CTA "Commencer la discussion" (avec animation pulse) ─── */
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

/* ─── Suggestions de questions fréquentes (chips horizontaux scrollables) ─── */
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

/* ─── Zone de contenu + fil d'Ariane ─── */
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

/* ─── Liste des catégories (niveau 1 du corpus) ─── */
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

/* ─── Liste des sous-catégories (niveau 2) ─── */
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

/* ─── Cards (fiches individuelles du corpus) ─── */
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

/* ─── Bottom Sheet (détail d'une fiche, slide-up depuis le bas) ─── */
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

/* Lignes clé/valeur dans le détail */
.ec-detail-row { display: flex; gap: 12px; margin-bottom: 12px; align-items: flex-start; }
.ec-detail-icon { font-size: 18px; color: var(--ec-text-secondary); margin-top: 2px; flex-shrink: 0; }
.ec-detail-content { flex: 1; }
.ec-detail-label { font-size: 12px; color: #9e9e9e; font-weight: 500; margin: 0 0 2px; }
.ec-detail-value { font-size: 14px; color: var(--ec-text-color); margin: 0; word-break: break-word; }

/* Boutons d'action dans le bottom sheet */
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

/* Mini-carte dans le bottom sheet (détail d'une fiche géolocalisée) */
.ec-sheet-map { width: 100%; height: 180px; border-radius: 12px; overflow: hidden; margin-top: 16px; border: 1px solid var(--ec-border); }

/* ─── Carte plein écran (onglet Carte) ─── */
.ec-map-page { flex: 1; position: relative; }
.ec-map-page .ec-map-full { width: 100%; height: 100%; position: absolute; inset: 0; }

/* Bouton toggle carte dans la vue cards */
.ec-map-btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 18px; border-radius: 24px; border: none;
    background: white; color: var(--ec-text-secondary);
    font-size: 13px; font-weight: 600; cursor: pointer;
    box-shadow: var(--ec-shadow); transition: all 0.15s; font-family: inherit; margin-bottom: 16px;
}
.ec-map-btn:hover { box-shadow: var(--ec-shadow-lg); color: var(--ec-accent-color); }
.ec-map-btn.active { background: var(--ec-accent-color); color: white; }

/* ─── Iframe chatbot / contact ─── */
.ec-iframe-page { flex: 1; position: relative; overflow: hidden; }
.ec-iframe { width: 100%; height: 100%; position: absolute; inset: 0; border: none; background: white; }

/* ─── État placeholder (chatbot/contact non configuré) ─── */
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

/* ─── État de chargement ─── */
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

/* ─── Responsive (l'app reste centrée et compacte) ─── */
@media (min-width: 640px) { #elaia-corpus-app { max-width: 520px; } }
@media (min-width: 1024px) { #elaia-corpus-app { max-width: 480px; } }
</style>

<!-- ═══════════════════════════════════════════════════════════════
     GESTION DES ERREURS API
     ═══════════════════════════════════════════════════════════════ -->

<?php if (!$api_ok): ?>
  <div style="max-width:480px;margin:20px auto;padding:20px;background:#fef2f2;border:1px solid #fecaca;border-radius:12px;color:#b91c1c;">
    <strong>Erreur API :</strong> <?php echo esc_html($api_err); ?>
  </div>
<?php elseif ($api_code < 200 || $api_code >= 300): ?>
  <div style="max-width:480px;margin:20px auto;padding:20px;background:#fef2f2;border:1px solid #fecaca;border-radius:12px;color:#b91c1c;">
    <strong>Réponse API inattendue (code <?php echo (int)$api_code; ?>)</strong>
  </div>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════════
     CONTAINER PRINCIPAL
     Toute la config est passée au JS via data-config (JSON)
     ═══════════════════════════════════════════════════════════════ -->

<div id="elaia-corpus-app"
     data-config='<?php echo esc_attr(wp_json_encode([
         'domain'         => $domain,
         'lang'           => $currentLang,
         'flagsPath'      => $flagsPath,
         'chatHost'       => $chat_host,
         'appHost'        => $app_host,
         'chatbotKey'     => $chatbot_key,
         'agentName'      => $agent_name,
         'agentPicture'   => $agent_picture,
         'defaultPicture' => $default_picture,
         'hookText'       => $hook_text,
         'primaryColor'   => $primary_color,
         'corpus'         => $corpus,
         'suggests'       => $suggests,
     ])); ?>'>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     JAVASCRIPT — Application Corpus (SPA-like, rendu client)
     ═══════════════════════════════════════════════════════════════ -->

<script>
(function () {
    'use strict';

    // ═══════════════════════════════════════
    // INITIALISATION — Lecture de la config
    // ═══════════════════════════════════════

    var appContainer = document.getElementById('elaia-corpus-app');
    if (!appContainer) return;

    var config = JSON.parse(appContainer.dataset.config || '{}');

    // URLs des services externes
    var CHAT_HOST  = config.chatHost  || 'https://chatbot.ela-ia.com';
    var APP_HOST   = config.appHost   || 'https://app.ela-ia.com';
    var FLAGS_PATH = config.flagsPath || '';

    // Langue courante (modifiable par le sélecteur)
    var currentLang = config.lang || 'fr';

    // Couleurs du thème (modifiées dynamiquement)
    var themeColors = { bg: '#ffffff', accent: '#16a34a', text: '#212121' };

    // Données du corpus
    var corpusData     = config.corpus         || [];
    var chatbotKey     = config.chatbotKey      || null;
    var suggestsList   = config.suggests        || [];
    var agentName      = config.agentName       || '';
    var agentPicture   = config.agentPicture    || null;
    var defaultPicture = config.defaultPicture  || null;
    var hookText       = config.hookText        || '';

    // Appliquer la couleur primaire au thème
    if (config.primaryColor) {
        themeColors.bg     = config.primaryColor;
        themeColors.accent = config.primaryColor;
    }

    // ═══════════════════════════════════════
    // ÉTAT DE L'APPLICATION
    // ═══════════════════════════════════════

    var activeTab           = 'home';         // Onglet actif : home | map | chatbot | contact
    var currentView         = 'categories';   // Vue dans l'onglet home : categories | subcategories | cards
    var selectedCategory    = null;           // Catégorie sélectionnée (objet)
    var selectedSubCategory = null;           // Sous-catégorie sélectionnée (objet)
    var leafletMapInstance  = null;           // Instance Leaflet de la carte plein écran
    var isShowingMap        = false;          // Affichage carte dans la vue cards

    // ═══════════════════════════════════════
    // CONSTANTES — Icônes et locales
    // ═══════════════════════════════════════

    /** Correspondance Flutter icon name → emoji */
    var CATEGORY_ICONS = {
        'Icons.info': 'ℹ️', 'Icons.store': '🏢', 'Icons.shopping_basket': '🛒',
        'Icons.restaurant': '🍽️', 'Icons.directions_bus': '🗺️', 'Icons.hotel': '🏨',
        'Icons.pool': '🏊', 'Icons.sports': '⚽', 'Icons.local_activity': '🎯',
    };

    /** Langues disponibles dans le sélecteur (9 locales) */
    var AVAILABLE_LOCALES = [
        { code: 'fr',  flag: '🇫🇷', label: 'FR' },
        { code: 'en',  flag: '🇬🇧', label: 'EN' },
        { code: 'es',  flag: '🇪🇸', label: 'ES' },
        { code: 'de',  flag: '🇩🇪', label: 'DE' },
        { code: 'nl',  flag: '🇳🇱', label: 'NL' },
        { code: 'it',  flag: '🇮🇹', label: 'IT' },
        { code: 'pt',  flag: '🇵🇹', label: 'PT' },
        { code: 'eus', flag: '<img class="ec-flag-img" src="' + FLAGS_PATH + '/eus.svg" alt="EUS">', label: 'EUS' },
        { code: 'cat', flag: '<img class="ec-flag-img" src="' + FLAGS_PATH + '/cat.svg" alt="CAT">', label: 'CAT' },
    ];

    // ═══════════════════════════════════════
    // TRADUCTIONS UI (9 langues)
    // ═══════════════════════════════════════

    var UI_TRANSLATIONS = {
        fr:  { welcome:'Bienvenue !',online:'En ligne',frequent_questions:'💡 Questions fréquentes',home:'Accueil',map:'Carte',conversations:'Conversations',contact:'Contact',start_chat:'Commencer la discussion',see_cards:'Voir les fiches',see_on_map:'Voir sur la carte',see_more:'👁 En voir +',detailed_info:'Informations détaillées',directions:'🧭 Itinéraires',close:'Fermer',loading:'Chargement...',no_section:'Aucune rubrique.',no_card:'Aucune fiche.',card:'fiche',cards:'fiches',chatbot_unavailable:'Chatbot non disponible',chatbot_not_configured:'Le chatbot n\'est pas configuré pour ce site.',contact_unavailable:'Contact non disponible',contact_not_configured:'Les informations de contact ne sont pas configurées.',load_error:'Impossible de charger les données.' },
        en:  { welcome:'Welcome!',online:'Online',frequent_questions:'💡 Frequently asked questions',home:'Home',map:'Map',conversations:'Conversations',contact:'Contact',start_chat:'Start a conversation',see_cards:'View cards',see_on_map:'View on map',see_more:'👁 See more',detailed_info:'Detailed information',directions:'🧭 Directions',close:'Close',loading:'Loading...',no_section:'No section.',no_card:'No card.',card:'card',cards:'cards',chatbot_unavailable:'Chatbot unavailable',chatbot_not_configured:'The chatbot is not configured for this site.',contact_unavailable:'Contact unavailable',contact_not_configured:'Contact information is not configured.',load_error:'Unable to load data.' },
        es:  { welcome:'¡Bienvenido!',online:'En línea',frequent_questions:'💡 Preguntas frecuentes',home:'Inicio',map:'Mapa',conversations:'Conversaciones',contact:'Contacto',start_chat:'Iniciar conversación',see_cards:'Ver fichas',see_on_map:'Ver en el mapa',see_more:'👁 Ver más',detailed_info:'Información detallada',directions:'🧭 Cómo llegar',close:'Cerrar',loading:'Cargando...',no_section:'Sin sección.',no_card:'Sin ficha.',card:'ficha',cards:'fichas',chatbot_unavailable:'Chatbot no disponible',chatbot_not_configured:'El chatbot no está configurado para este sitio.',contact_unavailable:'Contacto no disponible',contact_not_configured:'La información de contacto no está configurada.',load_error:'No se pueden cargar los datos.' },
        de:  { welcome:'Willkommen!',online:'Online',frequent_questions:'💡 Häufige Fragen',home:'Startseite',map:'Karte',conversations:'Gespräche',contact:'Kontakt',start_chat:'Gespräch starten',see_cards:'Karten anzeigen',see_on_map:'Auf der Karte',see_more:'👁 Mehr sehen',detailed_info:'Detaillierte Informationen',directions:'🧭 Wegbeschreibung',close:'Schließen',loading:'Laden...',no_section:'Kein Bereich.',no_card:'Keine Karte.',card:'Karte',cards:'Karten',chatbot_unavailable:'Chatbot nicht verfügbar',chatbot_not_configured:'Der Chatbot ist für diese Seite nicht konfiguriert.',contact_unavailable:'Kontakt nicht verfügbar',contact_not_configured:'Kontaktinformationen sind nicht konfiguriert.',load_error:'Daten konnten nicht geladen werden.' },
        nl:  { welcome:'Welkom!',online:'Online',frequent_questions:'💡 Veelgestelde vragen',home:'Home',map:'Kaart',conversations:'Gesprekken',contact:'Contact',start_chat:'Start een gesprek',see_cards:'Bekijk kaarten',see_on_map:'Op de kaart',see_more:'👁 Meer zien',detailed_info:'Gedetailleerde informatie',directions:'🧭 Routebeschrijving',close:'Sluiten',loading:'Laden...',no_section:'Geen sectie.',no_card:'Geen kaart.',card:'kaart',cards:'kaarten',chatbot_unavailable:'Chatbot niet beschikbaar',chatbot_not_configured:'De chatbot is niet geconfigureerd voor deze site.',contact_unavailable:'Contact niet beschikbaar',contact_not_configured:'Contactinformatie is niet geconfigureerd.',load_error:'Kan gegevens niet laden.' },
        it:  { welcome:'Benvenuto!',online:'Online',frequent_questions:'💡 Domande frequenti',home:'Home',map:'Mappa',conversations:'Conversazioni',contact:'Contatto',start_chat:'Inizia una conversazione',see_cards:'Vedi schede',see_on_map:'Vedi sulla mappa',see_more:'👁 Vedi di più',detailed_info:'Informazioni dettagliate',directions:'🧭 Indicazioni',close:'Chiudi',loading:'Caricamento...',no_section:'Nessuna sezione.',no_card:'Nessuna scheda.',card:'scheda',cards:'schede',chatbot_unavailable:'Chatbot non disponibile',chatbot_not_configured:'Il chatbot non è configurato per questo sito.',contact_unavailable:'Contatto non disponibile',contact_not_configured:'Le informazioni di contatto non sono configurate.',load_error:'Impossibile caricare i dati.' },
        pt:  { welcome:'Bem-vindo!',online:'Online',frequent_questions:'💡 Perguntas frequentes',home:'Início',map:'Mapa',conversations:'Conversas',contact:'Contacto',start_chat:'Iniciar conversa',see_cards:'Ver fichas',see_on_map:'Ver no mapa',see_more:'👁 Ver mais',detailed_info:'Informações detalhadas',directions:'🧭 Direções',close:'Fechar',loading:'A carregar...',no_section:'Sem secção.',no_card:'Sem ficha.',card:'ficha',cards:'fichas',chatbot_unavailable:'Chatbot indisponível',chatbot_not_configured:'O chatbot não está configurado para este site.',contact_unavailable:'Contacto indisponível',contact_not_configured:'As informações de contacto não estão configuradas.',load_error:'Não foi possível carregar os dados.' },
        eus: { welcome:'Ongi etorri!',online:'Linean',frequent_questions:'💡 Maiz egiten diren galderak',home:'Hasiera',map:'Mapa',conversations:'Elkarrizketak',contact:'Kontaktua',start_chat:'Elkarrizketa hasi',see_cards:'Fitxak ikusi',see_on_map:'Mapan ikusi',see_more:'👁 Gehiago ikusi',detailed_info:'Informazio xehatua',directions:'🧭 Nola iritsi',close:'Itxi',loading:'Kargatzen...',no_section:'Atalik ez.',no_card:'Fitxarik ez.',card:'fitxa',cards:'fitxa',chatbot_unavailable:'Chatbot-a ez dago eskuragarri',chatbot_not_configured:'Chatbot-a ez dago gune honetarako konfiguratuta.',contact_unavailable:'Kontaktua ez dago eskuragarri',contact_not_configured:'Kontaktu informazioa ez dago konfiguratuta.',load_error:'Ezin izan dira datuak kargatu.' },
        cat: { welcome:'Benvingut!',online:'En línia',frequent_questions:'💡 Preguntes freqüents',home:'Inici',map:'Mapa',conversations:'Converses',contact:'Contacte',start_chat:'Iniciar conversa',see_cards:'Veure fitxes',see_on_map:'Veure al mapa',see_more:'👁 Veure més',detailed_info:'Informació detallada',directions:'🧭 Com arribar-hi',close:'Tancar',loading:'Carregant...',no_section:'Cap secció.',no_card:'Cap fitxa.',card:'fitxa',cards:'fitxes',chatbot_unavailable:'Chatbot no disponible',chatbot_not_configured:'El chatbot no està configurat per a aquest lloc.',contact_unavailable:'Contacte no disponible',contact_not_configured:'La informació de contacte no està configurada.',load_error:'No s\'han pogut carregar les dades.' },
    };

    /** Retourne la traduction d'une clé UI dans la langue courante (fallback: français) */
    function t(key) { return UI_TRANSLATIONS[currentLang]?.[key] || UI_TRANSLATIONS.fr[key] || key; }

    // ═══════════════════════════════════════
    // HELPERS DE TRADUCTION DU CORPUS
    // Les données du corpus ont des traductions
    // imbriquées dans chaque objet (cat, sub, card)
    // ═══════════════════════════════════════

    /** Nom traduit d'une catégorie */
    function translateCategoryName(category) { return (currentLang !== 'fr' && category.translations?.[currentLang]?.name) || category.category; }

    /** Description traduite d'une catégorie */
    function translateCategoryDesc(category) { return (currentLang !== 'fr' && category.translations?.[currentLang]?.description) || category.description || ''; }

    /** Nom traduit d'une sous-catégorie */
    function translateSubCategoryName(subCategory) { return (currentLang !== 'fr' && subCategory.translations?.[currentLang]?.name) || subCategory['sub-category']; }

    /** Description traduite d'une sous-catégorie */
    function translateSubCategoryDesc(subCategory) { return (currentLang !== 'fr' && subCategory.translations?.[currentLang]?.description) || subCategory.description || ''; }

    /** Nom traduit d'une card (fiche) */
    function translateCardName(card) {
        if (currentLang === 'fr') return card.name;
        return card.name_translations?.[currentLang] || card.translations?.[currentLang]?.name || card.name;
    }

    /** Description traduite d'une card */
    function translateCardDesc(card) { return (currentLang !== 'fr' && card.translations?.[currentLang]?.description) || card.description || ''; }

    /**
     * Retourne les valeurs clé/valeur d'une card traduites dans la langue courante
     * Gère le format { question: "...", answer: "..." } des traductions
     */
    function translateCardValues(card) {
        if (!card.values || typeof card.values !== 'object') return {};
        if (currentLang === 'fr' || !card.translations?.[currentLang]) return card.values;
        var langData = card.translations[currentLang], result = {};
        for (var key in card.values) {
            var translation = langData[key];
            if (translation && typeof translation === 'object' && translation.question) {
                result[translation.question] = translation.answer || card.values[key];
            } else if (typeof translation === 'string') {
                result[key] = translation;
            } else {
                result[key] = card.values[key];
            }
        }
        return result;
    }

    /** Titre traduit d'une suggestion */
    function translateSuggestTitle(suggest) { return (currentLang !== 'fr' && suggest.translations?.[currentLang]?.title) || suggest.title || ''; }

    // ═══════════════════════════════════════
    // COULEURS — Application dynamique du thème
    // ═══════════════════════════════════════

    /** Applique les couleurs du thème aux CSS custom properties du container */
    function applyThemeColors() {
        appContainer.style.setProperty('--ec-bg-color', themeColors.bg);
        appContainer.style.setProperty('--ec-accent-color', themeColors.accent);
        appContainer.style.setProperty('--ec-text-color', themeColors.text);
        appContainer.style.setProperty('--ec-accent-light', themeColors.accent + '1A');
        appContainer.style.setProperty('--ec-accent-medium', themeColors.accent + '33');
    }

    /** Calcule la couleur de texte contrastée (clair/sombre) pour un fond donné */
    function getContrastTextColor(hexColor) {
        var hex = hexColor.replace('#', '');
        var r = parseInt(hex.substring(0, 2), 16);
        var g = parseInt(hex.substring(2, 4), 16);
        var b = parseInt(hex.substring(4, 6), 16);
        return (0.299 * r + 0.587 * g + 0.114 * b) / 255 > 0.6 ? '#212121' : '#ffffff';
    }

    // ═══════════════════════════════════════
    // RENDU PRINCIPAL — Orchestrateur
    // ═══════════════════════════════════════

    /** Re-rend toute l'application selon l'état courant */
    function render() {
        applyThemeColors();

        // État de chargement
        if (corpusData === null) {
            appContainer.innerHTML = '<div class="ec-loading"><div class="ec-spinner"></div><span class="ec-loading-text">' + t('loading') + '</span></div>';
            return;
        }

        var html = '';
        switch (activeTab) {
            case 'home':    html += '<div class="ec-scroll">' + renderHomePage() + '</div>'; break;
            case 'map':     html += '<div class="ec-map-page"><div id="ec-map-full" class="ec-map-full"></div></div>'; break;
            case 'chatbot': html += renderChatbotPage(); break;
            case 'contact': html += renderContactPage(); break;
        }
        html += renderBottomNavbar();

        appContainer.innerHTML = html;
        bindAllEvents();

        if (activeTab === 'map') initializeFullScreenMap();
    }

    // ═══════════════════════════════════════
    // RENDU — Barre de navigation inférieure
    // ═══════════════════════════════════════

    /** Rend la barre de navigation avec les 4 onglets */
    function renderBottomNavbar() {
        var tabs = [
            { id: 'home',    icon: '🏠', label: t('home') },
            { id: 'map',     icon: '🗺️', label: t('map') },
            { id: 'chatbot', icon: '💬', label: t('conversations') },
            { id: 'contact', icon: '📞', label: t('contact') },
        ];
        return '<nav class="ec-navbar">' + tabs.map(function(tab) {
            return '<button class="ec-nav-item ' + (activeTab === tab.id ? 'active' : '') + '" data-tab="' + tab.id + '">'
                + '<span class="ec-nav-icon">' + tab.icon + '</span>'
                + '<span class="ec-nav-label">' + tab.label + '</span>'
                + '</button>';
        }).join('') + '</nav>';
    }

    // ═══════════════════════════════════════
    // RENDU — Page d'accueil (onglet Home)
    // ═══════════════════════════════════════

    /** Rend la page d'accueil complète : header + suggestions + navigation corpus */
    function renderHomePage() {
        var html = renderHeader();
        html += '<div class="ec-content">';

        // Suggestions de questions fréquentes
        if (suggestsList.length > 0) html += renderSuggestions();

        // Fil d'Ariane
        html += renderBreadcrumb();

        // Bouton toggle carte (visible uniquement si des points GPS existent dans la sous-catégorie)
        if (currentView === 'cards' && getGeolocatedCards(selectedSubCategory).length > 0) {
            html += '<button class="ec-map-btn ' + (isShowingMap ? 'active' : '') + '" data-action="toggle-map">📍 '
                + (isShowingMap ? t('see_cards') : t('see_on_map')) + '</button>';
        }

        // Contenu principal selon la vue
        if (isShowingMap) {
            html += '<div style="border-radius:16px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:16px;">'
                + '<div id="ec-map" style="width:100%;height:350px;"></div></div>';
        } else {
            switch (currentView) {
                case 'categories':    html += renderCategoryList(); break;
                case 'subcategories': html += renderSubCategoryList(); break;
                case 'cards':         html += renderCardList(); break;
            }
        }
        html += '</div>';
        return html;
    }

    /** Rend le header : avatar, nom de l'agent, statut en ligne, sélecteur de langue, CTA */
    function renderHeader() {
        var headerBg   = themeColors.bg;
        var headerText = getContrastTextColor(headerBg);
        var mutedColor = headerText === '#ffffff' ? 'rgba(255,255,255,0.7)' : 'rgba(0,0,0,0.5)';

        // Avatar de l'agent
        var avatarHtml = agentPicture
            ? '<img class="ec-header-avatar" src="' + APP_HOST + '/' + escapeHtml(agentPicture) + '" alt="" onerror="this.className=\'ec-header-avatar-placeholder\';this.textContent=\'👤\';">'
            : '<div class="ec-header-avatar-placeholder">👤</div>';

        // Locale courante pour le bouton langue
        var currentLocale = AVAILABLE_LOCALES.find(function(l) { return l.code === currentLang; }) || AVAILABLE_LOCALES[0];
        var title = agentName || t('welcome');

        return '<div class="ec-header" style="background:' + headerBg + ';color:' + headerText + '">'
            + '<div class="ec-header-top">'
            + '<div class="ec-header-status" style="color:' + mutedColor + '"><span class="ec-header-status-dot"></span> ' + t('online') + '</div>'
            // Sélecteur de langue
            + '<div class="ec-lang-switcher"><button class="ec-lang-btn" id="ec-lang-toggle">' + currentLocale.flag + '</button>'
            + '<div class="ec-lang-dropdown" id="ec-lang-dropdown">'
            + AVAILABLE_LOCALES.map(function(locale) {
                return '<button class="ec-lang-option ' + (locale.code === currentLang ? 'active' : '') + '" data-lang="' + locale.code + '">'
                    + '<span>' + locale.flag + '</span><span>' + locale.label + '</span></button>';
            }).join('')
            + '</div></div></div>'
            + avatarHtml
            + '<h1 class="ec-header-title" style="color:' + headerText + '">' + escapeHtml(title) + '</h1>'
            + (hookText ? '<span class="ec-header-badge">' + escapeHtml(hookText) + '</span>' : '')
            + '</div>'
            // Bouton CTA "Commencer la discussion"
            + '<div class="ec-cta-wrap"><button class="ec-start-chat-btn" id="ec-start-chat">'
            + '<span class="ec-start-chat-pulse"></span>'
            + '<span class="ec-start-chat-icon">💬</span>'
            + '<span class="ec-start-chat-text">' + t('start_chat') + '</span>'
            + '<span class="ec-start-chat-arrow">›</span>'
            + '</button></div>';
    }

    /** Rend les chips de suggestions de questions fréquentes (scroll horizontal) */
    function renderSuggestions() {
        return '<div class="ec-suggests"><p class="ec-suggests-label">' + t('frequent_questions') + '</p><div class="ec-suggests-scroll">'
            + suggestsList.map(function(suggest, index) {
                return '<button class="ec-suggest-chip" data-suggest="' + index + '">' + escapeHtml(translateSuggestTitle(suggest)) + '</button>';
            }).join('')
            + '</div></div>';
    }

    /** Rend le fil d'Ariane (breadcrumb) pour la navigation dans le corpus */
    function renderBreadcrumb() {
        if (currentView === 'categories') return '';
        var items = ['<button class="ec-crumb" data-nav="categories">' + t('home') + '</button>'];
        if (selectedCategory) {
            items.push('<span class="ec-crumb-sep">›</span>');
            items.push('<button class="ec-crumb ' + (currentView === 'subcategories' ? 'active' : '') + '" data-nav="subcategories">' + escapeHtml(translateCategoryName(selectedCategory)) + '</button>');
        }
        if (selectedSubCategory && currentView === 'cards') {
            items.push('<span class="ec-crumb-sep">›</span>');
            items.push('<span class="ec-crumb active">' + escapeHtml(translateSubCategoryName(selectedSubCategory)) + '</span>');
        }
        return '<nav class="ec-breadcrumb">' + items.join('') + '</nav>';
    }

    /** Rend la liste des catégories (niveau 1 du corpus) */
    function renderCategoryList() {
        if (!corpusData.length) {
            return '<div class="ec-placeholder-page" style="padding:40px 20px;text-align:center;">'
                + '<div class="ec-placeholder-icon">📋</div>'
                + '<p style="color:var(--ec-text-hint);font-size:14px;">' + t('no_section') + '</p>'
                + '</div>';
        }
        return '<div class="ec-category-list">' + corpusData.map(function(category, index) {
            return '<div class="ec-category-row" data-category="' + index + '">'
                + '<div class="ec-cat-icon">' + (CATEGORY_ICONS[category.icon] || '📁') + '</div>'
                + '<div class="ec-cat-info">'
                + '<p class="ec-cat-title">' + escapeHtml(translateCategoryName(category)) + '</p>'
                + '<p class="ec-cat-subtitle">' + escapeHtml(translateCategoryDesc(category)) + '</p>'
                + '</div><span class="ec-cat-chevron">›</span></div>';
        }).join('') + '</div>';
    }

    /** Rend la liste des sous-catégories (niveau 2, avec compteur de fiches) */
    function renderSubCategoryList() {
        if (!selectedCategory || !selectedCategory['sub-categories']) return '<p class="ec-error">' + t('no_section') + '</p>';
        return '<div class="ec-subcat-list">' + selectedCategory['sub-categories'].map(function(subCategory, index) {
            var cardCount = (subCategory.cards || []).length;
            return '<div class="ec-subcat-row" data-subcategory="' + index + '">'
                + '<div class="ec-subcat-icon">📋</div>'
                + '<div class="ec-subcat-info">'
                + '<p class="ec-subcat-name">' + escapeHtml(translateSubCategoryName(subCategory)) + '</p>'
                + '<p class="ec-subcat-desc">' + escapeHtml(translateSubCategoryDesc(subCategory)) + ' · ' + cardCount + ' ' + (cardCount > 1 ? t('cards') : t('card')) + '</p>'
                + '</div><span class="ec-subcat-chevron">›</span></div>';
        }).join('') + '</div>';
    }

    /** Rend la grille de cards (fiches individuelles avec image, nom, description) */
    function renderCardList() {
        if (!selectedSubCategory || !selectedSubCategory.cards) return '<p class="ec-error">' + t('no_card') + '</p>';
        return '<div class="ec-cards-grid">' + selectedSubCategory.cards.map(function(card, index) {
            var imageHtml = card.image
                ? '<img class="ec-card-img" src="' + escapeHtml(card.image) + '" alt="' + escapeHtml(translateCardName(card)) + '" loading="lazy" onerror="' + defaultPicture + '">'
                : '<img class="ec-card-img" src="' + defaultPicture + '" alt="' + escapeHtml(translateCardName(card)) + '" loading="lazy" onerror="' + defaultPicture + '">';
            return '<div class="ec-card" data-card="' + index + '">' + imageHtml
                + '<div class="ec-card-body">'
                + '<h4 class="ec-card-title">' + escapeHtml(translateCardName(card)) + '</h4>'
                + '<p class="ec-card-desc">' + escapeHtml(translateCardDesc(card)) + '</p>'
                + '<button class="ec-card-action">' + t('see_more') + '</button>'
                + '</div></div>';
        }).join('') + '</div>';
    }

    // ═══════════════════════════════════════
    // RENDU — Carte plein écran (onglet Map)
    // ═══════════════════════════════════════

    /** Initialise la carte Leaflet plein écran avec tous les points GPS du corpus */
    function initializeFullScreenMap() {
        setTimeout(function() {
            var mapElement = document.getElementById('ec-map-full');
            if (!mapElement || typeof L === 'undefined') return;

            // Nettoyer une éventuelle instance précédente
            if (leafletMapInstance) { leafletMapInstance.remove(); leafletMapInstance = null; }

            var allPoints = getAllGeolocatedCards();
            leafletMapInstance = L.map(mapElement).setView([46.6, 1.9], 6);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OSM' }).addTo(leafletMapInstance);

            if (!allPoints.length) return;

            var bounds = [];
            allPoints.forEach(function(card) {
                var marker = L.marker([card.latitude, card.longitude]).addTo(leafletMapInstance);
                marker.bindPopup('<strong>' + escapeHtml(translateCardName(card)) + '</strong><br>' + escapeHtml(translateCardDesc(card)));
                marker.on('click', function() { openDetailSheet(card); });
                bounds.push([card.latitude, card.longitude]);
            });

            // Ajuster le zoom pour voir tous les points
            bounds.length > 1
                ? leafletMapInstance.fitBounds(bounds, { padding: [30, 30] })
                : leafletMapInstance.setView(bounds[0], 13);

            setTimeout(function() { leafletMapInstance && leafletMapInstance.invalidateSize(); }, 100);
        }, 50);
    }

    // ═══════════════════════════════════════
    // RENDU — Chatbot & Contact (iframes)
    // ═══════════════════════════════════════

    /** Rend l'onglet Chatbot (iframe vers le chat Elaia, ou placeholder si non configuré) */
    function renderChatbotPage() {
        if (!chatbotKey) {
            return '<div class="ec-placeholder-page"><div class="ec-placeholder-icon">💬</div>'
                + '<h2 class="ec-placeholder-title">' + t('chatbot_unavailable') + '</h2>'
                + '<p class="ec-placeholder-desc">' + t('chatbot_not_configured') + '</p></div>';
        }
        return '<div class="ec-iframe-page"><iframe class="ec-iframe" src="' + CHAT_HOST + '/chat-plugin?k=' + encodeURIComponent(chatbotKey) + '&lang=' + currentLang + '" allow="microphone"></iframe></div>';
    }

    /** Rend l'onglet Contact (iframe vers le formulaire, ou placeholder si non configuré) */
    function renderContactPage() {
        if (!chatbotKey) {
            return '<div class="ec-placeholder-page"><div class="ec-placeholder-icon">📞</div>'
                + '<h2 class="ec-placeholder-title">' + t('contact_unavailable') + '</h2>'
                + '<p class="ec-placeholder-desc">' + t('contact_not_configured') + '</p></div>';
        }
        return '<div class="ec-iframe-page"><iframe class="ec-iframe" src="' + CHAT_HOST + '/contact-plugin?k=' + encodeURIComponent(chatbotKey) + '&lang=' + currentLang + '" allow="microphone"></iframe></div>';
    }

    // ═══════════════════════════════════════
    // BOTTOM SHEET — Détail d'une fiche
    // ═══════════════════════════════════════

    /**
     * Ouvre le bottom sheet de détail pour une card
     * Affiche : image, nom, description, valeurs clé/valeur, mini-carte GPS, bouton itinéraire
     */
    function openDetailSheet(card) {
        var cardName   = translateCardName(card);
        var cardDesc   = translateCardDesc(card);
        var cardValues = translateCardValues(card);

        // Image (optionnelle)
        var imageHtml = card.image
            ? '<img class="ec-sheet-img" src="' + escapeHtml(card.image) + '" alt="' + escapeHtml(cardName) + '" onerror="this.style.display=\'none\'">'
            : '';

        // Champs clé/valeur
        var valuesHtml = '';
        if (cardValues && typeof cardValues === 'object' && Object.keys(cardValues).length > 0) {
            valuesHtml = '<p class="ec-sheet-section-title">' + t('detailed_info') + '</p>';
            Object.entries(cardValues).forEach(function(entry) {
                valuesHtml += '<div class="ec-detail-row">'
                    + '<span class="ec-detail-icon">ℹ️</span>'
                    + '<div class="ec-detail-content">'
                    + '<p class="ec-detail-label">' + escapeHtml(entry[0]) + '</p>'
                    + '<p class="ec-detail-value">' + escapeHtml(String(entry[1])) + '</p>'
                    + '</div></div>';
            });
        }

        // Coordonnées GPS (pour mini-carte et bouton itinéraire)
        var hasGpsCoords    = card.latitude && card.longitude;
        var miniMapHtml     = hasGpsCoords ? '<div id="ec-detail-map" class="ec-sheet-map"></div>' : '';
        var subCategoryName = selectedSubCategory ? translateSubCategoryName(selectedSubCategory) : '';

        // Couleurs du bouton itinéraire
        var headerBg   = themeColors.bg;
        var headerText = getContrastTextColor(headerBg);

        // Boutons d'action
        var actionsHtml = hasGpsCoords
            ? '<div class="ec-sheet-actions">'
                + '<a class="ec-btn ec-btn-primary" style="background:' + headerBg + ';color:' + headerText + '" href="https://www.google.com/maps/dir/?api=1&destination=' + card.latitude + ',' + card.longitude + '" target="_blank" rel="noopener">' + t('directions') + '</a>'
                + '<button class="ec-btn ec-btn-secondary" data-close-sheet>' + t('close') + '</button></div>'
            : '<div class="ec-sheet-actions"><button class="ec-btn ec-btn-full" data-close-sheet style="background:gray;color:black">' + t('close') + '</button></div>';

        // Assemblage et injection du bottom sheet
        var overlay = document.createElement('div');
        overlay.className = 'ec-overlay';
        overlay.innerHTML = '<div class="ec-sheet"><div class="ec-sheet-handle"></div><div class="ec-sheet-body">'
            + imageHtml
            + '<h2 class="ec-sheet-title">' + escapeHtml(cardName) + '</h2>'
            + (subCategoryName ? '<span class="ec-sheet-badge">' + escapeHtml(subCategoryName) + '</span>' : '')
            + '<p class="ec-sheet-desc">' + escapeHtml(cardDesc) + '</p>'
            + valuesHtml + miniMapHtml + actionsHtml
            + '</div></div>';

        document.body.appendChild(overlay);

        // Fermeture : boutons, clic overlay, touche Escape
        overlay.querySelectorAll('[data-close-sheet]').forEach(function(btn) {
            btn.addEventListener('click', function() { overlay.remove(); });
        });
        overlay.addEventListener('click', function(e) { if (e.target === overlay) overlay.remove(); });
        document.addEventListener('keydown', function escapeHandler(e) {
            if (e.key === 'Escape') { overlay.remove(); document.removeEventListener('keydown', escapeHandler); }
        });

        // Mini-carte Leaflet dans le bottom sheet
        if (hasGpsCoords) {
            setTimeout(function() {
                var miniMapElement = document.getElementById('ec-detail-map');
                if (!miniMapElement || typeof L === 'undefined') return;
                var miniMap = L.map(miniMapElement, { zoomControl: false }).setView([card.latitude, card.longitude], 14);
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OSM' }).addTo(miniMap);
                L.marker([card.latitude, card.longitude]).addTo(miniMap);
                setTimeout(function() { miniMap.invalidateSize(); }, 100);
            }, 50);
        }
    }

    // ═══════════════════════════════════════
    // HELPERS — Données géolocalisées
    // ═══════════════════════════════════════

    /** Retourne les cards géolocalisées d'une sous-catégorie */
    function getGeolocatedCards(subCategory) {
        return (subCategory && subCategory.cards || []).filter(function(card) {
            return card.latitude && card.longitude;
        });
    }

    /** Retourne toutes les cards géolocalisées de tout le corpus */
    function getAllGeolocatedCards() {
        if (!corpusData) return [];
        var points = [];
        corpusData.forEach(function(category) {
            (category['sub-categories'] || []).forEach(function(subCategory) {
                (subCategory.cards || []).forEach(function(card) {
                    if (card.latitude && card.longitude) points.push(card);
                });
            });
        });
        return points;
    }

    /** Échappe le HTML pour éviter les injections XSS */
    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // ═══════════════════════════════════════
    // ÉVÉNEMENTS — Binding de tous les handlers
    // ═══════════════════════════════════════

    /** Attache tous les event listeners après chaque render() */
    function bindAllEvents() {

        // ─── Sélecteur de langue ───
        var langToggleBtn = document.getElementById('ec-lang-toggle');
        var langDropdown  = document.getElementById('ec-lang-dropdown');
        if (langToggleBtn && langDropdown) {
            langToggleBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                langDropdown.classList.toggle('open');
            });
            document.addEventListener('click', function() { langDropdown.classList.remove('open'); });
            langDropdown.querySelectorAll('[data-lang]').forEach(function(option) {
                option.addEventListener('click', function(e) {
                    e.stopPropagation();
                    currentLang = option.dataset.lang;
                    langDropdown.classList.remove('open');
                    render();
                });
            });
        }

        // ─── Bouton CTA "Commencer la discussion" ───
        var startChatBtn = document.getElementById('ec-start-chat');
        if (startChatBtn) {
            startChatBtn.addEventListener('click', function() { activeTab = 'chatbot'; render(); });
        }

        // ─── Chips de suggestions → ouvrir le chatbot ───
        appContainer.querySelectorAll('[data-suggest]').forEach(function(chip) {
            chip.addEventListener('click', function() {
                if (!suggestsList[+chip.dataset.suggest]) return;
                activeTab = 'chatbot';
                render();
            });
        });

        // ─── Onglets de la navbar ───
        appContainer.querySelectorAll('[data-tab]').forEach(function(tabBtn) {
            tabBtn.addEventListener('click', function() {
                var tab = tabBtn.dataset.tab;
                if (tab === activeTab) return;
                activeTab = tab;
                // Reset de la navigation si retour à l'accueil
                if (tab === 'home') {
                    currentView = 'categories';
                    selectedCategory = null;
                    selectedSubCategory = null;
                    isShowingMap = false;
                }
                // Nettoyer la carte si on quitte l'onglet carte
                if (tab !== 'map' && leafletMapInstance) {
                    leafletMapInstance.remove();
                    leafletMapInstance = null;
                }
                render();
            });
        });

        // ─── Navigation dans le corpus : catégories → sous-catégories → cards ───
        appContainer.querySelectorAll('[data-category]').forEach(function(row) {
            row.addEventListener('click', function() {
                selectedCategory = corpusData[+row.dataset.category];
                selectedSubCategory = null;
                currentView = 'subcategories';
                isShowingMap = false;
                render();
            });
        });

        appContainer.querySelectorAll('[data-subcategory]').forEach(function(row) {
            row.addEventListener('click', function() {
                selectedSubCategory = selectedCategory['sub-categories'][+row.dataset.subcategory];
                currentView = 'cards';
                isShowingMap = false;
                render();
            });
        });

        // ─── Clic sur une card → ouvrir le bottom sheet ───
        appContainer.querySelectorAll('[data-card]').forEach(function(cardElement) {
            cardElement.addEventListener('click', function() {
                openDetailSheet(selectedSubCategory.cards[+cardElement.dataset.card]);
            });
        });

        // ─── Fil d'Ariane (navigation retour) ───
        appContainer.querySelectorAll('[data-nav]').forEach(function(crumb) {
            crumb.addEventListener('click', function() {
                if (crumb.dataset.nav === 'categories') {
                    currentView = 'categories';
                    selectedCategory = null;
                    selectedSubCategory = null;
                } else if (crumb.dataset.nav === 'subcategories') {
                    currentView = 'subcategories';
                    selectedSubCategory = null;
                }
                isShowingMap = false;
                render();
            });
        });

        // ─── Toggle carte dans la vue cards ───
        appContainer.querySelectorAll('[data-action="toggle-map"]').forEach(function(toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                isShowingMap = !isShowingMap;
                render();

                // Initialiser la mini-carte Leaflet après le render
                if (isShowingMap) {
                    setTimeout(function() {
                        var mapElement = document.getElementById('ec-map');
                        if (!mapElement || typeof L === 'undefined') return;
                        var geoCards = getGeolocatedCards(selectedSubCategory);
                        if (!geoCards.length) return;

                        var miniMap = L.map(mapElement).setView([46.6, 1.9], 6);
                        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OSM' }).addTo(miniMap);

                        var bounds = [];
                        geoCards.forEach(function(card) {
                            var marker = L.marker([card.latitude, card.longitude]).addTo(miniMap);
                            marker.bindPopup('<strong>' + escapeHtml(translateCardName(card)) + '</strong>');
                            marker.on('click', function() { openDetailSheet(card); });
                            bounds.push([card.latitude, card.longitude]);
                        });

                        bounds.length > 1
                            ? miniMap.fitBounds(bounds, { padding: [30, 30] })
                            : miniMap.setView(bounds[0], 13);

                        setTimeout(function() { miniMap.invalidateSize(); }, 100);
                    }, 50);
                }
            });
        });
    }

    // ═══════════════════════════════════════
    // BOOT — Lancement de l'application
    // ═══════════════════════════════════════

    render();
})();
</script>