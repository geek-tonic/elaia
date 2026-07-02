<?php

use Elaia\Utils\ElaiaPagesMethods;

if (!defined('ABSPATH') || !defined('ELAIA_PLUGIN_DIR')) exit;

if (!function_exists('elaia_prepare_corpus_payload')) {
    function elaia_prepare_corpus_payload()
    {
        // Récupérer le domain depuis le shortcode ou détecter automatiquement
        global $elaia_corpus_domain;
        $dev_domain = (defined('WP_DEBUG') && WP_DEBUG) ? getenv('ELAIA_DEV_DOMAIN') : '';
        $domain  = $dev_domain ?: ($elaia_corpus_domain ?: ElaiaPagesMethods::detect_domain());

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
        $app_config = null;

        if ($cached !== false) {
            $corpus = $cached['corpus'] ?? [];
            $chatbot_key = $cached['chatbot_key'] ?? null;
            $settings = $cached['settings'] ?? null;
            $suggests = $cached['suggests'] ?? [];
            $app_config = $cached['app_config'] ?? null;
            $api_ok = true;
            $api_code = 200;
        } else {
            // 1. Fetch corpus + key
            $webapp_url = $API_HOST . '/elaiaapp/chatbots/metadatas/webapp?domain=' . urlencode($domain);
            $webapp_response = wp_remote_get($webapp_url, [
                'timeout' => 20,
                'headers' => array_filter([
                    'Accept'  => 'application/json',
                    'Referer' => $domain ?: null,
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
                        $app_config = $chatbot_body['app_config'] ?? null;
                    }
                }

                // Cache tout
                set_transient($cache_key_data, [
                    'corpus'      => $corpus,
                    'chatbot_key' => $chatbot_key,
                    'settings'    => $settings,
                    'suggests'    => $suggests,
                    'app_config'  => $app_config ?? null,
                ], $ttl);
            }
        }

        // Extraire les variables pour la vue.
        // Tout ce qui touche au DESIGN (couleur, hero, avatar, tagline, suggestions)
        // est piloté par AppConfig (MyElaia > Paramétrage) — source unique partagée
        // avec l'app mobile. Les `settings` legacy ne servent plus que pour les
        // champs non migrés (agent_name, default_picture, etc.).
        $agent_name = $settings['agent_name'] ?? '';
        $default_picture = $settings['default_picture'] ?? null;
        $has_planning = !empty($settings['has_planning']);
        $chat_host = 'https://chatbot.ela-ia.com';
        $app_host = 'https://app.ela-ia.com';

        $agent_picture = $app_config['avatar']['image_url'] ?? ($settings['agent_picture'] ?? null);
        $hook_text = $app_config['tagline'] ?? ($settings['hook'] ?? '');
        $primary_color = $app_config['primary_color'] ?? ($settings['primary_color'] ?? '#16a34a');

        // Traductions AppConfig (source partagée avec l'app MyElaia) :
        //   translations[locale] = { tagline, default_prompt, prompt_suggestions[] }
        // On expose le tagline traduit par locale ; le fallback FR est géré côté vue.
        $app_translations = (isset($app_config['translations']) && is_array($app_config['translations']))
            ? $app_config['translations'] : [];
        $hook_text_translations = [];
        foreach ($app_translations as $locale => $trans) {
            if (!empty($trans['tagline'])) {
                $hook_text_translations[$locale] = $trans['tagline'];
            }
        }
        $welcome_video_url = $app_config['welcome_video_url'] ?? ($settings['welcome_video_url'] ?? null);

        // Hero : type + valeurs associées. Si AppConfig absent on retombe sur
        // solid avec primary_color.
        $hero = [
            'type' => $app_config['hero']['type'] ?? 'solid',
            'color' => $app_config['hero']['color'] ?? $primary_color,
            'gradient_start' => $app_config['hero']['gradient_start'] ?? null,
            'gradient_end' => $app_config['hero']['gradient_end'] ?? null,
            'gradient_orientation' => $app_config['hero']['gradient_orientation'] ?? 'to bottom',
            'image_url' => $app_config['hero']['image_url'] ?? null,
        ];

        // Suggestions : on privilégie le nouveau format AppConfig si présent,
        // sinon on garde les suggests legacy. On normalise pour que le JS ait
        // toujours { title, real_prompt, translations? }.
        if (!empty($app_config['prompt_suggestions'])) {
            $base = array_values($app_config['prompt_suggestions']);
            $suggests = [];
            foreach ($base as $index => $s) {
                $position = isset($s['position']) ? $s['position'] : $index;

                // Traductions par locale : match par position (comme l'app MyElaia),
                // fallback par index. On ne garde que les titres non vides.
                $trans = [];
                foreach ($app_translations as $locale => $data) {
                    $localeSuggestions = (isset($data['prompt_suggestions']) && is_array($data['prompt_suggestions']))
                        ? array_values($data['prompt_suggestions']) : [];
                    if (empty($localeSuggestions)) continue;

                    $match = null;
                    foreach ($localeSuggestions as $ls) {
                        if (isset($ls['position']) && $ls['position'] == $position) {
                            $match = $ls;
                            break;
                        }
                    }
                    if ($match === null && isset($localeSuggestions[$index])) {
                        $match = $localeSuggestions[$index];
                    }
                    if ($match !== null && !empty($match['title'])) {
                        $trans[$locale] = ['title' => $match['title']];
                    }
                }

                $suggests[] = [
                    'title' => $s['title'] ?? '',
                    'real_prompt' => $s['prompt'] ?? '',
                    'color' => $s['color'] ?? null,
                    'translations' => !empty($trans) ? $trans : null,
                ];
            }
        }

        include_once ELAIA_PLUGIN_DIR . 'views/corpus.php';
    }
}
