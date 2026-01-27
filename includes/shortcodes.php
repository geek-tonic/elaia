<?php

add_action(
  'init',
  function () {
    add_shortcode('elaia_faq', function () {
      if (!defined('ELAIA_PLUGIN_DIR')) return '';

      ob_start();
      include_once ELAIA_PLUGIN_DIR . 'includes/Pages/Faq.php';
      elaia_prepare_faq_payload();
      return ob_get_clean();
    });

    add_shortcode('elaia_metadatas', function () {
      if (!defined('ELAIA_PLUGIN_DIR')) return '';

      ob_start();
      include_once ELAIA_PLUGIN_DIR . 'includes/Pages/Metadata.php';
      elaia_prepare_metadata_payload();
      return ob_get_clean();
    });
  },
  20
);
