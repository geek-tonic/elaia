<?php

namespace Elaia\Utils;

class ElaiaChatbotSitemapProvider extends \WP_Sitemaps_Provider
{

  public function __construct()
  {
    $this->name = 'elaia-chatbot';
    $this->object_type = 'page';
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
