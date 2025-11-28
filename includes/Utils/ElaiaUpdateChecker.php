<?php

namespace Elaia\Utils;

if (!defined('ABSPATH') || !defined('ELAIA_PLUGIN_DIR')) exit;

class ElaiaUpdateChecker
{

  private $plugin_slug;
  private $version;
  private $cache_key;
  private $cache_allowed;

  public function __construct()
  {
    $this->plugin_slug    = 'elaia/elaia.php'; // doit correspondre au chemin relatif dans WP
    $this->version        = '%RELEASE_VERSION%'; // version actuelle
    $this->cache_key      = 'elaia_update_info';
    $this->cache_allowed  = false;

    add_filter('plugins_api', array($this, 'info'), 20, 3);
    add_filter('site_transient_update_plugins', array($this, 'update'));
    add_action('upgrader_process_complete', array($this, 'purge'), 10, 2);
  }

  private function request()
  {
    $remote = get_transient($this->cache_key);

    if (false === $remote || ! $this->cache_allowed) {
      $remote = wp_remote_get(
        'https://ela-ia.com/wp-content/uploads/elaia/info.json',
        array(
          'timeout' => 10,
          'headers' => array('Accept' => 'application/json')
        )
      );

      if (
        is_wp_error($remote) ||
        200 !== wp_remote_retrieve_response_code($remote) ||
        empty(wp_remote_retrieve_body($remote))
      ) {
        return false;
      }

      set_transient($this->cache_key, $remote, DAY_IN_SECONDS);
    }

    return json_decode(wp_remote_retrieve_body($remote));
  }

  public function info($res, $action, $args)
  {
    if ('plugin_information' !== $action) {
      return $res;
    }

    if ($args->slug !== $this->plugin_slug && $args->slug !== dirname($this->plugin_slug)) {
      return $res;
    }

    $remote = $this->request();
    if (! $remote) return $res;

    $res = new \stdClass();
    $res->name           = $remote->name;
    $res->slug           = $this->plugin_slug;
    $res->version        = $remote->version;
    $res->tested         = $remote->tested;
    $res->requires       = $remote->requires;
    $res->requires_php   = $remote->requires_php;
    $res->download_link  = $remote->download_url;
    $res->trunk          = $remote->download_url;
    $res->last_updated   = $remote->last_updated;
    $res->sections       = (array) $remote->sections;

    if (! empty($remote->banners)) {
      $res->banners = (array) $remote->banners;
    }

    return $res;
  }

  public function update($transient)
  {
    if (empty($transient->checked)) {
      return $transient;
    }

    $remote = $this->request();
    if (
      $remote &&
      version_compare($this->version, $remote->version, '<') &&
      version_compare($remote->requires, get_bloginfo('version'), '<=') &&
      version_compare($remote->requires_php, PHP_VERSION, '<=')
    ) {
      $res = new \stdClass();
      $res->slug        = $this->plugin_slug;
      $res->plugin      = $this->plugin_slug;
      $res->new_version = $remote->version;
      $res->tested      = $remote->tested;
      $res->package     = $remote->download_url;

      $transient->response[$this->plugin_slug] = $res;
    }

    return $transient;
  }

  public function purge($upgrader, $options)
  {
    if (
      $this->cache_allowed &&
      $options['action'] === 'update' &&
      $options['type'] === 'plugin'
    ) {
      delete_transient($this->cache_key);
    }
  }
}
