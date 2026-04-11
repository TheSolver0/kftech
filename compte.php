<?php
session_start();
require_once __DIR__ . '/config/api.php';
require_once __DIR__ . '/config/db.php';

$user = getCurrentUser();
if (!$user) {
    header('Location: login.php?redirect=compte');
    exit;
}

$pageTitle = 'Mon compte - KF Tech';
$pageDesc  = 'Gérez votre compte client KF Tech.';
include __DIR__ . '/includes/header.php';
?>

<section class="account-page pad">
  <div class="container">
    <div class="account-card">
      <div class="account-card-head">
        <span class="section-label">Mon espace</span>
        <h1>Bonjour <?= htmlspecialchars($user['prenom']) ?></h1>
        <p>Voici les informations enregistrées sur votre compte. Vous pouvez supprimer votre compte à tout moment.</p>
      </div>

      <div class="account-details">
        <div class="account-section">
          <h2>Informations du compte</h2>
          <ul>
            <li><strong>Nom :</strong> <?= htmlspecialchars($user['nom'] ?? 'Non renseigné') ?></li>
            <li><strong>Email :</strong> <?= htmlspecialchars($user['email'] ?? 'Non renseigné') ?></li>
            <li><strong>WhatsApp :</strong> <?= htmlspecialchars($user['telephone'] ?? 'Non renseigné') ?></li>
          </ul>
        </div>

        <div class="account-section account-danger">
          <h2>Supprimer le compte</h2>
          <p>Cette action est irréversible. Toutes vos données seront définitivement supprimées.</p>
          <button id="deleteAccountBtn" class="btn-outline">Supprimer mon compte</button>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
(function() {
  var btn = document.getElementById('deleteAccountBtn');
  if (!btn) return;
  btn.addEventListener('click', function() {
    if (!confirm('Voulez-vous vraiment supprimer votre compte ? Cette action est irréversible.')) {
      return;
    }

    fetch('api/auth.php?action=delete_account', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({})
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.succes) {
        alert(data.message || 'Compte supprimé.');
        window.location.href = 'index.php';
      } else {
        alert(data.message || 'Impossible de supprimer le compte.');
      }
    })
    .catch(function() {
      alert('Erreur réseau. Réessayez plus tard.');
    });
  });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
