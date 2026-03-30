<?php

use Elaia\Utils\ElaiaPagesMethods;

if (!defined('ABSPATH') || !defined('ELAIA_PLUGIN_DIR')) exit;

if (!function_exists('elaia_prepare_corpus_payload')) {
    function elaia_prepare_corpus_payload()
    {
        // Récupérer le domain depuis le shortcode ou détecter automatiquement
        global $elaia_corpus_domain;
        $domain  = $elaia_corpus_domain ?: ElaiaPagesMethods::detect_domain();
        $referer = ElaiaPagesMethods::detect_referer();

        $API_HOST = 'https://app.ela-ia.com/api';

        // ── Cache ──
        $ttl = 30 * MINUTE_IN_SECONDS;
        $cache_key_base = 'elaia_corpus_' . md5(strtolower(trim((string)$domain)));
        $cache_key_data = $cache_key_base . '_data';

        $bypass_cache = isset($_GET['elaia_nocache']) && $_GET['elaia_nocache'] == '1';

        if (is_user_logged_in() && current_user_can('manage_options') && isset($_GET['elaia_clear_cache']) && $_GET['elaia_clear_cache'] == '1') {
            delete_transient($cache_key_data);
        }

        $cached = (!$bypass_cache) ? get_transient($cache_key_data) : false;

        $api_ok = false;
        $api_code = 0;
        $api_err = '';
        $corpus = [];
        $chatbot_key = null;
        $settings = null;
        $suggests = [];

        if ($cached !== false) {
            $corpus = $cached['corpus'] ?? [];
            $chatbot_key = $cached['chatbot_key'] ?? null;
            $settings = $cached['settings'] ?? null;
            $suggests = $cached['suggests'] ?? [];
            $api_ok = true;
            $api_code = 200;
        } else {
            // 1. Fetch corpus + key
            $webapp_url = $API_HOST . '/elaiaapp/chatbots/metadatas/webapp?domain=' . urlencode($domain);
            $webapp_response = wp_remote_get($webapp_url, [
                'timeout' => 20,
                'headers' => array_filter([
                    'Accept'  => 'application/json',
                    'Referer' => $referer ?: null,
                ]),
            ]);

            $api_ok = !is_wp_error($webapp_response);
            $api_code = $api_ok ? (int) wp_remote_retrieve_response_code($webapp_response) : 0;
            $api_err = $api_ok ? '' : $webapp_response->get_error_message();

            if ($api_ok && $api_code >= 200 && $api_code < 300) {
                $body = json_decode(wp_remote_retrieve_body($webapp_response), true);
                $corpus = $body['data']['corpus'] ?? $body['corpus'] ?? [];
                $chatbot_key = $body['data']['key'] ?? $body['key'] ?? null;

                // 2. Fetch chatbot settings + suggests
                if ($chatbot_key) {
                    $chatbot_url = $API_HOST . '/v1/chatbot/' . urlencode($chatbot_key);
                    $chatbot_response = wp_remote_get($chatbot_url, [
                        'timeout' => 10,
                        'headers' => ['Accept' => 'application/json'],
                    ]);

                    if (!is_wp_error($chatbot_response)) {
                        $chatbot_body = json_decode(wp_remote_retrieve_body($chatbot_response), true);
                        $settings = $chatbot_body['settings'] ?? null;
                        $suggests = $chatbot_body['suggests'] ?? [];
                    }
                }

                // Cache tout
                set_transient($cache_key_data, [
                    'corpus'      => $corpus,
                    'chatbot_key' => $chatbot_key,
                    'settings'    => $settings,
                    'suggests'    => $suggests,
                ], $ttl);
            }
        }

        // Extraire les variables pour la vue
        $agent_name = $settings['agent_name'] ?? '';
        $agent_picture = $settings['agent_picture'] ?? null;
        $default_picture = $settings['default_picture'] ?? null;
        $hook_text = $settings['hook'] ?? '';
        $primary_color = $settings['primary_color'] ?? '#16a34a';
        $chat_host = 'https://chatbot.ela-ia.com';
        $app_host = 'https://app.ela-ia.com';

        include_once ELAIA_PLUGIN_DIR . 'views/corpus.php';
    }
}