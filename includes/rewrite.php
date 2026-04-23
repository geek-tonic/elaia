<?php

if (!defined('ABSPATH') || !defined('ELAIA_PLUGIN_DIR'))
    exit;

// ── Redirections 301 des anciennes URLs rewrite ──
add_filter('query_vars', function ($vars) {
    $vars[] = ELAIA_PAGE_FAQ_PARAM;
    $vars[] = ELAIA_PAGE_METADATA_PARAM;
    return $vars;
});

add_action('template_redirect', function () {
    if (get_query_var(ELAIA_PAGE_FAQ_PARAM) == 1) {
        wp_safe_redirect(home_url('/' . ELAIA_PAGE_FAQ_REWRITE . '/'), 301);
        exit;
    }
    if (get_query_var(ELAIA_PAGE_METADATA_PARAM) == 1) {
        wp_safe_redirect(home_url('/' . ELAIA_PAGE_METADATA_REWRITE . '/'), 301);
        exit;
    }
});

// ── Corpus : rendu SANS header/footer (template_redirect + exit) ──
add_action('template_redirect', function () {
    global $post;

    if (!$post || !has_shortcode($post->post_content ?? '', 'elaia_corpus')) return;

    // Laisser passer les crawlers internes (Yoast sitemap, etc.)
    if (wp_doing_ajax() || wp_doing_cron() || defined('XMLRPC_REQUEST')) return;
    if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'WordPress') !== false) return;

    // Résoudre le domaine (dev override > shortcode attr > auto-detect)
    $dev_domain = (defined('WP_DEBUG') && WP_DEBUG) ? getenv('ELAIA_DEV_DOMAIN') : '';
    $domain = $dev_domain;
    if (!$domain && preg_match('/\[elaia_corpus\b([^\]]*)\]/', $post->post_content, $m)) {
        $atts = shortcode_parse_atts($m[1]);
        $domain = $atts['domain'] ?? '';
    }
    if (!$domain) $domain = \Elaia\Utils\ElaiaPagesMethods::detect_domain();

    // Gate : refuser l'accès si pas de subscription my-elaia (API down → 404 fail-safe)
    $subscription = elaia_get_myelaia_domains($domain);
    if (empty($subscription['has_subscription'])) {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        nocache_headers();
        include get_404_template();
        exit;
    }

    $custom = ELAIA_PLUGIN_DIR . 'templates/elaia-corpus.php';
    if (file_exists($custom)) {
        include $custom;
        exit;
    }
}, 5);

// ── Metadata / FAQ : rendu AVEC header/footer du thème (template_redirect + exit) ──
// Indispensable pour les thèmes qui n'appellent pas the_content() (ex: Falconheavy)
add_action('template_redirect', function () {
    global $post;
    if (!$post) return;

    if (wp_doing_ajax() || wp_doing_cron() || defined('XMLRPC_REQUEST')) return;
    if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'WordPress') !== false) return;

    $shortcode = null;
    $template  = null;
    $globalVar = null;

    if (has_shortcode($post->post_content, 'elaia_metadatas')) {
        $shortcode = 'elaia_metadatas';
        $template  = ELAIA_PLUGIN_DIR . 'templates/elaia-metadata.php';
        $globalVar = 'elaia_metadatas_domain';
    } elseif (has_shortcode($post->post_content, 'elaia_faq')) {
        $shortcode = 'elaia_faq';
        $template  = ELAIA_PLUGIN_DIR . 'templates/elaia-faq.php';
        $globalVar = 'elaia_faq_domain';
    } else {
        return;
    }

    if (!file_exists($template)) return;

    // Extraire l'attribut "domain" du shortcode pour le mode groupe
    if (preg_match('/\[' . preg_quote($shortcode, '/') . '\b([^\]]*)\]/', $post->post_content, $m)) {
        $atts = shortcode_parse_atts($m[1]);
        $GLOBALS[$globalVar] = !empty($atts['domain']) ? $atts['domain'] : null;
    }

    include $template;
    exit;
}, 5);

add_action('init', function () {
    if (get_transient('elaia_needs_flush')) {
        delete_transient('elaia_needs_flush');
        flush_rewrite_rules(false);
    }
}, 99);