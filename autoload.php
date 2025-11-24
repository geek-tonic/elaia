<?php

// Les classes Utiles
require_once plugin_dir_path(__FILE__) . 'includes/Utils/ElaiaChatbotSitemapProvider.php';
require_once plugin_dir_path(__FILE__) . 'includes/Utils/ElaiaUpdateChecker.php';

// Les fichiers de base pour le fonctionnement
require_once plugin_dir_path(__FILE__) . 'includes/rewrite.php';
require_once plugin_dir_path(__FILE__) . 'includes/enqueues.php';
require_once plugin_dir_path(__FILE__) . 'includes/sitemap.php';
require_once plugin_dir_path(__FILE__) . 'includes/cache.php';

// Les pages dédiées Ela-IA
require_once plugin_dir_path(__FILE__) . 'includes/Pages/Faq.php';
require_once plugin_dir_path(__FILE__) . 'includes/Pages/Metadata.php';
