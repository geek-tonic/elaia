<?php if (!defined('ABSPATH') || !defined('ELAIA_PLUGIN_DIR')) exit; ?>

<style>
  :root {
    --color-primary: <?php echo $style['primary_color'] ?? '#3b82f6'; ?>;
    --color-bg: <?php echo $style['background_color'] ?? '#f9fafb'; ?>;
    --color-card: <?php echo $style['card_bg_color'] ?? '#fff'; ?>;
    --color-border: <?php echo $style['card_border_color'] ?? '#e5e7eb'; ?>;
    --color-text: <?php echo $style['text_color'] ?? '#111827'; ?>;
    --color-text-light: color-mix(in srgb, var(--color-text) 65%, transparent);
    --color-success: #10b981;
    --color-warning: #f59e0b;
    --color-danger: #ef4444;
  }

  /* Layout principal */
  .elaia-container {
    display: flex;
    gap: 24px;
    max-width: 1400px;
    margin: 0 auto;
    padding: 24px;
  }

  .elaia-sidebar {
    flex: 0 0 280px;
    position: sticky;
    top: 24px;
    height: fit-content;
  }

  .elaia-main {
    flex: 1;
    min-width: 0;
  }

  /* Sidebar - Filtres */
  .elaia-filters {
    background: var(--color-card);
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, .04);
    border: 1px solid var(--color-border);
  }

  .elaia-filters__title {
    font-size: 18px;
    font-weight: 700;
    margin: 0 0 16px;
    color: var(--color-text);
  }

  .elaia-filter-group {
    margin-bottom: 20px;
  }

  .elaia-filter-group:last-child {
    margin-bottom: 0;
  }

  .elaia-filter-label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: var(--color-text);
    margin-bottom: 8px;
  }

  .elaia-filter-checkbox {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 0;
    cursor: pointer;
  }

  .elaia-filter-checkbox input {
    cursor: pointer;
    accent-color: var(--color-primary);
  }

  .elaia-filter-checkbox span {
    font-size: 14px;
    color: var(--color-text-light);
  }

  .elaia-filter-reset {
    width: 100%;
    padding: 10px;
    background: var(--color-text);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 16px;
    transition: background .2s;
  }

  .elaia-filter-reset:hover {
    background: #000;
  }

  /* Stats bar */
  .elaia-stats {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
  }

  .elaia-stat {
    background: var(--color-card);
    border: 1px solid var(--color-border);
    border-radius: 10px;
    padding: 12px 18px;
    font-size: 14px;
    color: var(--color-text-light);
  }

  .elaia-stat strong {
    color: var(--color-text);
    font-weight: 700;
    margin-left: 4px;
  }

  /* Cards list */
  .elaia-cards {
    display: grid;
    gap: 20px;
  }

  .elaia-card {
    display: flex;
    gap: 18px;
    background: var(--color-card);
    border: 1px solid var(--color-border);
    border-radius: 16px;
    padding: 18px;
    transition: all .2s ease;
    box-shadow: 0 2px 6px rgba(0, 0, 0, .03);
  }

  .elaia-card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, .08);
    transform: translateY(-2px);
  }

  .elaia-card__img {
    flex: 0 0 220px;
    border-radius: 12px;
    overflow: hidden;
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
  }

  .elaia-card__img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .elaia-card__badge {
    position: absolute;
    top: 10px;
    left: 10px;
    padding: 6px 10px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    backdrop-filter: blur(8px);
  }

  .badge--accom {
    background: rgba(16, 185, 129, .9);
    color: #fff;
  }

  .badge--city {
    background: rgba(59, 130, 246, .9);
    color: #fff;
  }

  .badge--activity {
    background: rgba(168, 85, 247, .9);
    color: #fff;
  }

  .badge--event {
    background: rgba(239, 68, 68, .9);
    color: #fff;
  }

  .elaia-card__body {
    flex: 1;
    display: flex;
    flex-direction: column;
  }

  .elaia-card__title {
    font-size: 20px;
    font-weight: 700;
    margin: 0 0 10px;
    color: var(--color-text);
    line-height: 1.3;
  }

  .elaia-card__title a {
    color: inherit;
    text-decoration: none;
  }

  .elaia-card__title a:hover {
    color: var(--color-primary);
  }

  .elaia-card__tags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 12px;
  }

  .elaia-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    background: #f3f4f6;
    color: var(--color-text-light);
  }

  .elaia-tag svg {
    width: 14px;
    height: 14px;
  }

  .elaia-tag--primary {
    background: #dbeafe;
    color: #1e40af;
  }

  .elaia-tag--success {
    background: #d1fae5;
    color: #065f46;
  }

  .elaia-tag--warning {
    background: #fed7aa;
    color: #92400e;
  }

  .elaia-tag--purple {
    background: #ede9fe;
    color: #6b21a8;
  }

  .elaia-card__desc {
    font-size: 14px;
    color: var(--color-text-light);
    line-height: 1.6;
    margin: 0 0 12px;
    display: -webkit-box;
    line-clamp: 3;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .elaia-card__grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px 16px;
    margin: auto 0 12px 0;
    font-size: 13px;
  }

  .elaia-card__row {
    display: flex;
    gap: 6px;
    align-items: start;
  }

  .elaia-card__row svg {
    width: 16px;
    height: 16px;
    color: var(--color-text-light);
    flex-shrink: 0;
    margin-top: 2px;
  }

  .elaia-card__row span {
    color: var(--color-text);
  }

  .elaia-card__footer {
    display: flex;
    gap: 10px;
    margin-top: auto;
  }

  .elaia-btn {
    padding: 10px 18px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all .2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: none;
    cursor: pointer;
  }

  .elaia-btn--primary {
    background: var(--color-primary);
    color: #fff;
  }

  .elaia-btn--primary:hover {
    background: color-mix(in srgb, var(--color-primary) 85%, transparent);
    ;
  }

  .elaia-btn--secondary {
    background: #f3f4f6;
    color: var(--color-text);
  }

  .elaia-btn--secondary:hover {
    background: #e5e7eb;
  }

  /* No results */
  .elaia-empty {
    text-align: center;
    padding: 60px 20px;
    color: var(--color-text-light);
  }

  .elaia-empty svg {
    width: 80px;
    height: 80px;
    margin-bottom: 16px;
    color: #d1d5db;
  }

  /* Responsive */
  @media (max-width:1024px) {
    .elaia-container {
      flex-direction: column;
    }

    .elaia-sidebar {
      position: static;
      flex-basis: auto;
    }
  }

  @media (max-width:720px) {
    .elaia-card {
      flex-direction: column;
    }

    .elaia-card__img {
      flex-basis: auto;
      height: 200px;
    }

    .elaia-card__grid {
      grid-template-columns: 1fr;
    }

    .elaia-container {
      padding: 16px;
    }
  }
</style>


<?php if (!$api_ok): ?>
  <div style="max-width:1200px;margin:20px auto;padding:20px;background:#fee;border:1px solid #fcc;border-radius:12px;color:#c00;">
    <strong>Erreur réseau API :</strong> <?php echo esc_html($api_err); ?>
  </div>
<?php elseif ($api_code < 200 || $api_code >= 300): ?>
  <div style="max-width:1200px;margin:20px auto;padding:20px;background:#fee;border:1px solid #fcc;border-radius:12px;color:#c00;">
    <strong>Réponse API inattendue (code <?php echo (int)$api_code; ?>)</strong>
  </div>
<?php endif; ?>

<?php
if (is_array($geoMetadatas) && !empty($geoMetadatas)) {
  echo '<script type="application/ld+json">' . wp_json_encode($geoMetadatas, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
}
?>

<?php if (is_array($metas) && !empty($metas)): ?>

  <div class="elaia-container">
    <!-- SIDEBAR FILTRES -->
    <aside class="elaia-sidebar">
      <div class="elaia-filters">
        <h3 class="elaia-filters__title">🔍 Filtres</h3>

        <!-- Filtre par type -->
        <div class="elaia-filter-group">
          <label class="elaia-filter-label">Type</label>
          <label class="elaia-filter-checkbox">
            <input type="checkbox" value="accommodation" class="filter-type" checked>
            <span>Hébergements</span>
          </label>
          <label class="elaia-filter-checkbox">
            <input type="checkbox" value="city" class="filter-type" checked>
            <span>Villes</span>
          </label>
          <label class="elaia-filter-checkbox">
            <input type="checkbox" value="activity" class="filter-type" checked>
            <span>Activités</span>
          </label>
          <label class="elaia-filter-checkbox">
            <input type="checkbox" value="event" class="filter-type" checked>
            <span>Événements</span>
          </label>
        </div>

        <!-- Filtre hébergements -->
        <div class="elaia-filter-group">
          <label class="elaia-filter-label">Hébergements</label>
          <label class="elaia-filter-checkbox">
            <input type="checkbox" value="wifi" class="filter-feature">
            <span>WiFi</span>
          </label>
          <label class="elaia-filter-checkbox">
            <input type="checkbox" value="parking" class="filter-feature">
            <span>Parking</span>
          </label>
          <label class="elaia-filter-checkbox">
            <input type="checkbox" value="pets" class="filter-feature">
            <span>Animaux acceptés</span>
          </label>
          <label class="elaia-filter-checkbox">
            <input type="checkbox" value="pool" class="filter-feature">
            <span>Proche piscine</span>
          </label>
        </div>

        <button class="elaia-filter-reset" onclick="resetFilters()">Réinitialiser</button>
      </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="elaia-main">
      <!-- Stats -->
      <div class="elaia-stats">
        <div class="elaia-stat">
          Total : <strong id="stat-total">0</strong>
        </div>
        <div class="elaia-stat">
          Affichés : <strong id="stat-visible">0</strong>
        </div>
      </div>

      <!-- Cards -->
      <div class="elaia-cards" id="cards-container">
        <?php
        foreach ($metas as $m) {
          if (!is_array($m)) continue;
          $type = isset($m['type']) ? strtolower(trim((string)$m['type'])) : '';
          $data = isset($m['data']) ? (is_string($m['data']) ? json_decode($m['data'], true) : $m['data']) : [];
          if (!is_array($data)) $data = [];

          // Normaliser type
          if ($type === 'accomodation') $type = 'accommodation';
          if (!in_array($type, ['accommodation', 'city', 'activity', 'event'], true)) continue;

          $name = $data['name'] ?? '';
          $link = $data['link'] ?? '';
          $img = $data['image'] ?? $data['images'] ?? '';
          if (is_string($img) && strpos($img, "\n") !== false) {
            $img = explode("\n", $img)[0];
          }
          $img = trim($img);

          // Data attributes pour filtres
          $dataAttrs = 'data-type="' . esc_attr($type) . '"';

          // Features hébergements
          if ($type === 'accommodation') {
            $features = [];
            if (!empty($data['wifi'])) $features[] = 'wifi';
            if (!empty($data['parking'])) $features[] = 'parking';
            if (!empty($data['pets_policy']) && $data['pets_policy'] !== 'Refusés') $features[] = 'pets';
            if (!empty($data['near_pool'])) $features[] = 'pool';
            $dataAttrs .= ' data-features="' . esc_attr(implode(',', $features)) . '"';
          }

          // Badge type
          $badge_label = 'Info';
          $badge_class = '';
          if ($type === 'accommodation') {
            $badge_label = 'Hébergement';
            $badge_class = 'badge--accom';
          } elseif ($type === 'city') {
            $badge_label = 'Ville';
            $badge_class = 'badge--city';
          } elseif ($type === 'activity') {
            $badge_label = 'Activité';
            $badge_class = 'badge--activity';
          } elseif ($type === 'event') {
            $badge_label = 'Événement';
            $badge_class = 'badge--event';
          }

          echo '<article class="elaia-card" ' . $dataAttrs . '>';

          // Image
          echo '<div class="elaia-card__img">';
          if ($img && filter_var($img, FILTER_VALIDATE_URL)) {
            echo '<img src="' . esc_url($img) . '" alt="' . esc_attr($name) . '" loading="lazy" onerror="this.src=\'https://app.ela-ia.com/images/placeholder-2.png\';">';
          } else {
            echo '<img src="https://app.ela-ia.com/images/placeholder-2.png" alt="Placeholder">';
          }
          echo '<div class="elaia-card__badge ' . esc_attr($badge_class) . '">' . esc_html($badge_label) . '</div>';
          echo '</div>';

          // Body
          echo '<div class="elaia-card__body">';

          // Titre
          if ($name) {
            if ($link && filter_var($link, FILTER_VALIDATE_URL)) {
              echo '<h3 class="elaia-card__title"><a href="' . esc_url($link) . '" target="_blank" rel="noopener">' . esc_html($name) . '</a></h3>';
            } else {
              echo '<h3 class="elaia-card__title">' . esc_html($name) . '</h3>';
            }
          }

          // Tags selon type
          echo '<div class="elaia-card__tags">';
          if ($type === 'accommodation') {
            if (!empty($data['nb_persons'])) echo '<span class="elaia-tag elaia-tag--primary"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>' . esc_html($data['nb_persons']) . ' pers.</span>';
            if (!empty($data['surface_avg']) || !empty($data['surface_min'])) echo '<span class="elaia-tag elaia-tag--success">' . esc_html($data['surface_avg'] ?? $data['surface_min']) . ' m²</span>';
            if (!empty($data['wifi'])) echo '<span class="elaia-tag elaia-tag--purple">WiFi</span>';
            if (!empty($data['parking'])) echo '<span class="elaia-tag">Parking</span>';
          } elseif ($type === 'city') {
            if (!empty($data['distance_kilometers'])) echo '<span class="elaia-tag elaia-tag--primary">' . esc_html($data['distance_kilometers']) . ' km</span>';
            if (!empty($data['driving_time_minutes'])) echo '<span class="elaia-tag elaia-tag--warning">' . esc_html($data['driving_time_minutes']) . ' min</span>';
          } elseif ($type === 'activity') {
            if (!empty($data['price'])) echo '<span class="elaia-tag elaia-tag--success">' . esc_html($data['price']) . '</span>';
            if (!empty($data['place'])) echo '<span class="elaia-tag elaia-tag--primary">' . esc_html($data['place']) . '</span>';
          } elseif ($type === 'event') {
            if (!empty($data['date'])) echo '<span class="elaia-tag elaia-tag--warning">' . esc_html($data['date']) . '</span>';
            if (!empty($data['place'])) echo '<span class="elaia-tag elaia-tag--primary">' . esc_html($data['place']) . '</span>';
          }
          echo '</div>';

          // Description
          $desc = $data['description'] ?? $data['desciption'] ?? $data['comment'] ?? '';
          if ($desc) {
            echo '<p class="elaia-card__desc">' . esc_html($desc) . '</p>';
          }

          // Footer
          echo '<div class="elaia-card__footer">';
          if ($link && filter_var($link, FILTER_VALIDATE_URL)) {
            echo '<a href="' . esc_url($link) . '" target="_blank" rel="noopener" class="elaia-btn elaia-btn--primary">En savoir plus</a>';
          }
          echo '</div>';

          echo '</div>'; // body
          echo '</article>';
        }
        ?>
      </div>

      <!-- Empty state -->
      <div class="elaia-empty" id="empty-state" style="display:none;">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <p><strong>Aucun résultat</strong><br>Modifiez vos filtres pour voir plus de résultats</p>
      </div>
    </main>
  </div>

  <script>
    function applyFilters() {
      const typeCheckboxes = document.querySelectorAll('.filter-type:checked');
      const featureCheckboxes = document.querySelectorAll('.filter-feature:checked');

      const selectedTypes = Array.from(typeCheckboxes).map(cb => cb.value);
      const selectedFeatures = Array.from(featureCheckboxes).map(cb => cb.value);

      const cards = document.querySelectorAll('.elaia-card');
      let visibleCount = 0;

      cards.forEach(card => {
        const cardType = card.getAttribute('data-type');
        const cardFeatures = (card.getAttribute('data-features') || '').split(',').filter(f => f);

        // Check type
        const typeMatch = selectedTypes.includes(cardType);

        // Check features (only for accommodations)
        let featureMatch = true;
        if (cardType === 'accommodation' && selectedFeatures.length > 0) {
          featureMatch = selectedFeatures.every(f => cardFeatures.includes(f));
        }

        if (typeMatch && featureMatch) {
          card.style.display = 'flex';
          visibleCount++;
        } else {
          card.style.display = 'none';
        }
      });

      // Update stats
      document.getElementById('stat-total').textContent = cards.length;
      document.getElementById('stat-visible').textContent = visibleCount;

      // Show/hide empty state
      document.getElementById('empty-state').style.display = visibleCount === 0 ? 'block' : 'none';
    }

    function resetFilters() {
      document.querySelectorAll('.filter-type').forEach(cb => cb.checked = true);
      document.querySelectorAll('.filter-feature').forEach(cb => cb.checked = false);
      applyFilters();
    }

    // Event listeners
    document.querySelectorAll('.filter-type, .filter-feature').forEach(checkbox => {
      checkbox.addEventListener('change', applyFilters);
    });

    // Init
    applyFilters();
  </script>

<?php endif; ?>