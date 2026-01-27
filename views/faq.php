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

  .elaia-faq__grid details {
    border: 1px solid #e2e2e2;
    padding: 0 1rem;
    background: white;
    border-radius: 9px;
  }

  .elaia-faq__grid details+details {}

  .elaia-faq__grid details[open] {
    padding-bottom: 1em;
  }

  .elaia-faq__grid summary {
    padding: 1rem 2em 1rem 0;
    font-size: 1rem;
    font-weight: bold;
    cursor: pointer;
  }

  .elaia-faq__grid .elaia-faq__p {
    margin: 0;
    padding: 0;
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
      echo '<details>';
      echo '<summary for="faq-' . $i . '" class="elaia-faq__q">' . $qname . '</summary>';
      echo '<p class="elaia-faq__p">' . $atext . '</p>';
      echo '</details>';
      $i++;
    }
    echo '</div>';
  } else {
    if ($api_ok && $api_code >= 200 && $api_code < 300) {
      echo '<div class="elaia-faq__loading">Aucune question trouvée dans la réponse.</div>';
    }
  }
  ?>

</div>