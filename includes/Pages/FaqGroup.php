<?php

use Elaia\Utils\ElaiaPagesMethods;

if (! defined('ABSPATH') || ! defined('ELAIA_PLUGIN_DIR')) {
    exit;
}

if (! function_exists('elaia_prepare_faq_group_payload')) {
    /**
     * Rend un groupe de FAQ (sous-ensemble du Corpus public) côté serveur, pour le
     * shortcode [elaia_faq_group group="{slug}"]. Calqué sur elaia_prepare_faq_payload()
     * mais tape l'endpoint /v1/chatbot/faq-group avec le slug du groupe.
     */
    function elaia_prepare_faq_group_payload($group)
    {
        global $elaia_faq_group_domain;

        $group = trim((string) $group);
        if ($group === '') {
            return;
        }

        $dev_domain = (defined('WP_DEBUG') && WP_DEBUG) ? getenv('ELAIA_DEV_DOMAIN') : '';
        $domain = $dev_domain ?: ($elaia_faq_group_domain ?: ElaiaPagesMethods::detect_domain());

        $API_URL = 'https://app.ela-ia.com/api/v1/chatbot/faq-group';

        // ---- Cache (transient) ----
        $ttl = 30 * MINUTE_IN_SECONDS;

        // Clé stable + courte : dépend du domaine ET du groupe.
        $cache_key_base = 'elaia_faqgrp_'.md5(strtolower(trim((string) $domain)).'|'.strtolower($group));
        $cache_key_data = $cache_key_base.'_data';
        $cache_key_meta = $cache_key_base.'_meta';

        $bypass_cache = isset($_GET['elaia_nocache']) && $_GET['elaia_nocache'] == '1';

        $cached_payload = (! $bypass_cache) ? get_transient($cache_key_data) : false;

        if (is_user_logged_in() && current_user_can('manage_options') && isset($_GET['elaia_clear_cache']) && $_GET['elaia_clear_cache'] == '1') {
            delete_transient($cache_key_data);
            delete_transient($cache_key_meta);
        }

        $api_ok = false;
        $api_code = 0;
        $api_raw = '';
        $api_err = '';
        $payload = null;

        if ($cached_payload !== false) {
            $payload = $cached_payload;
            $api_ok = true;
            $api_code = 200;
        } else {
            $args = [
                'timeout' => 20,
                'headers' => array_filter([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Referer' => $domain ?: null,
                ]),
                'body' => [
                    'domain' => $domain,
                    'group' => $group,
                ],
            ];

            $api_wp_response = wp_remote_post($API_URL, $args);

            $api_ok = ! is_wp_error($api_wp_response);
            $api_code = $api_ok ? (int) wp_remote_retrieve_response_code($api_wp_response) : 0;
            $api_raw = $api_ok ? (string) wp_remote_retrieve_body($api_wp_response) : '';
            $api_err = $api_ok ? '' : $api_wp_response->get_error_message();

            if ($api_ok && $api_code >= 200 && $api_code < 300) {
                $decoded = json_decode($api_raw, true);
                if (is_array($decoded)) {
                    $payload = $decoded;

                    set_transient($cache_key_data, $payload, $ttl);
                    set_transient($cache_key_meta, [
                        'cached_at' => time(),
                        'domain' => $domain,
                        'group' => $group,
                    ], $ttl);
                }
            }
        }

        // Normaliser corpus & all_questions
        $corpus = null;
        $questions = [];

        if (is_array($payload)) {
            if (! empty($payload['corpus'])) {
                $corpus = is_string($payload['corpus']) ? json_decode($payload['corpus'], true) : $payload['corpus'];
            }
            if (! empty($payload['all_questions'])) {
                $questions = is_string($payload['all_questions']) ? json_decode($payload['all_questions'], true) : $payload['all_questions'];
            }
        }

        include ELAIA_PLUGIN_DIR.'views/faq-group.php';
    }
}
