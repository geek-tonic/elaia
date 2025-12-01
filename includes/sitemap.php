<?php

if (!defined('ABSPATH') || !defined('ELAIA_PLUGIN_DIR')) exit;

/**********************************************
 * 
 * W O R D P R E S S   N A T I F
 * 
 **********************************************/
add_action('init', function () {
  if (function_exists('wp_register_sitemap_provider')) {
    wp_register_sitemap_provider(
      'elaia',
      new \Elaia\Utils\ElaiaChatbotSitemapProvider()
    );
  }
}, 0);


/**********************************************
 * 
 * Y O A S T   S E O
 * 
 **********************************************/
add_filter('wpseo_sitemap_entries_per_page', function () {
  return 1000;
});

add_filter('wpseo_sitemap_page_content', function ($content, $type = 'page') {

  // Nous n’ajoutons les URLs que dans le sitemap des pages
  if ($type !== 'page') {
    return $content;
  }

  $extra  = '';
  $urls   = [
    home_url('/' . ELAIA_PAGE_FAQ_REWRITE . '/'),
    home_url('/' . ELAIA_PAGE_METADATA_REWRITE . '/'),
  ];

  foreach ($urls as $url) {
    $extra .= '<url><loc>' . esc_url($url) . '</loc><priority>0.8</priority></url>';
  }

  return $content . $extra;
}, 10, 2);




/**********************************************
 * 
 * R A N K M A T H
 * 
 **********************************************/
add_filter('rank_math/sitemap/entry', function ($url) {
  return $url;
}, 10, 1);

add_filter('rank_math/sitemap/page', function ($entries) {

  $entries[] = [
    'loc' => home_url('/' . ELAIA_PAGE_FAQ_REWRITE . '/'),
    'lastmod' => gmdate('Y-m-d\TH:i:s\Z'),
  ];

  $entries[] = [
    'loc' => home_url('/' . ELAIA_PAGE_METADATA_REWRITE . '/'),
    'lastmod' => gmdate('Y-m-d\TH:i:s\Z'),
  ];

  return $entries;
});


/**********************************************
 * 
 * S E O   P R E S S
 * 
 **********************************************/
add_filter('seopress_sitemaps_single', function ($urls, $type) {

  if ($type !== 'page') {
    return $urls;
  }

  $urls[] = [
    'loc' => home_url('/' . ELAIA_PAGE_FAQ_REWRITE . '/'),
    'mod' => date('c'),
    'img' => [],
  ];

  $urls[] = [
    'loc' => home_url('/' . ELAIA_PAGE_METADATA_REWRITE . '/'),
    'mod' => date('c'),
    'img' => [],
  ];

  return $urls;
}, 10, 2);


/**********************************************
 *
 * A J O U T   A U   S I T E M A P   I N D E X
 *
 **********************************************/
add_filter('wp_sitemaps_index', function ($sitemaps) {
  $sitemaps['elaia'] = [
    'loc' => home_url('/wp-sitemap-elaia-1.xml'),
  ];
  return $sitemaps;
});
