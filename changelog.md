# Changelog

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
- `rewrite.php` : rewrite rules simplifiées (racine uniquement), les sous-chemins sont gérés par les vraies pages WP
- `rewrite.php` : ajout de `parse_request` pour éviter le conflit entre rewrite rule et vraie page WP
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