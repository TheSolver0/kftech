<?php
session_start();
require_once __DIR__ . '/config/api.php';
require_once __DIR__ . '/config/db.php';

$user = getCurrentUser();
if (!$user) {
    header('Location: login.php?redirect=favoris&msg=favoris');
    exit;
}

$userId = $user['id'];
$cookieKey = 'kftech_favoris_' . $userId;
$favorisIds = [];
if (!empty($_COOKIE[$cookieKey])) {
    $ids = json_decode($_COOKIE[$cookieKey], true);
    if (is_array($ids)) {
        $favorisIds = array_values(array_unique(array_filter($ids, function($item) {
            return $item !== null && $item !== '';
        })));
    }
}

$favoris = [];
foreach ($favorisIds as $id) {
    $product = apiGet('/products/' . urlencode($id));
    if (!empty($product) && empty($product['erreur'])) {
        $favoris[] = $product;
    }
}

function starsHtml(float $n): string {
    $n = (int)round($n);
    return str_repeat('&#9733;', $n) . str_repeat('&#9734;', 5 - $n);
}

$pageTitle = 'Mes Favoris - KF Tech';
$pageDesc  = 'Vos produits favoris KF Tech.';
include __DIR__ . '/includes/header.php';
?>

<style>
.favoris-page { padding: 32px 0 60px; }
.favoris-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; flex-wrap: wrap; gap: 12px; }
.favoris-header h1 { font-family: 'Barlow Condensed', sans-serif; font-size: 32px; font-weight: 800; }
.favoris-header h1 span { color: var(--orange); }
.favoris-count { font-size: 14px; color: #888; margin-top: 4px; }
.favoris-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
.fav-card {
  border: 1px solid var(--border);
  border-radius: 10px;
  background: #fff;
  overflow: hidden;
  transition: box-shadow .25s, transform .25s;
  position: relative;
  cursor: pointer;
}
.fav-card:hover { box-shadow: 0 8px 28px rgba(0,0,0,.1); transform: translateY(-3px); }
.fav-remove {
  position: absolute;
  top: 10px; right: 10px;
  width: 32px; height: 32px;
  background: #fff;
  border: 1px solid #f0f0f0;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  font-size: 14px;
  color: #e63946;
  z-index: 2;
  transition: all .2s;
  box-shadow: 0 2px 6px rgba(0,0,0,.1);
}
.fav-remove:hover { background: #e63946; color: #fff; }
.fav-img { height: 170px; background: var(--light-gray); display: flex; align-items: center; justify-content: center; overflow: hidden; }
.fav-img img { max-width: 90%; max-height: 150px; object-fit: contain; transition: transform .3s; }
.fav-card:hover .fav-img img { transform: scale(1.05); }
.fav-info { padding: 14px; }
.fav-cat { font-size: 11px; color: var(--orange); font-weight: 600; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 5px; }
.fav-name { font-size: 14px; font-weight: 700; color: var(--black); margin-bottom: 8px; line-height: 1.3; }
.fav-stars { color: #f5a623; font-size: 12px; margin-bottom: 8px; }
.fav-prix { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; }
.fav-prix-new { color: var(--orange); font-weight: 800; font-size: 16px; }
.fav-prix-old { color: #bbb; font-size: 13px; text-decoration: line-through; }
.btn-add-fav {
  width: 100%; height: 40px;
  background: var(--orange);
  color: #fff; border: none;
  border-radius: 6px;
  font-size: 13px; font-weight: 700;
  font-family: 'Barlow', sans-serif;
  cursor: pointer;
  transition: background .2s;
}
.btn-add-fav:hover { background: var(--orange-dark); }

/* Vide */
.favoris-empty { text-align: center; padding: 80px 20px; }
.favoris-empty i { font-size: 64px; color: #ddd; display: block; margin-bottom: 20px; }
.favoris-empty h2 { font-size: 24px; font-weight: 800; margin-bottom: 10px; color: #444; }
.favoris-empty p { color: #888; font-size: 15px; margin-bottom: 28px; }

@media (max-width: 900px) { .favoris-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 600px) { .favoris-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; } }
</style>

<div class="favoris-page">
<div class="container">

  <!-- Breadcrumb -->
  <div style="font-size:13px;color:#888;margin-bottom:20px">
    <a href="index.php" style="color:#888;text-decoration:none">Accueil</a> ›
    <strong style="color:var(--black)">Mes Favoris</strong>
  </div>

  <div class="favoris-header">
    <div>
      <h1>Mes <span>Favoris</span> <i class="fas fa-heart" style="color:var(--orange);font-size:26px"></i></h1>
      <p class="favoris-count">
        <?= count($favoris) ?> produit<?= count($favoris) > 1 ? 's' : '' ?> sauvegardé<?= count($favoris) > 1 ? 's' : '' ?>
      </p>
    </div>
    <?php if (!empty($favoris)): ?>
      <a href="catalog.php" class="btn-outline" style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px">
        <i class="fas fa-plus"></i> Ajouter plus de produits
      </a>
    <?php endif; ?>
  </div>

  <?php if (empty($favoris)): ?>
    <div class="favoris-empty">
      <i class="fas fa-heart"></i>
      <h2>Aucun favori pour l'instant</h2>
      <p>Parcourez notre catalogue et cliquez sur le ❤ pour sauvegarder vos produits préférés.</p>
      <a href="catalog.php" class="btn-primary" style="display:inline-block">
        <i class="fas fa-shopping-bag"></i> Découvrir nos produits
      </a>
    </div>

  <?php else: ?>
    <div class="favoris-grid" id="favorisGrid">
      <?php foreach ($favoris as $p):
        $prix = (float)($p['prix'] ?? 0);
        $ancien_prix = (float)($p['ancien_prix'] ?? 0);
        $disc = ($ancien_prix && $ancien_prix > $prix)
                ? round((1 - $prix / $ancien_prix) * 100) : 0;
      ?>
      <div class="fav-card" id="fav-<?= $p['id'] ?>" onclick="window.location='product.php?id=<?= $p['id'] ?>'">

        <!-- Bouton retirer -->
        <button class="fav-remove" data-id="<?= $p['id'] ?>"
                onclick="event.stopPropagation(); retirerFavori(<?= $p['id'] ?>)"
                title="Retirer des favoris">
          <i class="fas fa-heart"></i>
        </button>

        <?php if ($disc): ?>
          <span style="position:absolute;top:10px;left:10px;background:#e63946;color:#fff;font-size:10px;font-weight:700;padding:3px 7px;border-radius:3px;z-index:1">
            -<?= $disc ?>%
          </span>
        <?php endif; ?>

        <div class="fav-img">
          <img src="<?= htmlspecialchars($p['image'] ?? '') ?>"
               alt="<?= htmlspecialchars($p['nom'] ?? '') ?>"
               loading="lazy"
               onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22150%22><rect fill=%22%23f5f5f5%22 width=%22200%22 height=%22150%22/></svg>'"/>
        </div>
        <div class="fav-info">
          <p class="fav-cat"><?= htmlspecialchars($p['categorie_nom'] ?? $p['cat_nom'] ?? $p['categorie'] ?? '') ?></p>
          <p class="fav-name"><?= htmlspecialchars($p['nom'] ?? '') ?></p>
          <div class="fav-stars"><?= starsHtml($p['note'] ?? 0) ?> <span style="color:#aaa;font-size:11px">(<?= $p['nb_avis'] ?? 0 ?>)</span></div>
          <div class="fav-prix">
            <span class="fav-prix-new">XAF <?= number_format($p['prix'] ?? 0, 0, '.', ' ') ?></span>
            <?php if ($p['ancien_prix'] ?? null): ?>
              <span class="fav-prix-old">XAF <?= number_format($p['ancien_prix'], 0, '.', ' ') ?></span>
            <?php endif; ?>
          </div>
          <button class="btn-add-fav btn-add" data-id="<?= $p['id'] ?? '' ?>"
                  onclick="event.stopPropagation()">
            <i class="fas fa-cart-plus"></i> Ajouter au panier
          </button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>
</div>

<script>
function retirerFavori(produitId) {
  fetch('api/favoris.php?action=toggle', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ produit_id: produitId })
  })
  .then(function(r) { return r.json(); })
  .then(function(d) {
    if (d.succes && d.action === 'retire') {
      var card = document.getElementById('fav-' + produitId);
      if (card) {
        card.style.transition = 'opacity .3s, transform .3s';
        card.style.opacity = '0';
        card.style.transform = 'scale(.95)';
        setTimeout(function() {
          card.remove();
          // Mettre à jour le compteur
          var grid = document.getElementById('favorisGrid');
          var remaining = grid ? grid.querySelectorAll('.fav-card').length : 0;
          var countEl = document.querySelector('.favoris-count');
          if (countEl) countEl.textContent = remaining + ' produit' + (remaining > 1 ? 's' : '') + ' sauvegardé' + (remaining > 1 ? 's' : '');
          // Si plus rien, recharger pour afficher le message vide
          if (remaining === 0) window.location.reload();
        }, 300);
      }
      // Mettre à jour le badge favori dans le header
      updateWishBadge();
    }
  });
}

function updateWishBadge() {
  fetch('api/favoris.php?action=ids')
    .then(function(r) { return r.json(); })
    .then(function(d) {
      var badge = document.getElementById('wishBadge');
      if (badge) badge.textContent = d.ids ? d.ids.length : 0;
    });
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
