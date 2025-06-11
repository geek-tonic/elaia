<?php
/**
 * Plugin Name: Elaia
 * Plugin URI: https://ela-ia.com/
 * Description: Plugin WordPress pour utiliser Elaia
 * Version: 1.1.2
 * Author: Elaia
 * Author URI: https://ela-ia.com/
 */

if (!defined('ABSPATH')) exit;
require_once plugin_dir_path( __FILE__ ) . 'elaia-update-checker.php';
require_once plugin_dir_path(__FILE__) . 'elaia-logs.php';
require_once plugin_dir_path(__FILE__) . 'elaia-updates.php';

add_action('plugins_loaded', function () {
    if (!elaia_is_log_table_created()) {
        elaia_logger_install();
    }
});

elaia_enable_global_error_logging();
register_activation_hook(__FILE__, 'elaia_logger_install');
register_deactivation_hook(__FILE__, 'elaia_logger_uninstall');

// Injecte le script dans le footer
add_action('wp_footer', function() {
    echo '<!-- Elaia Chatbot -->';
    echo '<script type="text/javascript" src="https://chatbot.ela-ia.com/chatbot-v1.js?v=' . time() . '" defer></script>';
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

function elaia_admin_page() {
    global $wpdb;

    $host = get_site_url();  // Récupère l'URL du site courant
    $api_url = 'https://app.ela-ia.com/api/v1';  // L'URL de ton API Laravel

    // Appel à l'API Laravel pour vérifier la synchronisation
    $response = wp_remote_get($api_url . '?key=' . urlencode($host));
    
    if (is_wp_error($response)) {
        $message = 'Erreur lors de la connexion avec Elaia. Veuillez réessayer.';
        $status = 'error';
    } else {
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        if (isset($data['error'])) {
            $message = 'Le site n\'est pas encore synchronisé avec Elaia.';
            $status = 'warning';
        } else {
            $message = 'Le site est déjà synchronisé avec Elaia.';
            $status = 'success';
        }
    }

    // Affichage de la page d'administration
    echo '<div class="wrap">';
    echo '<h1>Chatbot Elaia</h1>';
    echo '<p>Le script du chatbot est automatiquement injecté dans le footer de votre site.</p>';
    echo '<p><strong>URL du script :</strong> <code>https://chatbot.ela-ia.com/chatbot-v1.js</code></p>';
    echo '<p>Aucune configuration n’est requise.</p>';

    echo '<hr>';

    // -- Synchronisation avec Elaia --
    echo '<h2>Synchronisation avec Elaia</h2>';
    if ($status == 'success') {
        echo "<p><strong>✅ $message</strong></p>";
    } else {
        echo "<p><strong>❌ $message</strong></p>";
        echo '<form method="post"><input type="hidden" name="force_sync" value="1">';
        submit_button('Synchroniser avec Elaia');
        echo '</form>';
    }

    echo '<hr>';

    // -- Actions POST pour la synchronisation --
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['force_sync'])) {
        // Effectuer la synchronisation avec l'API Laravel
        elaia_sync_with_elaia();  // Cette fonction devrait gérer la synchronisation réelle
        echo '<div class="updated notice"><p>✅ Synchronisation réussie.</p></div>';
    }

    echo '</div>';

    // -- Flush --
    echo '<h2>Flush des logs</h2>';
    echo '<p>Vous pouvez ici forcer le vidage des logs.</p>';
    echo '<form method="post"><input type="hidden" name="force_flush_now" value="1">';
    submit_button('Forcer le flush maintenant');
    echo '</form>';

    // -- Actions POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['force_flush_now'])) {
            elaia_flush_logs();
            echo '<div class="updated notice"><p>✅ Flush manuel effectué.</p></div>';
        }
    }
}

function elaia_sync_with_elaia() {
    $host = get_site_url();  // URL du site
    $api_url = 'https://app.ela-ia.com/api/v1';  

    // Effectuer l'appel API pour lier le site avec Elaia
    $response = wp_remote_get($api_url . '?key=' . urlencode($host));

    if (is_wp_error($response)) {
        echo '<div class="error notice"><p>Erreur lors de la synchronisation avec Elaia.</p></div>';
    } else {
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        if (isset($data['error'])) {
            echo '<div class="error notice"><p>Erreur : ' . $data['error'] . '</p></div>';
        } else {
            // Mise à jour de l'option de synchronisation ou autre action
            update_option('elaia_last_sync_time', time());  // Enregistrer l'heure de la dernière synchronisation
            echo '<div class="updated notice"><p>✅ Synchronisation réussie avec Elaia.</p></div>';
        }
    }
}
