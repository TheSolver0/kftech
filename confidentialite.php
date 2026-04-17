<?php
session_start();
require_once __DIR__ . '/config/api.php';

$pageTitle = 'Confidentialité - KF Tech';
$pageDesc  = 'Politique de confidentialité et protection des données chez KF Tech.';
include __DIR__ . '/includes/header.php';
?>

<section class="pad">
  <div class="container" style="max-width:900px;">
    <h1>Politique de confidentialité</h1>
    <p>Chez KF Tech, nous respectons votre vie privée. Les données personnelles collectées sont réservées à l’usage des services de commande, de livraison et de contact client.</p>
    <div style="margin-top:24px;">
      <h2>Collecte des données</h2>
      <p>Nous collectons uniquement les informations nécessaires pour traiter vos commandes et répondre à vos demandes.</p>
      <h2 style="margin-top:20px;">Sécurité</h2>
      <p>Nous protégeons vos données avec des mesures techniques et organisationnelles adaptées pour prévenir tout accès non autorisé.</p>
      <h2 style="margin-top:20px;">Partage</h2>
      <p>Nous ne partageons pas vos données personnelles avec des tiers commerciaux sans votre consentement, sauf pour la livraison et le traitement des commandes.</p>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>