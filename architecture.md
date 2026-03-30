# Elaia WordPress Plugin — Architecture & Directives

> **⚠️ Ce fichier est destiné aux développeurs et agents IA.**
> **Ne PAS inclure dans les builds de production** (à exclure du déploiement via `.distignore` ou script de build).

---

## 1. Vue d'ensemble

Le plugin Elaia intègre un chatbot IA et des pages SEO (FAQ, métadonnées géo, corpus) sur les sites WordPress des clients. Il communique avec l'API Laravel hébergée sur `app.ela-ia.com`.

### Trois types de pages virtuelles

| Slug                  | Constante                    | Shortcode           | Accès                  | Template dédié |
|-----------------------|------------------------------|----------------------|------------------------|----------------|
| `elaia-glossary`      | `ELAIA_PAGE_FAQ_REWRITE`     | `[elaia_faq]`        | Tous les clients       | Non (thème)    |
| `elaia-metadatas`     | `ELAIA_PAGE_METADATA_REWRITE`| `[elaia_metadatas]`  | Tous les clients       | Non (thème)    |
| `my-elaia-plugin`     | `ELAIA_PAGE_CORPUS_REWRITE`  | `[elaia_corpus]`     | Abonnés uniquement     | Oui (nu, sans header/footer) |

### Mode groupe (mono-domaine, multi-path)

Certains clients comme Campasun ont un seul domaine (`www.campasun.eu`) mais plusieurs sous-sites identifiés par des paths :

```
www.campasun.eu/international/le-camping
www.campasun.eu/lesoleil/le-camping
```

Chaque sous-site a son propre chatbot et ses propres pages Elaia, créées comme pages enfants WordPress :

```
/international/le-camping/elaia-glossary
/international/le-camping/elaia-metadatas
/international/le-camping/my-elaia-plugin
```

---

## 2. Structure des fichiers

```
elaia/
├── elaia.php                              # Point d'entrée, constantes, hooks activation/désactivation
├── autoload.php                           # Autoloader PSR-4
├── plugin_infos.json                      # Métadonnées du plugin (version, etc.)
├── changelog.md                           # Historique des versions
├── readme.md                              # README public
├── ARCHITECTURE.md                        # Logique du plugin
├── .gitignore
│
├── assets/
│   ├── cat.svg                            # Drapeau catalan (sélecteur de langue corpus)
│   ├── eus.svg                            # Drapeau basque (sélecteur de langue corpus)
│   ├── icon-128x128.png                   # Icône plugin (listing WP)
│   └── icon-256x256.png                   # Icône plugin HD
│
├── includes/
│   ├── activation.php                     # Création/mise à jour des pages WP à l'activation
│   ├── rewrite.php                        # Rewrite rules (fallback) + template_redirect corpus
│   ├── shortcodes.php                     # Shortcodes [elaia_faq] et [elaia_metadatas]
│   ├── shortcodes-corpus.php              # Shortcode [elaia_corpus] (séparé car logique spécifique)
│   ├── sitemap.php                        # Intégration sitemap (Yoast, RankMath, SEOPress, natif WP)
│   ├── enqueues.php                       # Enregistrement des styles/scripts front
│   ├── upgrade.php                        # Logique de migration entre versions
│   │
│   ├── Pages/
│   │   ├── Faq.php                        # elaia_prepare_faq_payload() — Appel API + normalisation FAQ
│   │   ├── Metadata.php                   # elaia_prepare_metadata_payload() — Appel API + normalisation métadonnées
│   │   └── Corpus.php                     # elaia_prepare_corpus_payload() — Appel API + normalisation corpus
│   │
│   ├── Shortcodes/
│   │   └── ElaiaCorpusShortcode.php       # Classe shortcode corpus (enregistrement + rendu)
│   │
│   └── Utils/
│       ├── ElaiaPagesMethods.php          # detect_domain(), detect_referer()
│       ├── ElaiaUpdateChecker.php         # Auto-update du plugin depuis le repo
│       └── ElaiaChatbotSitemapProvider.php # Provider sitemap natif WP (désactivé, les vraies pages suffisent)
│
├── templates/
│   ├── elaia-faq.php                      # Template WP pour page virtuelle FAQ (get_header + get_footer)
│   ├── elaia-metadata.php                 # Template WP pour page virtuelle métadonnées (get_header + get_footer)
│   └── elaia-corpus.php                   # Template WP pour corpus (HTML nu, SANS header/footer)
│
└── views/
    ├── faq.php                            # Vue HTML/CSS/JS — Page FAQ (Schema.org FAQPage)
    ├── metadata.php                       # Vue HTML/CSS/JS — Page métadonnées (carte Leaflet, filtres, cards, modal)
    └── corpus.php                         # Vue HTML/CSS/JS — Page corpus (app mobile-like, navbar, chatbot iframe)
```

---

## 3. Flux de données

### 3.1. Activation du plugin (`activation.php`)

```
1. Appel API : GET /api/v1/has-my-elaia?domain={host}
2. Réponse : { has_subscription: bool, domains: [...] }
   (avec ou sans wrapper "data" — le plugin gère les deux formats)
3. Selon la réponse :
   ├─ domains non vide + has_subscription → Crée FAQ + Metadatas + Corpus (sous chaque path)
   ├─ domains non vide + !has_subscription → Crée FAQ + Metadatas seulement (sous chaque path)
   └─ domains vide → Crée FAQ + Metadatas à la racine, dépublie Corpus
```

### 3.2. Rendu d'une page (ex: `/international/le-camping/elaia-glossary`)

```
1. WordPress résout l'URL → trouve la vraie page WP (enfant de "le-camping")
2. Le thème rend la page → exécute the_content() → shortcode [elaia_faq domain="www.campasun.eu/international/le-camping"]
3. Le shortcode set la global $elaia_faq_domain = "www.campasun.eu/international/le-camping"
4. Faq.php utilise ce domaine (priorité) ou detect_domain() (fallback)
5. Appel API : POST /api/v1/chatbot/corpus { domain: "www.campasun.eu/international/le-camping" }
6. L'API construit les candidats ["www.campasun.eu/international/le-camping", "www.campasun.eu/international", "www.campasun.eu"]
7. L'API retourne les données du chatbot le plus spécifique trouvé
8. La vue faq.php/metadata.php affiche le contenu
```

### 3.3. Rendu du corpus (`/my-elaia-plugin`) — SANS header/footer

```
1. WordPress résout l'URL → trouve la vraie page WP avec [elaia_corpus]
2. Le hook template_redirect (priorité 5) intercepte AVANT le thème
3. Vérifie que $post contient [elaia_corpus]
4. Si vrai visiteur (pas un bot WordPress interne) → rendu HTML complet + exit
5. Si crawl interne (Yoast sitemap, etc.) → laisse passer, le shortcode fait un return normal
```

---

## 4. API Endpoints (Laravel — `app.ela-ia.com`)

### 4.1. Résolution des domaines

```
GET /api/v1/has-my-elaia?domain={host}
```

- **Rôle** : Retourne tous les domaines/paths rattachés à un host WordPress, et si le client a l'abonnement My Elaia
- **Sécurité** : Vérification du header Referer (host uniquement)
- **Format de réponse** : L'API retourne actuellement sans wrapper `data`. Le plugin gère les deux formats via fallback (`$body['data']['key'] ?? $body['key']`).
  ```json
  {
    "has_subscription": true,
    "domains": [
      { "domain": "www.campasun.eu", "path": "", "name": "Campasun" },
      { "domain": "www.campasun.eu/international/le-camping", "path": "international/le-camping", "name": "Campasun International" }
    ]
  }
  ```
- **Logique abonnement** : `has_subscription` est `true` si au moins un client sur ce domaine est dans la liste des IDs My Elaia (hardcodée pour l'instant, à migrer vers un champ en base)

### 4.2. FAQ / Corpus

```
POST /api/v1/chatbot/corpus { domain: "..." }
```

- **Rôle** : Retourne les questions FAQ du chatbot au format Schema.org FAQPage
- **Résolution** : Système de candidats (du plus spécifique au plus générique) — même logique que `integration()`
- **Cache** : Transient WP côté plugin (30 min)

### 4.3. Métadonnées géo

```
POST /api/v1/chatbot/metadatas { domain: "..." }
```

- **Rôle** : Retourne les métadonnées géographiques (hébergements, activités, villes, etc.)
- **Cache** : Transient WP côté plugin (30 min) + Cache Laravel (30 min)

### 4.4. Intégration chatbot

```
POST /api/elaiaapp/chatbots/integration { domain, lang, page_url, admin_url }
```

- **Rôle** : Retourne le HTML du widget chatbot pour injection dans la page
- **Résolution** : Système de candidats basé sur `page_url` (path complet de la page visitée)
- **Référence** : Cette méthode est le modèle de résolution de domaine. Toute nouvelle méthode doit suivre le même pattern.

---

## 5. Système de résolution de domaine (candidats)

### Pattern standard (à reproduire dans toute nouvelle méthode API)

```php
// Entrée : "www.campasun.eu/international/le-camping"
$domain_host = explode('/', $domain, 2)[0];  // "www.campasun.eu"
$path = explode('/', $domain, 2)[1] ?? '';     // "international/le-camping"

// Construction des candidats du plus spécifique au plus générique
$path_segments = array_filter(explode('/', trim($path, '/')));
$candidates = [];
while (count($path_segments) > 0) {
    $candidates[] = $domain_host . '/' . implode('/', $path_segments);
    array_pop($path_segments);
}
$candidates[] = $domain_host;

// Résultat : ["www.campasun.eu/international/le-camping", "www.campasun.eu/international", "www.campasun.eu"]
```

### Query Eloquent standard

```php
$chatbot = WebsiteChatbot::with(['settings'])
    ->where(function ($query) use ($candidates): void {
        $query->whereIn('domain', $candidates)
            ->orWhere(function ($q) use ($candidates): void {
                foreach ($candidates as $candidate) {
                    $q->orWhereJsonContains('other_domains', $candidate);
                }
            });
    })
    ->get()
    ->sortBy(function ($bot) use ($candidates) {
        $domainIndex = array_search($bot->domain, $candidates);
        if ($domainIndex !== false) return $domainIndex;
        $otherDomains = $bot->other_domains ?? [];
        $bestIndex = PHP_INT_MAX;
        foreach ($candidates as $index => $candidate) {
            if (in_array($candidate, $otherDomains)) {
                $bestIndex = min($bestIndex, $index);
            }
        }
        return $bestIndex;
    })
    ->first();
```

### Vérification Referer (standard)

```php
// Toujours comparer sur le HOST uniquement (pas le path)
$domain_host = explode('/', $domain, 2)[0];
$referer_host = parse_url($referer, PHP_URL_HOST);
if ($domain_host !== $referer_host) { /* 403 */ }
```

---

## 6. Cache

### Côté WordPress (Transients)

| Clé                           | TTL     | Contenu                          | Invalidation              |
|-------------------------------|---------|----------------------------------|---------------------------|
| `elaia_myelaia_domains`       | 1h      | `{has_subscription, domains[]}` | Réactivation plugin       |
| `elaia_faq_{md5(domain)}_data`| 30 min  | Payload API FAQ                  | `?elaia_nocache=1` ou `?elaia_clear_cache=1` (admin) |
| `elaia_metadatas_{md5(domain)}_data` | 30 min | Payload API métadonnées | Idem |
| `elaia_corpus_{md5(domain)}_data` | 30 min | Payload API corpus          | Idem |

**Règle de cache pour `elaia_myelaia_domains`** : On cache uniquement si l'API répond correctement (HTTP 200). Si l'API est down, pas de cache → retry à chaque page load.

**Format de cache pour `elaia_myelaia_domains`** : Le transient stocke un tableau associatif `['domains' => [...], 'has_subscription' => bool]`. En cas d'erreur API, la fonction retourne `['domains' => [], 'has_subscription' => false]` sans cacher (pour permettre le retry).

### Côté Laravel (Cache)

| Clé                           | TTL     | Invalidation           |
|-------------------------------|---------|------------------------|
| `metadatas_chatbot_{id}`      | 30 min  | `?elaia_nocache=1`     |

---

## 7. Shortcodes

### Signature

```php
[elaia_faq domain="www.campasun.eu/international/le-camping"]
[elaia_metadatas domain="www.campasun.eu/international/le-camping"]
[elaia_corpus domain="www.campasun.eu/international/le-camping"]
```

### Mécanisme de passage du domaine

1. `activation.php` crée les pages avec l'attribut `domain` dans le shortcode
2. Le shortcode extrait `domain` via `shortcode_atts()`
3. La valeur est passée via une `global` (`$elaia_faq_domain`, `$elaia_metadatas_domain`, `$elaia_corpus_domain`)
4. Le fichier PHP de la page utilise la global en priorité, fallback sur `ElaiaPagesMethods::detect_domain()`

**Attention** : `detect_domain()` retourne uniquement le host (`www.campasun.eu`), jamais le path. C'est pourquoi le passage via shortcode est indispensable pour le mode groupe.

---

## 8. Sitemap

Les pages Elaia sont de **vraies pages WordPress**. Elles apparaissent automatiquement dans les sitemaps de Yoast, RankMath, SEOPress et du sitemap natif WP.

Le fichier `sitemap.php` :
- **Désactive** le provider custom `ElaiaChatbotSitemapProvider` (qui créait des doublons)
- **N'ajoute plus** manuellement les URLs dans les sitemaps des plugins SEO

Les pages enfants (mode groupe) apparaissent aussi automatiquement.

---

## 9. Rewrite Rules

Le fichier `rewrite.php` gère trois choses :

### 9.1. Fallback pages virtuelles (rewrite rules)

Pour les sites qui n'ont pas encore de vraies pages WP (premier chargement avant activation) :

```php
add_rewrite_rule(
    '^(elaia-glossary|elaia-metadatas|my-elaia-plugin)/?$',
    'index.php?elaia_virtual_page=$matches[1]',
    'top'
);
```

**Uniquement à la racine** — les sous-chemins sont gérés par les vraies pages WP.

Le `parse_request` empêche le conflit entre rewrite rule et vraie page WP : si une page publiée existe pour le slug, la query var `elaia_virtual_page` est supprimée et remplacée par `pagename` pour que WP affiche la vraie page.

### 9.2. Redirections 301 legacy

Les anciennes URLs basées sur les query params (`?elaia_faq=1`, `?elaia_metadata=1`) sont redirigées en 301 vers les nouvelles URLs propres.

### 9.3. Template redirect pour le corpus

Le corpus (`my-elaia-plugin`) doit s'afficher SANS header/footer du thème. Le hook `template_redirect` (priorité 5) intercepte avant le rendu du thème et fait un rendu HTML complet + `exit`.

**Exception** : Les crawlers internes (Yoast, etc.) sont laissés passer pour que le sitemap fonctionne.

### ⚠️ Pas de `flush_rewrite_rules()`

Le plugin **n'appelle jamais** `flush_rewrite_rules()`. Cette fonction réécrit le `.htaccess` en entier et peut casser des règles custom d'autres plugins ou des configurations serveur spécifiques (erreurs 500). Les pages Elaia sont de vraies pages WP — elles n'ont pas besoin de rewrite rules pour fonctionner. Si les rewrite rules fallback doivent être rafraîchies, l'admin peut aller dans Réglages → Permaliens → Enregistrer.

---

## 10. Conventions de nommage

### PHP

- Variables : `$camelCase` (`$primaryColor`, `$itemIndex`, `$categoryInfo`)
- Fonctions : `snake_case` avec préfixe `elaia_` (`elaia_create_or_update_page()`, `elaia_get_myelaia_domains()`)
- Constantes : `UPPER_SNAKE` avec préfixe `ELAIA_` (`ELAIA_PAGE_FAQ_REWRITE`)
- Globals shortcode : `$elaia_{type}_domain` (`$elaia_faq_domain`, `$elaia_corpus_domain`)

### CSS

- Préfixe `em-` pour les métadonnées (`em-card`, `em-map`, `em-modal`)
- Préfixe `ec-` pour le corpus (`ec-navbar`, `ec-sheet`, `ec-card`)
- BEM simplifié : `.em-card-title`, `.em-card--highlight`, `.em-modal-close-btn`

### JavaScript

- Variables : `camelCase` (`activeCategoryFilter`, `searchQuery`, `gpsPoints`)
- Fonctions : `camelCase` (`applyFilters()`, `openDetailModal()`, `createMarkerIcon()`)
- Constantes : `UPPER_SNAKE` (`FIELD_LABELS`, `EXCLUDED_KEYS`)
- IIFE pour isoler le scope : `(function() { 'use strict'; ... })();`

---

## 11. Règles pour les agents IA

### À faire

- **Toujours utiliser le système de candidats** pour résoudre un domaine côté API (voir section 5)
- **Toujours comparer le referer sur le host uniquement**, jamais sur le domaine complet avec path
- **Toujours passer le domaine via l'attribut shortcode** pour les pages en mode groupe
- **Toujours cacher uniquement sur succès API** (HTTP 200) — pas de cache si erreur
- **Toujours retourner un format cohérent** dans `elaia_get_myelaia_domains()` : `['domains' => [], 'has_subscription' => false]`
- **Toujours gérer les deux formats de réponse API** (avec ou sans wrapper `data`)
- **Toujours préfixer** les fonctions PHP avec `elaia_`, les classes CSS avec `em-` ou `ec-`
- **Toujours tester** sur un site simple (ex: `ela-ia.com`) ET un site en mode groupe (ex: `campasun.eu`)

### À ne pas faire

- **Ne JAMAIS appeler `flush_rewrite_rules()`** — ça réécrit le `.htaccess` et peut casser des sites (erreurs 500). Les vraies pages WP n'en ont pas besoin.
- **Ne pas utiliser `detect_domain()`** comme source unique de domaine pour les appels API — toujours vérifier la global du shortcode d'abord
- **Ne pas appeler `exit`** dans un shortcode — ça casse le sitemap et les crawlers
- **Ne pas cacher les erreurs API** — un tableau vide valide est différent d'une erreur réseau
- **Ne pas ajouter manuellement** les pages Elaia dans les sitemaps — les vraies pages WP y apparaissent automatiquement
- **Ne pas supprimer le `www.`** lors des comparaisons de domaines — on garde le domaine tel quel
- **Ne pas hardcoder** la liste des IDs clients My Elaia dans le plugin WP — c'est l'API qui sait
- **Ne pas utiliser de regex trop large** dans les rewrite rules (pas de `(?:.+/)?`) — les pages enfants sont gérées par WP nativement

### Pièges connus

1. **Double rendu** : Si une rewrite rule et une vraie page WP coexistent pour le même slug, le template virtuel et le shortcode s'exécutent tous les deux → crash. Le `parse_request` dans `rewrite.php` prévient ce cas en redirigeant vers `pagename`.

2. **Thème FSE (block theme)** : Les thèmes Full Site Editing injectent le header/footer via `wp_head()`/`wp_footer()` et les block templates, pas via `get_header()`/`get_footer()`. Un simple template custom ne suffit pas → il faut `template_redirect` + `exit`.

3. **Cache transient et domaine** : La clé de cache inclut un `md5()` du domaine. Si le même chatbot est accessible via `campasun.eu` et `www.campasun.eu`, ce sont deux caches différents. Le domaine doit être cohérent partout.

4. **`include_once` vs `include`** : Les fichiers `Faq.php`, `Metadata.php`, `Corpus.php` utilisent `include_once` pour la définition de fonction. Si le même fichier est inclus par le shortcode ET par le template, la fonction n'est définie qu'une fois — c'est voulu.

5. **`.htaccess` et `flush_rewrite_rules()`** : Certains sites ont des règles custom dans le `.htaccess` (RewriteBase modifié, règles d'autres plugins). `flush_rewrite_rules()` réécrit tout le fichier et écrase ces règles → erreurs 500. Ne jamais appeler cette fonction dans le plugin.

6. **Format de réponse API** : L'API `has-my-elaia` retourne sans wrapper `data`, contrairement à la convention documentée. Le plugin gère les deux formats via fallback, mais si l'API est modifiée, garder la compatibilité.

---

## 12. Déploiement

### Fichiers à exclure du build de production

```
ARCHITECTURE.md
.git/
.github/
tests/
*.md (sauf README.md si public)
```

### Procédure de mise à jour des pages clients

1. Modifier l'API si nécessaire (nouveau endpoint, nouveau champ)
2. Mettre à jour `activation.php` si la logique de création de pages change
3. Déployer le plugin
4. Les clients doivent **désactiver puis réactiver** le plugin pour que `activation.php` s'exécute
5. Alternative : ajouter un hook `admin_init` ou un WP-Cron pour la synchro périodique (à implémenter)

### ⚠️ TODO

- [ ] Migrer la liste des IDs clients My Elaia vers un champ en base (`has_myelaia_subscription` sur le modèle `Client` ou via l'offre)
- [ ] Ajouter un WP-Cron ou un hook `admin_init` pour la synchro périodique des pages (sans réactivation manuelle)
- [ ] Standardiser le format de réponse API `has-my-elaia` avec le wrapper `data` (aligner sur les autres endpoints)