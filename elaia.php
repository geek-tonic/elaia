<?php

/**
 * Plugin Name: Elaia
 * Plugin URI: https://ela-ia.com/
 * Description: Intégration du chatbot Elaia en mode fenêtre overlay
 * Version: 1.1.18
 * Author: Elaia
 * Author URI: https://ela-ia.com/
 * 
 * @package elaia
 */

if (!defined('ABSPATH')) exit;

define('ELAIA_PAGE_FAQ_REWRITE', 'elaia-glossary');
define('ELAIA_PAGE_FAQ_PARAM', 'elaia_faq');
define('ELAIA_PAGE_METADATA_REWRITE', 'elaia-metadatas');
define('ELAIA_PAGE_METADATA_PARAM', 'elaia_metadata');

require_once plugin_dir_path(__FILE__) . 'autoload.php';

new Elaia\Utils\ElaiaUpdateChecker();
