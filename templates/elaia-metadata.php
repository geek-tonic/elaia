<?php
if (!defined('ABSPATH')) exit;

get_header();

include ELAIA_PLUGIN_DIR . 'includes/Pages/Metadata.php';
elaia_prepare_metadata_payload();

get_footer();
