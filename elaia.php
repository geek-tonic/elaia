<?php

/**
 * Plugin Name: Elaia
 * Plugin URI: https://ela-ia.com/
 * Description: Intégration du chatbot Elaia en mode fenêtre overlay
 * Version: %RELEASE_VERSION%
 * Author: Elaia
 * Author URI: https://ela-ia.com/
 * 
 * @package elaia
 */

if (!defined('ABSPATH'))
  exit;

try {
  define('ELAIA_VERSION', '%RELEASE_VERSION%');
  define('ELAIA_PLUGIN_BASENAME', plugin_basename(__FILE__));
  define('ELAIA_PLUGIN_DIR', plugin_dir_path(__FILE__));
  define('ELAIA_PAGE_FAQ_REWRITE', 'elaia-glossary');
  define('ELAIA_PAGE_FAQ_PARAM', 'elaia_faq');
  define('ELAIA_PAGE_METADATA_REWRITE', 'elaia-metadatas');
  define('ELAIA_PAGE_METADATA_PARAM', 'elaia_metadata');
  define('ELAIA_PAGE_CORPUS_REWRITE', 'my-elaia-plugin');
  define('ELAIA_PAGE_CORPUS_PARAM', 'elaia_corpus');


  require_once ELAIA_PLUGIN_DIR . 'autoload.php';

  new Elaia\Utils\ElaiaUpdateChecker();

  // Activation du plugin (compatible multisite)
  register_activation_hook(__FILE__, 'elaia_activate_plugin_handler');

  // Désactivation du plugin (compatible multisite)
  register_deactivation_hook(__FILE__, 'elaia_deactivate_plugin_handler');

  // Multisite : créer les pages quand un nouveau sous-site est ajouté
  add_action('wp_insert_site', 'elaia_on_new_site');
} catch (Exception $e) {
  throw new WP_Exception($e->getMessage());
}
