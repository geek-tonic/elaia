<?php if (! defined('ABSPATH') || ! defined('ELAIA_PLUGIN_DIR')) {
    exit;
} ?>
<style>
  .elaia-faq__wrap { max-width: 900px !important; margin: 24px auto !important; padding: 0 16px !important; }
  .elaia-faq__grid { display: grid !important; gap: 12px !important; }
  .elaia-faq__grid details.elaia-faq__item {
    border: 1px solid #e2e2e2; padding: 1rem; background: white; border-radius: 9px;
    height: auto !important; max-height: none !important; overflow: visible !important;
  }
  .elaia-faq__grid details.elaia-faq__item[open] { padding-bottom: 1em; height: auto !important; max-height: none !important; overflow: visible !important; }
  .elaia-faq__grid details.elaia-faq__item > summary { font-size: 1rem; font-weight: bold; cursor: pointer; height: auto !important; max-height: none !important; overflow: visible !important; }
  .elaia-faq__grid details.elaia-faq__item > p { margin: 0 !important; padding: 1rem 0 0 0 !important; font-style: normal; font-size: 1rem; }
  .elaia-faq__notice { color: #6b7280 !important; background: #f9fafb !important; border: 1px solid #e5e7eb !important; border-radius: 8px !important; padding: 12px !important; font-size: 14px !important; }
</style>

<div class="elaia-faq__wrap">
  <?php
  $is_admin = is_user_logged_in() && current_user_can('manage_options');

// Erreur réseau ou réponse non-2xx : silencieux pour les visiteurs, diagnostic pour les admins.
if (! $api_ok || $api_code < 200 || $api_code >= 300) {
    if ($is_admin) {
        if (! $api_ok) {
            echo '<div class="elaia-faq__notice">Elaia (admin) — Erreur réseau API : '.esc_html($api_err).'</div>';
        } elseif ($api_code === 404) {
            echo '<div class="elaia-faq__notice">Elaia (admin) — Groupe de FAQ introuvable pour ce site. Vérifiez le slug du shortcode.</div>';
        } else {
            echo '<div class="elaia-faq__notice">Elaia (admin) — Réponse API inattendue (code '.(int) $api_code.').</div>';
        }
    }

    return;
}

// Inject JSON-LD (SEO + GEO)
if (is_array($corpus) && ! empty($corpus)) {
    echo '<script type="application/ld+json">'.wp_json_encode($corpus, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).'</script>';
}

// Rendu FAQ
if (is_array($questions) && ! empty($questions)) {
    echo '<div class="elaia-faq__grid">';
    foreach ($questions as $q) {
        $qname = isset($q['name']) ? wp_kses_post($q['name']) : 'Question';
        $atext = (! empty($q['acceptedAnswer']) && is_array($q['acceptedAnswer']) && ! empty($q['acceptedAnswer']['text']))
            ? wp_kses_post($q['acceptedAnswer']['text'])
            : '<em>Réponse à renseigner.</em>';
        echo '<details class="elaia-faq__item">';
        echo '<summary>'.$qname.'</summary>';
        echo '<p>'.$atext.'</p>';
        echo '</details>';
    }
    echo '</div>';
} elseif ($is_admin) {
    echo '<div class="elaia-faq__notice">Elaia (admin) — Ce groupe de FAQ ne contient encore aucune question.</div>';
}
?>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.elaia-faq__grid details').forEach(d => {
      d.style.height = 'auto';
      d.style.overflow = 'visible';
      d.addEventListener('toggle', () => {
        d.style.height = 'auto';
        d.style.overflow = 'visible';
      });
    });
  });
</script>
