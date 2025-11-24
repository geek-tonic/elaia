<!doctype html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo esc_html(get_bloginfo('name')); ?> — FAQ</title>
  <?php
  // Si votre thème a wp_head(); on peut l'appeler pour hériter des styles
  if (function_exists('wp_head')) wp_head();
  ?>
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

    .elaia-faq__item {
      border: 1px solid #e5e7eb !important;
      border-radius: 10px !important;
      background: #fff !important;
      padding: 0 14px 10px !important;
      transition: box-shadow .2s ease !important;
    }

    .faq-toggle {
      display: none !important;
    }

    .faq-toggle:checked+.elaia-faq__q+.elaia-faq__a {
      display: block !important;
    }

    .elaia-faq__item[open] {
      box-shadow: 0 6px 20px rgba(0, 0, 0, .05) !important;
    }

    .elaia-faq__q {
      cursor: pointer !important;
      font-weight: 600 !important;
      padding: 14px 0 !important;
      list-style: none !important;
    }

    .elaia-faq__q:after {
      content: '+' !important;
      float: right !important;
      font-weight: 700 !important;
      transform: translateY(-1px) !important;
    }

    .faq-toggle:checked+.elaia-faq__q:after {
      content: '−' !important;
    }

    .elaia-faq__a {
      display: none !important;
      padding: 0 0 6px !important;
      color: #374151 !important;
      line-height: 1.6 !important;
    }

    .elaia-faq__error {
      color: #b91c1c !important;
      background: #fee2e2 !important;
      border: 1px solid #fecaca !important;
      border-radius: 8px !important;
      padding: 12px !important;
    }
  </style>
</head>

<body <?php body_class(); ?>>

  <div class="elaia-faq__wrap">
    <h1 class="elaia-faq__title">FAQ</h1>

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
        echo '<div class="elaia-faq__item">';
        echo '<input type="radio" id="faq-' . $i . '" class="faq-toggle" name="faq">';
        echo '<label for="faq-' . $i . '" class="elaia-faq__q">' . $qname . '</label>';
        echo '<div class="elaia-faq__a">' . $atext . '</div>';
        echo '</div>';
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

  <?php
  // wp_footer si dispo (optionnel)
  if (function_exists('wp_footer')) wp_footer();
  ?>
</body>

</html>