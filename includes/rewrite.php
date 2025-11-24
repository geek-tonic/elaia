<?php

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


// Refresh permalinks only if rules change
function elaia_maybe_flush_rules()
{
  $current = 1;
  $saved   = (int) get_option('elaia_rewrite_version', 0);

  if ($saved !== $current) {
    elaia_register_rewrites();
    flush_rewrite_rules();
    update_option('elaia_rewrite_version', $current);
  }
}
add_action('init', 'elaia_maybe_flush_rules', 99);


// Gestion des pages spécifiques via l'action template_redirect
add_action('template_redirect', function () {
  if (get_query_var(ELAIA_PAGE_FAQ_PARAM) == 1) {
    include plugin_dir_path(__FILE__) . 'includes/Pages/Faq.php';
    exit;
  }

  if (get_query_var(ELAIA_PAGE_METADATA_PARAM) == 1) {
    include plugin_dir_path(__FILE__) . 'includes/Pages/Metadata.php';
    exit;
  }
});
