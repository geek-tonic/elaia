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

// wptexturize encode les `&` du JS inline (`&&` → `&#038;&#038;`) car les `<` et `>` de
// comparaison du JS piègent son tag parser → il sort à tort du contexte <script> et texturise
// la suite comme du texte. Les thèmes FSE l'appellent en plus DIRECTEMENT (wp-includes/
// block-template.php) sur tout le contenu rendu, pas via le filtre the_content — donc un
// simple `remove_filter('the_content','wptexturize')` ne suffit pas.
// On le court-circuite via `run_wptexturize` (filtre statique appelé une fois par requête),
// uniquement sur les pages qui contiennent nos shortcodes. Effet de bord : pas de guillemets
// typographiques/dashes intelligents sur ces pages — acceptable.
add_action('wp', function () {
    if (!is_singular()) return;
    $post = get_post();
    if (!$post) return;
    if (has_shortcode($post->post_content, 'elaia_metadatas')
     || has_shortcode($post->post_content, 'elaia_faq')) {
        add_filter('run_wptexturize', '__return_false');
    }
});
