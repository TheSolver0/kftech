<?php
session_start();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: catalog.php');
    exit;
}

// ============================================================
// HELPER API
// ============================================================
define('API_BASE', 'http://localhost:5273/api/');

function apiGet(string $path): array {
    $ctx = stream_context_create(['http' => [
        'timeout'       => 5,
        'ignore_errors' => true,
    ]]);
    $raw = @file_get_contents(API_BASE . $path, false, $ctx);
    if ($raw === false) return [];
    return json_decode($raw, true) ?? [];
}

// ============================================================
// DONNÉES — un seul appel API remplace les 3 requêtes SQL
// GET /api/ecom/products/{id} retourne :
//   produit + avis + similaires dans la même réponse
// ============================================================
$p = apiGet('/products/' . $id);

// Produit introuvable ou inactif → redirection
if (empty($p) || isset($p['erreur'])) {
    header('Location: catalog.php');
    exit;
}

// ---- AVIS ----
$avisList = $p['avis'] ?? [];

// Moyenne et répartition calculées depuis les avis reçus
$avgNote     = 0;
$repartition = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

if (!empty($avisList)) {
    $avgNote = round(
        array_sum(array_column($avisList, 'note')) / count($avisList),
        1
    );
    foreach ($avisList as $a) {
        $repartition[(int)$a['note']]++;
    }
} else {
    // Fallback sur la note globale du produit si pas d'avis individuels
    $avgNote = (float)($p['note'] ?? 0);
}

// ---- SIMILAIRES ----
$similaires = $p['similaires'] ?? [];

// ---- SPECS ----
$specs = [
    'Marque'    => $p['marque']        ?: '—',
    'Catégorie' => $p['categorie_nom'] ?: '—',
    'Stock'     => ($p['stock'] ?? 0) . ' unités',
    'Badge'     => $p['badge']         ?: '—',
    'Note'      => $avgNote . '/5',
];

// ---- REMISE ----
$disc = (!empty($p['ancien_prix']) && $p['ancien_prix'] > $p['prix'])
        ? round((1 - $p['prix'] / $p['ancien_prix']) * 100)
        : 0;

// ---- META PAGE ----
$pageTitle = htmlspecialchars($p['nom']) . ' - KF Tech';
$pageDesc  = substr(strip_tags($p['description'] ?? ''), 0, 160);
$activeCat = $p['categorie_slug'] ?? '';

function stars(float $n): string {
    $n = (int)round($n);
    return str_repeat('★', $n) . str_repeat('☆', 5 - $n);
}

include __DIR__ . '/includes/header.php';
?>
<style>
/* ===== PRODUCT PAGE ===== */
.prod-page { padding:32px 0 60px; }
.breadcrumb-bar { font-size:13px; color:#888; padding:14px 0; border-bottom:1px solid var(--border); margin-bottom:28px; }
.breadcrumb-bar a { color:#888; text-decoration:none; }
.breadcrumb-bar a:hover { color:var(--orange); }
.breadcrumb-bar span { margin:0 6px; }

.prod-layout { display:grid; grid-template-columns:1fr 1fr 300px; gap:32px; align-items:start; margin-bottom:48px; }

/* Galerie */
.gallery-main { background:var(--light-gray); border-radius:12px; border:1px solid var(--border); height:380px; display:flex; align-items:center; justify-content:center; overflow:hidden; margin-bottom:12px; position:relative; cursor:zoom-in; }
.gallery-main img { max-width:90%; max-height:340px; object-fit:contain; transition:transform .3s; }
.gallery-main:hover img { transform:scale(1.06); }
.g-badges { position:absolute; top:14px; left:14px; display:flex; flex-direction:column; gap:6px; }
.g-badge-new  { background:var(--orange); color:#fff; font-size:11px; font-weight:700; padding:4px 10px; border-radius:3px; }
.g-badge-disc { background:#e63946; color:#fff; font-size:11px; font-weight:700; padding:4px 10px; border-radius:3px; }
.gallery-thumbs { display:flex; gap:10px; }
.g-thumb { width:74px; height:66px; border:2px solid var(--border); border-radius:8px; background:var(--light-gray); display:flex; align-items:center; justify-content:center; cursor:pointer; overflow:hidden; transition:border-color .2s; }
.g-thumb img { max-width:90%; max-height:90%; object-fit:contain; }
.g-thumb:hover, .g-thumb.active { border-color:var(--orange); }

/* Infos produit */
.pi-badge-wrap { margin-bottom:10px; }
.pi-cat-link { font-size:12px; color:var(--orange); text-decoration:none; font-weight:600; }
.pi-cat-link:hover { text-decoration:underline; }
.pi-name { font-family:'Barlow Condensed',sans-serif; font-size:30px; font-weight:800; color:var(--black); line-height:1.2; margin:8px 0 12px; }
.pi-meta { display:flex; align-items:center; gap:14px; margin-bottom:14px; flex-wrap:wrap; }
.pi-stars-val { color:#f5a623; font-size:16px; }
.pi-reviews-cnt { font-size:13px; color:#888; }
.pi-brand-tag { font-size:12px; background:var(--light-gray); padding:3px 10px; border-radius:20px; color:#555; }
.pi-prices { display:flex; align-items:center; gap:14px; margin-bottom:14px; }
.pi-price-new { font-size:32px; font-weight:800; color:var(--orange); }
.pi-price-old { font-size:18px; color:#bbb; text-decoration:line-through; }
.pi-disc-tag { background:#e63946; color:#fff; font-size:12px; font-weight:700; padding:4px 10px; border-radius:4px; }
.pi-stock-row { display:flex; align-items:center; gap:8px; margin-bottom:14px; font-size:13px; }
.stock-indicator { width:10px; height:10px; border-radius:50%; }
.stock-ok  { background:#2ecc71; }
.stock-low { background:#f5a623; }
.stock-out { background:#e63946; }
.pi-desc { font-size:14px; color:#555; line-height:1.75; margin-bottom:20px; padding-bottom:20px; border-bottom:1px solid var(--border); }

.color-section { margin-bottom:18px; }
.color-section label { font-size:13px; font-weight:700; color:var(--black); display:block; margin-bottom:8px; }
.color-opts { display:flex; gap:10px; flex-wrap:wrap; }
.col-opt { width:30px; height:30px; border-radius:50%; border:3px solid transparent; cursor:pointer; transition:border-color .2s; position:relative; }
.col-opt.active, .col-opt:hover { border-color:var(--orange); }
.col-opt::after { content:attr(data-label); position:absolute; bottom:-20px; left:50%; transform:translateX(-50%); font-size:10px; white-space:nowrap; color:#888; }

.cart-row { display:flex; align-items:center; gap:10px; margin-bottom:12px; flex-wrap:wrap; }
.qty-box-p { display:flex; align-items:center; border:1.5px solid var(--border); border-radius:8px; overflow:hidden; }
.qty-box-p button { width:42px; height:48px; background:var(--light-gray); border:none; font-size:20px; cursor:pointer; transition:background .2s; }
.qty-box-p button:hover { background:var(--orange); color:#fff; }
.qty-box-p input { width:50px; text-align:center; border:none; outline:none; font-size:16px; font-weight:700; font-family:'Barlow',sans-serif; }
.btn-add-prod { flex:1; height:48px; background:var(--orange); color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:700; font-family:'Barlow',sans-serif; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; transition:background .2s; min-width:160px; }
.btn-add-prod:hover { background:var(--orange-dark); }
.btn-wish-prod { width:48px; height:48px; border:1.5px solid var(--border); background:#fff; border-radius:8px; font-size:18px; color:#bbb; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all .2s; }
.btn-wish-prod:hover { border-color:#e63946; color:#e63946; }
.btn-wish-prod.active { border-color:#e63946; color:#e63946; background:#fff5f5; }
.btn-buy-prod { width:100%; height:50px; background:var(--black); color:#fff; border:none; border-radius:8px; font-size:15px; font-weight:700; font-family:'Barlow',sans-serif; cursor:pointer; transition:background .2s; margin-bottom:16px; }
.btn-buy-prod:hover { background:#333; }

.pi-share { display:flex; align-items:center; gap:10px; font-size:13px; color:#888; }
.pi-share a { width:34px; height:34px; border:1px solid var(--border); border-radius:50%; display:flex; align-items:center; justify-content:center; color:#666; font-size:13px; transition:all .2s; text-decoration:none; }
.pi-share a:hover { background:var(--orange); color:#fff; border-color:var(--orange); }

/* Sidebar livraison */
.del-card { border:1px solid var(--border); border-radius:10px; padding:18px; background:#fff; margin-bottom:14px; }
.del-card h4 { font-size:14px; font-weight:700; margin-bottom:14px; color:var(--black); border-bottom:2px solid var(--orange); padding-bottom:8px; display:inline-block; }
.del-opt { display:flex; gap:10px; align-items:flex-start; margin-bottom:12px; }
.del-opt i { color:var(--orange); font-size:18px; flex-shrink:0; margin-top:2px; }
.del-opt p { font-size:13px; font-weight:600; color:var(--black); margin-bottom:2px; }
.del-opt span { font-size:12px; color:#888; }
.seller-row { display:flex; align-items:center; gap:12px; margin-bottom:12px; }
.seller-av { width:46px; height:46px; background:var(--orange); border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-size:18px; font-weight:800; font-family:'Barlow Condensed',sans-serif; flex-shrink:0; }
.seller-name { font-weight:700; font-size:14px; }
.seller-rt { color:#f5a623; font-size:12px; }
.btn-wa { width:100%; height:40px; background:#25d366; color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; font-family:'Barlow',sans-serif; transition:background .2s; text-decoration:none; }
.btn-wa:hover { background:#20b858; }
.guar-list { list-style:none; }
.guar-list li { display:flex; align-items:center; gap:8px; font-size:13px; color:#555; padding:6px 0; border-bottom:1px solid #f5f5f5; }
.guar-list li i { color:#2ecc71; }

/* Tabs */
.prod-tabs-section { margin-top:40px; }
.p-tabs { display:flex; border-bottom:2px solid var(--border); margin-bottom:24px; }
.p-tab { padding:12px 24px; font-size:14px; font-weight:600; color:#888; cursor:pointer; border-bottom:3px solid transparent; margin-bottom:-2px; background:none; border-top:none; border-left:none; border-right:none; font-family:'Barlow',sans-serif; transition:all .2s; }
.p-tab:hover { color:var(--orange); }
.p-tab.active { color:var(--orange); border-bottom-color:var(--orange); }
.tab-panel { display:none; }
.tab-panel.active { display:block; }
.desc-full { font-size:14px; color:#555; line-height:1.8; }
.spec-tbl { width:100%; border-collapse:collapse; font-size:14px; }
.spec-tbl tr { border-bottom:1px solid var(--border); }
.spec-tbl td { padding:12px 16px; }
.spec-tbl td:first-child { font-weight:600; color:var(--black); width:200px; background:var(--light-gray); }

/* Avis */
.reviews-top { display:flex; gap:32px; align-items:center; margin-bottom:28px; padding-bottom:20px; border-bottom:1px solid var(--border); flex-wrap:wrap; }
.avg-bloc { text-align:center; }
.avg-num { font-family:'Barlow Condensed',sans-serif; font-size:64px; font-weight:800; color:var(--orange); line-height:1; }
.avg-st { color:#f5a623; font-size:20px; margin:4px 0; }
.avg-cnt { font-size:12px; color:#888; }
.bars-bloc { flex:1; }
.bar-row { display:flex; align-items:center; gap:10px; font-size:12px; margin-bottom:6px; }
.bar-row .star-n { width:14px; text-align:right; }
.bar-bg { flex:1; background:var(--border); border-radius:2px; height:8px; overflow:hidden; }
.bar-fill { height:100%; background:var(--orange); border-radius:2px; }
.bar-cnt { width:28px; color:#888; }
.avis-item { padding:20px 0; border-bottom:1px solid var(--border); }
.avis-head { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:6px; }
.avis-name { font-weight:700; font-size:14px; }
.avis-date { font-size:12px; color:#aaa; }
.avis-stars { color:#f5a623; font-size:14px; margin-bottom:8px; }
.avis-txt { font-size:13px; color:#555; line-height:1.6; }
.avis-verifie { font-size:11px; color:#2ecc71; margin-top:6px; }

/* Formulaire avis */
.avis-form-box { background:var(--light-gray); border-radius:10px; padding:24px; margin-top:24px; }
.avis-form-box h4 { font-size:16px; font-weight:700; margin-bottom:16px; }
.stars-input { display:flex; gap:6px; margin-bottom:14px; cursor:pointer; }
.stars-input i { font-size:24px; color:#ddd; transition:color .15s; }
.stars-input i.active { color:#f5a623; }
.f-group-av { margin-bottom:12px; }
.f-group-av label { display:block; font-size:12px; font-weight:700; color:#555; margin-bottom:5px; text-transform:uppercase; }
.f-group-av input, .f-group-av textarea {
  width:100%; border:1.5px solid var(--border); border-radius:8px;
  padding:10px 14px; font-size:14px; font-family:'Barlow',sans-serif; outline:none; resize:vertical;
  transition:border-color .2s;
}
.f-group-av input:focus, .f-group-av textarea:focus { border-color:var(--orange); }

/* Similaires */
.similar-section { margin-top:48px; }
.similar-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-top:16px; }

@media (max-width:1100px) { .prod-layout { grid-template-columns:1fr 1fr; } .del-card:nth-child(3) { grid-column:span 2; } }
@media (max-width:768px) {
  .prod-layout { grid-template-columns:1fr; }
  .similar-grid { grid-template-columns:repeat(2,1fr); }
  .reviews-top { flex-direction:column; align-items:flex-start; }
}
</style>

<!-- BREADCRUMB -->
<div class="container">
  <div class="breadcrumb-bar">
    <a href="index.php">Accueil</a><span>›</span>
    <a href="catalog.php">Catalogue</a><span>›</span>
    <a href="catalog.php?cat=<?= $p['categorie_slug'] ?>"><?= htmlspecialchars($p['categorie_nom']) ?></a><span>›</span>
    <strong><?= htmlspecialchars($p['nom']) ?></strong>
  </div>
</div>

<div class="prod-page">
<div class="container">

<!-- LAYOUT PRODUIT -->
<div class="prod-layout">

  <!-- GALERIE -->
  <div class="gallery">
    <div class="gallery-main" id="mainImgWrap">
      <div class="g-badges">
        <span class="g-badge-new"><?= htmlspecialchars($p['badge']) ?></span>
        <?php if ($disc): ?><span class="g-badge-disc">-<?= $disc ?>%</span><?php endif; ?>
      </div>
      <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['nom']) ?>" id="mainImg"/>
    </div>
    <div class="gallery-thumbs">
      <!-- 3 thumbnails (même image pour l'instant, remplacer par images multiples) -->
      <?php for ($i = 0; $i < 3; $i++): ?>
      <div class="g-thumb <?= $i===0 ? 'active' : '' ?>" onclick="switchImg(this, '<?= htmlspecialchars($p['image']) ?>')">
        <img src="<?= htmlspecialchars($p['image']) ?>" alt="Vue <?= $i+1 ?>"/>
      </div>
      <?php endfor; ?>
    </div>
  </div>

  <!-- INFOS -->
  <div class="pi-info">
    <div class="pi-badge-wrap">
      <a href="catalog.php?cat=<?= $p['categorie_slug'] ?>" class="pi-cat-link">
        <i class="<?= 'fas fa-tag'  ?>"></i> <?= htmlspecialchars($p['categorie_nom']) ?>
      </a>
    </div>
    <h1 class="pi-name"><?= htmlspecialchars($p['nom']) ?></h1>
    <div class="pi-meta">
      <span class="pi-stars-val"><?= stars($avgNote) ?></span>
      <span class="pi-reviews-cnt">(<?= count($avisList) ?> avis)</span>
      <?php if ($p['marque']): ?>
        <span class="pi-brand-tag">Marque : <strong><?= htmlspecialchars($p['marque']) ?></strong></span>
      <?php endif; ?>
    </div>
    <div class="pi-prices">
      <span class="pi-price-new">XAF <?= number_format($p['prix'], 0, '.', ' ') ?></span>
      <?php if ($p['ancien_prix']): ?>
        <span class="pi-price-old">XAF <?= number_format($p['ancien_prix'], 0, '.', ' ') ?></span>
      <?php endif; ?>
      <?php if ($disc): ?><span class="pi-disc-tag">-<?= $disc ?>%</span><?php endif; ?>
    </div>
    <div class="pi-stock-row">
      <div class="stock-indicator <?= $p['stock'] > 10 ? 'stock-ok' : ($p['stock'] > 0 ? 'stock-low' : 'stock-out') ?>"></div>
      <?php if ($p['stock'] > 10): ?>
        <span>En stock : <strong style="color:#2ecc71"><?= $p['stock'] ?> unités</strong></span>
      <?php elseif ($p['stock'] > 0): ?>
        <span>Stock limité : <strong style="color:#f5a623"><?= $p['stock'] ?> unités restantes</strong></span>
      <?php else: ?>
        <span style="color:#e63946"><strong>Rupture de stock</strong></span>
      <?php endif; ?>
    </div>
    <p class="pi-desc"><?= nl2br(htmlspecialchars($p['description'])) ?></p>

    <!-- Couleurs -->
    <div class="color-section">
      <label>Couleur :</label>
      <div class="color-opts">
        <div class="col-opt active" style="background:#1a1a1a" data-label="Noir"></div>
        <div class="col-opt" style="background:#c0c0c0" data-label="Argent"></div>
        <div class="col-opt" style="background:var(--orange)" data-label="Orange"></div>
      </div>
    </div>

    <!-- Quantité + panier -->
    <div style="margin-top:24px">
      <div class="cart-row">
        <div class="qty-box-p">
          <button id="qMinus">−</button>
          <input type="text" id="qVal" value="1" readonly/>
          <button id="qPlus">+</button>
        </div>
        <button class="btn-add-prod" id="addToCartBtn">
          <i class="fas fa-shopping-cart"></i> Ajouter au panier
        </button>
        <button class="btn-wish-prod" id="wishBtn"
                data-wish-id="<?= $p['id'] ?>"
                title="Ajouter aux favoris">
          <i class="fas fa-heart"></i>
        </button>
      </div>
      <button class="btn-buy-prod" id="buyNowBtn">⚡ Acheter maintenant</button>
    </div>

    <div class="pi-share">
      <span>Partager :</span>
      <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode("http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
      <a href="https://twitter.com/intent/tweet?text=<?= urlencode($p['nom']) ?>&url=<?= urlencode("http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) ?>" target="_blank"><i class="fab fa-twitter"></i></a>
      <a href="https://wa.me/?text=<?= urlencode($p['nom'].' - XAF '.number_format($p['prix'],0,'.',' ').' - '.'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) ?>" target="_blank"><i class="fab fa-whatsapp"></i></a>
      <a href="#" onclick="navigator.clipboard.writeText(window.location.href);this.innerHTML='<i class=\'fas fa-check\'></i>';return false;"><i class="fas fa-link"></i></a>
    </div>
  </div>

  <!-- SIDEBAR LIVRAISON -->
  <div class="prod-sidebar">
    <div class="del-card">
      <h4><i class="fas fa-truck" style="color:var(--orange);margin-right:6px"></i> Livraison</h4>
      <div class="del-opt">
        <i class="fas fa-truck"></i>
        <div><p>Livraison standard</p><span>2-3 jours · Gratuite dès 300 000 XAF</span></div>
      </div>
      <div class="del-opt">
        <i class="fas fa-bolt"></i>
        <div><p>Livraison express</p><span>24h · 5 000 XAF</span></div>
      </div>
      <div class="del-opt">
        <i class="fas fa-store"></i>
        <div><p>Retrait en boutique</p><span>Disponible aujourd'hui · Gratuit</span></div>
      </div>
    </div>

    <div class="del-card">
      <h4><i class="fas fa-user-circle" style="color:var(--orange);margin-right:6px"></i> Vendeur</h4>
      <div class="seller-row">
        <div class="seller-av">KF</div>
        <div>
          <div class="seller-name">KF Tech Sarl</div>
          <div class="seller-rt">★★★★★ (4.9) · Vérifié</div>
        </div>
      </div>
      <a href="https://wa.me/237690048482?text=Bonjour, je suis intéressé par : <?= urlencode($p['nom']) ?>" target="_blank" class="btn-wa">
        <i class="fab fa-whatsapp"></i> Contacter via WhatsApp
      </a>
    </div>

    <div class="del-card">
      <h4><i class="fas fa-shield-alt" style="color:var(--orange);margin-right:6px"></i> Garanties</h4>
      <ul class="guar-list">
        <li><i class="fas fa-check-circle"></i> Produit neuf et authentique</li>
        <li><i class="fas fa-check-circle"></i> Garantie 12 mois</li>
        <li><i class="fas fa-check-circle"></i> Retour sous 7 jours</li>
        <li><i class="fas fa-check-circle"></i> Paiement 100% sécurisé</li>
      </ul>
    </div>
  </div>
</div>

<!-- TABS -->
<div class="prod-tabs-section">
  <div class="p-tabs">
    <button class="p-tab active" data-tab="desc">Description</button>
    <button class="p-tab" data-tab="specs">Spécifications</button>
    <button class="p-tab" data-tab="reviews">Avis (<?= count($avisList) ?>)</button>
  </div>

  <!-- Description -->
  <div class="tab-panel active" id="tab-desc">
    <div class="desc-full">
      <p><?= nl2br(htmlspecialchars($p['description'])) ?></p>
      <br>
      <p>Ce produit est vendu par <strong>KF Tech Sarl</strong>, votre boutique informatique de confiance à Douala.
      Tous nos produits sont neufs, authentiques et livrés avec leur emballage d'origine et leur facture officielle.</p>
    </div>
  </div>

  <!-- Spécifications -->
  <div class="tab-panel" id="tab-specs">
    <table class="spec-tbl">
      <?php foreach ($specs as $k => $v): ?>
        <tr><td><?= htmlspecialchars($k) ?></td><td><?= htmlspecialchars($v) ?></td></tr>
      <?php endforeach; ?>
    </table>
  </div>

  <!-- Avis -->
  <div class="tab-panel" id="tab-reviews">
    <!-- Résumé -->
    <div class="reviews-top">
      <div class="avg-bloc">
        <div class="avg-num"><?= number_format($avgNote, 1) ?></div>
        <div class="avg-st"><?= stars($avgNote) ?></div>
        <div class="avg-cnt"><?= count($avisList) ?> avis</div>
      </div>
      <div class="bars-bloc">
        <?php foreach ([5,4,3,2,1] as $s):
          $cnt  = $repartition[$s];
          $pct  = count($avisList) ? round($cnt/count($avisList)*100) : 0;
        ?>
        <div class="bar-row">
          <span class="star-n"><?= $s ?></span>
          <i class="fas fa-star" style="color:#f5a623;font-size:10px"></i>
          <div class="bar-bg"><div class="bar-fill" style="width:<?= $pct ?>%"></div></div>
          <span class="bar-cnt"><?= $cnt ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Liste avis -->
    <?php if (empty($avisList)): ?>
      <p style="color:#aaa;font-size:14px;margin-bottom:20px">Aucun avis pour l'instant. Soyez le premier !</p>
    <?php else: ?>
      <?php foreach ($avisList as $a): ?>
      <div class="avis-item">
        <div class="avis-head">
          <span class="avis-name"><?= htmlspecialchars($a['nom_auteur']) ?></span>
          <span class="avis-date"><?= date('d M Y', strtotime($a['created_at'])) ?></span>
        </div>
        <div class="avis-stars"><?= stars($a['note']) ?></div>
        <p class="avis-txt"><?= nl2br(htmlspecialchars($a['commentaire'])) ?></p>
        <?php if ($a['verifie']): ?>
          <p class="avis-verifie"><i class="fas fa-check-circle"></i> Achat vérifié</p>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <!-- Formulaire avis -->
    <div class="avis-form-box">
      <h4>Laisser un avis</h4>
      <div class="stars-input" id="starsInput">
        <?php for ($i=1;$i<=5;$i++): ?>
          <i class="fas fa-star" data-val="<?= $i ?>"></i>
        <?php endfor; ?>
      </div>
      <div class="f-group-av"><label>Nom</label><input type="text" id="avisNom" placeholder="Votre nom"/></div>
      <div class="f-group-av"><label>Commentaire</label><textarea id="avisTxt" rows="4" placeholder="Votre avis..."></textarea></div>
      <button class="btn-primary" id="submitAvis" style="margin-top:8px;border:none;cursor:pointer;height:44px;padding:0 28px">Publier mon avis</button>
    </div>
  </div>
</div>

<!-- SIMILAIRES -->
<?php if (!empty($similaires)): ?>
<div class="similar-section">
  <h2 class="sec-title">Produits Similaires</h2>
  <div class="similar-grid">
    <?php foreach ($similaires as $s):
      $sDisc = ($s['ancien_prix'] && $s['ancien_prix'] > $s['prix']) ? round((1-$s['prix']/$s['ancien_prix'])*100) : 0;
    ?>
    <div class="prod-card" onclick="window.location='product.php?id=<?= $s['id'] ?>'">
      <span class="prod-badge"><?= htmlspecialchars($s['badge']) ?></span>
      <?php if ($sDisc): ?><span class="prod-disc">-<?= $sDisc ?>%</span><?php endif; ?>
      <div class="prod-img"><img src="<?= htmlspecialchars($s['image']) ?>" alt="<?= htmlspecialchars($s['nom']) ?>" loading="lazy"/></div>
      <div class="prod-info">
        <div class="prod-stars"><?= stars($s['note']) ?> <span>(<?= $s['nb_avis'] ?>)</span></div>
        <p class="prod-name"><?= htmlspecialchars($s['nom']) ?></p>
        <div class="prod-prices">
          <span class="prod-price-new">XAF <?= number_format($s['prix'],0,'.',' ') ?></span>
          <?php if ($s['ancien_prix']): ?><span class="prod-price-old">XAF <?= number_format($s['ancien_prix'],0,'.',' ') ?></span><?php endif; ?>
        </div>
        <button class="btn-add" data-id="<?= $s['id'] ?>">Ajouter au panier</button>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

</div><!-- /container -->
</div><!-- /prod-page -->

<script>
var PROD = {
  id:    <?= $p['id'] ?>,
  nom:   <?= json_encode($p['nom']) ?>,
  prix:  <?= $p['prix'] ?>,
  image: <?= json_encode($p['image']) ?>,
  stock: <?= $p['stock'] ?>
};

// Galerie
function switchImg(thumb, src) {
  document.getElementById('mainImg').src = src;
  document.querySelectorAll('.g-thumb').forEach(function(t){ t.classList.remove('active'); });
  thumb.classList.add('active');
}

// Quantité
var qty = 1;
document.getElementById('qMinus').addEventListener('click', function(){ if(qty>1){ qty--; document.getElementById('qVal').value=qty; } });
document.getElementById('qPlus').addEventListener('click', function(){ if(qty<PROD.stock){ qty++; document.getElementById('qVal').value=qty; } });

// Couleurs
document.querySelectorAll('.col-opt').forEach(function(o){
  o.addEventListener('click', function(){
    document.querySelectorAll('.col-opt').forEach(function(x){ x.classList.remove('active'); });
    o.classList.add('active');
  });
});

// Tabs
document.querySelectorAll('.p-tab').forEach(function(tab){
  tab.addEventListener('click', function(){
    document.querySelectorAll('.p-tab').forEach(function(t){ t.classList.remove('active'); });
    document.querySelectorAll('.tab-panel').forEach(function(p){ p.classList.remove('active'); });
    tab.classList.add('active');
    document.getElementById('tab-'+tab.dataset.tab).classList.add('active');
  });
});

// Stars input avis
var selectedStar = 0;
document.querySelectorAll('#starsInput i').forEach(function(star){
  star.addEventListener('mouseover', function(){
    var v = parseInt(star.dataset.val);
    document.querySelectorAll('#starsInput i').forEach(function(s,i){ s.classList.toggle('active', i<v); });
  });
  star.addEventListener('click', function(){
    selectedStar = parseInt(star.dataset.val);
  });
});
document.getElementById('starsInput').addEventListener('mouseleave', function(){
  document.querySelectorAll('#starsInput i').forEach(function(s,i){ s.classList.toggle('active', i<selectedStar); });
});

// Soumettre avis
document.getElementById('submitAvis').addEventListener('click', function(){
  var nom = document.getElementById('avisNom').value.trim();
  var txt = document.getElementById('avisTxt').value.trim();
  if (!selectedStar) { alert('Veuillez sélectionner une note.'); return; }
  if (!nom || !txt)  { alert('Veuillez remplir tous les champs.'); return; }

  fetch('api/avis.php', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ produit_id: PROD.id, nom_auteur: nom, note: selectedStar, commentaire: txt })
  })
  .then(function(r){ return r.json(); })
  .then(function(d){
    if (d.succes) { location.reload(); }
    else { alert(d.message || 'Erreur.'); }
  })
  .catch(function(){ alert('Erreur réseau.'); });
});


// Ajouter au panier — utilise les fonctions de main.js
document.getElementById('addToCartBtn').addEventListener('click', function() {
  addToCart(PROD, qty);
});

// Acheter maintenant — ouvre le mini-formulaire WhatsApp
document.getElementById('buyNowBtn').addEventListener('click', function() {
  acheterMaintenant(PROD, qty);
});

// Boutons "Ajouter au panier" des produits similaires — déjà gérés par main.js
// Retirer les onclick inline qui causent des conflits
document.querySelectorAll('.btn-add').forEach(function(btn) {
  btn.removeAttribute('onclick');
});

// Init badge panier au chargement
updateCartUI();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>