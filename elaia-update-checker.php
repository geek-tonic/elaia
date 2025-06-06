<?php

function elaia_check_for_updates($transient) {
    // URL du fichier JSON contenant les informations de mise à jour
    $update_info_url = 'https://raw.githubusercontent.com/geek-tonic/elaia/main/update-info.json';

    // Récupérer les données du fichier JSON
    $response = @file_get_contents($update_info_url);

    if ($response === false) {
        error_log('Erreur lors de la récupération du fichier JSON depuis : ' . $update_info_url);
        return $transient; // Retourner sans mise à jour si échec
    }

    // Décoder la réponse JSON
    $update_info = json_decode($response, true);

    if (!$update_info) {
        error_log('Erreur lors du décodage du fichier JSON ou données manquantes.');
        return $transient; // Retourner sans mise à jour si échec
    }

    // Assurer que les informations nécessaires sont présentes dans le fichier JSON
    if (!isset($update_info['version']) || !isset($update_info['download_url'])) {
        error_log('Fichier JSON mal formé, version ou download_url manquant.');
        return $transient;
    }

    // Vérifier la version actuelle du plugin
    $current_version = get_plugin_data(plugin_dir_path(__FILE__) . 'elaia.php')['Version'];
    error_log('Version actuelle du plugin Elaia : ' . $current_version);
    error_log('Version disponible dans le fichier JSON : ' . $update_info['version']);

    // Comparer la version actuelle avec la version disponible dans le fichier JSON
    if (version_compare($update_info['version'], $current_version, '>')) {
        // Créer un objet de mise à jour
        $plugin_update = (object) [
            'slug' => 'elaia',
            'plugin' => 'elaia/elaia.php',
            'new_version' => $update_info['version'],
            'url' => $update_info['download_url'],
            'package' => $update_info['download_url']
        ];

        // Ajouter cette mise à jour à l'objet $transient
        if (isset($transient->response)) {
            $transient->response['elaia/elaia.php'] = $plugin_update;
        } else {
            $transient->response = ['elaia/elaia.php' => $plugin_update];
        }

        // Log de la mise à jour ajoutée
        error_log('Mise à jour détectée pour Elaia: Version ' . $update_info['version']);
    } else {
        // Si la version installée est la même ou plus récente
        error_log('Aucune mise à jour disponible, version actuelle est plus récente ou identique.');
    }

    return $transient;
}

// Utiliser le filtre pour enregistrer la mise à jour dans WordPress
add_filter('pre_set_site_transient_update_plugins', 'elaia_check_for_updates');