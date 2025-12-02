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
  define('ELAIA_PLUGIN_DIR', plugin_dir_path(__FILE__));
  define('ELAIA_PAGE_FAQ_REWRITE', 'elaia-glossary');
  define('ELAIA_PAGE_FAQ_PARAM', 'elaia_faq');
  define('ELAIA_PAGE_METADATA_REWRITE', 'elaia-metadatas');
  define('ELAIA_PAGE_METADATA_PARAM', 'elaia_metadata');

  require_once ELAIA_PLUGIN_DIR . 'autoload.php';

  new Elaia\Utils\ElaiaUpdateChecker();

  // Activation du plugin : enregistrer les règles de réécriture
  register_activation_hook(__FILE__, 'elaia_activate_plugin');

  // Désactivation du plugin : nettoyer les règles de réécriture
  register_deactivation_hook(__FILE__, 'elaia_deactivate_plugin');

} catch (Exception $e) {
  throw new WP_Exception($e->getMessage());
}
