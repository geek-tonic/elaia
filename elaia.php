<?php
/**
 * Plugin Name: Elaia
 * Plugin URI: https://ela-ia.com/
 * Description: Plugin WordPress pour utiliser Elaia
 * Version: 1.1
 * Author: Elaia
 * Author URI: https://ela-ia.com/
 */

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'elaia-logs.php';

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

function elaia_admin_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'elaia_logs';

    echo '<div class="wrap">';
    echo '<h1>Chatbot Elaia</h1>';
    echo '<p>Le script du chatbot est automatiquement injecté dans le footer de votre site.</p>';
    echo '<p><strong>URL du script :</strong> <code>https://chatbot.ela-ia.com/chatbot-v1.js</code></p>';
    echo '<p>Aucune configuration n’est requise.</p>';

    echo '<hr>';

    // -- Statut de la table --
    echo '<h2>Statut de la table de logs</h2>';
    if (elaia_is_log_table_created()) {
        echo '<p style="color: green;">✅ Table <code>elaia_logs</code> détectée.</p>';
    } else {
        echo '<p style="color: red;">❌ Table <code>elaia_logs</code> non trouvée.</p>';
        echo '<form method="post"><input type="hidden" name="force_create_table" value="1">';
        submit_button('Créer la table maintenant');
        echo '</form>';
    }

    // -- Derniers logs --
    if (elaia_is_log_table_created()) {
        echo '<h3>Derniers logs enregistrés</h3>';
        $logs = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC LIMIT 5", ARRAY_A);

        if ($logs) {
            echo '<table class="widefat"><thead><tr>
                <th>Type</th><th>Message</th><th>Fichier</th><th>Ligne</th><th>Date</th>
            </tr></thead><tbody>';
            foreach ($logs as $log) {
                echo '<tr>';
                echo "<td>{$log['type']}</td>";
                echo "<td>{$log['message']}</td>";
                echo "<td>{$log['file']}</td>";
                echo "<td>{$log['line']}</td>";
                echo "<td>{$log['created_at']}</td>";
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<p>Aucun log enregistré pour le moment.</p>';
        }

        // -- Statut du flush
        echo '<h3>État du flush automatique</h3>';
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        $next = wp_next_scheduled('elaia_flush_logs_event');

        if (!$next) {
            echo '<p style="color:red;">❌ Cron non planifié.</p>';
        } else {
            $remaining = $next - time();
            $minutes = floor($remaining / 60);
            $seconds = $remaining % 60;

            echo "<p>🧮 Nombre de logs en base : <strong>$count</strong></p>";
            echo "<p>⏳ Prochain vidage automatique dans <strong id='elaia-countdown'>$minutes min $seconds sec</strong></p>";

            echo '<script>
            (function(){
                let secondsLeft = ' . intval($remaining) . ';
                const countdownEl = document.getElementById("elaia-countdown");

                function updateCountdown() {
                    if (secondsLeft <= 0) {
                        countdownEl.innerText = "En cours...";
                        return;
                    }
                    const min = Math.floor(secondsLeft / 60);
                    const sec = secondsLeft % 60;
                    countdownEl.innerText = `${min} min ${sec < 10 ? "0" : ""}${sec} sec`;
                    secondsLeft--;
                }

                updateCountdown();
                setInterval(updateCountdown, 1000);
            })();
            </script>';
        }

        echo '<form method="post">
            <input type="hidden" name="force_flush_now" value="1">';
        submit_button('Forcer le flush maintenant');
        echo '</form>';
    }

    echo '<hr>';

    // -- Maintenance : vider le cache
    echo '<h2>Maintenance</h2>';
    echo '<p>Vous pouvez ici vider les caches du site si nécessaire.</p>';
    echo '<form method="post"><input type="hidden" name="vider_cache" value="1">';
    submit_button('Vider le cache du site');
    echo '</form>';

    echo '</div>';

    // -- Actions POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['vider_cache'])) {
            elaia_vider_le_cache();
            echo '<div class="updated notice"><p>✅ Cache vidé.</p></div>';
        }

        if (isset($_POST['purge_logs'])) {
            $wpdb->query("TRUNCATE TABLE $table");
            echo '<div class="updated notice"><p>✅ Table des logs vidée.</p></div>';
        }

        if (isset($_POST['force_create_table'])) {
            elaia_logger_install();
            echo '<div class="updated notice"><p>✅ Table recréée (si manquante).</p></div>';
        }

        if (isset($_POST['force_flush_now'])) {
            elaia_flush_logs();
            echo '<div class="updated notice"><p>✅ Flush manuel effectué.</p></div>';
        }
    }
}


