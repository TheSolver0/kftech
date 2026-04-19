<?php
// includes/header.php
// Variables attendues : $pageTitle, $pageDesc, $activeCat
if (!isset($pageTitle)) $pageTitle = 'KF Tech - Boutique Informatique';
if (!isset($pageDesc))  $pageDesc  = 'KF Tech, votre boutique informatique à Douala.';

require_once __DIR__ . '/../config/api.php';

// Récupérer catégories depuis l'API
$cats = apiGet('categories');
if (!is_array($cats)) $cats = [];

// Vérifier si utilisateur connecté (via session ou cookie local)
$user = null;
if (isset($_SESSION['user_id'])) {
    $user = [
        'id' => $_SESSION['user_id'],
        'prenom' => $_SESSION['user_prenom'] ?? 'Utilisateur',
        'nom' => $_SESSION['user_nom'] ?? ''
    ];
} elseif (!empty($_COOKIE['kftech_user'])) {
    $localUser = json_decode($_COOKIE['kftech_user'], true);
    if (is_array($localUser) && !empty($localUser['id'])) {
        $user = [
            'id' => $localUser['id'],
            'prenom' => $localUser['prenom'] ?? 'Utilisateur',
            'nom' => $localUser['nom'] ?? ''
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="description" content="<?= htmlspecialchars($pageDesc) ?>"/>
  <meta name="keywords" content="KF Tech, électronique, laptop, smartphone, tablette, informatique, Douala"/>
  <meta name="author" content="KF Tech SARL"/>
  <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1"/>
  <meta name="theme-color" content="#FF6B35"/>
  <title><?= htmlspecialchars($pageTitle) ?></title>
  
  <!-- Favicon pour Google et navigateurs -->
  <link rel="icon" type="image/png" href="assets/images/logo.png" />
  <link rel="shortcut icon" type="image/png" href="assets/images/logo.png" />
  <link rel="apple-touch-icon" href="assets/images/logo.png" />
  
  <!-- Web App Manifest -->
  <link rel="manifest" href="manifest.json" />
  
  <!-- Open Graph pour partage social -->
  <meta property="og:type" content="website" />
  <meta property="og:url" content="https://kftech237.com" />
  <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>" />
  <meta property="og:description" content="<?= htmlspecialchars($pageDesc) ?>" />
  <meta property="og:image" content="https://kftech237.com/assets/images/logo.png" />
  
  <!-- Twitter Card -->
  <meta property="twitter:card" content="summary_large_image" />
  <meta property="twitter:title" content="<?= htmlspecialchars($pageTitle) ?>" />
  <meta property="twitter:description" content="<?= htmlspecialchars($pageDesc) ?>" />
  <meta property="twitter:image" content="https://kftech237.com/assets/images/logo.png" />
  
  <!-- Schema.org JSON-LD -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "LocalBusiness",
    "name": "KF Tech - Boutique Informatique Douala",
    "url": "https://kftech237.com",
    "logo": "https://kftech237.com/assets/images/logo.png",
    "image": "https://kftech237.com/assets/images/logo.png",
    "description": "<?= htmlspecialchars($pageDesc) ?>",
    "telephone": "+237651271617",
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "Douala, Rond point Deido",
      "addressLocality": "Douala",
      "addressCountry": "CM"
    },
    "contactPoint": {
      "@type": "ContactPoint",
      "contactType": "Customer Support",
      "telephone": "+237651271617",
      "url": "https://wa.me/237651271617"
    },
    "sameAs": [
      "https://www.facebook.com/share/1DHfKAHDLW/",
      "https://www.tiktok.com/@kf.tech.sarl?_r=1&_t=ZS-95awLw1bF7n",
      "https://www.instagram.com/kftechsarl?igsh=MWY3NGFrMnRweTIzZg=="
    ]
  }
  </script>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700;800&family=Barlow+Condensed:wght@700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="assets/css/style.css"/>
  <?php if (isset($extraCss)) echo $extraCss; ?>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
  <div class="container topbar-inner">
    <div class="topbar-left">
      <span><i class="fas fa-map-marker-alt"></i> Douala, Rond point Deido</span>
      <span><i class="fas fa-truck"></i> Suivre ma commande</span>
      <span><i class="fas fa-phone"></i>  +237 6 51 27 16 17</span>
      <span class="topbar-msg"><i class="fas fa-heart"></i> Bienvenue chez KF-Tech. Les meilleurs produits électroniques !</span>
    </div>
    <div class="topbar-right">
      <span><i class="fas fa-calendar-alt"></i> <?= date('d F, Y') ?></span>
      <a href="https://www.facebook.com/share/1DHfKAHDLW/" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f"></i></a>
      <a href="https://www.tiktok.com/@kf.tech.sarl?_r=1&_t=ZS-95awLw1bF7n" target="_blank" rel="noopener noreferrer"><i class="fab fa-tiktok"></i></a>
      <a href="https://www.instagram.com/kftechsarl?igsh=MWY3NGFrMnRweTIzZg==" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i></a>
    </div>
  </div>
</div>

<!-- HEADER -->
<header class="header" id="mainHeader">
  <div class="container header-inner">
    <a href="index.php" class="logo">
      <img src="assets/images/logo.png" alt="KF Tech" class="logo-img"
           onerror="this.style.display='none'"/>
      <span class="logo-kf">KF</span><span class="logo-tech">TECH</span>
    </a>
    <div class="search-bar">
      <div class="search-category">
        <select id="searchCatSelect">
          <option value="">Toutes les Catégories</option>
          <?php foreach ($cats as $c): ?>
            <option value="<?= $c['slug'] ?>"
              <?= (isset($activeCat) && $activeCat === $c['slug']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['nom']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <input type="text" id="searchInput" placeholder="Rechercher des produits..."
             value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"/>
      <button class="btn-search" id="searchBtn"><i class="fas fa-search"></i></button>
    </div>
    <div class="header-actions">
      <div class="action-item currency">XAF <i class="fas fa-chevron-down"></i></div>
      <div class="action-item lang">Français <i class="fas fa-chevron-down"></i></div>
      <div class="user-dropdown-wrapper">
        <button class="action-icon user-dropdown-btn" id="userDropdownBtn"
                title="<?= $user ? htmlspecialchars($user['prenom'].' '.$user['nom']) : 'Connexion' ?>">
          <i class="fas fa-user-circle"></i>
        </button>
        <div class="user-dropdown-menu" id="userDropdownMenu">
          <?php if ($user): ?>
            <div class="dropdown-header">
              <strong><?= htmlspecialchars($user['prenom'].' '.$user['nom']) ?></strong>
            </div>
            <a href="compte.php" class="dropdown-item">
              <i class="fas fa-user"></i> Mon Compte
            </a>
            <a href="api/auth.php?action=deconnexion" class="dropdown-item">
              <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
          <?php else: ?>
            <a href="login.php" class="dropdown-item">
              <i class="fas fa-sign-in-alt"></i> Connexion
            </a>
            <a href="login.php?mode=signup" class="dropdown-item">
              <i class="fas fa-user-plus"></i> Créer un Compte
            </a>
          <?php endif; ?>
        </div>
      </div>
      <a href="#" class="action-icon">
        <i class="fas fa-heart"></i><span class="badge" id="wishBadge">0</span>
      </a>
      <a href="#" class="action-icon" id="cartBtn">
        <i class="fas fa-shopping-cart"></i><span class="badge" id="cartBadge">0</span>
      </a>
    </div>
  </div>
</header>

<!-- NAVBAR -->
<nav class="navbar" id="mainNav">
  <!-- NAVBAR MOBILE (visible sur mobile, caché sur desktop) -->
  <div class="navbar-mobile">
    <button class="nav-menu-btn"><i class="fas fa-bars"></i></button>
    <a href="index.php" class="navbar-mobile-logo">
      <img src="assets/images/logo.png" alt="KF Tech" class="navbar-mobile-brand" />
      <span class="logo-kf">KF</span><span class="logo-tech">TECH</span>
    </a>
    <div class="navbar-mobile-actions">
      <div class="user-dropdown-wrapper-mobile">
        <button class="nav-action-icon user-dropdown-btn-mobile" id="userDropdownBtnMobile">
          <i class="fas fa-circle-user"></i>
        </button>
        <div class="user-dropdown-menu-mobile" id="userDropdownMenuMobile">
          <?php if ($user): ?>
            <div class="dropdown-header">
              <strong><?= htmlspecialchars($user['prenom'].' '.$user['nom']) ?></strong>
            </div>
            <a href="compte.php" class="dropdown-item">
              <i class="fas fa-user"></i> Mon Compte
            </a>
            <a href="api/auth.php?action=deconnexion" class="dropdown-item">
              <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
          <?php else: ?>
            <a href="login.php" class="dropdown-item">
              <i class="fas fa-sign-in-alt"></i> Connexion
            </a>
            <a href="login.php?mode=signup" class="dropdown-item">
              <i class="fas fa-user-plus"></i> Créer un Compte
            </a>
          <?php endif; ?>
        </div>
      </div>
      <a href="#" class="nav-action-icon" id="wishBtnMobile">
        <i class="fas fa-heart"></i>
      </a>
      <a href="#" class="nav-action-icon" id="cartBtnMobile">
        <i class="fas fa-shopping-cart"></i><span class="nav-badge" id="cartBadgeMobile">0</span>
      </a>
    </div>
  </div>

  <!-- NAVBAR DESKTOP (caché sur mobile, visible sur desktop) -->
  <div class="navbar-desktop">
    <div class="container nav-inner">
      <div class="nav-left">
        <ul class="nav-links">
          <li>
            <a href="index.php">
              <i class="fas fa-home"></i> Accueil
            </a>
          </li>
          <?php foreach ($cats as $c): ?>
            <li>
              <a href="catalog.php?cat=<?= urlencode($c['slug']) ?>"
                 class="<?= (isset($activeCat) && $activeCat === $c['slug']) ? 'active-nav' : '' ?>"
                 title="<?= htmlspecialchars($c['nom']) ?>">
                <i class="<?= categoryIconClass($c) ?>"></i> <?= htmlspecialchars($c['nom']) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
    <!-- Burger menu caché (on le garde pour le JS mais on le montre au mobile) -->
    <button class="nav-menu-btn" style="display:none;"><i class="fas fa-bars"></i></button>
  </div>

  <!-- MENU MOBILE DÉROULANT -->
  <ul class="nav-links-mobile" id="navLinksMobile">
    <li>
      <a href="index.php">
        <i class="fas fa-home"></i> Accueil
      </a>
    </li>
    <?php foreach ($cats as $c): ?>
      <li>
        <a href="catalog.php?cat=<?= urlencode($c['slug']) ?>"
           class="<?= (isset($activeCat) && $activeCat === $c['slug']) ? 'active-nav' : '' ?>"
           title="<?= htmlspecialchars($c['nom']) ?>">
          <i class="<?= categoryIconClass($c) ?>"></i> <?= htmlspecialchars($c['nom']) ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</nav>

<!-- MOBILE SEARCH BAR (visible only on mobile) -->
<div class="mobile-search">
  <div class="container">
    <div class="search-bar">
      <div class="search-category">
        <select id="searchCatSelectMobile">
          <option value="">Toutes les Catégories</option>
          <?php foreach ($cats as $c): ?>
            <option value="<?= $c['slug'] ?>"
              <?= (isset($activeCat) && $activeCat === $c['slug']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['nom']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <input type="text" id="searchInputMobile" placeholder="Rechercher des produits..."
             value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"/>
      <button class="btn-search" id="searchBtnMobile"><i class="fas fa-search"></i></button>
    </div>
  </div>
</div>
<div class="cart-overlay" id="cartOverlay"></div>
<div class="cart-drawer" id="cartDrawer">
  <div class="drawer-header">
    <h3>Mon Panier <span id="cartCount">(0)</span></h3>
    <button id="closeCart"><i class="fas fa-times"></i></button>
  </div>
  <div class="drawer-items" id="cartItems">
    <p class="empty">Votre panier est vide.</p>
  </div>
  <div class="drawer-footer">
    <div class="total-row">Total : <strong id="cartTotal">0 XAF</strong></div>
    <div style="display:grid;gap:10px">
      <button id="btnCommander" class="btn-primary w100"
              style="width:100%;text-align:center;display:flex;align-items:center;justify-content:center;gap:8px;height:48px;border:none;cursor:pointer;font-family:Barlow,sans-serif;font-size:15px;font-weight:700">
        <i class="fab fa-whatsapp"></i> Commander via WhatsApp
      </button>
    </div>
  </div>
</div>

<a href="#" class="back-top" id="backTop"><i class="fas fa-arrow-up"></i></a>
