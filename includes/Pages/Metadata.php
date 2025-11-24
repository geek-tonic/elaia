<?php

use Elaia\Utils\ElaiaPagesMethods;

if (!defined('ABSPATH') || !defined('ELAIA_PLUGIN_DIR')) exit;

if (!function_exists('elaia_prepare_metadata_payload')) {
    function elaia_prepare_metadata_payload()
    {
        $domain = ElaiaPagesMethods::detect_domain();
        $referer = ElaiaPagesMethods::detect_referer();
        $API_URL = 'https://app.ela-ia.com/api/v1/chatbot/metadatas';

        $args = [
            'timeout' => 20,
            'headers' => array_filter([
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Referer' => $referer ?: null,
            ]),
            'body' => ['domain' => $domain],
        ];

        $api_wp_response = wp_remote_post($API_URL, $args);
        $api_ok = !is_wp_error($api_wp_response);
        $api_code = $api_ok ? wp_remote_retrieve_response_code($api_wp_response) : 0;
        $api_raw = $api_ok ? wp_remote_retrieve_body($api_wp_response) : '';
        $api_err = $api_ok ? '' : $api_wp_response->get_error_message();

        $payload = null;
        if ($api_ok && $api_code >= 200 && $api_code < 300) {
            $payload = json_decode($api_raw, true);
        }

        $geoMetadatas = null;
        $metas = [];

        if (is_array($payload)) {
            if (!empty($payload['schema_json'])) {
                $geoMetadatas = is_string($payload['schema_json']) ? json_decode($payload['schema_json'], true) : $payload['schema_json'];
            }
            if (!empty($payload['all_metadatas'])) {
                $metas = is_string($payload['all_metadatas']) ? json_decode($payload['all_metadatas'], true) : $payload['all_metadatas'];
            }
        }

        // Rendu HTML
        status_header(200);
        nocache_headers();
        header('Content-Type: text/html; charset=' . get_option('blog_charset'));

        include_once ELAIA_PLUGIN_DIR . 'views/metadata.php';

        exit;
    }
}
