<?php

use Elaia\Utils\ElaiaPagesMethods;

if (!defined('ABSPATH') || !defined('ELAIA_PLUGIN_DIR')) exit;

if (!function_exists('elaia_prepare_metadata_payload')) {
    function elaia_prepare_metadata_payload()
    {
        $domain = ElaiaPagesMethods::detect_domain();
        $referer = ElaiaPagesMethods::detect_referer();

        $API_URL = 'https://app.ela-ia.com/api/v1/chatbot/metadatas';

        // ---- Cache (transient) ----
        $ttl = 30 * MINUTE_IN_SECONDS;

        $cache_key_base = 'elaia_metadatas_' . md5(strtolower(trim((string)$domain)));
        $cache_key_data = $cache_key_base . '_data';
        $cache_key_meta = $cache_key_base . '_meta';

        $bypass_cache = isset($_GET['elaia_nocache']) && $_GET['elaia_nocache'] == '1';

        if (is_user_logged_in() && current_user_can('manage_options') && isset($_GET['elaia_clear_cache']) && $_GET['elaia_clear_cache'] == '1') {
            delete_transient($cache_key_data);
            delete_transient($cache_key_meta);
        }

        $cached_payload = (!$bypass_cache) ? get_transient($cache_key_data) : false;

        // Valeurs par défaut (mêmes variables que ton code)
        $api_ok   = false;
        $api_code = 0;
        $api_raw  = '';
        $api_err  = '';
        $payload  = null;

        if ($cached_payload !== false) {
            // On sert le cache
            $payload  = $cached_payload;
            $api_ok   = true;
            $api_code = 200;
            $api_raw  = '';   // volontaire : pas de raw en cache
            $api_err  = '';
        } else {
            // Appel API
            $args = [
                'timeout' => 20,
                'headers' => array_filter([
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Referer'      => $referer ?: null,
                ]),
                'body'    => ['domain' => $domain],
            ];

            $api_wp_response = wp_remote_post($API_URL, $args);

            $api_ok   = !is_wp_error($api_wp_response);
            $api_code = $api_ok ? (int) wp_remote_retrieve_response_code($api_wp_response) : 0;
            $api_raw  = $api_ok ? (string) wp_remote_retrieve_body($api_wp_response) : '';
            $api_err  = $api_ok ? '' : $api_wp_response->get_error_message();

            if ($api_ok && $api_code >= 200 && $api_code < 300) {
                $decoded = json_decode($api_raw, true);
                if (is_array($decoded)) {
                    $payload = $decoded;

                    // Cache uniquement si JSON valide
                    set_transient($cache_key_data, $payload, $ttl);
                    set_transient($cache_key_meta, [
                        'cached_at' => time(),
                        'domain'    => $domain,
                    ], $ttl);
                }
            }
        }

        // ---- Normalisation ----
        $geoMetadatas = null;
        $metas = [];
        $style = [
            'primary_color' => "#3b82f6",
            'background_color' => "#f9fafb",
            'text_color' => "#111827",
            'card_bg_color' => "#ffffff",
            'card_border_color' => "#e5e7eb"
        ];

        if (is_array($payload)) {
            if (!empty($payload['style'])) {
                $style = is_string($payload['style']) ? json_decode($payload['style'], true) : $payload['style'];
            }
            if (!empty($payload['schema_json'])) {
                $geoMetadatas = is_string($payload['schema_json']) ? json_decode($payload['schema_json'], true) : $payload['schema_json'];
            }
            if (!empty($payload['all_metadatas'])) {
                $metas = is_string($payload['all_metadatas']) ? json_decode($payload['all_metadatas'], true) : $payload['all_metadatas'];
            }
        }

        include_once ELAIA_PLUGIN_DIR . 'views/metadata.php';
    }
}
