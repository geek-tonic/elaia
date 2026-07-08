# Elaia WordPress Plugin

Ce plugin WordPress permet d'intégrer les fonctionnalités de [https://app.ela-ia.com](https://app.ela-ia.com) à votre site : chatbot IA, pages FAQ SEO, métadonnées géographiques et corpus de connaissances.

## Version

Actuellement à la version **%RELEASE_VERSION%**.

## Fonctionnalités

- **Chatbot IA** : widget conversationnel intégré automatiquement sur toutes les pages
- **Page FAQ** (`/elaia-glossary/`) : questions fréquentes générées par le chatbot, au format Schema.org FAQPage
- **Page Métadonnées** (`/elaia-metadatas/`) : carte interactive des points d'intérêt autour de vous (hébergements, activités, villes…)
- **Page Corpus** (`/my-elaia-plugin/`) : application mobile-like avec accès au chatbot et aux informations pratiques (abonnés My Elaia uniquement)
- **Groupe de FAQ** (shortcode `[elaia_faq_group]`) : publiez un sous-ensemble ciblé de FAQ sur n'importe quelle page, avec balisage Schema.org FAQPage (SEO/GEO)
- **Multilingue** : chatbot et pages traduits selon la langue du site (fr, en, es, de, nl, it, pt, basque, catalan), avec repli automatique sur le français
- **Mode groupe** : support des sites mono-domaine avec plusieurs sous-sites (pages créées automatiquement sous chaque path)
- **Sitemap** : les pages Elaia apparaissent automatiquement dans les sitemaps (Yoast, RankMath, SEOPress, natif WP)

## Shortcodes

| Shortcode | Rôle |
|---|---|
| `[elaia_faq]` | Page FAQ complète générée par le chatbot |
| `[elaia_faq_group group="slug"]` | Groupe de FAQ ciblé (rendu serveur + JSON-LD FAQPage) |
| `[elaia_metadatas]` | Carte interactive des points d'intérêt |
| `[elaia_corpus]` | Application corpus (abonnés My Elaia) |

Chaque shortcode accepte un attribut optionnel `domain="…"` pour forcer le domaine résolu (utile en mode groupe).

## Installation

1. Téléchargez le fichier ZIP de la version %RELEASE_VERSION%.
2. Rendez-vous dans l'administration WordPress, section **Extensions > Ajouter**.
3. Cliquez sur **Téléverser une extension** et sélectionnez le ZIP téléchargé.
4. Activez le plugin après l'installation.

Les pages FAQ et Métadonnées sont créées automatiquement à l'activation. La page Corpus est créée uniquement si votre abonnement le permet.

## Mise à jour

Le plugin se met à jour automatiquement depuis l'interface WordPress (compatible avec les gestionnaires de maintenance type WP Umbrella / ManageWP / MainWP). Vous pouvez également le mettre à jour manuellement en téléchargeant la dernière version.

À chaque mise à jour, les pages Elaia (FAQ, Métadonnées, Corpus) sont vérifiées et restaurées automatiquement si nécessaire — **sans réactivation du plugin ni réglage des permaliens**.

## Prérequis

- WordPress 5.5+
- PHP 7.4+