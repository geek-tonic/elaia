<?php if (!defined('ABSPATH') || !defined('ELAIA_PLUGIN_DIR')) exit; ?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" data-no-optimize="1">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" data-no-optimize="1" data-no-minify="1" data-cfasync="false"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<?php
// ═══════════════════════════════════════════════════════════════
// VARIABLES DE STYLE
// ═══════════════════════════════════════════════════════════════

$primaryColor      = $style['primary_color'] ?? '#3b82f6';
$primaryColorLight = $primaryColor . '18'; // Variante transparente pour hover/focus
?>

<style>
  /* ═══════════════════════════════════════
     BASE — Layout principal et typographie
     ═══════════════════════════════════════ */
  .em-wrap {
    /* On force le plugin à créer son propre contexte d'empilement indépendant */
    position: relative !important;
    z-index: 10 !important;
    /* Doit être supérieur aux autres éléments du site */
    isolation: isolate;
    /* Empêche les styles extérieurs de se mélanger aux z-index internes */
    /* height: 100vh; */
    display: flex;
    flex-direction: column;
    /* overflow: hidden; */
  }

  .em-wrap {
    font-family: 'Inter', -apple-system, sans-serif !important;
    color: #0f172a;
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 24px;
    -webkit-font-smoothing: antialiased;
  }

  .em-wrap * {
    box-sizing: border-box;
  }

  /* ─── Header — Titre + barre de recherche ─── */
  .em-header {
    padding: 40px 0 24px;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    flex-shrink: 0;
    /* le header ne se compresse pas */
  }

  .em-header-title {
    font-size: 24px !important;
    font-weight: 800 !important;
    margin: 0 !important;
    color: #0f172a;
  }

  .em-header-sub {
    font-size: 14px;
    color: #64748b;
    margin: 4px 0 0;
  }

  /* ─── Recherche — Champ avec icône loupe ─── */
  .em-search-wrap {
    position: relative;
  }

  .em-sidebar .em-search-wrap {
    margin-top: 16px;
  }

  .em-search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    color: #94a3b8;
    pointer-events: none;
  }

  .em-search {
    width: 100% !important;
    padding: 10px 16px 10px 36px !important;
    font-size: 14px !important;
    font-family: inherit;
    border: 1px solid #e2e8f0 !important;
    border-radius: 12px !important;
    background: #fff !important;
    color: #0f172a;
    outline: none;
  }

  .em-search:focus {
    border-color: <?php echo $primaryColor; ?> !important;
    box-shadow: 0 0 0 3px <?php echo $primaryColorLight; ?> !important;
  }

  .em-search::placeholder {
    color: #94a3b8;
  }

  .em-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 24px;
    /* min-height: 0; */
    /* ou hauteur fixe */
    overflow: hidden;
    /* empêche le débordement */
  }

  /* ─── Onglets — Filtrage par catégorie ─── */
  /* --em-header-offset est calculé au runtime par le JS (hauteur d'un header sticky/fixé du thème, sinon 0) */
  .em-tabs {
    display: flex !important;
    flex-wrap: wrap;
    gap: 8px;
    /* margin-bottom: 24px; */
    /* retire position: sticky, c'est inutile ici */
    position: static;
    flex-shrink: 0;
    top: calc(var(--em-header-offset, 0px) + 24px);
    z-index: 5;
    /* padding: 20px;
    background: #fff; */
    flex-shrink: 0;
  }

  /* Pseudos qui prolongent le fond blanc au-dessus (cache le gap top:0→top sticky) et en-dessous (conserve l'écart visuel avec le contenu pendant le scroll) */
  /* .em-tabs::before {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    bottom: 100%;
    height: 24px;
    z-index: 5;
    background: inherit;
  }

  .em-tabs::after {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    top: 100%;
    height: 24px;
    z-index: 5;
    background: inherit;
  } */

  /* Overlay opaque sous le header du thème, affiché uniquement pendant le scroll — masque les cards qui défilent à travers un header semi-transparent */
  #em-header-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: var(--em-header-offset, 0px);
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.15s;
  }

  html.em-scrolled #em-header-overlay {
    opacity: 1;
  }

  .em-tab {
    flex-shrink: 0;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #64748b;
    transition: all 0.15s;
    font-family: inherit;
    white-space: nowrap;
  }

  .em-tab:hover {
    border-color: #cbd5e1;
  }

  .em-tab.active {
    background: <?php echo $primaryColor; ?> !important;
    color: #fff !important;
    border-color: <?php echo $primaryColor; ?> !important;
  }

  .em-tab-count {
    margin-left: 6px;
    font-size: 12px;
    opacity: 0.7;
  }

  .em-main-body {
    display: flex;
    flex-direction: column;
    /* flex: 1; */
    overflow-y: auto;
    /* le scroll est ici, pas sur le body global */
    /* min-height: 0; */
    /* crucial en flexbox */
    gap: 24px;
  }


  /* ═══════════════════════════════════════
     CARTE — Leaflet + légende + marqueurs
     ═══════════════════════════════════════ */
  .em-map-section {
    /* margin-bottom: 24px; */
    isolation: isolate;
  }

  .em-map-section--hidden {
    display: none;
  }

  .em-map-wrap {
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  }

  .em-map {
    height: 420px;
    width: 100%;
  }

  /* Personnalisation des popups Leaflet */
  .em-map .leaflet-popup-content-wrapper {
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    padding: 0;
    overflow: hidden;
  }

  .em-map .leaflet-popup-content {
    margin: 0;
    min-width: 200px;
  }

  .em-map .leaflet-popup-tip {
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  }

  .em-map-popup {
    padding: 12px 14px;
  }

  .em-map-popup-name {
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 4px;
    line-height: 1.3;
  }

  .em-map-popup-cat {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin: 0 0 8px;
  }

  .em-map-popup-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    font-weight: 600;
    color: <?php echo $primaryColor; ?>;
    cursor: pointer;
    background: none;
    border: none;
    padding: 0;
    font-family: inherit;
  }

  .em-map-popup-btn:hover {
    text-decoration: underline;
  }

  /* Légende de la carte */
  .em-map-legend {
    display: flex;
    gap: 16px;
    padding: 10px 16px;
    background: #fff;
    border-top: 1px solid #e2e8f0;
    flex-wrap: wrap;
  }

  .em-map-legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
    transition: opacity 0.15s;
  }

  .em-map-legend-item:hover {
    opacity: 0.7;
  }

  .em-map-legend-item--off {
    opacity: 0.3;
  }

  .em-map-legend-item--off .em-map-legend-dot {
    background: #cbd5e1 !important;
    box-shadow: none !important;
  }

  .em-map-legend-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
    box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.08);
  }

  /* Bouton toggle carte */
  .em-map-toggle {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #64748b;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
    transition: all 0.15s;
    margin-bottom: 16px;
  }

  .em-map-toggle:hover {
    border-color: <?php echo $primaryColor; ?>;
    color: <?php echo $primaryColor; ?>;
  }

  .em-map-toggle.active {
    background: <?php echo $primaryColor; ?>;
    color: #fff;
    border-color: <?php echo $primaryColor; ?>;
  }

  /* Quand le bouton Carte est placé dans la barre des onglets, alignement avec .em-tab */
  .em-tabs .em-map-toggle {
    font-size: 14px;
    padding: 8px 16px;
    margin-bottom: 0;
    flex-shrink: 0;
  }

  /* Marqueurs personnalisés SVG */
  .em-marker {
    transition: transform 0.15s ease;
  }

  .em-marker:hover {
    transform: scale(1.3);
    z-index: 1000 !important;
  }

  .em-marker--active {
    transform: scale(1.4);
    z-index: 1000 !important;
  }

  /* ═══════════════════════════════════════
     LAYOUT — Sidebar filtres + grille cards
     ═══════════════════════════════════════ */
  .em-layout {
    display: flex;
    gap: 24px;
    /* flex: 1; */
    /* min-height: 0; */
    /* crucial */
    /* overflow: hidden; */
    height: calc(100vh - var(--em-header-offset, 0px) - 24px);
    position: sticky;
    top: calc(var(--em-header-offset, 0px) + 24px);
    overflow: hidden;
  }

  .em-sidebar {
    flex: 0 0 260px;
    /* position: sticky; */
    /* top: calc(var(--em-header-offset, 0px) + 24px); */
    height: fit-content;
  }


  /* ─── Filtres latéraux ─── */
  .em-filters {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  }

  .em-filters-title {
    font-size: 15px !important;
    font-weight: 700 !important;
    margin: 0 0 16px !important;
    color: #0f172a;
  }

  .em-filter-group {
    margin-bottom: 18px;
  }

  .em-filter-group:last-child {
    margin-bottom: 0;
  }

  .em-filter-label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
  }

  .em-filter-check {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 5px 0;
    cursor: pointer;
  }

  .em-filter-check input[type="checkbox"] {
    cursor: pointer;
    accent-color: <?php echo $primaryColor; ?>;
    width: 15px;
    height: 15px;
  }

  .em-filter-check span {
    font-size: 13px;
    color: #64748b;
    font-weight: 500;
  }

  .em-filter-reset {
    width: 100%;
    padding: 10px;
    background: #0f172a;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
    margin-top: 16px;
  }

  .em-filter-reset:hover {
    background: #000;
  }

  /* ─── Compteurs ─── */
  .em-stats {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
  }

  .em-stat {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px 14px;
    font-size: 13px;
    color: #64748b;
  }

  .em-stat strong {
    color: #0f172a;
    font-weight: 700;
    margin-left: 4px;
  }

  /* ═══════════════════════════════════════
     CARDS — Grille de fiches métadonnées
     ═══════════════════════════════════════ */
  .em-cards {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)) !important;
    gap: 20px !important;
  }

  .em-card {
    background: #fff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 12px !important;
    overflow: hidden !important;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    display: block !important;
  }

  .em-card.em-hidden {
    display: none !important;
  }

  .em-card:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.07) !important;
    transform: translateY(-2px);
    border-color: <?php echo $primaryColor; ?> !important;
  }

  .em-card--highlight {
    box-shadow: 0 0 0 3px <?php echo $primaryColor; ?> !important;
  }

  /* Image de la card */
  .em-card-img-wrap {
    position: relative;
  }

  .em-card-img {
    width: 100% !important;
    height: 160px !important;
    object-fit: cover !important;
    display: block !important;
    background: #f1f5f9;
  }

  .em-card-placeholder {
    width: 100%;
    height: 160px;
    background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    font-size: 14px;
    font-weight: 500;
  }

  .em-card-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    backdrop-filter: blur(8px);
  }

  /* Badges de catégorie (couleurs par type) */
  .badge-accom {
    background: rgba(16, 185, 129, 0.9);
    color: #fff;
  }

  .badge-city {
    background: rgba(59, 130, 246, 0.9);
    color: #fff;
  }

  .badge-activity {
    background: rgba(168, 85, 247, 0.9);
    color: #fff;
  }

  .badge-event {
    background: rgba(239, 68, 68, 0.9);
    color: #fff;
  }

  .badge-restaurant {
    background: rgba(245, 158, 11, 0.9);
    color: #fff;
  }

  .badge-service {
    background: rgba(99, 102, 241, 0.9);
    color: #fff;
  }

  .badge-default {
    background: rgba(100, 116, 139, 0.9);
    color: #fff;
  }

  /* Contenu de la card */
  .em-card-body {
    padding: 16px;
  }

  .em-card-title {
    font-size: 15px !important;
    font-weight: 700 !important;
    margin: 0 0 8px !important;
    color: #0f172a;
    line-height: 1.3;
  }

  .em-card-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-bottom: 10px;
  }

  .em-tag {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 3px 8px;
    border-radius: 5px;
    font-size: 11px;
    font-weight: 600;
    background: #f1f5f9;
    color: #64748b;
  }

  .em-tag-blue {
    background: #dbeafe;
    color: #1e40af;
  }

  .em-tag-green {
    background: #d1fae5;
    color: #065f46;
  }

  .em-tag-orange {
    background: #fed7aa;
    color: #92400e;
  }

  .em-tag-purple {
    background: #ede9fe;
    color: #6b21a8;
  }

  /* Champs résumés visibles sur la card */
  .em-card-fields {
    margin-bottom: 10px;
  }

  .em-card-field {
    display: flex;
    gap: 6px;
    font-size: 12px;
    margin-bottom: 4px;
  }

  .em-card-field-label {
    color: #94a3b8;
    flex-shrink: 0;
  }

  .em-card-field-value {
    color: #0f172a;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  /* Pied de card */
  .em-card-footer {
    padding-top: 10px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    font-weight: 600;
    color: <?php echo $primaryColor; ?>;
  }

  .em-card-footer svg {
    width: 14px;
    height: 14px;
    transition: transform 0.15s;
  }

  .em-card:hover .em-card-footer svg {
    transform: translateX(2px);
  }

  /* État vide */
  .em-empty {
    text-align: center;
    padding: 60px 20px;
    color: #64748b;
  }

  /* ═══════════════════════════════════════
     MODAL — Détail d'une fiche
     ═══════════════════════════════════════ */
  .em-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    animation: emFadeIn 0.2s ease;
  }

  @keyframes emFadeIn {
    from {
      opacity: 0
    }

    to {
      opacity: 1
    }
  }

  .em-modal {
    background: #fff;
    border-radius: 16px;
    max-width: 640px;
    width: 100%;
    max-height: 85vh;
    overflow: hidden;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
    display: flex;
    flex-direction: column;
    animation: emSlideUp 0.25s ease;
  }

  @keyframes emSlideUp {
    from {
      opacity: 0;
      transform: translateY(20px)
    }

    to {
      opacity: 1;
      transform: translateY(0)
    }
  }

  /* Modal avec image */
  .em-modal-img-wrap {
    position: relative;
    flex-shrink: 0;
  }

  .em-modal-img {
    width: 100%;
    height: 220px;
    object-fit: cover;
    display: block;
  }

  .em-modal-img-gradient {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.4), transparent);
  }

  .em-modal-img-info {
    position: absolute;
    bottom: 16px;
    left: 20px;
    right: 20px;
  }

  .em-modal-img-cat {
    font-size: 12px;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.8);
    margin: 0 0 4px;
  }

  .em-modal-img-title {
    font-size: 20px;
    font-weight: 800;
    color: #fff;
    margin: 0;
  }

  .em-modal-close {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.4);
    color: #fff;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
  }

  .em-modal-close:hover {
    background: rgba(0, 0, 0, 0.6);
  }

  /* Modal sans image */
  .em-modal-header-noimg {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid #e2e8f0;
  }

  .em-modal-header-noimg-cat {
    font-size: 12px;
    color: #94a3b8;
    margin: 0 0 2px;
  }

  .em-modal-header-noimg-title {
    font-size: 18px;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
  }

  .em-modal-close-noimg {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    color: #94a3b8;
    background: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
  }

  .em-modal-close-noimg:hover {
    background: #f1f5f9;
    color: #0f172a;
  }

  /* Corps et entrées de la modal */
  .em-modal-body {
    padding: 20px 24px;
    overflow-y: auto;
    flex: 1;
  }

  .em-modal-entry {
    margin-bottom: 16px;
  }

  .em-modal-entry-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #94a3b8;
    margin: 0 0 4px;
  }

  .em-modal-entry-value {
    font-size: 14px;
    color: #0f172a;
    margin: 0;
    line-height: 1.5;
    white-space: pre-line;
  }

  /* Pied de modal */
  .em-modal-footer {
    padding: 16px 24px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .em-modal-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    font-weight: 600;
    color: <?php echo $primaryColor; ?>;
    text-decoration: none;
  }

  .em-modal-link:hover {
    opacity: 0.8;
  }

  .em-modal-close-btn {
    padding: 8px 16px;
    font-size: 14px;
    font-weight: 500;
    color: #64748b;
    background: none;
    border: none;
    cursor: pointer;
    font-family: inherit;
  }

  .em-modal-close-btn:hover {
    color: #0f172a;
  }

  /* ═══════════════════════════════════════
     RESPONSIVE
     ═══════════════════════════════════════ */
  @media (max-width: 1024px) {
    .em-layout {
      flex-direction: column;
    }

    .em-sidebar {
      position: static;
      flex-basis: auto;
    }
  }

  @media (max-width: 720px) {
    .em-wrap {
      padding: 0 12px;
    }

    .em-header {
      flex-direction: column;
      align-items: stretch;
      padding: 24px 0 16px;
      gap: 12px;
    }

    .em-header-title {
      font-size: 20px !important;
    }

    .em-search-wrap {
      width: 100%;
    }

    .em-tabs {
      gap: 6px;
      margin-bottom: 16px;
    }

    .em-tab {
      padding: 6px 12px;
      font-size: 13px;
    }

    .em-tabs .em-map-toggle {
      padding: 6px 12px;
      font-size: 13px;
    }

    /* Carte mobile */
    .em-map {
      height: 280px;
    }

    .em-map-wrap {
      border-radius: 12px;
    }

    .em-map-legend {
      gap: 10px;
      padding: 8px 12px;
    }

    .em-map-legend-item {
      font-size: 11px;
    }

    /* Sidebar collapsible sur mobile */
    .em-sidebar {
      order: -1;
    }

    .em-filters {
      padding: 14px;
    }

    .em-filters-title {
      font-size: 14px !important;
      margin-bottom: 0 !important;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .em-filters-title::after {
      content: '▾';
      font-size: 12px;
      color: #94a3b8;
      transition: transform 0.2s;
    }

    .em-filters.collapsed .em-filters-title::after {
      transform: rotate(-90deg);
    }

    .em-filters.collapsed .em-filter-group,
    .em-filters.collapsed .em-filter-reset {
      display: none;
    }

    /* Cards mobile */
    .em-cards {
      grid-template-columns: 1fr !important;
      gap: 14px !important;
    }

    .em-card-img,
    .em-card-placeholder {
      height: 140px !important;
    }

    .em-card-body {
      padding: 12px;
    }

    .em-card-title {
      font-size: 14px !important;
    }

    .em-card-tags {
      gap: 4px;
    }

    .em-tag {
      font-size: 10px;
      padding: 2px 6px;
    }

    /* Compteurs compacts */
    .em-stats {
      gap: 8px;
      margin-bottom: 14px;
    }

    .em-stat {
      padding: 6px 10px;
      font-size: 12px;
    }

    /* Modal → bottom sheet sur mobile */
    .em-modal-overlay {
      align-items: flex-end;
      padding: 0;
    }

    .em-modal {
      border-radius: 16px 16px 0 0;
      max-height: 90vh;
      max-width: 100%;
      animation: emSlideUpMobile 0.3s cubic-bezier(0.22, 1, 0.36, 1);
    }

    @keyframes emSlideUpMobile {
      from {
        transform: translateY(100%);
      }

      to {
        transform: translateY(0);
      }
    }

    .em-modal-img {
      height: 180px;
    }

    .em-modal-body {
      padding: 16px;
    }

    .em-modal-footer {
      padding: 12px 16px;
    }

    .em-modal-entry-value {
      font-size: 13px;
    }
  }

  @media (max-width: 380px) {
    .em-map {
      height: 220px;
    }

    .em-card-img,
    .em-card-placeholder {
      height: 120px !important;
    }

    .em-card-fields {
      display: none;
    }
  }
</style>

<?php
// ═══════════════════════════════════════════════════════════════
// GESTION DES ERREURS API
// ═══════════════════════════════════════════════════════════════
?>

<?php if (!$api_ok): ?>
  <div style="max-width:1200px;margin:20px auto;padding:20px;background:#fef2f2;border:1px solid #fecaca;border-radius:12px;color:#b91c1c;">
    <strong>Erreur réseau API :</strong> <?php echo esc_html($api_err); ?>
  </div>
<?php elseif ($api_code < 200 || $api_code >= 300): ?>
  <div style="max-width:1200px;margin:20px auto;padding:20px;background:#fef2f2;border:1px solid #fecaca;border-radius:12px;color:#b91c1c;">
    <strong>Réponse API inattendue (code <?php echo (int)$api_code; ?>)</strong>
  </div>
<?php endif; ?>

<?php
// ═══════════════════════════════════════════════════════════════
// INJECTION DES DONNÉES STRUCTURÉES (Schema.org)
// ═══════════════════════════════════════════════════════════════

if (is_array($geoMetadatas) && !empty($geoMetadatas)) {
  echo '<script type="application/ld+json">' . wp_json_encode($geoMetadatas, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
}

// ═══════════════════════════════════════════════════════════════
// LABELS DES CHAMPS (traductions des clés API → labels lisibles)
// ═══════════════════════════════════════════════════════════════

$fieldLabels = [];
if (is_array($payload) && !empty($payload['field_labels'])) {
  $rawLabels = $payload['field_labels'];
  $fieldLabels = is_string($rawLabels) ? json_decode($rawLabels, true) : $rawLabels;
  if (!is_array($fieldLabels)) $fieldLabels = [];
}
?>

<?php if (is_array($metas) && !empty($metas)): ?>

  <?php
  // ═══════════════════════════════════════════════════════════════
  // PRÉPARATION DES DONNÉES — Catégories, points GPS, items
  // ═══════════════════════════════════════════════════════════════

  $categories = [];   // Catégories indexées par slug (type)
  $mapPoints  = [];   // Points GPS pour la carte Leaflet
  $allItems   = [];   // Toutes les fiches à afficher
  $itemIndex  = 0;    // Index courant pour lier cards ↔ marqueurs

  // Correspondances type → label lisible
  $categoryLabels = [
    'accommodation' => 'Hébergements',
    'accomodation' => 'Hébergements',
    'city' => 'Villes',
    'activity' => 'Activités',
    'event' => 'Événements',
    'restaurant' => 'Restaurants',
    'service' => 'Services',
  ];

  // Correspondances type → couleur de marqueur/badge
  $categoryColors = [
    'accommodation' => '#10b981',
    'accomodation' => '#10b981',
    'city' => '#3b82f6',
    'activity' => '#a855f7',
    'event' => '#ef4444',
    'restaurant' => '#f59e0b',
    'service' => '#6366f1',
  ];

  // Correspondances type → classe CSS du badge
  $categoryBadges = [
    'accommodation' => 'badge-accom',
    'accomodation' => 'badge-accom',
    'city' => 'badge-city',
    'activity' => 'badge-activity',
    'event' => 'badge-event',
    'restaurant' => 'badge-restaurant',
    'service' => 'badge-service',
  ];

  // Couleurs de secours pour les catégories inconnues
  $fallbackColors = ['#0891b2', '#db2777', '#65a30d', '#c026d3', '#ea580c', '#0d9488', '#7c3aed'];
  $fallbackColorIndex = 0;

  // ─── Parcours des métadonnées retournées par l'API ───

  foreach ($metas as $metaItem) {
    if (!is_array($metaItem)) continue;

    $type = isset($metaItem['type']) ? strtolower(trim((string)$metaItem['type'])) : '';
    $data = isset($metaItem['data']) ? (is_string($metaItem['data']) ? json_decode($metaItem['data'], true) : $metaItem['data']) : [];
    if (!is_array($data) || empty($type)) continue;

    $name = $data['name'] ?? '';
    if (empty($name)) continue;

    $apiLabel = $metaItem['type_label'] ?? null;

    // Enregistrer la catégorie si première occurrence
    if (!isset($categories[$type])) {
      $label = $apiLabel ?? $categoryLabels[$type] ?? ucfirst(str_replace(['-', '_'], ' ', $type));
      $color = $categoryColors[$type] ?? $fallbackColors[$fallbackColorIndex % count($fallbackColors)];
      if (!isset($categoryColors[$type])) $fallbackColorIndex++;

      $categories[$type] = [
        'slug'  => $type,
        'count' => 0,
        'label' => $label,
        'color' => $color,
        'badge' => $categoryBadges[$type] ?? 'badge-default',
      ];
    }
    $categories[$type]['count']++;

    // Extraire les coordonnées GPS (plusieurs formats possibles)
    $latitude  = $data['latitude'] ?? $data['gps_coordinates_latitude'] ?? $data['latitude_deg'] ?? null;
    $longitude = $data['longitude'] ?? $data['gps_coordinates_longitude'] ?? $data['longitude_deg'] ?? null;

    if ($latitude && $longitude && is_numeric($latitude) && is_numeric($longitude)) {
      $mapPoints[] = [
        'lat'  => (float)$latitude,
        'lng'  => (float)$longitude,
        'name' => $name,
        'type' => $type,
        'idx'  => $itemIndex,
      ];
    }

    // Détecter les équipements pour les hébergements (filtres)
    $features = [];
    if (in_array($type, ['accommodation', 'accomodation'])) {
      if (!empty($data['wifi']))                                          $features[] = 'wifi';
      if (!empty($data['parking']))                                       $features[] = 'parking';
      if (!empty($data['pets_policy']) && $data['pets_policy'] !== 'Refusés') $features[] = 'pets';
      if (!empty($data['near_pool']))                                     $features[] = 'pool';
    }

    $allItems[] = [
      'data'     => $data,
      'type'     => $type,
      'name'     => $name,
      'features' => $features,
      'idx'      => $itemIndex,
    ];
    $itemIndex++;
  }

  $totalItems = count($allItems);

  // ═══════════════════════════════════════════════════════════════
  // CONFIGURATION D'AFFICHAGE DES CARDS
  // ═══════════════════════════════════════════════════════════════

  // Clés à exclure de l'affichage dans les cards et la modal
  $excludedKeys = [
    'name',
    'image',
    'images',
    'link',
    'image_url',
    'latitude',
    'longitude',
    'gps_coordinates_latitude',
    'gps_coordinates_longitude',
    'latitude_deg',
    'longitude_deg',
    'description',
    'desciption',
    'comment',
    'address',
    'available_image_urls',
    'site_officiel',
  ];

  /**
   * Retourne le label lisible d'un champ
   * Priorité : labels API > formatage automatique de la clé
   */
  function em_field_label($key, $fieldLabels)
  {
    if (isset($fieldLabels[$key])) return $fieldLabels[$key];
    return ucfirst(str_replace('_', ' ', $key));
  }

  // Champs affichés en tags colorés sur les cards (avec suffixe et classe CSS)
  $tagFields = [
    'nb_persons'           => ['suffix' => ' pers.',  'class' => 'em-tag-blue'],
    'surface_avg'          => ['suffix' => ' m²',     'class' => 'em-tag-green'],
    'surface_min'          => ['suffix' => ' m²',     'class' => 'em-tag-green'],
    'distance_kilometers'  => ['suffix' => ' km',     'class' => 'em-tag-blue'],
    'distance_camper_km'   => ['suffix' => '',         'class' => 'em-tag-blue'],
    'driving_time_minutes' => ['suffix' => ' min',    'class' => 'em-tag-orange'],
    'temps_camper_min'     => ['suffix' => '',         'class' => 'em-tag-orange'],
    'price'                => ['suffix' => '',         'class' => 'em-tag-green'],
    'date'                 => ['suffix' => '',         'class' => 'em-tag-orange'],
    'city'                 => ['suffix' => '',         'class' => 'em-tag-blue'],
    'department'           => ['suffix' => '',         'class' => 'em-tag-purple'],
    'place'                => ['suffix' => '',         'class' => 'em-tag-blue'],
    'category_type'        => ['suffix' => '',         'class' => 'em-tag-purple'],
    'labels_touristiques'  => ['suffix' => '',         'class' => 'em-tag-green'],
  ];
  $tagFieldKeys = array_keys($tagFields);
  ?>

  <!-- Injection des labels pour le JS (modal) -->
  <script>
    var emFieldLabels = <?php echo wp_json_encode($fieldLabels); ?>;
  </script>

  <!-- ═══════════════════════════════════════════════════════════════
     HTML — Interface principale
     ═══════════════════════════════════════════════════════════════ -->

  <div class="em-wrap">

    <!-- ─── Header : titre ─── -->
    <div class="em-header">
      <div>
        <h1 class="em-header-title">Découvrez autour de vous</h1>
        <p class="em-header-sub"><?php echo $totalItems; ?> fiche(s) dans <?php echo count($categories); ?> catégorie(s)</p>
      </div>
      <button class="em-map-toggle active" id="em-map-toggle">📍 Carte</button>
    </div>



    <!-- ─── Layout principal : sidebar filtres + grille de cards ─── -->
    <div class="em-layout">

      <!-- Sidebar — Filtres par type et équipements -->
      <aside class="em-sidebar">
        <div class="em-filters">
          <h3 class="em-filters-title">Filtres</h3>
          <div class="em-stats">

            <div class="em-stat">Total : <strong id="em-stat-total"><?php echo $totalItems; ?></strong></div>
            <div class="em-stat">Affichés : <strong id="em-stat-visible"><?php echo $totalItems; ?></strong></div>
          </div>
          <div class="em-filter-group">
            <label class="em-filter-label">Type</label>
            <?php foreach ($categories as $categorySlug => $categoryInfo): ?>
              <label class="em-filter-check"><input type="checkbox" value="<?php echo esc_attr($categorySlug); ?>" class="em-filter-type" checked><span><?php echo esc_html($categoryInfo['label']); ?></span></label>
            <?php endforeach; ?>
          </div>
          <?php if (isset($categories['accommodation']) || isset($categories['accomodation'])): ?>
            <div class="em-filter-group">
              <label class="em-filter-label">Équipements</label>
              <label class="em-filter-check"><input type="checkbox" value="wifi" class="em-filter-feat"><span>WiFi</span></label>
              <label class="em-filter-check"><input type="checkbox" value="parking" class="em-filter-feat"><span>Parking</span></label>
              <label class="em-filter-check"><input type="checkbox" value="pets" class="em-filter-feat"><span>Animaux acceptés</span></label>
              <label class="em-filter-check"><input type="checkbox" value="pool" class="em-filter-feat"><span>Proche piscine</span></label>
            </div>
          <?php endif; ?>

          <!-- ─── Recherche (sous les filtres) ─── -->
          <div class="em-search-wrap">
            <svg class="em-search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input type="text" class="em-search" id="em-search" placeholder="Rechercher une fiche...">
          </div>
          <button class="em-filter-reset" id="em-reset-filters">Réinitialiser</button>
        </div>
      </aside>

      <!-- Contenu principal — Compteurs + grille de cards -->
      <main class="em-main">

        <!-- ─── Onglets de catégories (affichés si > 1 catégorie) ─── -->
        <?php if (count($categories) > 1): ?>
          <div class="em-tabs" id="em-tabs">

            <button class="em-tab active" data-filter="all">Tout<span class="em-tab-count"><?php echo $totalItems; ?></span></button>
            <?php foreach ($categories as $categorySlug => $categoryInfo): ?>
              <button class="em-tab" data-filter="<?php echo esc_attr($categorySlug); ?>"><?php echo esc_html($categoryInfo['label']); ?><span class="em-tab-count"><?php echo $categoryInfo['count']; ?></span></button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div class="em-main-body">
          <!-- ─── Carte interactive (affichée si des points GPS existent) ─── -->
          <?php if (!empty($mapPoints)): ?>
            <div class="em-map-section">

              <div class="em-map-wrap" id="em-map-wrap">
                <div id="em-map" class="em-map"></div>
                <div class="em-map-legend">
                  <?php foreach ($categories as $categorySlug => $categoryInfo): ?>
                    <div class="em-map-legend-item" data-type="<?php echo esc_attr($categorySlug); ?>"><span class="em-map-legend-dot" style="background:<?php echo $categoryInfo['color']; ?>;"></span> <?php echo esc_html($categoryInfo['label']); ?></div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <div class="em-cards" id="em-cards">
            <?php foreach ($allItems as $item):
              $itemData     = $item['data'];
              $itemType     = $item['type'];
              $itemName     = $item['name'];
              $itemFeatures = implode(',', $item['features']);
              $categoryInfo = $categories[$itemType];

              // Extraire l'image (peut être string multi-lignes ou tableau)
              $itemImage = $itemData['image'] ?? $itemData['images'] ?? '';
              if (is_string($itemImage) && strpos($itemImage, "\n") !== false) $itemImage = explode("\n", $itemImage)[0];
              if (is_array($itemImage)) $itemImage = $itemImage[0] ?? '';
              $itemImage = trim($itemImage);

              // Lien externe
              $itemLink = $itemData['link'] ?? $itemData['site_officiel'] ?? '';
            ?>
              <article class="em-card"
                data-type="<?php echo esc_attr($itemType); ?>"
                data-features="<?php echo esc_attr($itemFeatures); ?>"
                data-name="<?php echo esc_attr(strtolower($itemName)); ?>"
                data-json="<?php echo esc_attr(wp_json_encode([
                              'name'     => $itemName,
                              'image'    => $itemImage,
                              'link'     => $itemLink,
                              'data'     => $itemData,
                              'category' => $categoryInfo['label'],
                            ])); ?>">

                <!-- Image + badge catégorie -->
                <div class="em-card-img-wrap">
                  <?php if ($itemImage && filter_var($itemImage, FILTER_VALIDATE_URL)): ?>
                    <img class="em-card-img" src="<?php echo esc_url($itemImage); ?>" alt="<?php echo esc_attr($itemName); ?>" loading="lazy" onerror="this.style.setProperty('display','none','important');this.nextElementSibling.style.display='flex';">
                    <div class="em-card-placeholder" style="display:none;">📷 Image indisponible</div>
                  <?php else: ?>
                    <div class="em-card-placeholder">📷 Pas d'image</div>
                  <?php endif; ?>
                  <span class="em-card-badge <?php echo esc_attr($categoryInfo['badge']); ?>"><?php echo esc_html($categoryInfo['label']); ?></span>
                </div>

                <!-- Contenu de la card -->
                <div class="em-card-body">
                  <h3 class="em-card-title"><?php echo esc_html($itemName); ?></h3>

                  <!-- Tags résumés (max 4) -->
                  <div class="em-card-tags">
                    <?php $tagCount = 0;
                    foreach ($tagFields as $tagKey => $tagConfig):
                      if ($tagCount >= 4) break;
                      if (!empty($itemData[$tagKey]) && !is_array($itemData[$tagKey])): $tagCount++; ?>
                        <span class="em-tag <?php echo $tagConfig['class']; ?>"><?php echo esc_html($itemData[$tagKey] . $tagConfig['suffix']); ?></span>
                    <?php endif;
                    endforeach; ?>
                    <?php if (!empty($itemData['wifi'])): ?><span class="em-tag em-tag-purple">WiFi</span><?php endif; ?>
                  </div>

                  <!-- Champs supplémentaires (max 3) -->
                  <div class="em-card-fields">
                    <?php $fieldCount = 0;
                    foreach ($itemData as $fieldKey => $fieldValue):
                      if (in_array($fieldKey, $excludedKeys) || in_array($fieldKey, $tagFieldKeys) || $fieldKey === 'wifi') continue;
                      if (is_array($fieldValue) || is_object($fieldValue) || empty($fieldValue)) continue;
                      if ($fieldCount >= 3) break;
                      $fieldCount++;
                    ?>
                      <div class="em-card-field">
                        <span class="em-card-field-label"><?php echo esc_html(em_field_label($fieldKey, $fieldLabels)); ?> :</span>
                        <span class="em-card-field-value"><?php echo esc_html(is_bool($fieldValue) ? ($fieldValue ? 'Oui' : 'Non') : mb_substr((string)$fieldValue, 0, 60)); ?></span>
                      </div>
                    <?php endforeach; ?>
                  </div>

                  <!-- Lien vers le détail -->
                  <div class="em-card-footer">
                    <span>Voir le détail</span>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"></path>
                    </svg>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>

          <!-- État vide (aucun résultat après filtrage) -->
          <div class="em-empty" id="em-empty" style="display:none;">
            <p><strong>Aucun résultat</strong><br>Modifiez vos filtres pour voir plus de résultats</p>
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- ═══════════════════════════════════════════════════════════════
     JAVASCRIPT — Filtres, modal, carte Leaflet
     ═══════════════════════════════════════════════════════════════ -->

  <script>
    (function() {
      'use strict';

      // Labels des champs (injectés depuis PHP)
      var FIELD_LABELS = window.emFieldLabels || {};

      // Clés exclues de la modal (données internes / déjà affichées)
      var EXCLUDED_KEYS = [
        'name', 'image', 'images', 'image_url', 'link', 'site_officiel',
        'gps_coordinates_latitude', 'gps_coordinates_longitude',
        'latitude', 'longitude', 'latitude_deg', 'longitude_deg',
        'address', 'available_image_urls'
      ];

      /**
       * Retourne le label lisible d'un champ
       * Utilise les labels API si disponibles, sinon formate la clé
       */
      function getFieldLabel(key) {
        return FIELD_LABELS[key] || key.replace(/_/g, ' ').replace(/^\w/, function(c) {
          return c.toUpperCase();
        });
      }

      /**
       * Échappe le HTML pour éviter les injections XSS
       */
      function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
      }

      /**
       * Normalise une chaîne pour une recherche insensible aux accents et à la casse
       * "Café" → "cafe" | "Hôtel" → "hotel" | "BIÈRE" → "biere"
       */
      function stripAccents(str) {
        return (str || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
      }

      // ═══════════════════════════════════════
      // FILTRES — Onglets, checkboxes, recherche
      // ═══════════════════════════════════════

      var activeCategoryFilter = 'all';
      var searchQuery = '';

      /**
       * Applique tous les filtres actifs (catégorie, type, équipements, recherche)
       * Met à jour la visibilité des cards et les compteurs
       */
      function applyFilters() {
        var selectedTypes = Array.from(document.querySelectorAll('.em-filter-type:checked')).map(function(cb) {
          return cb.value;
        });
        var selectedFeatures = Array.from(document.querySelectorAll('.em-filter-feat:checked')).map(function(cb) {
          return cb.value;
        });
        var cards = document.querySelectorAll('.em-card');
        var visibleCount = 0;
        var normalizedQuery = stripAccents(searchQuery);

        cards.forEach(function(card) {
          var cardType = card.getAttribute('data-type');
          var cardFeatures = (card.getAttribute('data-features') || '').split(',').filter(Boolean);
          var cardName = card.getAttribute('data-name') || '';

          // Vérifier chaque critère de filtre
          var matchesCategory = (activeCategoryFilter === 'all' || activeCategoryFilter === cardType);
          var matchesType = selectedTypes.indexOf(cardType) !== -1;
          var matchesSearch = (normalizedQuery === '' || stripAccents(cardName).indexOf(normalizedQuery) !== -1);

          var isVisible = matchesCategory && matchesType && matchesSearch;

          // Filtre équipements (uniquement pour les hébergements)
          if (isVisible && (cardType === 'accommodation' || cardType === 'accomodation') && selectedFeatures.length > 0) {
            isVisible = selectedFeatures.every(function(feat) {
              return cardFeatures.indexOf(feat) !== -1;
            });
          }

          card.classList.toggle('em-hidden', !isVisible);
          if (isVisible) visibleCount++;
        });

        // Mise à jour des compteurs
        document.getElementById('em-stat-visible').textContent = visibleCount;
        document.getElementById('em-empty').style.display = visibleCount === 0 ? 'block' : 'none';

        // Mise à jour des marqueurs de la carte
        if (typeof updateMapMarkers === 'function') {
          updateMapMarkers(activeCategoryFilter === 'all' ? selectedTypes : [activeCategoryFilter]);
        }

        // Sync visuel de la légende carte avec les filtres actifs
        document.querySelectorAll('.em-map-legend-item').forEach(function(item) {
          var type = item.getAttribute('data-type');
          var matchesCategory = (activeCategoryFilter === 'all' || activeCategoryFilter === type);
          var matchesType = selectedTypes.indexOf(type) !== -1;
          item.classList.toggle('em-map-legend-item--off', !(matchesCategory && matchesType));
        });

        // Masque le groupe équipements si accommodation n'est pas dans les filtres actifs
        var featGroup = document.querySelector('.em-filter-group .em-filter-label');
        var equipGroup = Array.from(document.querySelectorAll('.em-filter-group')).find(function(g) {
          return g.querySelector('.em-filter-feat');
        });

        if (equipGroup) {
          var accomActive = (activeCategoryFilter === 'all' || activeCategoryFilter === 'accommodation' || activeCategoryFilter === 'accomodation') &&
            selectedTypes.some(function(t) {
              return t === 'accommodation' || t === 'accomodation';
            });
          equipGroup.style.display = accomActive ? '' : 'none';
        }
      }

      /**
       * Synchronise les checkboxes Type avec l'onglet actif
       * "all" → toutes cochées | type X → seule la checkbox X cochée
       */
      function syncTypeCheckboxesFromTab() {
        document.querySelectorAll('.em-filter-type').forEach(function(cb) {
          cb.checked = (activeCategoryFilter === 'all') || (cb.value === activeCategoryFilter);
        });
      }

      /**
       * Synchronise l'onglet actif avec l'état des checkboxes Type
       * Toutes cochées → "all" | exactement une → onglet de ce type | sous-ensemble → aucun onglet (cas personnalisé)
       */
      function syncTabsFromTypeCheckboxes() {
        var typeCbs = Array.from(document.querySelectorAll('.em-filter-type'));
        var checked = typeCbs.filter(function(cb) {
          return cb.checked;
        });
        var matchingTab = null;
        if (checked.length === typeCbs.length) matchingTab = 'all';
        else if (checked.length === 1) matchingTab = checked[0].value;
        // sous-ensemble : matchingTab reste null → aucun onglet n'est actif, le filtre logique tombe sur 'all'
        activeCategoryFilter = matchingTab || 'all';
        document.querySelectorAll('.em-tab').forEach(function(t) {
          t.classList.toggle('active', t.getAttribute('data-filter') === matchingTab);
        });
      }

      // ─── Onglets de catégories ───
      document.querySelectorAll('.em-tab').forEach(function(tab) {
        tab.addEventListener('click', function() {
          document.querySelectorAll('.em-tab').forEach(function(t) {
            t.classList.remove('active');
          });
          tab.classList.add('active');
          activeCategoryFilter = tab.getAttribute('data-filter');
          syncTypeCheckboxesFromTab();
          applyFilters();

          // Remonte en haut du body scrollable
          var mainBody = document.querySelector('.em-main-body');
          if (mainBody) mainBody.scrollTo({
            top: 0,
            behavior: 'smooth'
          });
        });
      });

      // ─── Checkboxes filtres ───
      document.querySelectorAll('.em-filter-type, .em-filter-feat').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
          if (checkbox.classList.contains('em-filter-type')) syncTabsFromTypeCheckboxes();
          applyFilters();
        });
      });

      // ─── Légende carte cliquable : toggle de la checkbox Type correspondante ───
      // Réutilise la pipeline checkbox (sync onglets + applyFilters via dispatchEvent)
      document.querySelectorAll('.em-map-legend-item').forEach(function(item) {
        item.addEventListener('click', function() {
          var type = item.getAttribute('data-type');
          var typeCb = Array.from(document.querySelectorAll('.em-filter-type')).find(function(cb) {
            return cb.value === type;
          });
          if (typeCb) {
            typeCb.checked = !typeCb.checked;
            typeCb.dispatchEvent(new Event('change'));
          }
        });
      });

      // ─── Barre de recherche ───
      var searchInput = document.getElementById('em-search');
      if (searchInput) {
        searchInput.addEventListener('input', function(e) {
          searchQuery = e.target.value;
          applyFilters();
        });
      }

      // ─── Bouton réinitialiser ───
      var resetButton = document.getElementById('em-reset-filters');
      if (resetButton) {
        resetButton.addEventListener('click', function() {
          document.querySelectorAll('.em-filter-type').forEach(function(cb) {
            cb.checked = true;
          });
          document.querySelectorAll('.em-filter-feat').forEach(function(cb) {
            cb.checked = false;
          });
          if (searchInput) searchInput.value = '';
          searchQuery = '';
          activeCategoryFilter = 'all';

          // Remettre l'onglet "Tout" actif
          document.querySelectorAll('.em-tab').forEach(function(t) {
            t.classList.remove('active');
          });
          var allTab = document.querySelector('.em-tab[data-filter="all"]');
          if (allTab) allTab.classList.add('active');

          applyFilters();
        });
      }

      // Application initiale des filtres
      applyFilters();

      // ═══════════════════════════════════════
      // MODAL — Détail d'une fiche
      // ═══════════════════════════════════════

      // Clic sur une card → ouvrir la modal
      document.querySelectorAll('.em-card').forEach(function(card) {
        card.addEventListener('click', function() {
          var cardData = JSON.parse(card.getAttribute('data-json'));
          openDetailModal(cardData);
        });
      });

      /**
       * Ouvre la modal de détail pour une fiche
       * Affiche l'image (si disponible), les champs et le lien externe
       */
      function openDetailModal(item) {
        // Filtrer les champs à afficher (exclure les clés internes)
        var entries = Object.entries(item.data || {}).filter(function(entry) {
          return EXCLUDED_KEYS.indexOf(entry[0]) === -1 &&
            entry[1] && entry[1] !== '' &&
            typeof entry[1] !== 'object';
        }).map(function(entry) {
          var value = entry[1];
          if (value === true || value === 'true') value = 'Oui';
          else if (value === false || value === 'false') value = 'Non';
          return {
            label: getFieldLabel(entry[0]),
            value: String(value)
          };
        });

        var hasImage = item.image && item.image.indexOf('http') === 0;
        var html = '<div class="em-modal">';

        // ─── Header de la modal (avec ou sans image) ───
        if (hasImage) {
          html += '<div class="em-modal-img-wrap">' +
            '<img class="em-modal-img" src="' + escapeHtml(item.image) + '" alt="' + escapeHtml(item.name) + '" onerror="this.style.display=\'none\'">' +
            '<div class="em-modal-img-gradient"></div>' +
            '<button class="em-modal-close" data-close>&times;</button>' +
            '<div class="em-modal-img-info">' +
            '<p class="em-modal-img-cat">' + escapeHtml(item.category || '') + '</p>' +
            '<h3 class="em-modal-img-title">' + escapeHtml(item.name) + '</h3>' +
            '</div></div>';
        } else {
          html += '<div class="em-modal-header-noimg"><div>' +
            '<p class="em-modal-header-noimg-cat">' + escapeHtml(item.category || '') + '</p>' +
            '<h3 class="em-modal-header-noimg-title">' + escapeHtml(item.name) + '</h3>' +
            '</div><button class="em-modal-close-noimg" data-close>&times;</button></div>';
        }

        // ─── Corps de la modal : champs clé/valeur ───
        html += '<div class="em-modal-body">';
        entries.forEach(function(entry) {
          html += '<div class="em-modal-entry">' +
            '<p class="em-modal-entry-label">' + escapeHtml(entry.label) + '</p>' +
            '<p class="em-modal-entry-value">' + escapeHtml(entry.value) + '</p>' +
            '</div>';
        });
        html += '</div>';

        // ─── Footer de la modal : lien externe + bouton fermer ───
        html += '<div class="em-modal-footer">';
        if (item.link && item.link.indexOf('http') === 0) {
          html += '<a href="' + escapeHtml(item.link) + '" target="_blank" rel="noopener" class="em-modal-link">Ouvrir le lien &rarr;</a>';
        } else {
          html += '<span></span>';
        }
        html += '<button class="em-modal-close-btn" data-close>Fermer</button></div></div>';

        // ─── Injection dans le DOM ───
        var overlay = document.createElement('div');
        overlay.className = 'em-modal-overlay';
        overlay.innerHTML = html;
        document.body.appendChild(overlay);

        // Fermeture : boutons, clic overlay, touche Escape
        overlay.querySelectorAll('[data-close]').forEach(function(btn) {
          btn.addEventListener('click', function() {
            overlay.remove();
          });
        });
        overlay.addEventListener('click', function(e) {
          if (e.target === overlay) overlay.remove();
        });
        document.addEventListener('keydown', function escapeHandler(e) {
          if (e.key === 'Escape') {
            overlay.remove();
            document.removeEventListener('keydown', escapeHandler);
          }
        });
      }

      // ═══════════════════════════════════════
      // CARTE LEAFLET — Marqueurs + interactions
      // ═══════════════════════════════════════

      var mapElement = document.getElementById('em-map');

      if (mapElement && typeof L !== 'undefined') {

        // Initialisation de la carte
        var map = L.map(mapElement, {
          zoomControl: false,
          attributionControl: false
        }).setView([46.6, 1.9], 6);

        // Tuiles en premier
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
          maxZoom: 19,
          subdomains: 'abcd'
        }).addTo(map);

        // Contrôles UI ensuite
        L.control.zoom({
          position: 'topright'
        }).addTo(map);
        L.control.attribution({
            position: 'bottomright',
            prefix: false
          })
          .addAttribution('&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; <a href="https://carto.com/">CARTO</a>')
          .addTo(map);

        // Bouton de recentrage
        L.Control.Recenter = L.Control.extend({
          onAdd: function() {
            const container = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
            const btn = L.DomUtil.create('a', '', container);
            btn.innerHTML = '<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><circle cx="12" cy="12" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>';
            btn.href = '#';
            btn.title = 'Recentrer la carte';
            btn.style.display = 'flex';
            btn.style.alignItems = 'center';
            btn.style.justifyContent = 'center';
            L.DomEvent.on(btn, 'click', function(e) {
              L.DomEvent.preventDefault(e);
              if (allBounds.length > 1) map.fitBounds(allBounds, {
                padding: [40, 40]
              });
              else if (allBounds.length === 1) map.setView(allBounds[0], 13);
            });
            return container;
          }
        });
        new L.Control.Recenter({
          position: 'topright'
        }).addTo(map);

        // Données injectées depuis PHP
        var gpsPoints = <?php echo wp_json_encode($mapPoints); ?>;
        var categoryColorMap = <?php echo wp_json_encode(array_map(fn($c) => $c['color'], $categories)); ?>;
        var categoryLabelMap = <?php echo wp_json_encode(array_map(fn($c) => $c['label'], $categories)); ?>;

        var markers = []; // Tous les marqueurs [{marker, type, latLng, idx}]
        var allBounds = []; // Coordonnées pour le fitBounds initial
        var activeMarker = null; // Marqueur actuellement sélectionné

        /**
         * Crée une icône SVG circulaire pour un marqueur Leaflet
         * @param {string} color  — Couleur de remplissage
         * @param {number} size   — Diamètre du cercle intérieur (défaut: 12)
         */
        function createMarkerIcon(color, size) {
          size = size || 12;
          var outerSize = size + 8;
          var center = outerSize / 2;
          return L.divIcon({
            className: 'em-marker',
            html: '<svg width="' + outerSize + '" height="' + outerSize + '" viewBox="0 0 ' + outerSize + ' ' + outerSize + '">' +
              '<circle cx="' + center + '" cy="' + center + '" r="' + (size / 2 + 2) + '" fill="white" opacity="0.9"/>' +
              '<circle cx="' + center + '" cy="' + center + '" r="' + (size / 2) + '" fill="' + color + '"/>' +
              '</svg>',
            iconSize: [outerSize, outerSize],
            iconAnchor: [center, center],
            popupAnchor: [0, -(size / 2 + 6)]
          });
        }

        // ─── Placement des marqueurs sur la carte ───
        gpsPoints.forEach(function(point) {
          var color = categoryColorMap[point.type] || '#6b7280';
          var catLabel = categoryLabelMap[point.type] || point.type;
          var icon = createMarkerIcon(color, 12);
          var marker = L.marker([point.lat, point.lng], {
            icon: icon
          }).addTo(map);

          // Popup au clic
          var popupHtml = '<div class="em-map-popup">' +
            '<p class="em-map-popup-name">' + escapeHtml(point.name) + '</p>' +
            '<p class="em-map-popup-cat" style="color:' + color + '">' + escapeHtml(catLabel) + '</p>' +
            '<button class="em-map-popup-btn" data-card-idx="' + point.idx + '">Voir la fiche →</button>' +
            '</div>';
          marker.bindPopup(popupHtml, {
            closeButton: false,
            minWidth: 180
          });

          // Highlight du marqueur actif
          marker.on('click', function() {
            if (activeMarker && activeMarker.getElement()) activeMarker.getElement().classList.remove('em-marker--active');
            if (marker.getElement()) marker.getElement().classList.add('em-marker--active');
            activeMarker = marker;
          });

          // Bouton "Voir la fiche" dans la popup → scroll vers la card
          marker.on('popupopen', function() {
            var popupBtn = document.querySelector('.em-map-popup-btn[data-card-idx="' + point.idx + '"]');
            if (popupBtn) {
              popupBtn.addEventListener('click', function() {
                var allCards = document.querySelectorAll('.em-card');
                var targetCard = allCards[point.idx];
                if (targetCard && !targetCard.classList.contains('em-hidden')) {
                  targetCard.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                  });
                  targetCard.classList.add('em-card--highlight');
                  setTimeout(function() {
                    targetCard.classList.remove('em-card--highlight');
                  }, 2000);
                }
                map.closePopup();
              });
            }
          });

          markers.push({
            marker: marker,
            type: point.type,
            latLng: [point.lat, point.lng],
            idx: point.idx
          });
          allBounds.push([point.lat, point.lng]);
        });

        // Cadrage initial de la carte
        if (allBounds.length > 1) map.fitBounds(allBounds, {
          padding: [40, 40]
        });
        else if (allBounds.length === 1) map.setView(allBounds[0], 13);
        setTimeout(function() {
          map.invalidateSize();
        }, 100);

        // ─── Interaction cards ↔ marqueurs (hover) ───
        document.querySelectorAll('.em-card').forEach(function(card, cardIndex) {
          card.addEventListener('mouseenter', function() {
            markers.forEach(function(m) {
              if (m.idx === cardIndex) {
                m.marker.setIcon(createMarkerIcon(categoryColorMap[m.type] || '#6b7280', 18));
                m.marker.setZIndexOffset(1000);
              }
            });
          });
          card.addEventListener('mouseleave', function() {
            markers.forEach(function(m) {
              if (m.idx === cardIndex) {
                m.marker.setIcon(createMarkerIcon(categoryColorMap[m.type] || '#6b7280', 12));
                m.marker.setZIndexOffset(0);
              }
            });
          });
        });

        /**
         * Met à jour la visibilité des marqueurs selon les types sélectionnés
         * Appelé par applyFilters() quand les filtres changent
         */
        window.updateMapMarkers = function(visibleTypes) {
          var visibleBounds = [];
          markers.forEach(function(m) {
            if (visibleTypes.indexOf(m.type) !== -1) {
              if (!map.hasLayer(m.marker)) map.addLayer(m.marker);
              visibleBounds.push(m.latLng);
            } else {
              if (map.hasLayer(m.marker)) map.removeLayer(m.marker);
            }
          });
          if (visibleBounds.length > 1) map.fitBounds(visibleBounds, {
            padding: [40, 40],
            maxZoom: 14
          });
          else if (visibleBounds.length === 1) map.setView(visibleBounds[0], 13);
        };

        // ─── Toggle visibilité de la carte ───
        var mapToggle = document.getElementById('em-map-toggle');
        var mapWrapper = document.getElementById('em-map-wrap');
        if (mapToggle && mapWrapper) {
          mapToggle.addEventListener('click', function() {
            var isVisible = mapWrapper.style.display !== 'none';
            mapWrapper.style.display = isVisible ? 'none' : '';
            mapToggle.classList.toggle('active', !isVisible);
            document.querySelector('.em-map-section').classList.toggle('em-map-section--hidden', isVisible);
            if (!isVisible) setTimeout(function() {
              map.invalidateSize();
            }, 100);
          });
        }
      }

      // ═══════════════════════════════════════
      // MOBILE — Sidebar filtres collapsible
      // ═══════════════════════════════════════

      /**
       * Sur mobile (≤720px), la sidebar filtres est repliée par défaut
       * Un clic sur le titre la déplie/replie
       */
      function initMobileFilters() {
        var filtersContainer = document.querySelector('.em-filters');
        var filtersTitle = document.querySelector('.em-filters-title');
        if (!filtersContainer || !filtersTitle) return;

        if (window.innerWidth <= 720) {
          filtersContainer.classList.add('collapsed');
        }

        filtersTitle.addEventListener('click', function() {
          if (window.innerWidth <= 720) {
            filtersContainer.classList.toggle('collapsed');
          }
        });
      }

      initMobileFilters();

      // ═══════════════════════════════════════
      // OFFSET HEADER THÈME — Détecte un header sticky/fixé + injecte un overlay opaque sous celui-ci pour les thèmes au header semi-transparent
      // ═══════════════════════════════════════
      var HEADER_SELECTORS = 'header, .site-header, #masthead, #header, .header, .navbar, .nav-bar, body > nav, body > .top-bar';

      // Inject l'overlay opaque dans le body (au niveau racine, évite les pièges de stacking context si le thème a un transform sur un ancêtre)
      if (!document.getElementById('em-header-overlay')) {
        var overlay = document.createElement('div');
        overlay.id = 'em-header-overlay';
        document.body.appendChild(overlay);
      }

      function updateThemeHeaderOffset() {
        var bottom = 0;
        document.querySelectorAll(HEADER_SELECTORS).forEach(function(el) {
          var style = window.getComputedStyle(el);
          if (style.position !== 'fixed' && style.position !== 'sticky') return;
          var rect = el.getBoundingClientRect();
          // On retient le bord bas de l'élément le plus bas qui touche encore le haut du viewport
          if (rect.top <= 50 && rect.bottom > bottom) bottom = rect.bottom;
        });
        document.documentElement.style.setProperty('--em-header-offset', Math.max(0, bottom) + 'px');
      }

      function updateScrolledClass() {
        document.documentElement.classList.toggle('em-scrolled', window.scrollY > 0);
      }
      updateThemeHeaderOffset();
      updateScrolledClass();
      window.addEventListener('resize', updateThemeHeaderOffset);
      // rAF-throttlé sur le scroll : update offset (header qui shrink) + état scrolled (toggle overlay)
      var headerTicking = false;
      window.addEventListener('scroll', function() {
        if (headerTicking) return;
        headerTicking = true;
        requestAnimationFrame(function() {
          updateThemeHeaderOffset();
          updateScrolledClass();
          headerTicking = false;
        });
      }, {
        passive: true
      });
    })();
  </script>

<?php endif; ?>