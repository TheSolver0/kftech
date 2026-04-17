<?php // includes/footer.php ?>
<?php
require_once __DIR__ . '/../config/api.php';
$footerCats = apiGet('/categories');
if (!is_array($footerCats)) {
    $footerCats = [];
}
?>
<footer class="footer">
  <div class="container footer-grid">
    <div class="f-brand">
      <a href="index.php" class="logo" style="margin-bottom:16px;display:inline-flex">
        <img src="assets/images/logo.png" alt="KF Tech" class="logo-img"
             onerror="this.style.display='none'"/>
        <span class="logo-kf">KF</span><span class="logo-tech">TECH</span>
      </a>
      <p>Douala, Rond point Deido</p>
      <div class="f-contact">
        <span class="ficon"><i class="fas fa-map-marker-alt"></i></span>Gare routière de Limbe
      </div>
      <div class="f-contact">
        <span class="ficon"><i class="fas fa-phone"></i></span>+237 6 51 27 16 17
      </div>
      <div class="f-contact">
        <span class="ficon"><i class="fas fa-envelope"></i></span>contact@kftech237.com
      </div>
    </div>
    <div class="f-links">
      <h4>Catégories</h4>
      <ul>
        <?php foreach ($footerCats as $cat): ?>
          <li><a href="catalog.php?cat=<?= urlencode($cat['slug'] ?? '') ?>"><?= htmlspecialchars($cat['nom'] ?? 'Catégorie') ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="f-links">
      <h4>Informations</h4>
      <ul>
        <li><a href="service-client.php">Service client</a></li>
        <li><a href="termes.php">Termes &amp; Conditions</a></li>
        <li><a href="apropos.php">À propos</a></li>
        <li><a href="confidentialite.php">Confidentialité</a></li>
      </ul>
    </div>
  </div>

  <div class="footer-bottom">
    <div class="container fb-inner">
      <p>© <?= date('Y') ?> <span>KF-Tech Sarl</span>. Tous droits réservés.</p>
      <div class="socials">
        <a href="https://www.facebook.com/share/1DHfKAHDLW/" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f"></i></a>
        <a href="https://www.tiktok.com/@kf.tech.sarl?_r=1&_t=ZS-95awLw1bF7n" target="_blank" rel="noopener noreferrer"><i class="fab fa-tiktok"></i></a>
        <a href="https://www.instagram.com/kftechsarl?igsh=MWY3NGFrMnRweTIzZg==" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i></a>
      </div>
    </div>
  </div>
</footer>

<script src="assets/js/main.js"></script>

<?php if (isset($extraJs)) echo $extraJs; ?>
</body>
</html>
