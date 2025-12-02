<?php

if (!defined('ABSPATH') || !defined('ELAIA_PLUGIN_DIR'))
    exit;

/**
 * Fonction appelée lors de l'activation du plugin
 * Enregistre les règles de réécriture et flush les permaliens
 */
function elaia_activate_plugin()
{
    // Enregistrer les règles de réécriture
    elaia_register_rewrites();

    // Flush les règles pour les activer
    flush_rewrite_rules();
}

/**
 * Fonction appelée lors de la désactivation du plugin
 * Nettoie les règles de réécriture
 */
function elaia_deactivate_plugin()
{
    // Flush les règles pour supprimer celles du plugin
    flush_rewrite_rules();
}
