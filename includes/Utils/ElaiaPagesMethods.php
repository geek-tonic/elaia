<?php

namespace Elaia\Pages;

class ElaiaPagesMethods
{
  public static function detect_domain()
  {
    $domain = '';
    if (isset($_SERVER['HTTP_HOST'])) {
      $domain = sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST']));
    } else {
      $home = home_url();
      $parts = wp_parse_url($home);
      if (!empty($parts['host'])) $domain = $parts['host'];
    }
    return $domain;
  }

  public static function detect_referer()
  {
    if (!empty($_SERVER['HTTP_REFERER'])) {
      return sanitize_text_field(wp_unslash($_SERVER['HTTP_REFERER']));
    }
    return (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  }
}
