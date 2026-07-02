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

  if (is_multisite() && is_plugin_active_for_network($my_plugin)) {
    $site_ids = get_sites(['fields' => 'ids', 'number' => 0]);
    foreach ($site_ids as $site_id) {
      switch_to_blog($site_id);
      elaia_run_upgrade_tasks(get_option('elaia_plugin_version', '0.0.0'), ELAIA_VERSION);
      update_option('elaia_plugin_version', ELAIA_VERSION, false);
      restore_current_blog();
    }
  } else {
    elaia_run_upgrade_tasks(get_option('elaia_plugin_version', '0.0.0'), ELAIA_VERSION);
    update_option('elaia_plugin_version', ELAIA_VERSION, false);
  }
}

add_action('admin_init', 'elaia_maybe_run_upgrade_fallback', 1);

function elaia_maybe_run_upgrade_fallback()
{
  if (!current_user_can('manage_options')) return;

  $installed = get_option('elaia_plugin_version');
  if (!$installed || version_compare(elaia_normalize_version($installed), elaia_normalize_version(ELAIA_VERSION), '<')) {
    elaia_run_upgrade_tasks($installed ?: '0.0.0', ELAIA_VERSION);
    update_option('elaia_plugin_version', ELAIA_VERSION, false);
  }
}

function elaia_normalize_version($version)
{
  return preg_replace('/^[vV]\.?/', '', trim((string) $version));
}

function elaia_run_upgrade_tasks($from_version, $to_version)
{
  $from_version = elaia_normalize_version($from_version);

  if (!function_exists('elaia_create_or_update_pages')) return;

  // À CHAQUE mise à jour : (re)crée/actualise les pages Elaia.
  // Les liens elaia-glossary / elaia-metadatas / my-elaia-plugin sont des pages
  // WordPress standard : elles fonctionnent dès leur publication, sans flush des
  // permaliens ni désactivation/réactivation. La fonction est idempotente
  // (create-or-update) et l'API est cachée 1h, donc l'appel est sûr et peu coûteux.
  elaia_create_or_update_pages();

  // Migration historique < 1.2.10 : flush des règles conservé à l'identique.
  if (version_compare($from_version, '1.2.10', '<')) {
    flush_rewrite_rules(false);
  }
}

/**
 * Auto-réparation : si une des pages Elaia manque (supprimée, non publiée…),
 * on relance la création — sans attendre une MAJ ni une réactivation.
 * Vérifié au plus une fois par heure, et la création n'est déclenchée que si
 * une page est réellement absente.
 */
add_action('admin_init', 'elaia_maybe_selfheal_pages', 2);

function elaia_maybe_selfheal_pages()
{
  if (!current_user_can('manage_options')) return;
  if (!function_exists('elaia_create_or_update_pages')) return;

  if (get_transient('elaia_pages_checked')) return;
  set_transient('elaia_pages_checked', 1, HOUR_IN_SECONDS);

  // FAQ + Metadatas : toujours attendues publiées.
  $slugs = [ELAIA_PAGE_FAQ_REWRITE, ELAIA_PAGE_METADATA_REWRITE];

  // Corpus (my-elaia-plugin) : attendu publié UNIQUEMENT si le client a un
  // abonnement my-elaia (sinon son état normal est "dépubliée"). L'appel API
  // est mis en cache 1h, donc pas de surcoût réseau à chaque check.
  $sub = function_exists('elaia_get_myelaia_domains') ? elaia_get_myelaia_domains() : [];
  if (!empty($sub['has_subscription'])) {
    $slugs[] = ELAIA_PAGE_CORPUS_REWRITE;
  }

  // Recherche par slug indépendamment du parent (compatible mode groupe/paths).
  foreach ($slugs as $slug) {
    $found = get_posts([
      'post_type'   => 'page',
      'post_status' => 'publish',
      'name'        => $slug,
      'numberposts' => 1,
      'fields'      => 'ids',
    ]);
    if (empty($found)) {
      elaia_create_or_update_pages();
      return;
    }
  }
}
