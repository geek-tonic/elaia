<?php

if (!defined('ABSPATH') || !defined('ELAIA_PLUGIN_DIR'))
  exit;

// Register rewrite rules for Elaia Pages
/*function elaia_register_rewrites()
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
add_action('init', 'elaia_register_rewrites');*/

// Redirections 301 des anciennes URLs (provenant du rewrite) vers les nouvelles pages créées
add_action('template_redirect', function () {
  if (get_query_var(ELAIA_PAGE_FAQ_PARAM) == 1) {
    wp_safe_redirect(home_url('/' . ELAIA_PAGE_FAQ_REWRITE . '/'), 301);
    exit;
  }
  if (get_query_var(ELAIA_PAGE_METADATA_PARAM) == 1) {
    wp_safe_redirect(home_url('/' . ELAIA_PAGE_METADATA_REWRITE . '/'), 301);
    exit;
  }
});





// Gestion des pages spécifiques via l'action template_redirect
/*add_action('template_redirect', function () {
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
});*/

add_filter('query_vars', function ($vars) {
  $vars[] = ELAIA_PAGE_FAQ_PARAM;
  $vars[] = ELAIA_PAGE_METADATA_PARAM;
  return $vars;
});

add_filter('template_include', function ($template) {

  if (get_query_var(ELAIA_PAGE_FAQ_PARAM) == 1) {
    // Optionnel mais utile pour WP/SEO
    status_header(200);
    nocache_headers();
    return ELAIA_PLUGIN_DIR . 'templates/elaia-faq.php';
  }

  if (get_query_var(ELAIA_PAGE_METADATA_PARAM) == 1) {
    status_header(200);
    nocache_headers();
    return ELAIA_PLUGIN_DIR . 'templates/elaia-metadata.php';
  }

  return $template;
}, 99);
