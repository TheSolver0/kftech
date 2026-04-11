<?php
// includes/header.php
// Variables attendues : $pageTitle, $pageDesc, $activeCat
if (!isset($pageTitle)) $pageTitle = 'KF Tech - Boutique Informatique';
if (!isset($pageDesc))  $pageDesc  = 'KF Tech, votre boutique informatique à Douala.';

require_once __DIR__ . '/../config/api.php';

// Récupérer catégories depuis l'API
$cats = apiGet('categories');
if (!is_array($cats)) $cats = [];

// Vérifier si utilisateur connecté (via session)
$user = null;
if (isset($_SESSION['user_id'])) {
    $user = [
        'id' => $_SESSION['user_id'],
        'prenom' => $_SESSION['user_prenom'] ?? 'Utilisateur',
        'nom' => $_SESSION['user_nom'] ?? ''
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="description" content="<?= htmlspecialchars($pageDesc) ?>"/>
  <title><?= htmlspecialchars($pageTitle) ?></title>
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
      <span><i class="fas fa-phone"></i> +237 6 96 85 39 48</span>
      <span class="topbar-msg"><i class="fas fa-heart"></i> Bienvenue chez KF-Tech. Les meilleurs produits électroniques !</span>
    </div>
    <div class="topbar-right">
      <span><i class="fas fa-calendar-alt"></i> <?= date('d F, Y') ?></span>
      <a href="#"><i class="fab fa-facebook-f"></i></a>
      <a href="#"><i class="fab fa-twitter"></i></a>
      <a href="#"><i class="fab fa-instagram"></i></a>
      <a href="#"><i class="fab fa-youtube"></i></a>
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
      <a href="login.php" class="action-icon"
         title="<?= $user ? htmlspecialchars($user['prenom'].' '.$user['nom']) : 'Connexion' ?>">
        <i class="fas fa-user"></i>
      </a>
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
  <div class="container nav-inner">
    <div class="nav-left">
      <button class="nav-menu-btn"><i class="fas fa-bars"></i></button>
      <ul class="nav-links">
          <!-- LIEN ACCUEIL POUR MOBILE -->
        <li>
          <a href="index.php">
            <i class="fas fa-home"></i> Accueil
          </a>
        </li>
        <li class="divider">|</li>
        <!-- CATEGORIES -->
        <?php foreach ($cats as $i => $c): ?>
          <li>
            <a href="catalog.php?cat=<?= $c['slug'] ?>"
               class="<?= (isset($activeCat) && $activeCat===$c['slug']) ? 'active-nav' : '' ?>">
              <i class="<?= $c['icone'] ?>"></i> <?= htmlspecialchars($c['nom']) ?>
            </a>
          </li>
          <?php if ($i < count($cats)-1): ?>
            <li class="divider">|</li>
          <?php endif; ?>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php if ($user): ?>
      <div style="display:flex;align-items:center;gap:10px">
        <span style="color:#ccc;font-size:13px">
          Bonjour, <strong style="color:var(--orange)"><?= htmlspecialchars($user['prenom']) ?></strong>
        </span>
        <a href="api/auth.php?action=deconnexion" class="btn-connexion" style="background:#555">
          Déconnexion
        </a>
      </div>
    <?php else: ?>
      <a href="login.php" class="btn-connexion">Connexion</a>
    <?php endif; ?>
  </div>
</nav>

<!-- CART DRAWER -->
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
    <button id="btnCommander" class="btn-primary w100"
            style="width:100%;text-align:center;display:flex;align-items:center;justify-content:center;gap:8px;height:48px;border:none;cursor:pointer;font-family:Barlow,sans-serif;font-size:15px;font-weight:700">
      <i class="fab fa-whatsapp"></i> Commander via WhatsApp
    </button>
  </div>
</div>

<a href="#" class="back-top" id="backTop"><i class="fas fa-arrow-up"></i></a>
