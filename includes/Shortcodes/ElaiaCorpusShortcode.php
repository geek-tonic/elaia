<?php

namespace Elaia\Shortcodes;

if (!defined('ABSPATH')) exit;

class ElaiaCorpusShortcode
{
    public static function register(): void
    {
        add_shortcode('elaia_corpus', [self::class, 'render']);
    }

    public static function render($atts): string
    {
        $atts = shortcode_atts([
            'domain' => '',
        ], $atts, 'elaia_corpus');

        global $elaia_corpus_domain;
        $elaia_corpus_domain = $atts['domain'] ?: null;

        ob_start();
        elaia_prepare_corpus_payload();
        return ob_get_clean();
    }
}