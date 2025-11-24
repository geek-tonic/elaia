<?php

namespace Elaia\Utils;

if (!defined('ABSPATH') || !defined('ELAIA_PLUGIN_DIR')) exit;

class ElaiaChatbotSitemapProvider extends \WP_Sitemaps_Provider
{
  public function __construct()
  {
    $this->name = 'elaia';
    $this->object_type = 'elaia';
  }

    public function get_object_subtypes()
    {
        return [];
    }

  public function get_url_list($page_num, $object_subtype = '')
  {
    return [
      [
        'loc' => home_url('/' . ELAIA_PAGE_FAQ_REWRITE . '/'),
        'lastmod' => gmdate('Y-m-d\TH:i:s\Z'),
      ],
      [
        'loc' => home_url('/' . ELAIA_PAGE_METADATA_REWRITE . '/'),
        'lastmod' => gmdate('Y-m-d\TH:i:s\Z'),
      ]
    ];
  }

  public function get_max_num_pages($object_subtype = '')
  {
    return 1;
  }
}
