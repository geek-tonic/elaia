<?php

if (!defined('ABSPATH') || !defined('ELAIA_PLUGIN_DIR'))
    exit;

/**
 * Fonction appelée lors de l'activation du plugin
 * Enregistre les règles de réécriture et flush les permaliens
 */
function elaia_activate_plugin()
{
    // Enregistrer les règles de réécriture
    // elaia_register_rewrites();

    // On va créer des pages WP (pour un rendu universel et SEO friendly)
    elaia_create_or_update_pages();

    // Flush les règles pour les activer
    flush_rewrite_rules();
}

/**
 * Fonction appelée lors de la désactivation du plugin
 * Nettoie les règles de réécriture
 */
function elaia_deactivate_plugin()
{
    // Flush les règles pour supprimer celles du plugin
    flush_rewrite_rules();
}

/**
 * Nos deux pages utiles et spécifiques pour Elaia
 */
function elaia_create_or_update_pages()
{
    // FAQ
    elaia_create_or_update_page(
        ELAIA_PAGE_FAQ_REWRITE,
        'FAQ',
        '[elaia_faq]'
    );

    // Metadatas
    elaia_create_or_update_page(
        ELAIA_PAGE_METADATA_REWRITE,
        'Déouvrez autour de vous',
        '[elaia_metadatas]'
    );
}

/**
 * Création de la page proprement
 */
function elaia_create_or_update_page($slug, $title, $shortcode)
{
    // On regarde si elle existe pour éviter les erreurs reloues
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

        // 🚨 Ne pas écraser le contenu si l'admin l'a déjà personnalisé
        // (on met le shortcode seulement s'il n'y est pas)
        $content = (string) $existing->post_content;
        if (strpos($content, $shortcode) === false) {
            unset($postarr['post_content']);
        }

        wp_update_post($postarr);
        return (int) $existing->ID;
    }

    return (int) wp_insert_post($postarr);
}
