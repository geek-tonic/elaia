<?php

if (!defined('ABSPATH') || !defined('ELAIA_PLUGIN_DIR')) exit;

if (ELAIA_VERSION === '%RELEASE_VERSION%') {
  // build non remplacé => on ne tente pas de migration versionnée
  return;
}

add_action('upgrader_process_complete', 'elaia_on_upgrader_complete', 10, 2);

function elaia_on_upgrader_complete($upgrader, $options)
{
  if (empty($options['type']) || $options['type'] !== 'plugin') return;
  if (empty($options['action']) || $options['action'] !== 'update') return;
  if (empty($options['plugins']) || !is_array($options['plugins'])) return;

  $my_plugin = ELAIA_PLUGIN_BASENAME;
  if (!in_array($my_plugin, $options['plugins'], true)) return;

  // Lance la migration
  elaia_run_upgrade_tasks(get_option('elaia_plugin_version', '0.0.0'), ELAIA_VERSION);

  // Sauvegarde la version installée
  update_option('elaia_plugin_version', ELAIA_VERSION, false);
}

add_action('admin_init', 'elaia_maybe_run_upgrade_fallback', 1);

function elaia_maybe_run_upgrade_fallback()
{
  if (!current_user_can('manage_options')) return;

  $installed = get_option('elaia_plugin_version');
  if (!$installed || version_compare($installed, ELAIA_VERSION, '<')) {
    elaia_run_upgrade_tasks($installed ?: '0.0.0', ELAIA_VERSION);
    update_option('elaia_plugin_version', ELAIA_VERSION, false);
  }
}

function elaia_run_upgrade_tasks($from_version, $to_version)
{
  // Version où tu introduis la migration (mets la vraie)
  if (version_compare($from_version, '1.2.7', '<')) {
    if (function_exists('elaia_create_or_update_pages')) {
      elaia_create_or_update_pages();
    }
  }
}
