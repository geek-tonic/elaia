<?php

add_filter('rocket_defer_inline_exclusions', function ($inline_exclusions_list) {
  if (! is_array($inline_exclusions_list)) {
    $inline_exclusions_list = array();
  }

  // Ajouter les scripts à exclure du cache (defer) ou de la minification
  $inline_exclusions_list[] = 'https://chatbot.ela-ia.com/*';
  $inline_exclusions_list[] = 'https://app.ela-ia.com/*';

  return $inline_exclusions_list;
});

// Exclure les scripts JS spécifiques de la mise en cache et de la minification
add_filter('rocket_exclude_js', function ($js) {
  $js[] = 'https://chatbot.ela-ia.com/*';  // Exclure ce script JS du cache
  $js[] = 'https://app.ela-ia.com/*';    // Exclure ce script JS du cache
  return $js;
});
