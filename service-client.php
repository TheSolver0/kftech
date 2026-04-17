<?php
session_start();
require_once __DIR__ . '/config/api.php';

$pageTitle = 'Service client - KF Tech';
$pageDesc  = 'Contactez le service client KF Tech pour toute demande d’aide, commande ou retour.';
include __DIR__ . '/includes/header.php';
?>

<section class="pad">
  <div class="container" style="max-width:900px;">
    <h1>Service client</h1>
    <p>Notre équipe est disponible pour vous aider avec vos commandes, retours, réparations et questions sur les produits.</p>
    <div style="display:grid;gap:18px;margin-top:24px;">
      <div style="padding:24px;border:1px solid var(--border);border-radius:12px;background:#fff;">
        <h2>Contact rapide</h2>
        <p>Appelez-nous au <strong>+237 6 51 27 16 17</strong> ou envoyez un message WhatsApp pour une réponse immédiate.</p>
      </div>
      <div style="padding:24px;border:1px solid var(--border);border-radius:12px;background:#fff;">
        <h2>Assistance par email</h2>
        <p>Écrivez-nous à <a href="mailto:contact@kftech237.com">contact@kftech237.com</a> pour toute demande commerciale, garantie ou suivi de commande.</p>
      </div>
      <div style="padding:24px;border:1px solid var(--border);border-radius:12px;background:#fff;">
        <h2>Heures d’ouverture</h2>
        <p>Du lundi au samedi, de 8h à 19h. Nous répondons rapidement aux messages et appels.</p>
      </div>
    </div>
    <div style="margin-top:30px;padding:24px;border:1px solid var(--border);border-radius:12px;background:var(--light-gray);">
      <h3>Questions fréquentes</h3>
      <ul style="margin-top:12px;list-style:disc;padding-left:20px;color:#444;">
        <li>Comment suivre ma commande ?</li>
        <li>Où puis-je consulter ma facture ?</li>
        <li>Comment retourner un produit ?</li>
      </ul>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>