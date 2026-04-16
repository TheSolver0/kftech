<?php // includes/footer.php ?>
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
        <li><a href="catalog.php?cat=laptops">Laptops &amp; Desktops</a></li>
        <li><a href="catalog.php?cat=smartphones">Smartphones</a></li>
        <li><a href="catalog.php?cat=tablettes">Tablettes</a></li>
        <li><a href="catalog.php?cat=gaming">Gaming &amp; Fun</a></li>
        <li><a href="catalog.php?cat=tv-audio">TV &amp; Audio</a></li>
        <li><a href="catalog.php?cat=accessoires">Accessoires</a></li>
      </ul>
    </div>
    <div class="f-links">
      <h4>Informations</h4>
      <ul>
        <li><a href="#">Service client</a></li>
        <li><a href="#">Termes &amp; Conditions</a></li>
        <li><a href="#">À propos</a></li>
        <li><a href="#">Confidentialité</a></li>
      </ul>
    </div>
  </div>

  <div class="footer-bottom">
    <div class="container fb-inner">
      <p>© <?= date('Y') ?> <span>KF-Tech Sarl</span>. Tous droits réservés.</p>
      <div class="socials">
        <a href="#"><i class="fab fa-facebook-f"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-youtube"></i></a>
        <a href="#"><i class="fab fa-pinterest-p"></i></a>
      </div>
    </div>
  </div>
</footer>

<script src="assets/js/main.js"></script>

<?php if (isset($extraJs)) echo $extraJs; ?>
</body>
</html>
