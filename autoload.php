<?php

if (!defined('ABSPATH') || !defined('ELAIA_PLUGIN_DIR')) exit;

// Les classes Utiles
require_once ELAIA_PLUGIN_DIR . 'includes/Utils/ElaiaChatbotSitemapProvider.php';
require_once ELAIA_PLUGIN_DIR . 'includes/Utils/ElaiaUpdateChecker.php';
require_once ELAIA_PLUGIN_DIR . 'includes/Utils/ElaiaPagesMethods.php';

// Les fichiers de base pour le fonctionnement
require_once ELAIA_PLUGIN_DIR . 'includes/rewrite.php';
require_once ELAIA_PLUGIN_DIR . 'includes/enqueues.php';
require_once ELAIA_PLUGIN_DIR . 'includes/sitemap.php';
require_once ELAIA_PLUGIN_DIR . 'includes/cache.php';

// Les pages dédiées Ela-IA
require_once ELAIA_PLUGIN_DIR . 'includes/Pages/Faq.php';
require_once ELAIA_PLUGIN_DIR . 'includes/Pages/Metadata.php';
