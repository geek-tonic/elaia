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

    $custom = ELAIA_PLUGIN_DIR . 'templates/elaia-corpus.php';
    if (file_exists($custom)) {
        include $custom;
        exit;
    }
}, 5);

add_action('init', function () {
    if (get_transient('elaia_needs_flush')) {
        delete_transient('elaia_needs_flush');
        flush_rewrite_rules(false);
    }
}, 99);