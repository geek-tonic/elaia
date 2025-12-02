<?php

if (!defined('ABSPATH') || !defined('ELAIA_PLUGIN_DIR'))
  exit;

// Register rewrite rules for Elaia Pages
function elaia_register_rewrites()
{
  // Declare rewrite tags
  add_rewrite_tag('%' . ELAIA_PAGE_FAQ_PARAM . '%', '([0-1])');
  add_rewrite_tag('%' . ELAIA_PAGE_METADATA_PARAM . '%', '([0-1])');

  // Rules for custom pages
  add_rewrite_rule(
    '^' . ELAIA_PAGE_FAQ_REWRITE . '/?$',
    'index.php?' . ELAIA_PAGE_FAQ_PARAM . '=1',
    'top'
  );

  add_rewrite_rule(
    '^' . ELAIA_PAGE_METADATA_REWRITE . '/?$',
    'index.php?' . ELAIA_PAGE_METADATA_PARAM . '=1',
    'top'
  );
}
add_action('init', 'elaia_register_rewrites');





// Gestion des pages spécifiques via l'action template_redirect
add_action('template_redirect', function () {
  if (get_query_var(ELAIA_PAGE_FAQ_PARAM) == 1) {
    include ELAIA_PLUGIN_DIR . 'includes/Pages/Faq.php';
    elaia_prepare_faq_payload();
    exit;
  }

  if (get_query_var(ELAIA_PAGE_METADATA_PARAM) == 1) {
    include ELAIA_PLUGIN_DIR . 'includes/Pages/Metadata.php';
    elaia_prepare_metadata_payload();
    exit;
  }
});
