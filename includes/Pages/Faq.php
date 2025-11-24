<?php

if (!defined('ABSPATH') || !defined('ELAIA_PLUGIN_DIR')) exit;

if(!function_exists('elaia_prepare_faq_payload')){
    function elaia_prepare_faq_payload() {

        $domain  = Elaia\Utils\ElaiaPagesMethods::detect_domain();
        $referer = Elaia\Utils\ElaiaPagesMethods::detect_referer();

        $API_URL = 'https://app.ela-ia.com/api/v1/chatbot/corpus';

        $args = [
            'timeout' => 20,
            'headers' => array_filter([
                'Accept'       => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Referer'      => $referer ?: null,
            ]),
            'body'    => [
                'domain' => $domain,
            ],
        ];

        $api_wp_response = wp_remote_post($API_URL, $args);

        $api_ok   = !is_wp_error($api_wp_response);
        $api_code = $api_ok ? wp_remote_retrieve_response_code($api_wp_response) : 0;
        $api_raw  = $api_ok ? wp_remote_retrieve_body($api_wp_response) : '';
        $api_err  = $api_ok ? '' : $api_wp_response->get_error_message();

        $payload = null;
        if ($api_ok && $api_code >= 200 && $api_code < 300) {
            $payload = json_decode($api_raw, true);
        }

        // Normaliser corpus & all_questions
        $corpus = null;
        $questions = [];

        if (is_array($payload)) {
            // corpus peut être un JSON string
            if (!empty($payload['corpus'])) {
                $corpus = is_string($payload['corpus']) ? json_decode($payload['corpus'], true) : $payload['corpus'];
            }
            if (!empty($payload['all_questions'])) {
                $questions = is_string($payload['all_questions']) ? json_decode($payload['all_questions'], true) : $payload['all_questions'];
            }
        }

        // -------- Rendu HTML --------
        status_header(200);
        nocache_headers();
        header('Content-Type: text/html; charset=' . get_option('blog_charset'));

        include_once ELAIA_PLUGIN_DIR . 'views/faq.php';

        // Ne pas laisser WordPress continuer à rendre le thème
        exit;

    }
}