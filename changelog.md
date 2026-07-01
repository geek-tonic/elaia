# Changelog

## [Non publié]
### Corrigé
- **`ElaiaUpdateChecker`** : la version renvoyée à WordPress (`new_version`, `version`) est désormais normalisée (préfixe `v` retiré) afin que le cœur WP ne stocke jamais un numéro qui casse ses propres comparaisons. `slug` corrigé vers le dossier `elaia` (au lieu du chemin `elaia/elaia.php`) — conforme à `plugins_api`, répare le lien « Voir les détails » et les bascules de mise à jour auto. Garde `is_object()` sur le transient. Transmission des `icons` distantes si présentes.

### Ajouté
- **`upgrade.php`** : `elaia_create_or_update_pages()` s'exécute à **chaque** mise à jour du plugin (plus seulement depuis une version < 1.2.10). Les liens `elaia-glossary` / `elaia-metadatas` / `my-elaia-plugin` sont donc (re)créés automatiquement après une MAJ, **sans désactivation/réactivation ni flush des permaliens** (ce sont des pages WP standard, fonctionnelles dès leur publication).
- **`upgrade.php`** : auto-réparation sur `admin_init` — si une page Elaia attendue est absente, la création est relancée (vérifié au plus 1×/h, recherche par slug indépendante du parent → compatible mode groupe). La page corpus `my-elaia-plugin` n'est attendue publiée que si le client a un abonnement my-elaia.

## [1.3.7] - 2026-06-30
### Corrigé
- **Workflow de release** : la version est lue depuis `github.event.release.tag_name` (extraction fiabilisée après plusieurs itérations).

## [1.3.6] - 2026-06-30
### Corrigé
- **Workflow de release** : extraction de version depuis `release.tag_name` + garde anti-version-vide (itérations de la chaîne de déploiement).

## [1.3.5] - 2026-06-30
### Corrigé
- **`ElaiaUpdateChecker`** : normalisation du préfixe `v` dans la comparaison de versions (`normalize_version()`). **Corrige le bug majeur qui empêchait de proposer les mises à jour** : les tags mélangeaient `v1.3.x` et `1.3.x`, or `version_compare('1.3.3', 'v1.3.4', '<')` renvoie `false` à cause du `v` → WordPress ne voyait jamais la version distante comme plus récente. ⚠️ Les installs restées sur une version antérieure à 1.3.5 (ancien checker) doivent être mises à jour manuellement une fois pour se débloquer.
- Convention de tag standardisée **sans préfixe `v`** à partir de cette version.

## [1.3.4] - 2026-06-24
### Corrigé
- **Erreur 403 / referer** (`Corpus.php`, `Metadata.php`, `Faq.php`) : utilisation du domaine courant au lieu du `Referer` pour la résolution du chatbot — corrige les accès refusés selon le contexte de navigation.
- **Marqueur Leaflet cassé** : remplacement par un `divIcon` SVG épinglé (évite l'icône par défaut manquante/brisée derrière un CDN ou un optimiseur).

### Ajouté
- **Page Metadata** : affichage de la galerie d'images et des documents PDF dans la modale détail, avec nouvelles cartes dédiées (PDF / Galerie).

## [1.3.3] - 2026-05-23
### Ajouté
- **Vidéo d'accueil sur la page corpus** : si l'admin renseigne une URL dans MyElaia > Paramétrage (champ "Vidéo d'accueil"), la vidéo se lance automatiquement au premier visit du visiteur. Le marqueur "vue" est stocké dans `localStorage` sous la clé `elaia_welcome_video_seen_<chatbotKey>` et contient l'URL (pas un booléen) : si l'admin change la vidéo, le visiteur la revoit automatiquement.
- **Bouton "Revoir la vidéo d'accueil"** dans le header de la page corpus (à gauche du sélecteur de langue), affiché uniquement quand une URL est configurée. Traduction `replay_welcome_video` × 9 locales (fr, en, es, de, nl, it, pt, eus, cat).
- **Design hero aligné sur MyElaia > Paramétrage** (`views/corpus.php`) : nouvelle fonction `buildHeroBackground()` qui applique le bon CSS background selon le type configuré dans AppConfig :
  - `solid` → couleur unie
  - `gradient` → `linear-gradient(orientation, start, end)`
  - `image` → image cover + `text-shadow` automatique sur le titre pour garantir la lisibilité
- **Avatar, tagline, primary_color, suggestions** désormais pilotés par AppConfig en priorité, fallback gracieux sur les `settings` legacy (`agent_picture`, `hook`, `primary_color`, `suggests`) si AppConfig n'est pas configuré côté admin.
- **Support des URLs absolues** pour l'avatar (AppConfig renvoie une URL résolue) en plus du chemin relatif legacy (vers `APP_HOST`).

### Modifié
- `includes/Pages/Corpus.php` : lit le nouveau nœud `app_config` retourné par `/v1/chatbot/{key}` (hero, avatar, tagline, primary_color, welcome_video_url, prompt_suggestions, translations). Le cache transient inclut ce nœud en plus des champs existants.

## [1.3.2] - 2026-05-11
### Corrigé
- **Compatibilité thèmes FSE (block themes)** :
  - `rewrite.php` : l'intercept `template_redirect` ajouté pour les thèmes sans `the_content()` (Falconheavy / Flower Campings) cassait les thèmes FSE en court-circuitant le moteur de templates en blocs → header et footer absents. Désormais skip de l'intercept via `wp_is_block_theme()` ; le shortcode est traité naturellement par le bloc `post-content`.
  - `shortcodes.php` : `wp-includes/block-template.php` appelle `wptexturize()` directement sur le HTML rendu (pas via filtre `the_content`). Les `<` et `>` du JS inline piégeaient son tag parser et encodaient les `&&` en `&#038;&#038;` → `SyntaxError` qui cassait l'IIFE complète (boutons + carte). Fix par `ob_start` au `template_redirect` qui défait l'encoding HTML uniquement à l'intérieur des `<script>`, indépendamment du timing d'appel de wptexturize.
  - `views/metadata.php` : init Leaflet rendu robuste (fonction `initLeafletMap` avec retry toutes les 100ms, cap à 10s) — si le `<script src>` Leaflet est différé par un optimiseur JS (WP Rocket Delay JS, Cloudflare Rocket Loader, etc.) ou si `#em-map` n'est pas encore parsé au moment de l'IIFE, l'init attend.
- **rewrite.php** : intercept `template_redirect` (priorité 5) pour les pages Metadata et FAQ sur thèmes classiques — les thèmes qui n'appellent pas `the_content()` (Falconheavy / Flower Campings) ne rendaient jamais le shortcode. L'intercept inclut les templates du plugin qui appellent `get_header()` / `get_footer()` → header et footer du thème préservés.
- Extraction automatique de l'attribut `domain` du shortcode pour le mode groupe.
- Crawlers internes (Yoast, WP-Cron, AJAX) exclus du hook pour ne pas casser le sitemap.
- **views/metadata.php** : attributs `data-no-optimize`, `data-no-minify`, `data-cfasync="false"` sur les balises Leaflet (CSS + JS) — empêche WP Rocket / CloudFlare Auto-Minify de casser l'affichage de la carte.

### Ajouté
- **Refonte complète de la page Metadata** (`views/metadata.php`) :
  - Filtres latéraux dynamiques (type, équipements WiFi/parking/animaux/piscine), reset, recherche insensible aux accents et à la casse.
  - Filtres équipements visibles uniquement si la catégorie `accommodation` est active, reset auto sur changement d'onglet.
  - Onglets de catégories avec compteurs, synchronisation bidirectionnelle filtres ↔ onglets ↔ légende carte.
  - Cartes Leaflet améliorées : marqueurs par catégorie, popup avec bouton "voir la fiche", légende cliquable, recentrage automatique, bouton de recentrage manuel, désactivation du zoom à la molette, fitBounds initial sur les points visibles.
  - La carte ne s'affiche que si la catégorie active contient des points GPS.
  - Modale détail fiche avec champs structurés, labels API, gestion image absente.
  - Layout sidebar collapsible sur tablette/mobile (< 1024px).
  - Sticky tabs et sidebar sous le header sticky/fixé du thème (variable CSS `--em-header-offset` calculée au runtime).
  - Overlay opaque sous le header thème pendant le scroll (pour les thèmes au header semi-transparent).
  - Gestion personnalisée du scroll en 3 phases (page → main → page-bottom) avec snap symétrique et clamp anti-overshoot dans les deux directions.
  - Stacking context isolé sur `.em-wrap` (`position: relative; z-index: 10; isolation: isolate`).
- **Multisite** : cache `elaia_myelaia_domains` partitionné par domaine (clé `elaia_myelaia_domains_<md5(domain)>`) — chaque sous-site multisite a désormais son propre cache d'abonnement, plus de pollution croisée entre sous-sites du même réseau.
- **Gate de souscription sur la page corpus** (`rewrite.php`) : si le domaine résolu n'a pas `has_subscription:true`, la page renvoie un 404 propre (`status_header(404)` + `nocache_headers()` + `get_404_template()`) avant de rendre le template. Fail-safe : API down → accès refusé par défaut.
- **Mode dev** : override du domaine via variable d'environnement `ELAIA_DEV_DOMAIN` (utilisée par `Metadata.php`, `Faq.php`, `Corpus.php` quand `WP_DEBUG` est actif) pour tester les pages en local avec un domaine de prod.
- **Environnement de dev VS Code** : ajout du dossier `.devcontainer/` (Docker Compose : WordPress + MySQL + WP-CLI), instructions Intelliphense, setup automatique.

## [1.3.1] - 2026-03-30
### Corrigé
- **activation.php** : `elaia_get_myelaia_domains()` retourne désormais toujours un tableau associatif `['domains' => [], 'has_subscription' => false]` — corrige le parsing qui renvoyait systématiquement `null` pour les domaines et l'abonnement
- **activation.php** : support du format de réponse API avec ou sans wrapper `data` (fallback `$body['data']['key'] ?? $body['key']`)
- **activation.php** : suppression de `flush_rewrite_rules()` directe — remplacé par un transient `elaia_needs_flush` consommé au prochain `init` via `flush_rewrite_rules(false)` (ne touche pas au `.htaccess`)
- **rewrite.php** : suppression complète des rewrite rules fallback (`elaia_virtual_page`, `parse_request`, `template_include`) — les vraies pages WP gèrent tout, les rewrite rules causaient des doublons sitemap et des conflits de routage
- **rewrite.php** : le corpus utilise `template_redirect` + `exit` (priorité 5) au lieu de `template_include` — corrige le rendu sans header/footer, notamment sur les thèmes FSE
- **rewrite.php** : restauration des redirections 301 des anciennes URLs rewrite (`?elaia_faq=1`, `?elaia_metadata=1`)
- **sitemap.php** : suppression de tous les ajouts manuels (Yoast, RankMath, SEOPress, natif WP) et du provider custom `ElaiaChatbotSitemapProvider` — les vraies pages WP apparaissent automatiquement dans les sitemaps, les ajouts manuels créaient des doublons

## [1.3.0] - 2026-03-30
### Ajouté
- **Mode groupe mono-domaine** : support des clients avec un seul domaine et plusieurs sous-sites (ex: Campasun avec `www.campasun.eu/international/le-camping`, `www.campasun.eu/lesoleil/le-camping`)
- Création automatique des pages Elaia comme pages enfants WordPress sous chaque path du mode groupe
- Nouvel endpoint API `GET /api/elaiaapp/domains/resolve?domain={host}` pour la résolution des domaines rattachés à un site
- Système de résolution par candidats (du plus spécifique au plus générique) sur `getCorpus` et `getMetadatas`, aligné sur le pattern de `integration()`
- Attribut `domain` sur les shortcodes `[elaia_faq]` et `[elaia_metadatas]` (déjà existant sur `[elaia_corpus]`)
- Page corpus (`my-elaia-plugin`) rendue sans header/footer du thème via `template_redirect`
- Fichier `ARCHITECTURE.md` pour documenter le système et guider les agents IA

### Modifié
- `activation.php` : logique de création de pages refactorisée — les 3 pages (FAQ, Metadatas, Corpus) sont créées selon `has_subscription` retourné par l'API ; FAQ et Metadatas sont toujours créées, Corpus uniquement pour les abonnés
- `activation.php` : suppression de `elaia_has_chatbot()` (tous les sites avec le plugin ont forcément un chatbot)
- `activation.php` : cache transient uniquement sur succès API (HTTP 200), pas de cache si erreur réseau
- `sitemap.php` : suppression du provider custom `ElaiaChatbotSitemapProvider` et des ajouts manuels Yoast/RankMath/SEOPress (les vraies pages WP apparaissent automatiquement)
- `shortcodes.php` : les shortcodes `elaia_faq` et `elaia_metadatas` acceptent et transmettent l'attribut `domain` via globales
- `Faq.php` / `Metadata.php` : utilisent le domaine du shortcode en priorité, fallback sur `detect_domain()`
- `views/metadata.php` : refactoring complet — variables renommées pour lisibilité, commentaires ajoutés sur chaque section (CSS, PHP, JS)
- API Laravel `getCorpus` / `getMetadatas` : vérification referer sur le host uniquement, résolution chatbot par système de candidats
- API Laravel `hasMyElaia` : ne retourne plus tous les clients, uniquement ceux rattachés au domaine demandé

### Sécurité
- Suppression de l'endpoint `/clients` qui exposait tous les domaines clients — remplacé par `domains/resolve` filtré par domaine

## [1.2.10] - 2026-02-03
- Version pour déclencher l'auto-upgrade

## [1.2.9] - 2026-02-03
- Création de pages pour Metadatas et FAQ Elaia
- Gestion propre du sitemap
- Auto-upgrade des données et gestion de cache transient

## [1.2.2] - 2025-11-28
- Suppression de la mise en cache de WP-Rocket

## [1.2.1] - 2025-11-24
- Modification du hook pour Yoast SEO (gestion avec multi CPT actif)

## [1.2.0] - 2025-11-24
- Soucis de déploiement automatisé

## [1.1.19] - 2025-11-24
- Gestion PHP7.4
- Ajout de sécurité et retours de tests

## [1.1.18] - 2025-11-24
- Gestion de la mise en sitemap des pages de Glossaire et de Metadatas
- Gestion du cache et de l'exclusion dans WP-ROCKET
- Gestion des rewrites proprement et suppression des soucis de Permalinks
- Gestion des insertions de Elaia dans les sites
- Ajout de sécurité
- Restructuration complète du code source

## [1.1.17] - 2025-11-06
- Correction height

## [1.1.16] - 2025-11-06
- Correction z-index

## [1.1.15] - 2025-10-09
- Cache le chatbot si form ouvert
- Bug corrigé sur placeholder

## [1.1.14] - 2025-10-09
- Cache le chatbot si form ouvert
- Bug corrigé sur placeholder

## [1.1.13] - 2025-10-08
- Ajout du geo pour metadatas
- !important sur le design faq

## [1.1.12] - 2025-10-06
- Ajout page elaia-metadata

## [1.1.11] - 2025-09-30
- Ajout page elaia-faq

## [1.1.8] - 2025-09-22
- Balise script pour encart overview

## [1.1.7] - 2025-07-28
- Ajout de la balise script

## [1.1.6] - 2025-07-18
- Suppression du checker de mise à jour des plugins
- Suppression des logs

## [1.1.5] - 2025-07-11
- Ajout de la langue du site dans l'url pour appeler le chatbot

## [1.1.4] - 2025-07-01
- Ajout d'un système de vérification des mises à jour pour permettre la mise à jour automatique du plugin

## [1.1.3] - 2025-06-24
- Ajout d'un fichier README

## [1.1.2] - 2025-06-17
- Changement de l'URL pour la synchronisation

## [1.1.1] - 2025-06-10
- Possibilité de mettre à jour le plugin directement depuis l'interface WordPress

## [1.1.0] - 2025-06-03
### Ajouté
- Système de logs tampon (`elaia_logs`) avec type, message, fichier, ligne, code d'erreur, date
- Création automatique de la table à l'activation
- Purge horaire via cron
- Fonction `elaia_log()` pour enregistrer des événements manuellement
- Capture automatique des erreurs PHP, exceptions et erreurs fatales

### Modifié
- Séparation du code en `elaia-plugin.php` et `elaia-logs.php`

## [1.0.0] - 2025-04-16
- Version initiale — ajout automatique du script chatbot dans le footer