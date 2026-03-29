<?php if (!defined('ABSPATH') || !defined('ELAIA_PLUGIN_DIR')) exit; ?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<?php
$pc = $style['primary_color'] ?? '#3b82f6';
$pc18 = $pc . '18';
?>

<style>
  /* ═══════════════════════════════════════
     BASE
     ═══════════════════════════════════════ */
  .em-wrap { font-family: 'Inter', -apple-system, sans-serif !important; color: #0f172a; max-width: 1400px; margin: 0 auto; padding: 0 24px; -webkit-font-smoothing: antialiased; }
  .em-wrap * { box-sizing: border-box; }

  /* ─── Header ─── */
  .em-header { padding: 40px 0 24px; display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
  .em-header-title { font-size: 24px !important; font-weight: 800 !important; margin: 0 !important; color: #0f172a; }
  .em-header-sub { font-size: 14px; color: #64748b; margin: 4px 0 0; }

  /* ─── Search ─── */
  .em-search-wrap { position: relative; width: 280px; }
  .em-search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: #94a3b8; pointer-events: none; }
  .em-search { width: 100% !important; padding: 10px 16px 10px 36px !important; font-size: 14px !important; font-family: inherit; border: 1px solid #e2e8f0 !important; border-radius: 12px !important; background: #fff !important; color: #0f172a; outline: none; }
  .em-search:focus { border-color: <?php echo $pc; ?> !important; box-shadow: 0 0 0 3px <?php echo $pc18; ?> !important; }
  .em-search::placeholder { color: #94a3b8; }

  /* ─── Tabs ─── */
  .em-tabs { display: flex !important; gap: 8px; overflow-x: auto; padding-bottom: 4px; margin-bottom: 24px; scrollbar-width: none; }
  .em-tabs::-webkit-scrollbar { display: none; }
  .em-tab { flex-shrink: 0; padding: 8px 16px; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; border: 1px solid #e2e8f0; background: #fff; color: #64748b; transition: all 0.15s; font-family: inherit; white-space: nowrap; }
  .em-tab:hover { border-color: #cbd5e1; }
  .em-tab.active { background: <?php echo $pc; ?> !important; color: #fff !important; border-color: <?php echo $pc; ?> !important; }
  .em-tab-count { margin-left: 6px; font-size: 12px; opacity: 0.7; }

  /* ═══════════════════════════════════════
     MAP
     ═══════════════════════════════════════ */
  .em-map-section { margin-bottom: 24px; }
  .em-map-wrap { border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
  .em-map { height: 420px; width: 100%; }

  /* Leaflet popup override */
  .em-map .leaflet-popup-content-wrapper { border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.12); padding: 0; overflow: hidden; }
  .em-map .leaflet-popup-content { margin: 0; min-width: 200px; }
  .em-map .leaflet-popup-tip { box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
  .em-map-popup { padding: 12px 14px; }
  .em-map-popup-name { font-size: 14px; font-weight: 700; color: #0f172a; margin: 0 0 4px; line-height: 1.3; }
  .em-map-popup-cat { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; margin: 0 0 8px; }
  .em-map-popup-btn { display: inline-flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 600; color: <?php echo $pc; ?>; cursor: pointer; background: none; border: none; padding: 0; font-family: inherit; }
  .em-map-popup-btn:hover { text-decoration: underline; }

  /* Legend */
  .em-map-legend { display: flex; gap: 16px; padding: 10px 16px; background: #fff; border-top: 1px solid #e2e8f0; flex-wrap: wrap; }
  .em-map-legend-item { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #64748b; cursor: pointer; transition: opacity 0.15s; }
  .em-map-legend-item:hover { opacity: 0.7; }
  .em-map-legend-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; box-shadow: 0 0 0 2px rgba(0,0,0,0.08); }

  /* Toggle */
  .em-map-toggle { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 8px; border: 1px solid #e2e8f0; background: #fff; color: #64748b; font-size: 13px; font-weight: 600; cursor: pointer; font-family: inherit; transition: all 0.15s; margin-bottom: 16px; }
  .em-map-toggle:hover { border-color: <?php echo $pc; ?>; color: <?php echo $pc; ?>; }
  .em-map-toggle.active { background: <?php echo $pc; ?>; color: #fff; border-color: <?php echo $pc; ?>; }

  /* Custom markers */
  .em-marker { transition: transform 0.15s ease; }
  .em-marker:hover { transform: scale(1.3); z-index: 1000 !important; }
  .em-marker--active { transform: scale(1.4); z-index: 1000 !important; }

  /* ═══════════════════════════════════════
     LAYOUT
     ═══════════════════════════════════════ */
  .em-layout { display: flex; gap: 24px; }
  .em-sidebar { flex: 0 0 260px; position: sticky; top: 24px; height: fit-content; }
  .em-main { flex: 1; min-width: 0; }

  /* ─── Filters ─── */
  .em-filters { background: #fff; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
  .em-filters-title { font-size: 15px !important; font-weight: 700 !important; margin: 0 0 16px !important; color: #0f172a; }
  .em-filter-group { margin-bottom: 18px; }
  .em-filter-group:last-child { margin-bottom: 0; }
  .em-filter-label { display: block; font-size: 12px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
  .em-filter-check { display: flex; align-items: center; gap: 8px; padding: 5px 0; cursor: pointer; }
  .em-filter-check input[type="checkbox"] { cursor: pointer; accent-color: <?php echo $pc; ?>; width: 15px; height: 15px; }
  .em-filter-check span { font-size: 13px; color: #64748b; font-weight: 500; }
  .em-filter-reset { width: 100%; padding: 10px; background: #0f172a; color: #fff; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; font-family: inherit; margin-top: 16px; }
  .em-filter-reset:hover { background: #000; }

  /* ─── Stats ─── */
  .em-stats { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
  .em-stat { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 14px; font-size: 13px; color: #64748b; }
  .em-stat strong { color: #0f172a; font-weight: 700; margin-left: 4px; }

  /* ═══════════════════════════════════════
     CARDS
     ═══════════════════════════════════════ */
  .em-cards { display: grid !important; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)) !important; gap: 20px !important; }
  .em-card { background: #fff !important; border: 1px solid #e2e8f0 !important; border-radius: 12px !important; overflow: hidden !important; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: block !important; }
  .em-card:hover { box-shadow: 0 4px 6px -1px rgba(0,0,0,0.07) !important; transform: translateY(-2px); border-color: <?php echo $pc; ?> !important; }
  .em-card--highlight { box-shadow: 0 0 0 3px <?php echo $pc; ?> !important; }
  .em-card-img-wrap { position: relative; }
  .em-card-img { width: 100% !important; height: 160px !important; object-fit: cover !important; display: block !important; background: #f1f5f9; }
  .em-card-placeholder { width: 100%; height: 160px; background: linear-gradient(135deg, #f1f5f9, #e2e8f0); display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 14px; font-weight: 500; }
  .em-card-badge { position: absolute; top: 10px; left: 10px; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; backdrop-filter: blur(8px); }
  .badge-accom { background: rgba(16,185,129,0.9); color: #fff; }
  .badge-city { background: rgba(59,130,246,0.9); color: #fff; }
  .badge-activity { background: rgba(168,85,247,0.9); color: #fff; }
  .badge-event { background: rgba(239,68,68,0.9); color: #fff; }
  .badge-restaurant { background: rgba(245,158,11,0.9); color: #fff; }
  .badge-service { background: rgba(99,102,241,0.9); color: #fff; }
  .badge-default { background: rgba(100,116,139,0.9); color: #fff; }
  .em-card-body { padding: 16px; }
  .em-card-title { font-size: 15px !important; font-weight: 700 !important; margin: 0 0 8px !important; color: #0f172a; line-height: 1.3; }
  .em-card-tags { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 10px; }
  .em-tag { display: inline-flex; align-items: center; gap: 3px; padding: 3px 8px; border-radius: 5px; font-size: 11px; font-weight: 600; background: #f1f5f9; color: #64748b; }
  .em-tag-blue { background: #dbeafe; color: #1e40af; }
  .em-tag-green { background: #d1fae5; color: #065f46; }
  .em-tag-orange { background: #fed7aa; color: #92400e; }
  .em-tag-purple { background: #ede9fe; color: #6b21a8; }
  .em-card-fields { margin-bottom: 10px; }
  .em-card-field { display: flex; gap: 6px; font-size: 12px; margin-bottom: 4px; }
  .em-card-field-label { color: #94a3b8; flex-shrink: 0; }
  .em-card-field-value { color: #0f172a; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .em-card-footer { padding-top: 10px; border-top: 1px solid #e2e8f0; display: flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 600; color: <?php echo $pc; ?>; }
  .em-card-footer svg { width: 14px; height: 14px; transition: transform 0.15s; }
  .em-card:hover .em-card-footer svg { transform: translateX(2px); }

  /* ─── Empty state ─── */
  .em-empty { text-align: center; padding: 60px 20px; color: #64748b; }

  /* ═══════════════════════════════════════
     MODAL
     ═══════════════════════════════════════ */
  .em-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 99999; display: flex; align-items: center; justify-content: center; padding: 16px; animation: emFadeIn 0.2s ease; }
  @keyframes emFadeIn { from { opacity: 0 } to { opacity: 1 } }
  .em-modal { background: #fff; border-radius: 16px; max-width: 640px; width: 100%; max-height: 85vh; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.08); display: flex; flex-direction: column; animation: emSlideUp 0.25s ease; }
  @keyframes emSlideUp { from { opacity: 0; transform: translateY(20px) } to { opacity: 1; transform: translateY(0) } }
  .em-modal-img-wrap { position: relative; flex-shrink: 0; }
  .em-modal-img { width: 100%; height: 220px; object-fit: cover; display: block; }
  .em-modal-img-gradient { position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.4), transparent); }
  .em-modal-img-info { position: absolute; bottom: 16px; left: 20px; right: 20px; }
  .em-modal-img-cat { font-size: 12px; font-weight: 500; color: rgba(255,255,255,0.8); margin: 0 0 4px; }
  .em-modal-img-title { font-size: 20px; font-weight: 800; color: #fff; margin: 0; }
  .em-modal-close { position: absolute; top: 12px; right: 12px; width: 32px; height: 32px; border-radius: 50%; background: rgba(0,0,0,0.4); color: #fff; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 16px; }
  .em-modal-close:hover { background: rgba(0,0,0,0.6); }
  .em-modal-header-noimg { display: flex; align-items: center; justify-content: space-between; padding: 20px 24px; border-bottom: 1px solid #e2e8f0; }
  .em-modal-header-noimg-cat { font-size: 12px; color: #94a3b8; margin: 0 0 2px; }
  .em-modal-header-noimg-title { font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; }
  .em-modal-close-noimg { width: 32px; height: 32px; border-radius: 8px; border: none; cursor: pointer; color: #94a3b8; background: none; display: flex; align-items: center; justify-content: center; font-size: 16px; }
  .em-modal-close-noimg:hover { background: #f1f5f9; color: #0f172a; }
  .em-modal-body { padding: 20px 24px; overflow-y: auto; flex: 1; }
  .em-modal-entry { margin-bottom: 16px; }
  .em-modal-entry-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; margin: 0 0 4px; }
  .em-modal-entry-value { font-size: 14px; color: #0f172a; margin: 0; line-height: 1.5; white-space: pre-line; }
  .em-modal-footer { padding: 16px 24px; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; }
  .em-modal-link { display: inline-flex; align-items: center; gap: 6px; font-size: 14px; font-weight: 600; color: <?php echo $pc; ?>; text-decoration: none; }
  .em-modal-link:hover { opacity: 0.8; }
  .em-modal-close-btn { padding: 8px 16px; font-size: 14px; font-weight: 500; color: #64748b; background: none; border: none; cursor: pointer; font-family: inherit; }
  .em-modal-close-btn:hover { color: #0f172a; }

  /* ═══════════════════════════════════════
     RESPONSIVE
     ═══════════════════════════════════════ */
  @media (max-width: 1024px) {
    .em-layout { flex-direction: column; }
    .em-sidebar { position: static; flex-basis: auto; }
  }

  @media (max-width: 720px) {
    .em-wrap { padding: 0 12px; }
    .em-header { flex-direction: column; align-items: stretch; padding: 24px 0 16px; gap: 12px; }
    .em-header-title { font-size: 20px !important; }
    .em-search-wrap { width: 100%; }
    .em-tabs { gap: 6px; margin-bottom: 16px; }
    .em-tab { padding: 6px 12px; font-size: 13px; }

    /* Map mobile */
    .em-map { height: 280px; }
    .em-map-wrap { border-radius: 12px; }
    .em-map-legend { gap: 10px; padding: 8px 12px; }
    .em-map-legend-item { font-size: 11px; }

    /* Sidebar → collapsible */
    .em-sidebar { order: -1; }
    .em-filters { padding: 14px; }
    .em-filters-title {
      font-size: 14px !important; margin-bottom: 0 !important;
      cursor: pointer; display: flex; align-items: center; justify-content: space-between;
    }
    .em-filters-title::after { content: '▾'; font-size: 12px; color: #94a3b8; transition: transform 0.2s; }
    .em-filters.collapsed .em-filters-title::after { transform: rotate(-90deg); }
    .em-filters.collapsed .em-filter-group,
    .em-filters.collapsed .em-filter-reset { display: none; }

    /* Cards mobile */
    .em-cards { grid-template-columns: 1fr !important; gap: 14px !important; }
    .em-card-img { height: 140px !important; }
    .em-card-body { padding: 12px; }
    .em-card-title { font-size: 14px !important; }
    .em-card-tags { gap: 4px; }
    .em-tag { font-size: 10px; padding: 2px 6px; }

    /* Stats compact */
    .em-stats { gap: 8px; margin-bottom: 14px; }
    .em-stat { padding: 6px 10px; font-size: 12px; }

    /* Modal → bottom sheet */
    .em-modal-overlay { align-items: flex-end; padding: 0; }
    .em-modal { border-radius: 16px 16px 0 0; max-height: 90vh; max-width: 100%; animation: emSlideUpMobile 0.3s cubic-bezier(0.22,1,0.36,1); }
    @keyframes emSlideUpMobile { from { transform: translateY(100%); } to { transform: translateY(0); } }
    .em-modal-img { height: 180px; }
    .em-modal-body { padding: 16px; }
    .em-modal-footer { padding: 12px 16px; }
    .em-modal-entry-value { font-size: 13px; }
  }

  @media (max-width: 380px) {
    .em-map { height: 220px; }
    .em-card-img { height: 120px !important; }
    .em-card-fields { display: none; }
  }
</style>

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
if (is_array($geoMetadatas) && !empty($geoMetadatas)) {
  echo '<script type="application/ld+json">' . wp_json_encode($geoMetadatas, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
}

$fieldLabels = [];
if (is_array($payload) && !empty($payload['field_labels'])) {
  $fl = $payload['field_labels'];
  $fieldLabels = is_string($fl) ? json_decode($fl, true) : $fl;
  if (!is_array($fieldLabels)) $fieldLabels = [];
}
?>

<?php if (is_array($metas) && !empty($metas)): ?>

<?php
$categories = [];
$mapPoints = [];
$allItems = [];
$idx = 0;

$knownLabels = [
  'accommodation' => 'Hébergements', 'accomodation' => 'Hébergements',
  'city' => 'Villes', 'activity' => 'Activités', 'event' => 'Événements',
  'restaurant' => 'Restaurants', 'service' => 'Services',
];
$knownColors = [
  'accommodation' => '#10b981', 'accomodation' => '#10b981',
  'city' => '#3b82f6', 'activity' => '#a855f7', 'event' => '#ef4444',
  'restaurant' => '#f59e0b', 'service' => '#6366f1',
];
$knownBadges = [
  'accommodation' => 'badge-accom', 'accomodation' => 'badge-accom',
  'city' => 'badge-city', 'activity' => 'badge-activity', 'event' => 'badge-event',
  'restaurant' => 'badge-restaurant', 'service' => 'badge-service',
];
$extraColors = ['#0891b2','#db2777','#65a30d','#c026d3','#ea580c','#0d9488','#7c3aed'];
$extraColorIdx = 0;

foreach ($metas as $m) {
  if (!is_array($m)) continue;
  $type = isset($m['type']) ? strtolower(trim((string)$m['type'])) : '';
  $data = isset($m['data']) ? (is_string($m['data']) ? json_decode($m['data'], true) : $m['data']) : [];
  if (!is_array($data) || empty($type)) continue;

  $name = $data['name'] ?? '';
  if (empty($name)) continue;

  $apiLabel = $m['type_label'] ?? null;

  if (!isset($categories[$type])) {
    $label = $apiLabel ?? $knownLabels[$type] ?? ucfirst(str_replace(['-','_'], ' ', $type));
    $color = $knownColors[$type] ?? $extraColors[$extraColorIdx % count($extraColors)];
    if (!isset($knownColors[$type])) $extraColorIdx++;
    $categories[$type] = [
      'slug' => $type, 'count' => 0, 'label' => $label, 'color' => $color,
      'badge' => $knownBadges[$type] ?? 'badge-default',
    ];
  }
  $categories[$type]['count']++;

  $lat = $data['latitude'] ?? $data['gps_coordinates_latitude'] ?? $data['latitude_deg'] ?? null;
  $lng = $data['longitude'] ?? $data['gps_coordinates_longitude'] ?? $data['longitude_deg'] ?? null;
  if ($lat && $lng && is_numeric($lat) && is_numeric($lng)) {
    $mapPoints[] = ['lat' => (float)$lat, 'lng' => (float)$lng, 'name' => $name, 'type' => $type, 'idx' => $idx];
  }

  $features = [];
  if (in_array($type, ['accommodation','accomodation'])) {
    if (!empty($data['wifi'])) $features[] = 'wifi';
    if (!empty($data['parking'])) $features[] = 'parking';
    if (!empty($data['pets_policy']) && $data['pets_policy'] !== 'Refusés') $features[] = 'pets';
    if (!empty($data['near_pool'])) $features[] = 'pool';
  }

  $allItems[] = ['data' => $data, 'type' => $type, 'name' => $name, 'features' => $features, 'idx' => $idx];
  $idx++;
}

$totalItems = count($allItems);
$excludedKeys = ['name','image','images','link','image_url','latitude','longitude','gps_coordinates_latitude','gps_coordinates_longitude','latitude_deg','longitude_deg','description','desciption','comment','address','available_image_urls','site_officiel'];

function em_field_label($key, $fieldLabels) {
  if (isset($fieldLabels[$key])) return $fieldLabels[$key];
  return ucfirst(str_replace('_', ' ', $key));
}

$tagFields = [
  'nb_persons' => ['suffix' => ' pers.', 'class' => 'em-tag-blue'],
  'surface_avg' => ['suffix' => ' m²', 'class' => 'em-tag-green'],
  'surface_min' => ['suffix' => ' m²', 'class' => 'em-tag-green'],
  'distance_kilometers' => ['suffix' => ' km', 'class' => 'em-tag-blue'],
  'distance_camper_km' => ['suffix' => '', 'class' => 'em-tag-blue'],
  'driving_time_minutes' => ['suffix' => ' min', 'class' => 'em-tag-orange'],
  'temps_camper_min' => ['suffix' => '', 'class' => 'em-tag-orange'],
  'price' => ['suffix' => '', 'class' => 'em-tag-green'],
  'date' => ['suffix' => '', 'class' => 'em-tag-orange'],
  'city' => ['suffix' => '', 'class' => 'em-tag-blue'],
  'department' => ['suffix' => '', 'class' => 'em-tag-purple'],
  'place' => ['suffix' => '', 'class' => 'em-tag-blue'],
  'category_type' => ['suffix' => '', 'class' => 'em-tag-purple'],
  'labels_touristiques' => ['suffix' => '', 'class' => 'em-tag-green'],
];
$tagFieldKeys = array_keys($tagFields);
?>

<script>var emFieldLabels = <?php echo wp_json_encode($fieldLabels); ?>;</script>

<div class="em-wrap">
  <div class="em-header">
    <div>
      <h1 class="em-header-title">Découvrez autour de vous</h1>
      <p class="em-header-sub"><?php echo $totalItems; ?> fiche(s) dans <?php echo count($categories); ?> catégorie(s)</p>
    </div>
    <div class="em-search-wrap">
      <svg class="em-search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
      <input type="text" class="em-search" id="em-search" placeholder="Rechercher une fiche...">
    </div>
  </div>

  <?php if (count($categories) > 1): ?>
  <div class="em-tabs" id="em-tabs">
    <button class="em-tab active" data-filter="all">Tout<span class="em-tab-count"><?php echo $totalItems; ?></span></button>
    <?php foreach ($categories as $slug => $cat): ?>
      <button class="em-tab" data-filter="<?php echo esc_attr($slug); ?>"><?php echo esc_html($cat['label']); ?><span class="em-tab-count"><?php echo $cat['count']; ?></span></button>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if (!empty($mapPoints)): ?>
  <div class="em-map-section">
    <button class="em-map-toggle active" id="em-map-toggle">📍 Carte</button>
    <div class="em-map-wrap" id="em-map-wrap">
      <div id="em-map" class="em-map"></div>
      <div class="em-map-legend">
        <?php foreach ($categories as $cat): ?>
          <div class="em-map-legend-item"><span class="em-map-legend-dot" style="background:<?php echo $cat['color']; ?>;"></span> <?php echo esc_html($cat['label']); ?></div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <div class="em-layout">
    <aside class="em-sidebar">
      <div class="em-filters">
        <h3 class="em-filters-title">Filtres</h3>
        <div class="em-filter-group">
          <label class="em-filter-label">Type</label>
          <?php foreach ($categories as $slug => $cat): ?>
            <label class="em-filter-check"><input type="checkbox" value="<?php echo esc_attr($slug); ?>" class="em-filter-type" checked><span><?php echo esc_html($cat['label']); ?></span></label>
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
        <button class="em-filter-reset" id="em-reset-filters">Réinitialiser</button>
      </div>
    </aside>

    <main class="em-main">
      <div class="em-stats">
        <div class="em-stat">Total : <strong id="em-stat-total"><?php echo $totalItems; ?></strong></div>
        <div class="em-stat">Affichés : <strong id="em-stat-visible"><?php echo $totalItems; ?></strong></div>
      </div>

      <div class="em-cards" id="em-cards">
        <?php foreach ($allItems as $item):
          $data = $item['data']; $type = $item['type']; $name = $item['name'];
          $feats = implode(',', $item['features']);
          $catInfo = $categories[$type];
          $img = $data['image'] ?? $data['images'] ?? '';
          if (is_string($img) && strpos($img, "\n") !== false) $img = explode("\n", $img)[0];
          if (is_array($img)) $img = $img[0] ?? '';
          $img = trim($img);
          $link = $data['link'] ?? $data['site_officiel'] ?? '';
        ?>
          <article class="em-card" data-type="<?php echo esc_attr($type); ?>" data-features="<?php echo esc_attr($feats); ?>" data-name="<?php echo esc_attr(strtolower($name)); ?>" data-json="<?php echo esc_attr(wp_json_encode(['name' => $name, 'image' => $img, 'link' => $link, 'data' => $data, 'category' => $catInfo['label']])); ?>">
            <div class="em-card-img-wrap">
              <?php if ($img && filter_var($img, FILTER_VALIDATE_URL)): ?>
                <img class="em-card-img" src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($name); ?>" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                <div class="em-card-placeholder" style="display:none;">📷 Image indisponible</div>
              <?php else: ?>
                <div class="em-card-placeholder">📷 Pas d'image</div>
              <?php endif; ?>
              <span class="em-card-badge <?php echo esc_attr($catInfo['badge']); ?>"><?php echo esc_html($catInfo['label']); ?></span>
            </div>
            <div class="em-card-body">
              <h3 class="em-card-title"><?php echo esc_html($name); ?></h3>
              <div class="em-card-tags">
                <?php $tagCount = 0; foreach ($tagFields as $tf => $tconf):
                  if ($tagCount >= 4) break;
                  if (!empty($data[$tf]) && !is_array($data[$tf])): $tagCount++; ?>
                    <span class="em-tag <?php echo $tconf['class']; ?>"><?php echo esc_html($data[$tf] . $tconf['suffix']); ?></span>
                <?php endif; endforeach; ?>
                <?php if (!empty($data['wifi'])): ?><span class="em-tag em-tag-purple">WiFi</span><?php endif; ?>
              </div>
              <div class="em-card-fields">
                <?php $fc = 0; foreach ($data as $key => $val):
                  if (in_array($key, $excludedKeys) || in_array($key, $tagFieldKeys) || $key === 'wifi') continue;
                  if (is_array($val) || is_object($val) || empty($val)) continue;
                  if ($fc >= 3) break; $fc++;
                ?>
                  <div class="em-card-field">
                    <span class="em-card-field-label"><?php echo esc_html(em_field_label($key, $fieldLabels)); ?> :</span>
                    <span class="em-card-field-value"><?php echo esc_html(is_bool($val) ? ($val ? 'Oui' : 'Non') : mb_substr((string)$val, 0, 60)); ?></span>
                  </div>
                <?php endforeach; ?>
              </div>
              <div class="em-card-footer">
                <span>Voir le détail</span>
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"></path></svg>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <div class="em-empty" id="em-empty" style="display:none;">
        <p><strong>Aucun résultat</strong><br>Modifiez vos filtres pour voir plus de résultats</p>
      </div>
    </main>
  </div>
</div>

<script>
(function() {
  var FL = window.emFieldLabels || {};
  var EXCLUDED = ['name','image','images','image_url','link','site_officiel','gps_coordinates_latitude','gps_coordinates_longitude','latitude','longitude','latitude_deg','longitude_deg','address','available_image_urls'];

  function fieldLabel(key) {
    return FL[key] || key.replace(/_/g, ' ').replace(/^\w/, function(c) { return c.toUpperCase(); });
  }

  function esc(s) { if (!s) return ''; var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

  // ═══════════════════════════════════════
  // FILTERS
  // ═══════════════════════════════════════

  var activeCategory = 'all', searchQuery = '';

  function applyFilters() {
    var selTypes = Array.from(document.querySelectorAll('.em-filter-type:checked')).map(function(c) { return c.value; });
    var selFeats = Array.from(document.querySelectorAll('.em-filter-feat:checked')).map(function(c) { return c.value; });
    var cards = document.querySelectorAll('.em-card'), vis = 0;
    cards.forEach(function(card) {
      var t = card.getAttribute('data-type'),
          f = (card.getAttribute('data-features') || '').split(',').filter(Boolean),
          n = card.getAttribute('data-name') || '';
      var ok = (activeCategory === 'all' || activeCategory === t)
            && selTypes.indexOf(t) !== -1
            && (searchQuery === '' || n.indexOf(searchQuery.toLowerCase()) !== -1);
      if (ok && (t === 'accommodation' || t === 'accomodation') && selFeats.length > 0) {
        ok = selFeats.every(function(x) { return f.indexOf(x) !== -1; });
      }
      card.style.display = ok ? '' : 'none';
      if (ok) vis++;
    });
    document.getElementById('em-stat-visible').textContent = vis;
    document.getElementById('em-empty').style.display = vis === 0 ? 'block' : 'none';
    if (typeof updateMapMarkers === 'function') updateMapMarkers(activeCategory === 'all' ? selTypes : [activeCategory]);
  }

  // Tabs
  document.querySelectorAll('.em-tab').forEach(function(t) {
    t.addEventListener('click', function() {
      document.querySelectorAll('.em-tab').forEach(function(x) { x.classList.remove('active'); });
      t.classList.add('active');
      activeCategory = t.getAttribute('data-filter');
      applyFilters();
    });
  });

  // Checkboxes
  document.querySelectorAll('.em-filter-type,.em-filter-feat').forEach(function(c) {
    c.addEventListener('change', applyFilters);
  });

  // Search
  var se = document.getElementById('em-search');
  if (se) se.addEventListener('input', function(e) { searchQuery = e.target.value; applyFilters(); });

  // Reset
  var re = document.getElementById('em-reset-filters');
  if (re) re.addEventListener('click', function() {
    document.querySelectorAll('.em-filter-type').forEach(function(c) { c.checked = true; });
    document.querySelectorAll('.em-filter-feat').forEach(function(c) { c.checked = false; });
    if (se) se.value = '';
    searchQuery = '';
    activeCategory = 'all';
    document.querySelectorAll('.em-tab').forEach(function(t) { t.classList.remove('active'); });
    var a = document.querySelector('.em-tab[data-filter="all"]');
    if (a) a.classList.add('active');
    applyFilters();
  });

  applyFilters();

  // ═══════════════════════════════════════
  // MODAL
  // ═══════════════════════════════════════

  document.querySelectorAll('.em-card').forEach(function(card) {
    card.addEventListener('click', function() {
      showModal(JSON.parse(card.getAttribute('data-json')));
    });
  });

  function showModal(item) {
    var entries = Object.entries(item.data || {}).filter(function(e) {
      return EXCLUDED.indexOf(e[0]) === -1 && e[1] && e[1] !== '' && typeof e[1] !== 'object';
    }).map(function(e) {
      var v = e[1];
      if (v === true || v === 'true') v = 'Oui';
      else if (v === false || v === 'false') v = 'Non';
      return { label: fieldLabel(e[0]), value: String(v) };
    });

    var hi = item.image && item.image.indexOf('http') === 0;
    var h = '<div class="em-modal">';

    if (hi) {
      h += '<div class="em-modal-img-wrap">'
        + '<img class="em-modal-img" src="' + esc(item.image) + '" alt="' + esc(item.name) + '" onerror="this.style.display=\'none\'">'
        + '<div class="em-modal-img-gradient"></div>'
        + '<button class="em-modal-close" data-close>&times;</button>'
        + '<div class="em-modal-img-info">'
        + '<p class="em-modal-img-cat">' + esc(item.category || '') + '</p>'
        + '<h3 class="em-modal-img-title">' + esc(item.name) + '</h3>'
        + '</div></div>';
    } else {
      h += '<div class="em-modal-header-noimg"><div>'
        + '<p class="em-modal-header-noimg-cat">' + esc(item.category || '') + '</p>'
        + '<h3 class="em-modal-header-noimg-title">' + esc(item.name) + '</h3>'
        + '</div><button class="em-modal-close-noimg" data-close>&times;</button></div>';
    }

    h += '<div class="em-modal-body">';
    entries.forEach(function(e) {
      h += '<div class="em-modal-entry">'
        + '<p class="em-modal-entry-label">' + esc(e.label) + '</p>'
        + '<p class="em-modal-entry-value">' + esc(e.value) + '</p>'
        + '</div>';
    });
    h += '</div><div class="em-modal-footer">';
    if (item.link && item.link.indexOf('http') === 0) {
      h += '<a href="' + esc(item.link) + '" target="_blank" rel="noopener" class="em-modal-link">Ouvrir le lien &rarr;</a>';
    } else {
      h += '<span></span>';
    }
    h += '<button class="em-modal-close-btn" data-close>Fermer</button></div></div>';

    var o = document.createElement('div');
    o.className = 'em-modal-overlay';
    o.innerHTML = h;
    document.body.appendChild(o);

    o.querySelectorAll('[data-close]').forEach(function(b) {
      b.addEventListener('click', function() { o.remove(); });
    });
    o.addEventListener('click', function(e) { if (e.target === o) o.remove(); });
    document.addEventListener('keydown', function escH(e) {
      if (e.key === 'Escape') { o.remove(); document.removeEventListener('keydown', escH); }
    });
  }

  // ═══════════════════════════════════════
  // MAP
  // ═══════════════════════════════════════

  var mapEl = document.getElementById('em-map');
  if (mapEl && typeof L !== 'undefined') {
    var map = L.map(mapEl, { zoomControl: false, attributionControl: false }).setView([46.6, 1.9], 6);

    L.control.zoom({ position: 'topright' }).addTo(map);
    L.control.attribution({ position: 'bottomright', prefix: false })
      .addAttribution('&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; <a href="https://carto.com/">CARTO</a>')
      .addTo(map);

    // CartoDB Voyager — cleaner tiles
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
      maxZoom: 19,
      subdomains: 'abcd'
    }).addTo(map);

    var pts = <?php echo wp_json_encode($mapPoints); ?>;
    var cc = <?php echo wp_json_encode(array_map(fn($c) => $c['color'], $categories)); ?>;
    var catLabels = <?php echo wp_json_encode(array_map(fn($c) => $c['label'], $categories)); ?>;
    var mks = [], ab = [];
    var activeMarker = null;

    function createMarkerIcon(color, size) {
      size = size || 12;
      var total = size + 8;
      var cx = total / 2;
      return L.divIcon({
        className: 'em-marker',
        html: '<svg width="' + total + '" height="' + total + '" viewBox="0 0 ' + total + ' ' + total + '">'
          + '<circle cx="' + cx + '" cy="' + cx + '" r="' + (size / 2 + 2) + '" fill="white" opacity="0.9"/>'
          + '<circle cx="' + cx + '" cy="' + cx + '" r="' + (size / 2) + '" fill="' + color + '"/>'
          + '</svg>',
        iconSize: [total, total],
        iconAnchor: [cx, cx],
        popupAnchor: [0, -(size / 2 + 6)]
      });
    }

    pts.forEach(function(p) {
      var color = cc[p.type] || '#6b7280';
      var catLabel = catLabels[p.type] || p.type;
      var ic = createMarkerIcon(color, 12);
      var mk = L.marker([p.lat, p.lng], { icon: ic }).addTo(map);

      // Rich popup
      var popupHtml = '<div class="em-map-popup">'
        + '<p class="em-map-popup-name">' + esc(p.name) + '</p>'
        + '<p class="em-map-popup-cat" style="color:' + color + '">' + esc(catLabel) + '</p>'
        + '<button class="em-map-popup-btn" data-card-idx="' + p.idx + '">Voir la fiche →</button>'
        + '</div>';
      mk.bindPopup(popupHtml, { closeButton: false, minWidth: 180 });

      mk.on('click', function() {
        if (activeMarker && activeMarker.getElement()) activeMarker.getElement().classList.remove('em-marker--active');
        if (mk.getElement()) mk.getElement().classList.add('em-marker--active');
        activeMarker = mk;
      });

      mk.on('popupopen', function() {
        var btn = document.querySelector('.em-map-popup-btn[data-card-idx="' + p.idx + '"]');
        if (btn) {
          btn.addEventListener('click', function() {
            var cards = document.querySelectorAll('.em-card');
            var target = cards[p.idx];
            if (target && target.style.display !== 'none') {
              target.scrollIntoView({ behavior: 'smooth', block: 'center' });
              target.classList.add('em-card--highlight');
              setTimeout(function() { target.classList.remove('em-card--highlight'); }, 2000);
            }
            map.closePopup();
          });
        }
      });

      mks.push({ marker: mk, type: p.type, ll: [p.lat, p.lng], idx: p.idx });
      ab.push([p.lat, p.lng]);
    });

    if (ab.length > 1) map.fitBounds(ab, { padding: [40, 40] });
    else if (ab.length === 1) map.setView(ab[0], 13);
    setTimeout(function() { map.invalidateSize(); }, 100);

    // Card hover ↔ marker highlight
    document.querySelectorAll('.em-card').forEach(function(card, cardIdx) {
      card.addEventListener('mouseenter', function() {
        mks.forEach(function(m) {
          if (m.idx === cardIdx) {
            m.marker.setIcon(createMarkerIcon(cc[m.type] || '#6b7280', 18));
            m.marker.setZIndexOffset(1000);
          }
        });
      });
      card.addEventListener('mouseleave', function() {
        mks.forEach(function(m) {
          if (m.idx === cardIdx) {
            m.marker.setIcon(createMarkerIcon(cc[m.type] || '#6b7280', 12));
            m.marker.setZIndexOffset(0);
          }
        });
      });
    });

    // Filter → update markers
    window.updateMapMarkers = function(st) {
      var v = [];
      mks.forEach(function(m) {
        if (st.indexOf(m.type) !== -1) {
          if (!map.hasLayer(m.marker)) map.addLayer(m.marker);
          v.push(m.ll);
        } else {
          if (map.hasLayer(m.marker)) map.removeLayer(m.marker);
        }
      });
      if (v.length > 1) map.fitBounds(v, { padding: [40, 40], maxZoom: 14 });
      else if (v.length === 1) map.setView(v[0], 13);
    };

    // Toggle map visibility
    var mt = document.getElementById('em-map-toggle'), mw = document.getElementById('em-map-wrap');
    if (mt && mw) {
      mt.addEventListener('click', function() {
        var v = mw.style.display !== 'none';
        mw.style.display = v ? 'none' : '';
        mt.classList.toggle('active', !v);
        if (!v) setTimeout(function() { map.invalidateSize(); }, 100);
      });
    }
  }

  // ═══════════════════════════════════════
  // COLLAPSIBLE FILTERS ON MOBILE
  // ═══════════════════════════════════════

  function initMobileFilters() {
    var filtersEl = document.querySelector('.em-filters');
    var filtersTitle = document.querySelector('.em-filters-title');
    if (!filtersEl || !filtersTitle) return;

    if (window.innerWidth <= 720) {
      filtersEl.classList.add('collapsed');
    }

    filtersTitle.addEventListener('click', function() {
      if (window.innerWidth <= 720) {
        filtersEl.classList.toggle('collapsed');
      }
    });
  }

  initMobileFilters();
})();
</script>

<?php endif; ?>