<?php

add_filter('query_vars', function ($vars) {
    $vars[] = 'elaia_virtual_page';
    return $vars;
});

function elaia_add_rewrite_rules() {
    add_rewrite_rule(
        '^(?:.+/)?(elaia-glossary|elaia-metadatas|my-elaia-plugin)/?$',
        'index.php?elaia_virtual_page=$matches[1]',
        'top'
    );
}
add_action('init', 'elaia_add_rewrite_rules');

add_filter('template_include', function ($template) {
    global $post;
    if ($post && has_shortcode($post->post_content, 'elaia_corpus')) {
        $custom = ELAIA_PLUGIN_DIR . 'templates/elaia-corpus.php';
        if (file_exists($custom)) {
            return $custom;
        }
    }
    return $template;
}, 99);

register_activation_hook(__FILE__, function () {
    elaia_add_rewrite_rules();
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function () {
    flush_rewrite_rules();
});
