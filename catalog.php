<?php
session_start();
require_once __DIR__ . '/config/api.php';

// ---- PARAMÈTRES ----
$catSlug = trim($_GET['cat']    ?? '');
$q       = trim($_GET['q']      ?? '');
$marque  = trim($_GET['marque'] ?? '');
$tri     = $_GET['tri']         ?? 'recent';
$page    = max(1, (int)($_GET['page'] ?? 1));
$pmin    = (int)($_GET['pmin']  ?? 0) ?: null;
$pmax    = (int)($_GET['pmax']  ?? 0) ?: null;
$limit   = 12;

// ============================================================
// DONNÉES VIA L'API .NET
// ============================================================

// ---- CATÉGORIES (sidebar + catégorie courante) ----
$allCats = apiGet('/categories');

$catInfo = null;
if ($catSlug) {
    foreach ($allCats as $c) {
        if ($c['slug'] === $catSlug) {
            $catInfo = $c;
            break;
        }
    }
}

// ---- PRODUITS + MARQUES + PAGINATION ----
$params = array_filter([
    'cat'    => $catSlug ?: null,
    'q'      => $q       ?: null,
    'marque' => $marque  ?: null,
    'pmin'   => $pmin,
    'pmax'   => $pmax,
    'tri'    => $tri,
    'page'   => $page,
    'limit'  => $limit,
], fn($v) => $v !== null && $v !== '');

$qs       = $params ? '?' . http_build_query($params) : '';
$data     = apiGet('/products' . $qs);
$produits   = $data['produits']  ?? [];
$total      = $data['total']     ?? 0;
$totalPages = $data['pages']     ?? 1;
$allMarques = $data['marques']   ?? [];

// ---- PAGE TITLE ----
if ($q)           $pageTitle = "Résultats pour \"$q\" - KF Tech";
elseif ($catInfo) $pageTitle = $catInfo['nom'] . " - KF Tech";
else              $pageTitle = "Catalogue - KF Tech";

$activeCat = $catSlug;

function buildUrl(array $extra = []): string {
    $base   = array_filter($_GET, fn($k) => $k !== 'page', ARRAY_FILTER_USE_KEY);
    $merged = array_merge($base, $extra);
    return 'catalog.php?' . http_build_query($merged);
}

include __DIR__ . '/includes/header.php';
?>
<style>
/* ===== CATALOG PAGE ===== */
.catalog-hero {
  background: linear-gradient(135deg, #f5f5f5 0%, #ffe8d6 100%);
  padding: 28px 0;
  border-bottom: 1px solid var(--border);
}
.catalog-hero h1 { font-family:'Barlow Condensed',sans-serif; font-size:32px; font-weight:800; color:var(--black); }
.catalog-hero h1 span { color:var(--orange); }
.breadcrumb-bar { font-size:13px; color:#888; margin-top:6px; }
.breadcrumb-bar a { color:#888; text-decoration:none; }
.breadcrumb-bar a:hover { color:var(--orange); }
.breadcrumb-bar span { margin:0 6px; }

.catalog-layout { display:grid; grid-template-columns:240px 1fr; gap:28px; padding:36px 0 60px; }

/* Sidebar */
.cat-sidebar-full { position:sticky; top:140px; align-self:start; }
.sidebar-box { border:1px solid var(--border); border-radius:10px; background:#fff; padding:20px; margin-bottom:16px; }
.sidebar-box h4 { font-size:14px; font-weight:800; color:var(--black); margin-bottom:14px; padding-bottom:8px; border-bottom:2px solid var(--orange); display:inline-block; }
.filter-cat-list { list-style:none; }
.filter-cat-item a {
  display:flex; align-items:center; justify-content:space-between;
  padding:9px 10px; border-radius:6px;
  font-size:13px; color:#555; text-decoration:none;
  transition:all .2s;
}
.filter-cat-item a:hover { background:var(--orange-light); color:var(--orange); }
.filter-cat-item a.active { background:var(--orange); color:#fff; font-weight:700; }
.filter-cat-item a .cnt { font-size:11px; opacity:.8; }
.filter-cat-item i { margin-right:8px; font-size:13px; }

.marque-list { display:flex; flex-direction:column; gap:6px; }
.marque-item { display:flex; align-items:center; gap:8px; font-size:13px; color:#555; cursor:pointer; }
.marque-item input { accent-color:var(--orange); }
.marque-item:hover { color:var(--orange); }

.prix-range { display:flex; gap:8px; align-items:center; margin-top:8px; }
.prix-range input { width:90px; height:36px; border:1px solid var(--border); border-radius:6px; padding:0 8px; font-size:13px; outline:none; }
.prix-range input:focus { border-color:var(--orange); }
.btn-filter-apply { width:100%; height:38px; background:var(--orange); color:#fff; border:none; border-radius:6px; font-size:13px; font-weight:700; cursor:pointer; margin-top:10px; transition:background .2s; font-family:'Barlow',sans-serif; }
.btn-filter-apply:hover { background:var(--orange-dark); }
.btn-reset { width:100%; height:34px; background:#fff; color:#888; border:1px solid var(--border); border-radius:6px; font-size:12px; cursor:pointer; margin-top:6px; font-family:'Barlow',sans-serif; }

/* Toolbar */
.catalog-toolbar {
  display:flex; justify-content:space-between; align-items:center;
  margin-bottom:20px; flex-wrap:wrap; gap:10px;
}
.results-count { font-size:14px; color:#666; }
.results-count strong { color:var(--black); }
.tri-select { padding:8px 14px; border:1px solid var(--border); border-radius:6px; font-size:13px; font-family:'Barlow',sans-serif; outline:none; cursor:pointer; }
.tri-select:focus { border-color:var(--orange); }

/* Recherche active badge */
.search-badge { display:inline-flex; align-items:center; gap:6px; background:var(--orange-light); border:1px solid var(--orange); color:var(--orange); padding:6px 12px; border-radius:20px; font-size:13px; font-weight:600; margin-bottom:16px; }
.search-badge a { color:var(--orange); margin-left:6px; font-size:16px; line-height:1; }

/* Grid produits */
.catalog-grid { display:grid; grid-template-columns:repeat(3, 1fr); gap:18px; }

/* Carte produit (réutilise .prod-card du style.css) */
.prod-card { cursor:pointer; }

/* Pas de résultats */
.no-results { text-align:center; padding:60px 20px; color:#aaa; }
.no-results i { font-size:48px; margin-bottom:16px; display:block; }
.no-results p { font-size:15px; margin-bottom:20px; }

/* Pagination */
.pagination { display:flex; justify-content:center; gap:6px; margin-top:40px; flex-wrap:wrap; }
.page-btn {
  width:38px; height:38px; border:1px solid var(--border); border-radius:6px;
  background:#fff; color:#555; font-size:14px; font-weight:600;
  display:flex; align-items:center; justify-content:center;
  text-decoration:none; transition:all .2s;
  font-family:'Barlow',sans-serif;
}
.page-btn:hover { border-color:var(--orange); color:var(--orange); }
.page-btn.active { background:var(--orange); color:#fff; border-color:var(--orange); }
.page-btn.disabled { opacity:.4; pointer-events:none; }

@media (max-width:900px) {
  .catalog-layout { grid-template-columns:1fr; }
  .cat-sidebar-full { position:static; display:grid; grid-template-columns:1fr 1fr; gap:12px; }
}
@media (max-width:600px) {
  .catalog-grid { grid-template-columns:repeat(2,1fr); gap:12px; }
  .cat-sidebar-full { grid-template-columns:1fr; }
}
</style>

<!-- HERO CATALOG -->
<div class="catalog-hero">
  <div class="container">
    <h1>
      <?php if ($q): ?>
        Résultats pour <span>"<?= htmlspecialchars($q) ?>"</span>
      <?php elseif ($catInfo): ?>
        <i class="<?= $catInfo['icone'] ?>" style="color:var(--orange);margin-right:10px"></i><?= htmlspecialchars($catInfo['nom']) ?>
      <?php else: ?>
        Tout notre <span>Catalogue</span>
      <?php endif; ?>
    </h1>
    <div class="breadcrumb-bar">
      <a href="index.php">Accueil</a><span>›</span>
      <a href="catalog.php">Catalogue</a>
      <?php if ($catInfo): ?>
        <span>›</span><strong><?= htmlspecialchars($catInfo['nom']) ?></strong>
      <?php endif; ?>
      <?php if ($q): ?>
        <span>›</span><strong>Recherche : <?= htmlspecialchars($q) ?></strong>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- LAYOUT PRINCIPAL -->
<div class="container">
  <div class="catalog-layout">

    <!-- SIDEBAR FILTRES -->
    <aside class="cat-sidebar-full">

      <!-- Catégories -->
      <div class="sidebar-box">
        <h4>Catégories</h4>
        <ul class="filter-cat-list">
          <li class="filter-cat-item">
            <a href="catalog.php" class="<?= !$catSlug ? 'active' : '' ?>">
              <span><i class="fas fa-th-large"></i> Toutes les catégories</span>
              <span class="cnt">(<?= array_sum(array_column($allCats, 'nb_produits')) ?>)</span>
            </a>
          </li>
          <?php foreach ($allCats as $c): ?>
          <li class="filter-cat-item">
            <a href="catalog.php?cat=<?= urlencode($c['slug']) ?><?= $q ? '&q='.urlencode($q) : '' ?>"
               class="<?= $catSlug === $c['slug'] ? 'active' : '' ?>">
              <span><i class="<?= categoryIconClass($c) ?>"></i> <?= htmlspecialchars($c['nom']) ?></span>
              <span class="cnt">(<?= intval($c['nb_produits'] ?? 0) ?>)</span>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Marques -->
      <?php if (!empty($allMarques)): ?>
      <div class="sidebar-box">
        <h4>Marques</h4>
        <form method="GET" action="catalog.php">
          <?php if ($catSlug): ?><input type="hidden" name="cat" value="<?= htmlspecialchars($catSlug) ?>"><?php endif; ?>
          <?php if ($q): ?><input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>"><?php endif; ?>
          <div class="marque-list">
            <?php foreach ($allMarques as $m): ?>
              <label class="marque-item">
                <input type="checkbox" name="marque" value="<?= htmlspecialchars($m) ?>"
                       <?= $marque === $m ? 'checked' : '' ?>
                       onchange="this.form.submit()"/>
                <?= htmlspecialchars($m) ?>
              </label>
            <?php endforeach; ?>
          </div>
        </form>
      </div>
      <?php endif; ?>

      <!-- Fourchette de prix -->
      <div class="sidebar-box">
        <h4>Prix (XAF)</h4>
        <form method="GET" action="catalog.php" id="prixForm">
          <?php if ($catSlug): ?><input type="hidden" name="cat" value="<?= htmlspecialchars($catSlug) ?>"><?php endif; ?>
          <?php if ($q): ?><input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>"><?php endif; ?>
          <?php if ($marque): ?><input type="hidden" name="marque" value="<?= htmlspecialchars($marque) ?>"><?php endif; ?>
          <div class="prix-range">
            <input type="number" name="pmin" placeholder="Min" value="<?= (int)($_GET['pmin'] ?? '') ?: '' ?>" min="0"/>
            <span style="color:#aaa">—</span>
            <input type="number" name="pmax" placeholder="Max" value="<?= (int)($_GET['pmax'] ?? '') ?: '' ?>" min="0"/>
          </div>
          <button type="submit" class="btn-filter-apply">Appliquer</button>
          <button type="button" class="btn-reset" onclick="window.location='catalog.php<?= $catSlug ? '?cat='.$catSlug : '' ?>'">Réinitialiser</button>
        </form>
      </div>

    </aside>

    <!-- PRODUITS -->
    <div class="catalog-main">

      <!-- Badge recherche -->
      <?php if ($q): ?>
        <div class="search-badge">
          <i class="fas fa-search"></i> "<?= htmlspecialchars($q) ?>"
          <a href="catalog.php<?= $catSlug ? '?cat='.$catSlug : '' ?>" title="Effacer la recherche">×</a>
        </div>
      <?php endif; ?>

      <!-- Toolbar -->
      <div class="catalog-toolbar">
        <div class="results-count">
          <strong><?= $total ?></strong> produit<?= $total > 1 ? 's' : '' ?> trouvé<?= $total > 1 ? 's' : '' ?>
          <?php if ($page > 1): ?> — page <?= $page ?>/<?= $totalPages ?><?php endif; ?>
        </div>
        <form method="GET" style="display:inline">
          <?php foreach ($_GET as $k => $v): if ($k === 'tri') continue; ?>
            <input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars($v) ?>"/>
          <?php endforeach; ?>
          <select name="tri" class="tri-select" onchange="this.form.submit()">
            <option value="recent"    <?= $tri==='recent'    ? 'selected' : '' ?>>Plus récents</option>
            <option value="prix_asc"  <?= $tri==='prix_asc'  ? 'selected' : '' ?>>Prix croissant</option>
            <option value="prix_desc" <?= $tri==='prix_desc' ? 'selected' : '' ?>>Prix décroissant</option>
            <option value="note"      <?= $tri==='note'      ? 'selected' : '' ?>>Mieux notés</option>
            <option value="promo"     <?= $tri==='promo'     ? 'selected' : '' ?>>Meilleures promos</option>
          </select>
        </form>
      </div>

      <!-- GRILLE -->
      <?php if (empty($produits)): ?>
        <div class="no-results">
          <i class="fas fa-search"></i>
          <p>Aucun produit trouvé<?= $q ? " pour \"$q\"" : '' ?>.</p>
          <a href="catalog.php" class="btn-primary">Voir tout le catalogue</a>
        </div>
      <?php else: ?>
        <div class="catalog-grid">
          <?php foreach ($produits as $p):
            $disc = ($p['ancien_prix'] && $p['ancien_prix'] > $p['prix'])
                    ? round((1 - $p['prix']/$p['ancien_prix'])*100) : 0;
          ?>
          <div class="prod-card" onclick="window.location='product.php?id=<?= $p['id'] ?>'">
            <span class="prod-badge"><?= htmlspecialchars($p['badge']) ?></span>
            <?php if ($disc): ?>
              <span class="prod-disc">-<?= $disc ?>%</span>
            <?php endif; ?>
            <div class="prod-img">
              <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['nom']) ?>" loading="lazy"/>
            </div>
            <div class="prod-info">
              <div class="prod-stars">
                <?= str_repeat('★', (int)round($p['note'])) ?><?= str_repeat('☆', 5-(int)round($p['note'])) ?>
                <span>(<?= $p['nb_avis'] ?>)</span>
              </div>
              <p class="prod-name"><?= htmlspecialchars($p['nom']) ?></p>
              <div class="prod-prices">
                <span class="prod-price-new">XAF <?= number_format($p['prix'], 0, '.', ' ') ?></span>
                <?php if ($p['ancien_prix']): ?>
                  <span class="prod-price-old">XAF <?= number_format($p['ancien_prix'], 0, '.', ' ') ?></span>
                <?php endif; ?>
              </div>
              <p class="prod-avail">Stock : <strong><?= $p['stock'] ?></strong></p>
              <div style="display:flex;gap:8px">
                <button class="btn-add" data-id="<?= $p['id'] ?>" style="flex:1">
                  Ajouter au panier
                </button>
                <button class="btn-wish" data-wish-id="<?= $p['id'] ?>"
                        style="width:40px;height:40px;border:1.5px solid var(--border);background:#fff;border-radius:6px;font-size:15px;color:#bbb;cursor:pointer;flex-shrink:0;transition:all .2s"
                        title="Ajouter aux favoris">
                  <i class="fas fa-heart"></i>
                </button>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- PAGINATION -->
        <?php if ($totalPages > 1): ?>
          <div class="pagination">
            <!-- Précédent -->
            <a href="<?= buildUrl(['page' => $page-1]) ?>"
               class="page-btn <?= $page <= 1 ? 'disabled' : '' ?>">
              <i class="fas fa-chevron-left"></i>
            </a>

            <?php
            // Afficher max 7 boutons
            $start = max(1, $page - 3);
            $end   = min($totalPages, $page + 3);
            if ($start > 1): ?>
              <a href="<?= buildUrl(['page'=>1]) ?>" class="page-btn">1</a>
              <?php if ($start > 2): ?><span class="page-btn" style="border:none;color:#aaa">…</span><?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
              <a href="<?= buildUrl(['page'=>$i]) ?>"
                 class="page-btn <?= $i===$page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($end < $totalPages): ?>
              <?php if ($end < $totalPages-1): ?><span class="page-btn" style="border:none;color:#aaa">…</span><?php endif; ?>
              <a href="<?= buildUrl(['page'=>$totalPages]) ?>" class="page-btn"><?= $totalPages ?></a>
            <?php endif; ?>

            <!-- Suivant -->
            <a href="<?= buildUrl(['page' => $page+1]) ?>"
               class="page-btn <?= $page >= $totalPages ? 'disabled' : '' ?>">
              <i class="fas fa-chevron-right"></i>
            </a>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
