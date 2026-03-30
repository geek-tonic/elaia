<?php

if (!defined('ABSPATH') || !defined('ELAIA_PLUGIN_DIR'))
    exit;

// ── Query vars ──
add_filter('query_vars', function ($vars) {
    $vars[] = 'elaia_virtual_page';
    // Garder les anciens pour les redirections 301
    $vars[] = ELAIA_PAGE_FAQ_PARAM;
    $vars[] = ELAIA_PAGE_METADATA_PARAM;
    return $vars;
});

// ── Rewrite rules (fallback racine uniquement) ──
function elaia_add_rewrite_rules()
{
    add_rewrite_rule(
        '^(elaia-glossary|elaia-metadatas|my-elaia-plugin)/?$',
        'index.php?elaia_virtual_page=$matches[1]',
        'top'
    );
}
add_action('init', 'elaia_add_rewrite_rules');

// ── Empêcher le conflit rewrite rule / vraie page WP ──
add_action('parse_request', function ($wp) {
    if (empty($wp->query_vars['elaia_virtual_page'])) return;

    $slug = $wp->query_vars['elaia_virtual_page'];
    $page = get_page_by_path($slug, OBJECT, 'page');

    if ($page && $page->post_status === 'publish') {
        unset($wp->query_vars['elaia_virtual_page']);
        $wp->query_vars['pagename'] = $slug;
    }
});
// ── Redirections 301 des anciennes URLs rewrite ──
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

// ── Fallback template_include pour pages virtuelles FAQ/Metadatas ──
add_filter('template_include', function ($template) {
    $virtual = get_query_var('elaia_virtual_page');

    if ($virtual === ELAIA_PAGE_FAQ_REWRITE) {
        status_header(200);
        nocache_headers();
        return ELAIA_PLUGIN_DIR . 'templates/elaia-faq.php';
    }

    if ($virtual === ELAIA_PAGE_METADATA_REWRITE) {
        status_header(200);
        nocache_headers();
        return ELAIA_PLUGIN_DIR . 'templates/elaia-metadata.php';
    }

    return $template;
}, 99);