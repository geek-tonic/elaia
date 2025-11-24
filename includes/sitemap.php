<?php

/**********************************************
 * 
 * W O R D P R E S S   N A T I F
 * 
 **********************************************/
add_filter('wp_sitemaps_add_provider', function ($providers) {
  $providers['elaia-chatbot'] = new \Elaia\Utils\ElaiaChatbotSitemapProvider();
  return $providers;
});


/**********************************************
 * 
 * Y O A S T   S E O
 * 
 **********************************************/
add_filter('wpseo_sitemap_entries_per_page', function () {
  return 1000;
});

add_filter('wpseo_sitemap_page_content', function ($content, $type) {

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
    $extra .= '<url><loc>' . esc_url($url) . '</loc><priority>0.5</priority></url>';
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
