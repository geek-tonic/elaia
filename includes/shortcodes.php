<?php

add_action(
  'init',
  function () {
    add_shortcode('elaia_faq', function ($atts) {
        if (!defined('ELAIA_PLUGIN_DIR')) return '';

        $atts = shortcode_atts(['domain' => ''], $atts);
        global $elaia_faq_domain;
        $elaia_faq_domain = $atts['domain'] ?: null;

        ob_start();
        include_once ELAIA_PLUGIN_DIR . 'includes/Pages/Faq.php';
        elaia_prepare_faq_payload();
        return ob_get_clean();
    });

    add_shortcode('elaia_metadatas', function ($atts) {
        if (!defined('ELAIA_PLUGIN_DIR')) return '';

        $atts = shortcode_atts(['domain' => ''], $atts);
        global $elaia_metadatas_domain;
        $elaia_metadatas_domain = $atts['domain'] ?: null;

        ob_start();
        include_once ELAIA_PLUGIN_DIR . 'includes/Pages/Metadata.php';
        elaia_prepare_metadata_payload();
        return ob_get_clean();
    });

    add_shortcode('elaia_corpus', function ($atts) {
        $atts = shortcode_atts(['domain' => ''], $atts);

        global $elaia_corpus_domain;
        $elaia_corpus_domain = $atts['domain'] ?: null;

        include ELAIA_PLUGIN_DIR . 'includes/Pages/Corpus.php';

        ob_start();
        elaia_prepare_corpus_payload();
        return ob_get_clean();
    });
  },
  20
);
