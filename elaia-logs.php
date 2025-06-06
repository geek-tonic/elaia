<?php
// elaia-logs.php

if (!defined('ABSPATH')) {
    exit; // Sécurité WordPress
}

// Fonction pour créer la table  et le cron (hourly )
function elaia_logger_install() {
    global $wpdb;
    $table = $wpdb->prefix . 'elaia_logs';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        type VARCHAR(50) NOT NULL,
        message TEXT NOT NULL,
        line INT NOT NULL,
        file TEXT NOT NULL,
        error_code VARCHAR(50) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        INDEX (error_code),
        INDEX (created_at)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    if (!wp_next_scheduled('elaia_flush_logs_event')) {
        wp_schedule_event(time(), 'hourly', 'elaia_flush_logs_event');
    }
}

// Supprimer la table et le cron
function elaia_logger_uninstall() {
    wp_clear_scheduled_hook('elaia_flush_logs_event');

    global $wpdb;
    $table = $wpdb->prefix . 'elaia_logs';
    $wpdb->query("DROP TABLE IF EXISTS $table");
}


// Fonction pour récupérer les logs
function elaia_enable_global_error_logging() {
    // Attrape les erreurs non fatales (warnings, notices, etc.)
    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            return;
        }

        $type = match($errno) {
            E_ERROR, E_USER_ERROR => 'E_ERROR',
            E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR => 'E_PARSE',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_STRICT => 'E_STRICT',
            E_USER_WARNING, E_WARNING => 'E_WARNING',
            E_USER_NOTICE, E_NOTICE => 'E_NOTICE',
            E_DEPRECATED, E_USER_DEPRECATED => 'E_DEPRECATED',
            default => 'E_UNKNOWN',
};

        elaia_log($type, $errstr, $errfile, $errline);
        return true;
    });

    // Attrape les exceptions non gérées (PHP 7+)
    set_exception_handler(function ($exception) {
        elaia_log('E_EXCEPTION', $exception->getMessage(), $exception->getFile(), $exception->getLine());
    });

    // Attrape les erreurs fatales à la fin du script
    register_shutdown_function(function () {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            elaia_log('E_FATAL', $error['message'], $error['file'], $error['line']);
        }
    });
}



// Fonction pour enregistrer un log en base de données
function elaia_log($type, $message, $file = '', $line = 0) {
    global $wpdb;
    $table = $wpdb->prefix . 'elaia_logs';

    $error_code = md5($type . $file . $line);

    $wpdb->insert($table, [
        'type'       => $type,
        'message'    => $message,
        'line'       => intval($line),
        'file'       => $file,
        'error_code' => $error_code,
        'created_at' => current_time('mysql'),
    ]);
}

// Traitement des logs toutes les heures
add_action('elaia_flush_logs_event', 'elaia_flush_logs');

function elaia_flush_logs() {
    global $wpdb;
    $table = $wpdb->prefix . 'elaia_logs';

    $logs = $wpdb->get_results("SELECT * FROM $table", ARRAY_A);

    if (empty($logs)) {
        return 'Aucun log à envoyer.';
    }

    $endpoint = 'https://app.ela-ia.com/api/v1/errors';

    $response = wp_remote_post($endpoint, [
        'method'  => 'POST',
        'headers' => [
            'Content-Type' => 'application/json',
            'Referer'       => home_url(),
        ],
        'body'    => json_encode(['logs' => $logs]),
        'timeout' => 10,
    ]);
    $wpdb->query("TRUNCATE TABLE $table");
    if (is_wp_error($response)) {
        error_log('Elaia log flush failed: ' . $response->get_error_message());
        return '❌ Échec de connexion à l’API.';
    }

    $code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response); // <- ici tu récupères le JSON
 
    if ($code === 200 || $code === 201) {
    
       
        return '✅ Logs envoyés avec succès. Réponse : ' . $body; // ← pour voir direct
    } else {
        error_log("Elaia log flush failed: HTTP $code – Body: $body");
        return "❌ Échec : HTTP $code – Body: $body";
    }

}




function elaia_is_log_table_created() {
    global $wpdb;
    $table = $wpdb->prefix . 'elaia_logs';
    return $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table;
}

add_shortcode('elaia_log_table_status', function () {
    if (elaia_is_log_table_created()) {
        return '<div style="padding: 10px; border: 1px solid green; color: green;">
            ✅ Table <code>elaia_logs</code> détectée dans la base de données.
        </div>';
    } else {
        return '<div style="padding: 10px; border: 1px solid red; color: red;">
            ❌ Table <code>elaia_logs</code> manquante ! Peut-être un problème d\'installation.
        </div>';
    }
});

