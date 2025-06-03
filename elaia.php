<?php
/**
 * Plugin Name: Elaia
 * Plugin URI: https://www.geek-tonic.com/
 * Description: Insertion automatique du chatbot Elaia dans le footer du site.
 * Version: 1.0
 * Author: Geek-Tonic
 * Author URI: https://www.geek-tonic.com/
 */

if (!defined('ABSPATH')) exit;

// Injecte le script dans le footer
add_action('wp_footer', function() {
    echo '<!-- Elaia Chatbot -->';
    echo '<script type="text/javascript" src="https://chatbot.ela-ia.com/chatbot-v1.js" defer></script>';
});

// Ajoute une page de présentation dans l'admin
add_action('admin_menu', function() {
    add_menu_page(
        'Elaia Chatbot',
        'Elaia',
        'manage_options',
        'elaia-chatbot',
        'elaia_admin_page',
        'dashicons-format-chat', // Icône WordPress
        100
    );
});

// Fonction pour vider le cache
function elaia_vider_le_cache() {
    global $wpdb;

    // Transients WordPress
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_site\_transient\_%'");

    // WP Super Cache
    if (function_exists('wp_cache_clear_cache')) {
        wp_cache_clear_cache();
    }

    // W3 Total Cache
    if (function_exists('w3tc_flush_all')) {
        w3tc_flush_all();
    }

    // Autoptimize
    if (function_exists('autoptimize_clearallcache')) {
        autoptimize_clearallcache();
    }

    // Object Cache
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }

    // OPcache PHP
    if (function_exists('opcache_reset')) {
        opcache_reset();
    }

    // Marqueur de succès
    add_action('admin_notices', function() {
        echo '<div class="notice notice-success is-dismissible"><p><strong>Le cache a été vidé avec succès.</strong></p></div>';
    });
}

// Affiche la page d'administration du plugin
function elaia_admin_page() {
    echo '<div class="wrap">';
    echo '<h1>Chatbot Elaia</h1>';
    echo '<p>Le script du chatbot est automatiquement injecté dans le footer de votre site.</p>';
    echo '<p><strong>URL du script :</strong> <code>https://chatbot.ela-ia.com/chatbot-v1.js</code></p>';
    echo '<p>Aucune configuration n’est requise.</p>';

    echo '<hr>';

    // Formulaire pour vider le cache
    echo '<h2>Maintenance</h2>';
    echo '<p>Vous pouvez ici vider les caches du site si nécessaire.</p>';
    echo '<form method="post">';
    submit_button('Vider le cache du site');
    echo '</form>';

    echo '</div>';

    // Si formulaire soumis, on vide le cache
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        elaia_vider_le_cache();
    }
}
