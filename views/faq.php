<?php if (!defined('ABSPATH') || !defined('ELAIA_PLUGIN_DIR')) exit; ?>
<style>
  /* Styles minimalistes pour l'accordéon */
  .elaia-faq__wrap {
    max-width: 900px !important;
    margin: 40px auto !important;
    padding: 0 16px !important;
  }

  .elaia-faq__title {
    font-size: clamp(22px, 3vw, 32px) !important;
    font-weight: 700 !important;
    margin: 0 0 12px !important;
  }

  .elaia-faq__meta {
    color: #6b7280 !important;
    font-size: 14px !important;
    margin-bottom: 18px !important;
    word-break: break-all !important;
  }

  .elaia-faq__loading {
    padding: 12px 0 !important;
    font-style: italic !important;
  }

  .elaia-faq__grid {
    display: grid !important;
    gap: 12px !important;
  }

  .elaia-faq__grid details.elaia-faq__item {
    border: 1px solid #e2e2e2;
    padding: 1rem;
    background: white;
    border-radius: 9px;

    height: auto !important;
    max-height: none !important;
    overflow: visible !important;
  }

  .elaia-faq__grid details.elaia-faq__item[open] {
    padding-bottom: 1em;
    height: auto !important;
    max-height: none !important;
    overflow: visible !important;
  }

  .elaia-faq__grid details.elaia-faq__item>summary {
    font-size: 1rem;
    font-weight: bold;
    cursor: pointer;

    height: auto !important;
    max-height: none !important;
    overflow: visible !important;
  }

  .elaia-faq__grid details.elaia-faq__item>p {
    margin: 0 !important;
    padding: 1rem 0 0 0 !important;
    font-style: normal;
    font-size: 1rem;
  }

  .elaia-faq__error {
    color: #b91c1c !important;
    background: #fee2e2 !important;
    border: 1px solid #fecaca !important;
    border-radius: 8px !important;
    padding: 12px !important;
  }
</style>

<div class="elaia-faq__wrap">
  <?php if (!$api_ok): ?>
    <div class="elaia-faq__error">Erreur réseau API : <?php echo esc_html($api_err); ?></div>
  <?php elseif ($api_code < 200 || $api_code >= 300): ?>
    <div class="elaia-faq__error">Réponse API inattendue (code <?php echo (int)$api_code; ?>)</div>
    <pre style="white-space:pre-wrap;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:10px;max-height:300px;overflow:auto;"><?php echo esc_html($api_raw); ?></pre>
  <?php endif; ?>

  <?php
  // Inject JSON-LD si dispo
  if (is_array($corpus) && !empty($corpus)) {
    echo '<script type="application/ld+json">' . wp_json_encode($corpus, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
  }

  // Rendu FAQ
  if (is_array($questions) && !empty($questions)) {
    echo '<div class="elaia-faq__grid">';
    $i = 0;
    foreach ($questions as $q) {
      $qname = isset($q['name']) ? wp_kses_post($q['name']) : 'Question';
      $atext = '';
      if (!empty($q['acceptedAnswer']) && is_array($q['acceptedAnswer']) && !empty($q['acceptedAnswer']['text'])) {
        $atext = wp_kses_post($q['acceptedAnswer']['text']);
      } else {
        $atext = '<em>Réponse à renseigner.</em>';
      }
      echo '<details class="elaia-faq__item">';
      echo '<summary >' . $qname . '</summary>';
      echo '<p>' . $atext . '</p>';
      echo '</details>';
      $i++;
    }
    echo '</div>';
  } else {
    if ($api_ok && $api_code >= 200 && $api_code < 300) {
      echo '<div class="elaia-faq__loading">Aucune question n\'a été trouvée pour le site.</div>';
    }
  }
  ?>

</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    // Sécurité ++ parce qu'il y a des scripts qui forcent des hauteurs fixes
    document.querySelectorAll('.elaia-faq__grid details').forEach(d => {
      // Nettoyage initial
      d.style.height = 'auto';
      d.style.overflow = 'visible';

      // A chaque toggle, on re-nettoie si un script externe réinjecte
      d.addEventListener('toggle', () => {
        d.style.height = 'auto';
        d.style.overflow = 'visible';
      });
    });
  });
</script>