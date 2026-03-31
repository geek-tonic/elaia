<?php

if (!defined('ABSPATH') || !defined('ELAIA_PLUGIN_DIR'))
    exit;

/**
 * Fonction appelée lors de l'activation du plugin
 */
function elaia_activate_plugin()
{
    try {
        elaia_create_or_update_pages();
    } catch (\Throwable $e) {
        error_log('Elaia activation error: ' . $e->getMessage());
    }
    set_transient('elaia_needs_flush', true);
}

/**
 * Fonction appelée lors de la désactivation du plugin
 */
function elaia_deactivate_plugin()
{
    
}

/**
 * Crée/met à jour les pages selon les droits du client :
 *   - FAQ + Metadatas → tout client avec un chatbot (via webapp?domain=)
 *   - Corpus          → uniquement les clients de /clients (app my-elaia)
 */
function elaia_create_or_update_pages()
{
    $result = elaia_get_myelaia_domains();
    $domains = $result['domains'] ?? [];
    $has_subscription = $result['has_subscription'] ?? false;

    // ── Mode groupe (avec paths) ──
    if (!empty($domains)) {
        foreach ($domains as $entry) {
            $path   = $entry['path'];
            $domain = $entry['domain'];
            $name   = $entry['name'];

            if (empty($path)) {
                elaia_create_or_update_page(ELAIA_PAGE_FAQ_REWRITE, 'FAQ', '[elaia_faq domain="' . esc_attr($domain) . '"]');
                elaia_create_or_update_page(ELAIA_PAGE_METADATA_REWRITE, 'Découvrez autour de vous', '[elaia_metadatas domain="' . esc_attr($domain) . '"]');

                if ($has_subscription) {
                    elaia_create_or_update_page(ELAIA_PAGE_CORPUS_REWRITE, 'Informations pratiques', '[elaia_corpus domain="' . esc_attr($domain) . '"]');
                }
            } else {
                $parent_id = elaia_ensure_parent_pages($path);
                $suffix = $name ? " — $name" : '';

                elaia_create_or_update_child_page(ELAIA_PAGE_FAQ_REWRITE, 'FAQ' . $suffix, '[elaia_faq domain="' . esc_attr($domain) . '"]', $parent_id);
                elaia_create_or_update_child_page(ELAIA_PAGE_METADATA_REWRITE, 'Découvrez autour de vous' . $suffix, '[elaia_metadatas domain="' . esc_attr($domain) . '"]', $parent_id);

                if ($has_subscription) {
                    elaia_create_or_update_child_page(ELAIA_PAGE_CORPUS_REWRITE, 'Informations pratiques' . $suffix, '[elaia_corpus domain="' . esc_attr($domain) . '"]', $parent_id);
                }
            }
        }

        if (!$has_subscription) {
            elaia_unpublish_page(ELAIA_PAGE_CORPUS_REWRITE);
        }

        return;
    }

    // ── Site simple, pas de domaines → FAQ + Metadatas à la racine ──
    elaia_create_or_update_page(ELAIA_PAGE_FAQ_REWRITE, 'FAQ', '[elaia_faq]');
    elaia_create_or_update_page(ELAIA_PAGE_METADATA_REWRITE, 'Découvrez autour de vous', '[elaia_metadatas]');
    elaia_unpublish_page(ELAIA_PAGE_CORPUS_REWRITE);
}

// ═══════════════════════════════════════
// API — Vérifications
// ═══════════════════════════════════════

function elaia_has_chatbot()
{
    $cached = get_transient('elaia_has_chatbot');
    if ($cached !== false) return $cached === 'yes';

    $domain = parse_url(home_url(), PHP_URL_HOST);
    $api_host = defined('ELAIA_API_HOST') ? ELAIA_API_HOST : 'https://app.ela-ia.com';
    $url = $api_host . '/api/elaiaapp/chatbots/metadatas/webapp?domain=' . urlencode($domain);

    $response = wp_remote_get($url, [
        'timeout' => 10,
        'headers' => ['Accept' => 'application/json'],
    ]);

    if (is_wp_error($response)) return false; // Pas de cache → retry au prochain appel

    $code = wp_remote_retrieve_response_code($response);
    if ($code !== 200) return false; // Pas de cache non plus

    $body = json_decode(wp_remote_retrieve_body($response), true);
    $key = $body['data']['key'] ?? null;
    $has = !empty($key);

    // On cache uniquement si l'API a répondu correctement
    set_transient('elaia_has_chatbot', $has ? 'yes' : 'no', HOUR_IN_SECONDS);

    return $has;
}

function elaia_get_myelaia_domains()
{
    $cached = get_transient('elaia_myelaia_domains');
    if ($cached !== false) return $cached;

    $domain = parse_url(home_url(), PHP_URL_HOST);
    $api_host = defined('ELAIA_API_HOST') ? ELAIA_API_HOST : 'https://app.ela-ia.com';
    $url = $api_host . '/api/v1/has-my-elaia?domain=' . urlencode($domain);

    $response = wp_remote_get($url, [
        'timeout' => 10,
        'headers' => ['Accept' => 'application/json'],
    ]);

    $default = ['domains' => [], 'has_subscription' => false];

    if (is_wp_error($response)) return $default;

    $code = wp_remote_retrieve_response_code($response);
    if ($code !== 200) return $default;

    $body = json_decode(wp_remote_retrieve_body($response), true);

    $result = [
        'domains'          => $body['data']['domains'] ?? $body['domains'] ?? [],
        'has_subscription'  => $body['data']['has_subscription'] ?? $body['has_subscription'] ?? false,
    ];

    set_transient('elaia_myelaia_domains', $result, HOUR_IN_SECONDS);

    return $result;
}

/**
 * Raccourci : vérifie si au moins un domaine matche pour le corpus
 */
function elaia_has_myelaia()
{
    return !empty(elaia_get_myelaia_domains());
}

// ═══════════════════════════════════════
// PAGES — Création et gestion
// ═══════════════════════════════════════

/**
 * Crée ou met à jour une page à la racine
 */
function elaia_create_or_update_page($slug, $title, $shortcode)
{
    $existing = get_page_by_path($slug, OBJECT, 'page');

    $postarr = [
        'post_title'   => $title,
        'post_name'    => $slug,
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_content' => $shortcode,
    ];

    if ($existing && !empty($existing->ID)) {
        $postarr['ID'] = $existing->ID;

        // Ne pas écraser le contenu si un shortcode Elaia est déjà présent
        $content = (string) $existing->post_content;
        if (strpos($content, '[elaia_') !== false) {
            unset($postarr['post_content']);
        }

        wp_update_post($postarr);
        return (int) $existing->ID;
    }

    return (int) wp_insert_post($postarr);
}

/**
 * Crée ou met à jour une page enfant sous un parent donné
 */
function elaia_create_or_update_child_page($slug, $title, $shortcode, $parent_id)
{
    $existing = get_posts([
        'post_type'   => 'page',
        'post_status' => 'any',
        'name'        => $slug,
        'post_parent' => $parent_id,
        'numberposts' => 1,
    ]);

    $postarr = [
        'post_title'   => $title,
        'post_name'    => $slug,
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_content' => $shortcode,
        'post_parent'  => $parent_id,
    ];

    if (!empty($existing)) {
        $postarr['ID'] = $existing[0]->ID;

        $content = (string) $existing[0]->post_content;
        if (strpos($content, '[elaia_') !== false) {
            unset($postarr['post_content']);
        }

        wp_update_post($postarr);
        return (int) $existing[0]->ID;
    }

    return (int) wp_insert_post($postarr);
}

/**
 * Assure que les pages parentes existent pour un path donné
 * Ex: "international/le-camping" → crée "international" puis "le-camping" dessous
 * Retourne l'ID de la page la plus profonde
 */
function elaia_ensure_parent_pages($path)
{
    $segments = array_filter(explode('/', trim($path, '/')));
    $parent_id = 0;
    $accumulated_path = '';

    foreach ($segments as $slug) {
        $accumulated_path .= ($accumulated_path ? '/' : '') . $slug;

        $existing = get_page_by_path($accumulated_path, OBJECT, 'page');

        if ($existing && !empty($existing->ID)) {
            $parent_id = $existing->ID;

            // Republier si elle était en brouillon
            if ($existing->post_status !== 'publish') {
                wp_update_post([
                    'ID'          => $existing->ID,
                    'post_status' => 'publish',
                ]);
            }
        } else {
            $parent_id = wp_insert_post([
                'post_title'   => ucfirst(str_replace('-', ' ', $slug)),
                'post_name'    => $slug,
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_parent'  => $parent_id,
                'post_content' => '',
            ]);
        }
    }

    return $parent_id;
}

/**
 * Dépublier une page par slug (passe en brouillon)
 */
function elaia_unpublish_page($slug)
{
    $page = get_page_by_path($slug, OBJECT, 'page');
    if ($page && $page->post_status === 'publish') {
        wp_update_post([
            'ID'          => $page->ID,
            'post_status' => 'draft',
        ]);
    }
}