<?php
session_start();
require_once __DIR__ . '/config/api.php';

$pageTitle = 'Termes et Conditions - KF Tech';
$pageDesc  = 'Termes et conditions générales d’utilisation et de vente chez KF Tech.';
include __DIR__ . '/includes/header.php';
?>

<section class="pad">
  <div class="container" style="max-width:900px;">
    <h1>Termes et Conditions</h1>
    <p>Bienvenue sur KF Tech. En utilisant notre site, vous acceptez nos conditions de vente et d’utilisation.</p>
    <div style="margin-top:24px;">
      <h2>1. Commandes</h2>
      <p>Toutes les commandes sont soumises à disponibilité et à validation par notre équipe. Les prix sont indiqués en XAF et peuvent être modifiés sans préavis.</p>
      <h2 style="margin-top:20px;">2. Livraison</h2>
      <p>Les délais de livraison sont estimatifs. Nous faisons de notre mieux pour livrer rapidement, mais des retards peuvent survenir.</p>
      <h2 style="margin-top:20px;">3. Retours et garanties</h2>
      <p>Vous pouvez retourner un produit sous 7 jours si celui-ci est dans son état d’origine. Les produits sont couverts par une garantie constructeur lorsque cela est indiqué.</p>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>