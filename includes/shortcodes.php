<?php

add_action(
    'init',
    function () {
        add_shortcode('elaia_faq', function ($atts) {
            if (! defined('ELAIA_PLUGIN_DIR')) {
                return '';
            }

            $atts = shortcode_atts(['domain' => ''], $atts);
            global $elaia_faq_domain;
            $elaia_faq_domain = $atts['domain'] ?: null;

            ob_start();
            include_once ELAIA_PLUGIN_DIR.'includes/Pages/Faq.php';
            elaia_prepare_faq_payload();

            return ob_get_clean();
        });

        add_shortcode('elaia_faq_group', function ($atts) {
            if (! defined('ELAIA_PLUGIN_DIR')) {
                return '';
            }

            $atts = shortcode_atts(['group' => '', 'domain' => ''], $atts);
            global $elaia_faq_group_domain;
            $elaia_faq_group_domain = $atts['domain'] ?: null;

            ob_start();
            include_once ELAIA_PLUGIN_DIR.'includes/Pages/FaqGroup.php';
            elaia_prepare_faq_group_payload($atts['group']);

            return ob_get_clean();
        });

        add_shortcode('elaia_metadatas', function ($atts) {
            if (! defined('ELAIA_PLUGIN_DIR')) {
                return '';
            }

            $atts = shortcode_atts(['domain' => ''], $atts);
            global $elaia_metadatas_domain;
            $elaia_metadatas_domain = $atts['domain'] ?: null;

            ob_start();
            include_once ELAIA_PLUGIN_DIR.'includes/Pages/Metadata.php';
            elaia_prepare_metadata_payload();

            return ob_get_clean();
        });

        add_shortcode('elaia_corpus', function ($atts) {
            $atts = shortcode_atts(['domain' => ''], $atts);

            global $elaia_corpus_domain;
            $elaia_corpus_domain = $atts['domain'] ?: null;

            include ELAIA_PLUGIN_DIR.'includes/Pages/Corpus.php';

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
// block-template.php) sur tout le contenu rendu, pas via le filtre the_content.
// Le filtre `run_wptexturize` ne marche que si on l'enregistre AVANT le premier appel à
// wptexturize() — ce qui n'est pas garanti si un autre plugin (SEO, sécurité…) la déclenche
// très tôt. On passe donc par un ob_start au template_redirect : on capture la sortie
// complète et on défait l'encodage HTML à l'intérieur des <script> uniquement.
add_action('template_redirect', function () {
    if (! is_singular()) {
        return;
    }
    $post = get_post();
    if (! $post) {
        return;
    }
    if (! has_shortcode($post->post_content, 'elaia_metadatas')
     && ! has_shortcode($post->post_content, 'elaia_faq')
     && ! has_shortcode($post->post_content, 'elaia_faq_group')) {
        return;
    }

    ob_start(function ($html) {
        if (strpos($html, '<script') === false) {
            return $html;
        }
        if (strpos($html, '&#038;') === false && strpos($html, '&amp;') === false) {
            return $html;
        }

        return preg_replace_callback('/<script\b[^>]*>(.*?)<\/script>/s', function ($m) {
            return str_replace(['&#038;', '&amp;'], '&', $m[0]);
        }, $html);
    });
}, 1);
