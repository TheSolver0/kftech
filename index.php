<?php
session_start();
require_once __DIR__ . '/config/api.php';

// Message flash (ex: après déconnexion)
$flash = null;
if (!empty($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

// ---- CATEGORIES (sidebar + hero) ----
$allCats = apiGet('/categories');
$heroSlides = [];
if (is_array($allCats) && count($allCats)) {
    foreach ($allCats as $cat) {
        if (empty($cat['slug'])) {
            continue;
        }
        $prodData = apiGet('/products?cat=' . urlencode($cat['slug']) . '&limit=1');
        $items = $prodData['produits'] ?? $prodData['items'] ?? [];
        $firstProduct = $items[0] ?? null;
        if (!$firstProduct) {
            continue;
        }

        $stockPct = 80;
        if (isset($firstProduct['stock']) && is_numeric($firstProduct['stock'])) {
            $stockPct = min(100, max(12, (int)$firstProduct['stock']));
        }

        $heroSlides[] = [
            'badge_texte'       => $cat['nom'] ?: ($firstProduct['badge'] ?? 'Produit'),
            'titre'             => $firstProduct['nom'] ?? 'Produit KF Tech',
            'sous_titre'        => 'Catégorie ' . ($cat['nom'] ?? ''),
            'prix'              => $firstProduct['prix'] ?? 0,
            'ancien_prix'       => $firstProduct['ancien_prix'] ?? 0,
            'image'             => $firstProduct['image'] ?? 'assets/images/produit1.jpg',
            'btn_principal_lien'=> 'product.php?id=' . ($firstProduct['id'] ?? ''),
            'stock_pct'         => $stockPct,
        ];
    }
}

// Si rien n'a été généré, on retombe sur l'ancien endpoint hero
if (empty($heroSlides)) {
    $heroData = apiGet('/hero');
    $heroSlides = $heroData['slides'] ?? [];
}

// ---- MEILLEURS PRODUITS (sidebar) ----
$bestData  = apiGet('/products/meilleurs');
$bestProds = $bestData['produits'] ?? [];

// ---- PRODUITS TENDANCE ----
$trendData  = apiGet('/products/tendance');
$trendProds = $trendData['produits'] ?? [];

// ---- SMARTPHONES / TABLETTES ----
$smartData1 = apiGet('/products?cat=smartphones&limit=4');
$smartData2 = apiGet('/products?cat=tablettes&limit=4');
$smartProds = array_merge(
    $smartData1['produits'] ?? [],
    $smartData2['produits'] ?? []
);

$catCards = [];
foreach ($allCats as $i => $cat) {
    if ($i >= 3) break;
    $prodData = apiGet('/products?cat=' . urlencode($cat['slug']) . '&limit=5');
    $items = $prodData['produits'] ?? $prodData['items'] ?? [];
    if (!is_array($items)) {
        $items = [];
    }
    $names = array_map(function($item) { return $item['nom'] ?? ''; }, array_slice($items, 0, 5));
    $catCards[] = [
        'nom'      => $cat['nom'] ?? 'Catégorie',
        'slug'     => $cat['slug'] ?? '',
        'produits' => $names,
        'image'    => $items[0]['image'] ?? 'assets/images/produit4.jpg',
    ];
}

// Premier slide hero par defaut
$firstSlide = $heroSlides[0] ?? null;

function stars(float $n): string {
    $n = (int)round($n);
    return str_repeat('*', $n) . str_repeat('o', 5 - $n);
}
function starsHtml(float $n): string {
    $n = (int)round($n);
    return str_repeat('&#9733;', $n) . str_repeat('&#9734;', 5 - $n);
}
function prix(float $p): string {
    return 'XAF ' . number_format($p, 0, '.', ' ');
}

$pageTitle = 'KF Tech - Boutique Informatique Douala';
$pageDesc  = 'KF Tech, votre boutique informatique a Douala. Laptops, smartphones, tablettes, accessoires au meilleur prix.';

include __DIR__ . '/includes/header.php';
?>

<?php if ($flash): ?>
<div id="flashMsg" style="
  position:fixed; top:80px; left:50%; transform:translateX(-50%);
  background:<?= $flash['type']==='success' ? '#2ecc71' : '#e63946' ?>;
  color:#fff; padding:14px 28px; border-radius:8px;
  font-size:15px; font-weight:600;
  z-index:9999; box-shadow:0 4px 16px rgba(0,0,0,.2);
  animation: slideDown .3s ease;
">
  <i class="fas fa-<?= $flash['type']==='success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
  <?= htmlspecialchars($flash['msg']) ?>
</div>
<style>
@keyframes slideDown { from { opacity:0; top:60px; } to { opacity:1; top:80px; } }
</style>
<script>
setTimeout(function() {
  var m = document.getElementById('flashMsg');
  if (m) { m.style.transition='opacity .4s'; m.style.opacity='0'; setTimeout(function(){ m.remove(); }, 400); }
}, 3500);
</script>
<?php endif; ?>

<!-- HERO DIAPORAMA -->
<section class="hero" id="heroSection">
  <div class="container hero-inner">

    <div class="hero-content">
      <span class="hero-badge" id="heroBadge"><?= $firstSlide ? htmlspecialchars($firstSlide['badge_texte']) : '100% meilleurs produits' ?></span>
      <h1 class="hero-title" id="heroTitle"><?= $firstSlide ? htmlspecialchars($firstSlide['titre']) : 'LENOVO YOGA PRO' ?></h1>
      <p class="hero-sub" id="heroSub"><?= $firstSlide ? htmlspecialchars($firstSlide['sous_titre']) : 'Widescreen 4k ultra Laptop' ?></p>
      <div class="hero-price">
        <span class="price-new" id="heroPriceNew"><?= $firstSlide ? prix((float)$firstSlide['prix']) : 'XAF 1 299 000' ?></span>
        <span class="price-old" id="heroPriceOld"><?= ($firstSlide && $firstSlide['ancien_prix']) ? prix((float)$firstSlide['ancien_prix']) : 'XAF 1 890 000' ?></span>
      </div>
      <div class="stock-wrap">
        <div class="stock-bar"><div class="stock-fill" id="heroStockFill" style="width:<?= $firstSlide ? $firstSlide['stock_pct'] : 65 ?>%"></div></div>
        <div class="stock-info">
          <span>Disponibles: <strong>334</strong></span>
          <span>Stock: <strong>180</strong></span>
        </div>
      </div>
      <div class="hero-btns">
        <?php if (count($heroSlides) > 1): ?>
        <div class="hero-arrows">
          <button class="hero-arr" id="heroPrev"><i class="fas fa-chevron-left"></i></button>
          <button class="hero-arr" id="heroNext"><i class="fas fa-chevron-right"></i></button>
        </div>
        <?php endif; ?>
        <a href="<?= $firstSlide ? htmlspecialchars($firstSlide['btn_principal_lien']) : 'catalog.php' ?>" class="btn-primary" id="heroBtnAcheter">Acheter Maintenant</a>
        <a href="#trending-section" class="btn-outline" id="heroBtnVoir">Voir Plus</a>
      </div>
      <?php if (count($heroSlides) > 1): ?>
      <div class="hero-dots">
        <?php foreach ($heroSlides as $i => $sl): ?>
          <button class="hero-dot <?= $i === 0 ? 'active' : '' ?>" data-idx="<?= $i ?>"></button>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Image hero -->
    <div class="hero-center">
      <div class="hero-circle"></div>
      <img
        id="heroImg"
        class="hero-img"
        src="<?= $firstSlide ? htmlspecialchars($firstSlide['image']) : 'assets/images/produit1.jpg' ?>"
        alt="<?= $firstSlide ? htmlspecialchars($firstSlide['titre']) : 'Produit KF Tech' ?>"
        onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=600&q=80'"
      />
      <div class="discount-badge" id="heroDisc">
        <?php
        if ($firstSlide && $firstSlide['prix'] && $firstSlide['ancien_prix']) {
            echo round((1 - $firstSlide['prix'] / $firstSlide['ancien_prix']) * 100) . '%<br><small>off</small>';
        } else { echo '29%<br><small>off</small>'; }
        ?>
      </div>
    </div>

    <!-- Sidebar meilleurs produits -->
    <div class="hero-sidebar">
      <div class="best-card">
        <div class="best-card-header">
          <h3>Meilleurs Produits</h3>
          <div class="card-nav">
            <button id="bPrev"><i class="fas fa-chevron-left"></i></button>
            <button id="bNext"><i class="fas fa-chevron-right"></i></button>
          </div>
        </div>
        <div class="best-slides" id="bestSlides">
          <?php foreach ($bestProds as $i => $bp): ?>
          <div class="best-slide <?= $i === 0 ? 'active' : '' ?>">
            <img src="<?= htmlspecialchars($bp['image']) ?>" alt="<?= htmlspecialchars($bp['nom']) ?>" loading="lazy"
                 onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=300&q=80'"/>
            <div class="stars"><?= starsHtml($bp['note']) ?></div>
            <p><?= htmlspecialchars($bp['nom']) ?></p>
            <span class="ptag"><?= prix((float)$bp['prix']) ?></span>
            <div class="avail"><span>Available: <?= $bp['stock'] ?></span><span>Sold: <?= $bp['nb_avis'] ?></span></div>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="thumb-row">
          <button id="tPrev"><i class="fas fa-chevron-left"></i></button>
          <div class="thumbs-wrap">
            <?php foreach ($bestProds as $i => $bp): ?>
            <img src="<?= htmlspecialchars($bp['image']) ?>" class="thumb <?= $i===0?'active':'' ?>" data-idx="<?= $i ?>" alt="" loading="lazy"
                 onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=70&q=80'"/>
            <?php endforeach; ?>
          </div>
          <button id="tNext"><i class="fas fa-chevron-right"></i></button>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- FEATURES -->
<section class="features">
  <div class="container features-grid">
    <div class="feat"><i class="fas fa-truck"></i><div><h4>Livraison A Domicile</h4><p>Livraison chez vous</p></div></div>
    <div class="feat"><i class="fas fa-lock"></i><div><h4>Paiement Securise</h4><p>Paiement sure</p></div></div>
    <div class="feat"><i class="fas fa-headset"></i><div><h4>Support 24/7</h4><p>Service client Disponible 24/7</p></div></div>
    <div class="feat"><i class="fas fa-shield-alt"></i><div><h4>100% Garanti</h4><p>Garanti sur tous nos produits</p></div></div>
    <div class="feat"><i class="fas fa-medal"></i><div><h4>Produits de Qualite</h4><p>Meilleure qualite</p></div></div>
  </div>
</section>

<!-- PRODUITS TENDANCE -->
<section class="trending-section pad" id="trending-section">
  <div class="container">
    <div class="two-col">
      <aside class="cat-sidebar">
        <h3 class="sidebar-title"><span>Categorie</span></h3>
        <ul class="cat-list">
          <?php foreach ($allCats as $i => $c): ?>
          <li class="cat-item <?= $i===0?'active':'' ?>" data-slug="<?= htmlspecialchars($c['slug'] ?? '') ?>">
            <i class="<?= categoryIconClass($c) ?>"></i>
            <span><?= htmlspecialchars($c['nom']) ?></span>
            <span style="margin-left:auto;font-size:11px;color:#bbb">(<?= intval($c['nb_produits'] ?? 0) ?>)</span>
            <i class="fas fa-chevron-right"></i>
          </li>
          <?php endforeach; ?>
        </ul>
      </aside>
      <div class="products-main">
        <div class="section-header-row">
          <h2 class="sec-title">Produit Tendance</h2>
          <div class="row-arrows">
            <button class="arr-btn"><i class="fas fa-chevron-left"></i></button>
            <button class="arr-btn"><i class="fas fa-chevron-right"></i></button>
          </div>
        </div>
        <div class="prod-grid" id="trendGrid">
          <?php foreach ($trendProds as $p):
            $disc = ($p['ancien_prix'] && $p['ancien_prix'] > $p['prix']) ? round((1-$p['prix']/$p['ancien_prix'])*100) : 0;
          ?>
          <div class="prod-card" onclick="window.location='product.php?id=<?= $p['id'] ?>'">
            <span class="prod-badge"><?= htmlspecialchars($p['badge']) ?></span>
            <?php if ($disc): ?><span class="prod-disc">-<?= $disc ?>%</span><?php endif; ?>
            <div class="prod-img">
              <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['nom']) ?>" loading="lazy"
                   onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=300&q=80'"/>
            </div>
            <div class="prod-info">
              <div class="prod-stars"><?= starsHtml($p['note']) ?> <span>(<?= $p['nb_avis'] ?>)</span></div>
              <p class="prod-name"><?= htmlspecialchars($p['nom']) ?></p>
              <div class="prod-prices">
                <span class="prod-price-new"><?= prix((float)$p['prix']) ?></span>
                <?php if ($p['ancien_prix']): ?><span class="prod-price-old"><?= prix((float)$p['ancien_prix']) ?></span><?php endif; ?>
              </div>
              <p class="prod-avail">Stock : <strong><?= $p['stock'] ?></strong></p>
              <button class="btn-add" data-id="<?= $p['id'] ?>"
                      data-name="<?= htmlspecialchars($p['nom'], ENT_QUOTES) ?>"
                      data-price="<?= htmlspecialchars($p['prix'], ENT_QUOTES) ?>"
                      data-image="<?= htmlspecialchars($p['image'], ENT_QUOTES) ?>"
                      onclick="event.stopPropagation()">Ajouter au panier</button>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- SMARTPHONES / TABLETTES -->
<section class="smart-section pad">
  <div class="container">
    <div class="section-header-row smart-header">
      <h2 class="sec-title"><span>Smartphone</span> / Tablet / Monitor</h2>
      <div class="brand-tabs">
        <button class="btab active" data-b="all">Tous</button>
        <button class="btab" data-b="samsung">Samsung</button>
        <button class="btab" data-b="apple">Apple</button>
        <button class="btab" data-b="huawei">Huawei</button>
        <button class="btab" data-b="xiaomi">Xiaomi</button>
      </div>
    </div>
    <div class="smart-layout">
      <div class="smart-grid" id="smartGrid">
        <?php foreach ($smartProds as $sp):
          $disc = ($sp['ancien_prix'] && $sp['ancien_prix'] > $sp['prix']) ? round((1-$sp['prix']/$sp['ancien_prix'])*100) : 0;
        ?>
        <div class="prod-card" onclick="window.location='product.php?id=<?= $sp['id'] ?>'">
          <span class="prod-badge"><?= htmlspecialchars($sp['badge']) ?></span>
          <?php if ($disc): ?><span class="prod-disc">-<?= $disc ?>%</span><?php endif; ?>
          <div class="prod-img">
            <img src="<?= htmlspecialchars($sp['image']) ?>" alt="<?= htmlspecialchars($sp['nom']) ?>" loading="lazy"
                 onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=300&q=80'"/>
          </div>
          <div class="prod-info">
            <div class="prod-stars"><?= starsHtml($sp['note']) ?> <span>(<?= $sp['nb_avis'] ?>)</span></div>
            <p class="prod-name"><?= htmlspecialchars($sp['nom']) ?></p>
            <div class="prod-prices">
              <span class="prod-price-new"><?= prix((float)$sp['prix']) ?></span>
              <?php if ($sp['ancien_prix']): ?><span class="prod-price-old"><?= prix((float)$sp['ancien_prix']) ?></span><?php endif; ?>
            </div>
            <p class="prod-avail">Available: <strong><?= $sp['stock'] ?></strong></p>
            <button class="btn-add" data-id="<?= $sp['id'] ?>"
                    data-name="<?= htmlspecialchars($sp['nom'], ENT_QUOTES) ?>"
                    data-price="<?= htmlspecialchars($sp['prix'], ENT_QUOTES) ?>"
                    data-image="<?= htmlspecialchars($sp['image'], ENT_QUOTES) ?>"
                    onclick="event.stopPropagation()">Ajouter au panier</button>
          </div>
        </div>
        <?php endforeach; ?>
 </div>
      <div class="review-card">
        <h3>5 Star Review</h3>
        <div class="rev-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        <img src="assets/images/Franck.jpeg"/>
        <p>"Produit incroyable, livraison rapide et service de qualité exceptionnelle chez KF Tech !"</p>
        <div class="reviewer"><strong>Franck Tiger</strong><small>Client verifie</small></div><br>

        <h3>5 Star Review</h3>
        <div class="rev-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        <img src="assets/images/Luc.jpeg"/>
        <p>"Service client réactif et produits de qualité supérieure. Je recommande vivement KF Tech !"</p>
        <div class="reviewer"><strong>Luc Armand</strong><small>Client verifie</small></div>

      </div>
    </div>
  </div>
</section>

<!-- BANNIERE -->
<div class="ship-banner">
  <div class="container">
    <p><span class="pct">10%</span> Livraison gratuite pour commande depassant <span class="amt">300 000 XAF</span></p>
  </div>
</div>

<!-- CATEGORIES DE PRODUITS -->
<section class="cat-prod-section pad">
  <div class="container">
    <h2 class="sec-title-lg">Catégories de produits</h2>
    <div class="cat-prod-grid">
      <?php foreach ($catCards as $card): ?>
      <div class="cpcard">
        <div class="cpimg"><img src="<?= htmlspecialchars($card['image']) ?>" alt="<?= htmlspecialchars($card['nom']) ?>" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=180&q=80'"/></div>
        <div class="cpinfo">
          <h4><?= htmlspecialchars($card['nom']) ?></h4>
          <ul>
            <?php foreach ($card['produits'] as $name): ?>
              <li><?= htmlspecialchars($name ?: 'Produit disponible') ?></li>
            <?php endforeach; ?>
          </ul>
          <a href="catalog.php?cat=<?= urlencode($card['slug']) ?>" class="more-link">Voir les produits</a>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (empty($catCards)): ?>
      <div class="cpcard"><div class="cpinfo"><p>Aucune catégorie disponible pour le moment.</p></div></div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- BANNIERES PROMO -->
<section class="promo-section pad">
  <div class="container promo-grid">
    <div class="promo-card dark-card">
      <div class="promo-txt"><h3>Achetez-en un.<br>Livraison gratuite.</h3><p>Profitez de nos meilleures offres</p><a href="catalog.php" class="pill-btn dark-pill">ACHETEZ MAINTENANT</a></div>
      <div class="promo-img-wrap"><img src="assets/images/produit1.jpg" alt="Promo" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1591488320449-011701bb6704?w=260&q=80'"/><div class="promo-pct red-pct">25%<br><small>off</small></div></div>
    </div>
    <div class="promo-card light-card">
      <div class="promo-txt"><h3>Buy One.<br>Get Free</h3><p class="org">Widescreen 4k ultra Laptop</p><a href="catalog.php?cat=laptops" class="pill-btn dark-pill">shop now</a></div>
      <div class="promo-img-wrap"><img src="assets/images/produit4.jpg" alt="Promo" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1616348436168-de43ad0db179?w=200&q=80'"/></div>
    </div>
  </div>
</section>

<!-- MARQUES -->
<section class="brands-bar">
  <div class="container brands-inner">
    <div class="brand-item">Dell</div>
    <div class="brand-item">HP</div>
    <div class="brand-item">Apple</div>
    <div class="brand-item">Samsung</div>
    <div class="brand-item">Lenovo</div>
    <div class="brand-item">Sony</div>
  </div>
</section>

<!-- JS INLINE : hero + interactions -->
<script>
var HERO_SLIDES  = <?= json_encode($heroSlides, JSON_UNESCAPED_UNICODE) ?>;
var HERO_CURRENT = 0;
var HERO_TIMER   = null;

function goToSlide(idx) {
  if (!HERO_SLIDES.length) return;
  HERO_CURRENT = (idx + HERO_SLIDES.length) % HERO_SLIDES.length;
  var s = HERO_SLIDES[HERO_CURRENT];
  var img = document.getElementById('heroImg');
  if (img) { img.style.opacity='0'; setTimeout(function(){ img.src=s.image; img.style.opacity='1'; }, 200); }
  var el = function(id){ return document.getElementById(id); };
  if (el('heroBadge'))     el('heroBadge').textContent     = s.badge_texte;
  if (el('heroTitle'))     el('heroTitle').textContent      = s.titre;
  if (el('heroSub'))       el('heroSub').textContent        = s.sous_titre;
  if (el('heroPriceNew'))  el('heroPriceNew').textContent   = 'XAF ' + parseInt(s.prix).toLocaleString('fr-FR');
  if (el('heroPriceOld'))  el('heroPriceOld').textContent   = s.ancien_prix ? 'XAF ' + parseInt(s.ancien_prix).toLocaleString('fr-FR') : '';
  if (el('heroStockFill')) el('heroStockFill').style.width  = (s.stock_pct||50) + '%';
  if (el('heroBtnAcheter')) el('heroBtnAcheter').href       = s.btn_principal_lien || 'catalog.php';
  if (el('heroDisc') && s.prix && s.ancien_prix) {
    var d = Math.round((1 - s.prix/s.ancien_prix)*100);
    el('heroDisc').innerHTML = d + '%<br><small>off</small>';
  }
  document.querySelectorAll('.hero-dot').forEach(function(dot,i){ dot.classList.toggle('active', i===HERO_CURRENT); });
}

function startHeroTimer() {
  if (HERO_TIMER) clearInterval(HERO_TIMER);
  if (HERO_SLIDES.length > 1) HERO_TIMER = setInterval(function(){ goToSlide(HERO_CURRENT+1); }, 5000);
}

var hPrev = document.getElementById('heroPrev');
var hNext = document.getElementById('heroNext');
if (hPrev) hPrev.addEventListener('click', function(){ goToSlide(HERO_CURRENT-1); startHeroTimer(); });
if (hNext) hNext.addEventListener('click', function(){ goToSlide(HERO_CURRENT+1); startHeroTimer(); });
document.querySelectorAll('.hero-dot').forEach(function(dot,i){
  dot.addEventListener('click', function(){ goToSlide(i); startHeroTimer(); });
});
document.getElementById('heroImg').style.transition = 'opacity 0.3s ease';
startHeroTimer();

// Best slider
var bestCurrent = 0;
function goBest(idx) {
  var slides = document.querySelectorAll('.best-slide');
  var thumbs = document.querySelectorAll('.thumbs-wrap .thumb');
  bestCurrent = (idx + slides.length) % slides.length;
  slides.forEach(function(s,i){ s.classList.toggle('active', i===bestCurrent); });
  thumbs.forEach(function(t,i){ t.classList.toggle('active', i===bestCurrent); });
}
document.getElementById('bPrev') && document.getElementById('bPrev').addEventListener('click', function(){ goBest(bestCurrent-1); });
document.getElementById('bNext') && document.getElementById('bNext').addEventListener('click', function(){ goBest(bestCurrent+1); });
document.getElementById('tPrev') && document.getElementById('tPrev').addEventListener('click', function(){ goBest(bestCurrent-1); });
document.getElementById('tNext') && document.getElementById('tNext').addEventListener('click', function(){ goBest(bestCurrent+1); });
document.querySelectorAll('.thumbs-wrap .thumb').forEach(function(t){
  t.addEventListener('click', function(){ goBest(parseInt(t.dataset.idx)); });
});
setInterval(function(){ goBest(bestCurrent+1); }, 4000);

// Cat sidebar AJAX
document.querySelectorAll('.cat-item').forEach(function(item){
  item.addEventListener('click', function(){
    document.querySelectorAll('.cat-item').forEach(function(i){ i.classList.remove('active'); });
    item.classList.add('active');
    var grid = document.getElementById('trendGrid');
    if (!grid) return;
    grid.innerHTML = '<p style="padding:20px;color:#aaa">Chargement...</p>';
    fetch('api/produits.php?action=liste&cat='+encodeURIComponent(item.dataset.slug)+'&limit=8')
      .then(function(r){ return r.json(); })
      .then(function(data){
        var list = data.produits || data.items || data || [];
        if (!Array.isArray(list)) {
          list = [];
        }
        grid.innerHTML = '';
        list.forEach(function(p){
          var disc = (p.ancien_prix && p.ancien_prix > p.prix) ? Math.round((1-p.prix/p.ancien_prix)*100) : 0;
          var c = document.createElement('div');
          c.className = 'prod-card'; c.onclick = function(){ window.location='product.php?id='+encodeURIComponent(p.id); };
          c.innerHTML = '<span class="prod-badge">'+(p.badge||'')+'</span>'+(disc?'<span class="prod-disc">-'+disc+'%</span>':'')+
            '<div class="prod-img"><img src="'+(p.image||'')+'" alt="'+(p.nom||'Produit')+'" loading="lazy" onerror="this.onerror=null;this.src=\'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=300&q=80\'"/></div>'+
            '<div class="prod-info"><p class="prod-name">'+(p.nom||'Produit KF Tech')+'</p>'+ 
            '<div class="prod-prices"><span class="prod-price-new">XAF '+parseInt(p.prix||0).toLocaleString('fr-FR')+'</span>'+(p.ancien_prix?'<span class="prod-price-old">XAF '+parseInt(p.ancien_prix).toLocaleString('fr-FR')+'</span>':'')+'</div>'+ 
            '<p class="prod-avail">Stock : <strong>'+((p.stock !== undefined) ? p.stock : '0')+'</strong></p>'+ 
            '<button class="btn-add" data-id="'+encodeURIComponent(p.id)+'" onclick="event.stopPropagation()">Ajouter au panier</button></div>';
          grid.appendChild(c);
        });
        if (!list.length) grid.innerHTML='<p style="padding:20px;color:#aaa">Aucun produit.</p>';
      })
      .catch(function(){ grid.innerHTML='<p style="padding:20px;color:#aaa">Erreur de chargement. Réessayez plus tard.</p>'; });

  });
});

// Formulaire d'assistance
var helpForm = document.getElementById('helpForm');
if (helpForm) {
  var helpMessageBox = document.getElementById('helpFormMessage');
  function showHelpFormMessage(text, type) {
    if (!helpMessageBox) return;
    helpMessageBox.textContent = text;
    helpMessageBox.className = 'help-form-message ' + (type === 'success' ? 'success' : 'error');
  }

  helpForm.addEventListener('submit', function(e) {
    e.preventDefault();
    var msg   = document.getElementById('helpMessage').value.trim();
    var phone = document.getElementById('helpPhone').value.trim();
    var email = document.getElementById('helpEmail').value.trim();

    if (!msg || !phone || !email) {
      showHelpFormMessage('Veuillez remplir tous les champs du formulaire.', 'error');
      return;
    }

    showHelpFormMessage('Données envoyées avec succès ! Nous vous contacterons bientôt.', 'success');

    var subject = encodeURIComponent('Demande d\'assistance KF Tech');
    var body = encodeURIComponent(
      'Bonjour KF Tech,%0D%0A%0D%0A' +
      'J\'ai besoin d\'aide avec :%0D%0A' + msg + '%0D%0A%0D%0A' +
      'Mon numéro WhatsApp : ' + phone + '%0D%0A' +
      'Mon email : ' + email + '%0D%0A%0D%0A' +
      'Merci.'
    );

    window.location.href = 'mailto:contact@kftech237.com?subject=' + subject + '&body=' + body;
  });
}

// Hamburger
// (function(){
//   var btn=document.querySelector('.nav-menu-btn'), links=document.querySelector('.nav-links');
//   if (!btn||!links) return;
//   btn.addEventListener('click', function(){ var o=links.classList.toggle('open'); btn.innerHTML=o?'<i class="fas fa-times"></i>':'<i class="fas fa-bars"></i>'; });
//   document.addEventListener('click', function(e){ if(!btn.contains(e.target)&&!links.contains(e.target)){ links.classList.remove('open'); btn.innerHTML='<i class="fas fa-bars"></i>'; } });
// })();

// Voir Plus scroll
var heroBtnVoir = document.getElementById('heroBtnVoir');
if (heroBtnVoir) heroBtnVoir.addEventListener('click', function(e){ e.preventDefault(); var s=document.getElementById('trending-section'); if(s) s.scrollIntoView({behavior:'smooth',block:'start'}); });

// Panier complet
(function(){
  function getCart(){ return JSON.parse(localStorage.getItem('kftech_cart')||'[]'); }
  function saveCart(c){ localStorage.setItem('kftech_cart', JSON.stringify(c)); }

  function renderCart(){
    var c=getCart(), badge=document.getElementById('cartBadge'), countEl=document.getElementById('cartCount'),
        totalEl=document.getElementById('cartTotal'), itemsEl=document.getElementById('cartItems');
    var t=c.reduce(function(s,i){ return s+i.qty; },0);
    var p=c.reduce(function(s,i){ return s+i.prix*i.qty; },0);
    if(badge) badge.textContent=t;
    if(countEl) countEl.textContent='('+t+')';
    if(totalEl) totalEl.textContent='XAF '+p.toLocaleString('fr-FR');
    if(!itemsEl) return;
    if(!c.length){ itemsEl.innerHTML='<p class="empty">Votre panier est vide.</p>'; return; }
    itemsEl.innerHTML=c.map(function(item,idx){
      return '<div class="cart-item"><img src="'+item.image+'" alt="'+item.nom+'"/>'+
        '<div class="ci-info"><p>'+item.nom+'</p><span>XAF '+(item.prix*item.qty).toLocaleString('fr-FR')+' (x'+item.qty+')</span></div>'+
        '<button class="ci-remove" data-idx="'+idx+'"><i class="fas fa-trash"></i></button></div>';
    }).join('');
    itemsEl.querySelectorAll('.ci-remove').forEach(function(btn){
      btn.addEventListener('click', function(){ var c2=getCart(); c2.splice(parseInt(btn.dataset.idx),1); saveCart(c2); renderCart(); });
    });
  }
  renderCart();

  var cartBtn=document.getElementById('cartBtn'), drawer=document.getElementById('cartDrawer'),
      overlay=document.getElementById('cartOverlay'), closeBtn=document.getElementById('closeCart');
  function openDr(){ drawer.classList.add('open'); overlay.classList.add('show'); document.body.style.overflow='hidden'; renderCart(); }
  function closeDr(){ drawer.classList.remove('open'); overlay.classList.remove('show'); document.body.style.overflow=''; }
  if(cartBtn) cartBtn.addEventListener('click', function(e){ e.preventDefault(); openDr(); });
  if(closeBtn) closeBtn.addEventListener('click', closeDr);
  if(overlay) overlay.addEventListener('click', function(e){ if(e.target===overlay) closeDr(); });

  document.addEventListener('click', function(e){
    var btn=e.target.closest('.btn-add'); if(!btn) return; e.stopPropagation();
    var id=btn.dataset.id;
    fetch('api/produits.php?action=single&id='+id).then(function(r){ return r.json(); }).then(function(p){
      if(!p||!p.id) return;
      var c=getCart(), ex=c.find(function(x){ return x.id==p.id; });
      if(ex) ex.qty++; else c.push({id:p.id,nom:p.nom,prix:parseFloat(p.prix),image:p.image,qty:1});
      saveCart(c); renderCart();
      btn.textContent='Ajoute !'; btn.style.background='#2ecc71';
      setTimeout(function(){ btn.textContent='Ajouter au panier'; btn.style.background=''; },1500);
      var t=document.createElement('div'); t.className='toast success'; t.textContent='"'+p.nom+'" ajoute au panier !';
      document.body.appendChild(t); setTimeout(function(){ t.classList.add('show'); },10);
      setTimeout(function(){ t.classList.remove('show'); setTimeout(function(){ t.remove(); },400); },3000);
    });
  });

  var backBtn=document.getElementById('backTop');
  window.addEventListener('scroll', function(){ if(backBtn) backBtn.classList.toggle('show', window.scrollY>400); });
  if(backBtn) backBtn.addEventListener('click', function(e){ e.preventDefault(); window.scrollTo({top:0,behavior:'smooth'}); });
  window.addEventListener('scroll', function(){ var nav=document.getElementById('mainNav'); if(nav) nav.style.boxShadow=window.scrollY>100?'0 4px 12px rgba(0,0,0,.15)':'none'; });

  function moveFeaturesOnMobile() {
    var features = document.querySelector('.features');
    var help = document.querySelector('.help-section');
    if (!features || !help) return;
    var originalParent = features.__originalParent || (features.__originalParent = features.parentNode);
    var originalNext = features.__originalNext || (features.__originalNext = features.nextElementSibling);
    if (window.innerWidth <= 900) {
      if (help.previousElementSibling !== features) {
        help.parentNode.insertBefore(features, help);
      }
    } else if (originalParent) {
      if (originalParent !== features.parentNode) {
        if (originalNext && originalNext.parentNode === originalParent) {
          originalParent.insertBefore(features, originalNext);
        } else {
          originalParent.appendChild(features);
        }
      }
    }
  }
  moveFeaturesOnMobile();
  window.addEventListener('resize', moveFeaturesOnMobile);

  // Toast déconnexion
  var urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('msg') === 'deconnecte') {
    setTimeout(function() {
      var t = document.createElement('div');
      t.className = 'toast success';
      t.textContent = 'Vous avez été déconnecté avec succès.';
      document.body.appendChild(t);
      setTimeout(function(){ t.classList.add('show'); }, 10);
      setTimeout(function(){ t.classList.remove('show'); setTimeout(function(){ t.remove(); }, 400); }, 4000);
    }, 300);
    // Nettoyer l'URL sans recharger
    history.replaceState(null, '', 'index.php');
  }

})();

document.addEventListener('DOMContentLoaded', function() {
  var helpToggle = document.getElementById('helpToggle');
  var helpSection = document.querySelector('.help-section');
  if (helpToggle && helpSection) {
    helpToggle.addEventListener('click', function(e) {
      e.preventDefault();
      var isOpen = helpSection.classList.toggle('open');
      helpToggle.setAttribute('aria-expanded', isOpen);
      helpToggle.textContent = isOpen ? 'Fermer l\'aide' : 'Besoin d\'aide ?';
    });
  }
});
</script>

<!-- SECTION ASSISTANCE -->
<section class="help-section pad">
  <div class="container">
    <button id="helpToggle" class="help-toggle" type="button" aria-expanded="false">Besoin d'aide ?</button>
  </div>
  <div class="container help-grid">
    <div class="help-info">
      <span class="section-label">Besoin d'aide ?</span>
      <h2>Nous sommes prêts à vous aider</h2>
      <p>Envoyez-nous votre problème, votre adresse email et votre numéro WhatsApp. Notre équipe vous répondra par email ou par WhatsApp dès que possible.</p>
      <div class="help-contact">
        <div><strong>Email :</strong> contact@kftech237.com</div>
        <div><strong>WhatsApp :</strong> +237 6 51 27 16 17</div>
      </div>
    </div>

    <form id="helpForm" class="help-form" novalidate>
      <label for="helpMessage">Votre message *</label>
      <textarea id="helpMessage" placeholder="Décrivez votre souci ici..."></textarea>

      <label for="helpPhone">Numéro WhatsApp *</label>
      <input type="tel" id="helpPhone" placeholder="+237 6 XX XX XX XX" />

      <label for="helpEmail">Email *</label>
      <input type="email" id="helpEmail" placeholder="votre@email.com" />

      <button type="submit" class="btn-primary">Envoyer</button>
      <div id="helpFormMessage" class="help-form-message" aria-live="polite"></div>
    </form>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
