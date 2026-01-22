<?php
if (!defined('ABSPATH')) exit;

get_header();

include ELAIA_PLUGIN_DIR . 'includes/Pages/Faq.php';

// Si ta fonction echo directement, ok.
// Si elle renvoie du HTML, tu l'echo.
elaia_prepare_faq_payload();

get_footer();
