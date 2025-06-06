<?php 

add_action('rest_api_init', function () {
    register_rest_route('elaia/v1', '/plugins/all', [
        'methods' => 'GET',
        'callback' => 'elaia_get_plugins',
    ]);

    register_rest_route('elaia/v1', '/plugins/updates', [
        'methods' => 'GET',
        'callback' => 'elaia_get_plugins_updates',
    ]);
});


function elaia_get_plugins() {
    require_once ABSPATH . 'wp-admin/includes/update.php';
    require_once ABSPATH . 'wp-admin/includes/plugin.php';

    $plugins = get_plugins();
    $activated_plugins = array();

    
    foreach ($plugins as $k => $plugin) {
        $plugin['Active'] = is_plugin_active($k); // Vérifie si le plugin est activé
        $plugin['slug'] = $k;
        array_push($activated_plugins, $plugin);
    }

    return $activated_plugins;
}


function elaia_get_plugins_updates() {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    require_once ABSPATH . 'wp-admin/includes/update.php';
     // Récupérer les plugins installés
    $installed_plugins = get_plugins();

    // Récupère les informations de mise à jour depuis le transient 'update_plugins'
    $plugin_updates = get_site_transient('update_plugins');
    return $plugin_updates ? $plugin_updates->response : array();
}