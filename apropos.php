<?php
session_start();
require_once __DIR__ . '/config/api.php';

$pageTitle = 'À propos - KF Tech';
$pageDesc  = 'Découvrez KF Tech, votre boutique informatique à Douala.';
include __DIR__ . '/includes/header.php';
?>

<section class="pad">
  <div class="container" style="max-width:900px;">
    <h1>À propos de KF Tech</h1>
    <p>KF Tech est une boutique informatique basée à Douala, spécialisée dans les laptops, smartphones, tablettes et accessoires. Nous proposons des produits de qualité, un service client réactif et un accompagnement personnalisé.</p>
    <div style="display:grid;gap:18px;margin-top:24px;">
      <div style="padding:24px;border:1px solid var(--border);border-radius:12px;background:#fff;">
        <h2>Notre mission</h2>
        <p>Offrir un choix de produits électroniques fiables à des prix compétitifs, tout en garantissant une expérience d’achat simple et sécurisée.</p>
      </div>
      <div style="padding:24px;border:1px solid var(--border);border-radius:12px;background:#fff;">
        <h2>Nos valeurs</h2>
        <p>Qualité, transparence, sécurité et proximité. Nous accompagnons chaque client avec soin et professionnalisme.</p>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>