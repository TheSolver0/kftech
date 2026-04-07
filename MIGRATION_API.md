# Migration vers Architecture Client-API

## 📋 Résumé des changements

Le site KF Tech a été complètement migré vers une architecture **client uniquement**, sans communication directe avec la base de données. Toutes les requêtes passent maintenant par l'API (http://localhost:5273/api/).

---

## 🔑 Point clé : Une seule constante APIURL

**Fichier centralisé:** [`config/api.php`](config/api.php)

```php
define('APIURL', 'http://localhost:5273/api/');
```

**C'est la SEULE ligne à modifier** pour changer l'URL de l'API (ex: en production).

---

## 📁 Fichiers modifiés

### Pages principales (client)

| Fichier | Changements |
|---------|----------|
| [`index.php`](index.php) | ✅ Remplace `require_once db.php` par `require_once config/api.php`<br>✅ Hero slides via `/api/hero` au lieu de requête SQL |
| [`catalog.php`](catalog.php) | ✅ Import de `config/api.php` centralisé<br>✅ Suppression du code `define('API_BASE', ...)`<br>✅ Catégories via API |
| [`product.php`](product.php) | ✅ Import de `config/api.php` centralisé<br>✅ Suppression des helper API locaux |
| [`favoris.php`](favoris.php) | ✅ Import de `config/api.php` centralisé<br>✅ Favoris récupérés via `/api/favoris?user_id=...` |
| [`includes/header.php`](includes/header.php) | ✅ Catégories via API au lieu de SQL<br>✅ Infos utilisateur depuis `$_SESSION` |

### Configuration

| Fichier | Changements |
|---------|----------|
| [`config/api.php`](config/api.php) | 🆕 **NOUVEAU FILE** - Contient `APIURL` et helpers (`apiGet()`, `apiPost()`, `jsonResponse()`) |
| [`config/db.php`](config/db.php) | 🔄 **Allégé** - Suppression de `getDB()`, `PDO`, helpers de session basiques seulement |

### API locale (proxy)

| Fichier | Changements |
|---------|----------|
| [`api/produits.php`](api/produits.php) | ✅ Import de `config/api.php` centralisé<br>✅ Utilise `APIURL` au lieu de `API_BASE` |
| [`api/avis.php`](api/avis.php) | ✅ Import de `config/api.php` centralisé |
| [`api/favoris.php`](api/favoris.php) | ✅ Import de `config/api.php` centralisé |
| [`api/auth.php`](api/auth.php) | ✅ Import de `config/api.php` centralisé |
| [`api/newsletter.php`](api/newsletter.php) | ✅ Import de `config/api.php` centralisé |

---

## 🚀 Utilisation

### Pour développeurs

1. **Modifier l'URL de l'API** → Allez dans [`config/api.php`](config/api.php) et changez `APIURL`
2. **Ajouter un nouvel appel API** → Utilisez simplement `apiGet()` ou `apiPost()`

### Exemple

```php
<?php
require_once __DIR__ . '/config/api.php';

// Récupérer les produits
$data = apiGet('/products?cat=smartphones');

// Ou avec POST
$response = apiPost('/auth/login', [
    'email' => 'user@example.com',
    'password' => 'secret'
]);
?>
```

---

## ✅ Bénéfices

- ✨ **Centralisé** : Une seule URL API à gérer
- 🔒 **Plus sûr** : Pas d'accès direct à la BD depuis le frontend
- 🚀 **Scalable** : Facile de changer l'API ou l'héberger ailleurs
- 📦 **Découplé** : Le client et l'API peuvent évolver indépendamment
- 🔄 **Maintenable** : Les helpers API sont centralisés

---

## ⚠️ Notes importantes

### Base de données locale

- Les fichiers API (`api/*.php`) **peuvent toujours** communiquer avec la BD locale si nécessaire
- Exemple : `api/auth.php` valide les credentials en interrogeant la BD
- L'important : Le **client web** (index.php, catalog.php, etc.) n'a **PLUS** d'accès direct à la BD

### Session utilisateur

- La session PHP continue à stocker les infos utilisateur dans `$_SESSION`
- Exemple : `$_SESSION['user_id']`, `$_SESSION['user_prenom']`, etc.
- Les helpers de session sont dans [`config/db.php`](config/db.php)

---

## 📊 Architecture

```
Client Web (index.php, catalog.php, etc.)
    ↓
    ├─→ config/api.php (APIURL constant + helpers)
    ↓
    ├─→ API .NET (http://localhost:5273/api/)
    │
    └─→ [API locale (api/*.php) si nécessaire]
            ↓
            └─→ Base de données MySQL
```

---

## 🔄 Migration checklist

- ✅ Constante `APIURL` centralisée dans `config/api.php`
- ✅ Tous les appels API passent par `apiGet()` / `apiPost()`
- ✅ Pages client utilisent `config/api.php` au lieu de `config/db.php`
- ✅ Catégories récupérées via API
- ✅ Hero slides récupérés via API
- ✅ Produits récupérés via API
- ✅ Favoris récupérés via API
- ✅ Fichiers API proxy mis à jour

---

**Dernier commit:** Avril 2026
