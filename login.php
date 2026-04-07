<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>KF Tech - Connexion</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700;800&family=Barlow+Condensed:wght@700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    /* === repris de login.html original === */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root { --orange:#f26522; --orange-dark:#d4541a; --black:#1a1a1a; --border:#e8e8e8; --gray:#888; }
    body { font-family:'Barlow',sans-serif; min-height:100vh; display:flex; background:#0f0f0f; overflow:hidden; }

    .left-panel {
      flex:1; background:linear-gradient(145deg,#f26522 0%,#e8531a 40%,#c4410e 100%);
      display:flex; flex-direction:column; justify-content:space-between;
      padding:40px 48px; position:relative; overflow:hidden;
    }
    .left-panel::before { content:''; position:absolute; width:420px; height:420px; background:rgba(255,255,255,.08); border-radius:50%; top:-120px; left:-100px; }
    .left-panel::after  { content:''; position:absolute; width:320px; height:320px; background:rgba(255,255,255,.06); border-radius:50%; bottom:-80px; right:-80px; }

    .panel-logo { display:flex; align-items:center; gap:10px; text-decoration:none; }
    .panel-logo img { height:44px; object-fit:contain; filter:brightness(0) invert(1); }
    .logo-kf   { color:#fff; font-family:'Barlow Condensed',sans-serif; font-size:34px; font-weight:800; }
    .logo-tech { color:rgba(255,255,255,.85); font-family:'Barlow Condensed',sans-serif; font-size:34px; font-weight:800; }

    .panel-content { position:relative; z-index:1; }
    .panel-content h1 { font-family:'Barlow Condensed',sans-serif; font-size:52px; font-weight:800; color:#fff; line-height:1.05; margin-bottom:16px; }
    .panel-content p { color:rgba(255,255,255,.8); font-size:15px; line-height:1.7; max-width:380px; margin-bottom:32px; }
    .features-list { list-style:none; display:flex; flex-direction:column; gap:14px; }
    .features-list li { display:flex; align-items:center; gap:12px; color:rgba(255,255,255,.9); font-size:14px; }
    .feat-icon { width:38px; height:38px; background:rgba(255,255,255,.2); border:1px solid rgba(255,255,255,.3); border-radius:8px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:15px; flex-shrink:0; }
    .panel-footer { position:relative; z-index:1; }
    .panel-footer p { color:rgba(255,255,255,.6); font-size:12px; }
    .panel-footer span { color:#fff; font-weight:700; }

    .right-panel { width:480px; background:#fff; display:flex; flex-direction:column; justify-content:center; padding:48px 44px; overflow-y:auto; }
    .back-link { display:inline-flex; align-items:center; gap:6px; color:#888; font-size:13px; text-decoration:none; margin-bottom:32px; transition:color .2s; }
    .back-link:hover { color:var(--orange); }
    .auth-tabs { display:flex; border-bottom:2px solid var(--border); margin-bottom:28px; }
    .auth-tab { flex:1; background:none; border:none; padding:12px; font-size:15px; font-weight:700; font-family:'Barlow',sans-serif; color:#bbb; cursor:pointer; border-bottom:3px solid transparent; margin-bottom:-2px; transition:all .2s; }
    .auth-tab.active { color:var(--orange); border-bottom-color:var(--orange); }
    .form-title { font-size:26px; font-weight:800; color:var(--black); margin-bottom:4px; }
    .form-sub   { font-size:13px; color:var(--gray); margin-bottom:24px; }
    .form-panel { display:none; }
    .form-panel.active { display:block; }
    .form-row-2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
    .f-group { margin-bottom:16px; }
    .f-group label { display:block; font-size:11px; font-weight:700; color:#555; margin-bottom:6px; text-transform:uppercase; letter-spacing:.5px; }
    .f-group input { width:100%; height:46px; border:1.5px solid var(--border); border-radius:8px; padding:0 14px; font-size:14px; font-family:'Barlow',sans-serif; outline:none; transition:border-color .2s,box-shadow .2s; color:var(--black); }
    .f-group input:focus { border-color:var(--orange); box-shadow:0 0 0 3px rgba(242,101,34,.1); }
    .f-group input.error { border-color:#e63946; }
    .pass-wrap { position:relative; }
    .pass-wrap input { padding-right:44px; }
    .eye-btn { position:absolute; right:13px; top:50%; transform:translateY(-50%); background:none; border:none; color:#bbb; cursor:pointer; font-size:15px; }
    .eye-btn:hover { color:var(--orange); }
    .form-extras { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; font-size:13px; }
    .check-label { display:flex; align-items:center; gap:7px; color:#555; cursor:pointer; }
    .check-label input[type="checkbox"] { accent-color:var(--orange); }
    .forgot-link { color:var(--orange); font-weight:600; text-decoration:none; }
    .btn-submit { width:100%; height:50px; background:var(--orange); color:#fff; border:none; border-radius:8px; font-size:15px; font-weight:700; font-family:'Barlow',sans-serif; cursor:pointer; transition:background .2s; }
    .btn-submit:hover { background:var(--orange-dark); }
    .btn-submit:disabled { background:#ccc; cursor:not-allowed; }
    .pass-strength { margin-top:6px; }
    .strength-bar { height:4px; border-radius:2px; background:var(--border); overflow:hidden; margin-bottom:4px; }
    .strength-fill { height:100%; border-radius:2px; transition:width .3s,background .3s; width:0; }
    .strength-label { font-size:11px; }
    .switch-text { text-align:center; font-size:13px; color:var(--gray); margin-top:16px; }
    .switch-text a { color:var(--orange); font-weight:700; text-decoration:none; }
    .alert { padding:12px 16px; border-radius:8px; font-size:13px; margin-bottom:16px; display:none; }
    .alert.error   { background:#fff0f0; border:1px solid #fcc; color:#c0392b; }
    .alert.success { background:#f0fff4; border:1px solid #9fc; color:#27ae60; }
    .alert.show { display:block; }
    @media (max-width:900px) { .left-panel { display:none; } .right-panel { width:100%; } }
    @media (max-width:480px) { .right-panel { padding:32px 24px; } .form-row-2 { grid-template-columns:1fr; } }
  </style>
</head>
<body>

<div class="left-panel">
  <a href="index.php" class="panel-logo">
    <img src="assets/images/logo.png" alt="KF Tech Logo"/>
    <span class="logo-kf">KF</span><span class="logo-tech">TECH</span>
  </a>
  <div class="panel-content">
    <h1>Bienvenue<br>chez KF Tech</h1>
    <p>Votre boutique informatique de référence à Douala. Les meilleurs produits électroniques au meilleur prix.</p>
    <ul class="features-list">
      <li><div class="feat-icon"><i class="fas fa-truck"></i></div><span>Livraison rapide partout au Cameroun</span></li>
      <li><div class="feat-icon"><i class="fas fa-lock"></i></div><span>Paiement 100% sécurisé</span></li>
      <li><div class="feat-icon"><i class="fas fa-headset"></i></div><span>Support client 24h/7j</span></li>
      <li><div class="feat-icon"><i class="fas fa-shield-alt"></i></div><span>Garantie sur tous nos produits</span></li>
      <li><div class="feat-icon"><i class="fas fa-undo"></i></div><span>Retour facile sous 7 jours</span></li>
    </ul>
  </div>
  <div class="panel-footer"><p>© 2025 <span>KF-Tech Sarl</span>. Tous droits réservés.</p></div>
</div>

<div class="right-panel">
  <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour à la boutique</a>

  <div class="auth-tabs">
    <button class="auth-tab active" id="tabLogin">Connexion</button>
    <button class="auth-tab" id="tabRegister">Inscription</button>
  </div>

  <!-- CONNEXION -->
  <div class="form-panel active" id="panelLogin">
    <h2 class="form-title">Content de vous revoir !</h2>
    <p class="form-sub">Connectez-vous à votre compte KF Tech</p>
    <div class="alert" id="alertLogin"></div>
    <div class="f-group"><label>Adresse Email</label><input type="email" id="loginEmail" placeholder="votre@email.com"/></div>
    <div class="f-group"><label>Mot de passe</label>
      <div class="pass-wrap"><input type="password" id="loginPass" placeholder="••••••••"/><button class="eye-btn" data-target="loginPass"><i class="fas fa-eye"></i></button></div>
    </div>
    <div class="form-extras">
      <label class="check-label"><input type="checkbox"/> Se souvenir de moi</label>
      <a href="#" class="forgot-link">Mot de passe oublié ?</a>
    </div>
    <button class="btn-submit" id="doLogin">Se connecter <i class="fas fa-arrow-right"></i></button>
    <p class="switch-text">Pas encore de compte ? <a href="#" id="toRegister">Créer un compte</a></p>
  </div>

  <!-- INSCRIPTION -->
  <div class="form-panel" id="panelRegister">
    <h2 class="form-title">Créer votre compte</h2>
    <p class="form-sub">Rejoignez KF Tech et profitez de nos offres</p>
    <div class="alert" id="alertRegister"></div>
    <div class="form-row-2">
      <div class="f-group"><label>Prénom</label><input type="text" id="regPrenom" placeholder="Prénom"/></div>
      <div class="f-group"><label>Nom</label><input type="text" id="regNom" placeholder="Nom"/></div>
    </div>
    <div class="f-group"><label>Email</label><input type="email" id="regEmail" placeholder="votre@email.com"/></div>
    <div class="f-group"><label>Téléphone</label><input type="tel" id="regPhone" placeholder="+237 6 XX XX XX XX"/></div>
    <div class="f-group"><label>Mot de passe</label>
      <div class="pass-wrap"><input type="password" id="regPass" placeholder="8 caractères minimum" oninput="checkStrength(this.value)"/><button class="eye-btn" data-target="regPass"><i class="fas fa-eye"></i></button></div>
      <div class="pass-strength"><div class="strength-bar"><div class="strength-fill" id="sFill"></div></div><span class="strength-label" id="sLabel"></span></div>
    </div>
    <div class="f-group"><label>Confirmer le mot de passe</label>
      <div class="pass-wrap"><input type="password" id="regConfirm" placeholder="••••••••"/><button class="eye-btn" data-target="regConfirm"><i class="fas fa-eye"></i></button></div>
    </div>
    <div class="f-group"><label class="check-label" style="font-size:12px;text-transform:none;letter-spacing:0"><input type="checkbox" id="acceptTerms"/> J'accepte les <a href="#" style="color:var(--orange)">conditions d'utilisation</a></label></div>
    <button class="btn-submit" id="doRegister">Créer mon compte <i class="fas fa-arrow-right"></i></button>
    <p class="switch-text">Déjà un compte ? <a href="#" id="toLogin">Se connecter</a></p>
  </div>
</div>

<script>
const AUTH_API = 'api/auth.php';

// ---- TABS ----
function showLogin() {
  document.getElementById('tabLogin').classList.add('active');
  document.getElementById('tabRegister').classList.remove('active');
  document.getElementById('panelLogin').classList.add('active');
  document.getElementById('panelRegister').classList.remove('active');
}
function showRegister() {
  document.getElementById('tabRegister').classList.add('active');
  document.getElementById('tabLogin').classList.remove('active');
  document.getElementById('panelRegister').classList.add('active');
  document.getElementById('panelLogin').classList.remove('active');
}
document.getElementById('tabLogin').addEventListener('click', showLogin);
document.getElementById('tabRegister').addEventListener('click', showRegister);
document.getElementById('toRegister').addEventListener('click', function(e){ e.preventDefault(); showRegister(); });
document.getElementById('toLogin').addEventListener('click', function(e){ e.preventDefault(); showLogin(); });

// ---- ALERT ----
function showAlert(id, msg, type) {
  var el = document.getElementById(id);
  el.innerHTML = msg;
  el.className = 'alert ' + type + ' show';
}

// ---- EYE TOGGLE ----
document.querySelectorAll('.eye-btn').forEach(function(btn) {
  btn.addEventListener('click', function() {
    var inp = document.getElementById(btn.dataset.target);
    var ico = btn.querySelector('i');
    if (inp.type === 'password') { inp.type = 'text'; ico.className = 'fas fa-eye-slash'; }
    else { inp.type = 'password'; ico.className = 'fas fa-eye'; }
  });
});

// ---- PASSWORD STRENGTH ----
function checkStrength(val) {
  var fill = document.getElementById('sFill');
  var label = document.getElementById('sLabel');
  var score = 0;
  if (val.length >= 8) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;
  var levels = [
    {w:'0%',bg:'',txt:''},
    {w:'25%',bg:'#e63946',txt:'Trop faible'},
    {w:'50%',bg:'#f5a623',txt:'Moyen'},
    {w:'75%',bg:'#2ecc71',txt:'Bon'},
    {w:'100%',bg:'#27ae60',txt:'Excellent !'}
  ];
  var l = val.length === 0 ? levels[0] : (levels[score] || levels[1]);
  fill.style.width = l.w; fill.style.background = l.bg;
  label.textContent = l.txt; label.style.color = l.bg;
}

// ---- CONNEXION → API PHP ----
document.getElementById('doLogin').addEventListener('click', function() {
  var email = document.getElementById('loginEmail').value.trim();
  var pass  = document.getElementById('loginPass').value;
  if (!email || !pass) { showAlert('alertLogin','Veuillez remplir tous les champs.','error'); return; }

  var btn = this;
  btn.disabled = true; btn.textContent = 'Connexion...';

  fetch(AUTH_API + '?action=connexion', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({email: email, mot_de_passe: pass})
  })
  .then(function(r){ return r.json(); })
  .then(function(data) {
    if (data.succes) {
      showAlert('alertLogin', data.message, 'success');
      setTimeout(function(){
        var redirect = new URLSearchParams(window.location.search).get('redirect');
        window.location.href = redirect ? redirect + '.php' : 'index.php';
      }, 1200);
    } else {
      // Si pas de compte → proposer l'inscription
      if (data.action === 'inscrire') {
        showAlert('alertLogin',
          data.message + ' <a href="#" onclick="showRegister();return false" style="color:var(--orange);font-weight:700;text-decoration:underline">Créer un compte →</a>',
          'error');
      } else {
        showAlert('alertLogin', data.message || 'Erreur de connexion.', 'error');
      }
      btn.disabled = false; btn.innerHTML = 'Se connecter <i class="fas fa-arrow-right"></i>';
    }
  })
  .catch(function(){ showAlert('alertLogin','Erreur réseau. Vérifiez votre connexion.','error'); btn.disabled=false; btn.innerHTML='Se connecter <i class="fas fa-arrow-right"></i>'; });
});

// ---- INSCRIPTION → API PHP ----
document.getElementById('doRegister').addEventListener('click', function() {
  var prenom  = document.getElementById('regPrenom').value.trim();
  var nom     = document.getElementById('regNom').value.trim();
  var email   = document.getElementById('regEmail').value.trim();
  var phone   = document.getElementById('regPhone').value.trim();
  var pass    = document.getElementById('regPass').value;
  var confirm = document.getElementById('regConfirm').value;
  var terms   = document.getElementById('acceptTerms').checked;

  if (!prenom || !nom || !email || !pass) { showAlert('alertRegister','Remplissez tous les champs obligatoires.','error'); return; }
  if (pass.length < 8) { showAlert('alertRegister','Le mot de passe doit faire au moins 8 caractères.','error'); return; }
  if (pass !== confirm) { showAlert('alertRegister','Les mots de passe ne correspondent pas.','error'); return; }
  if (!terms) { showAlert('alertRegister',"Acceptez les conditions d'utilisation.","error"); return; }

  var btn = this;
  btn.disabled = true; btn.textContent = 'Création...';

  fetch(AUTH_API + '?action=inscription', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({prenom:prenom, nom:nom, email:email, telephone:phone, mot_de_passe:pass})
  })
  .then(function(r){ return r.json(); })
  .then(function(data) {
    if (data.succes) {
      showAlert('alertRegister', data.message, 'success');
      setTimeout(function(){ window.location.href = 'index.php'; }, 1500);
    } else {
      showAlert('alertRegister', data.message || 'Erreur lors de la création.', 'error');
      btn.disabled = false; btn.innerHTML = 'Créer mon compte <i class="fas fa-arrow-right"></i>';
    }
  })
  .catch(function(){ showAlert('alertRegister','Erreur réseau.','error'); btn.disabled=false; btn.innerHTML='Créer mon compte <i class="fas fa-arrow-right"></i>'; });
});

// ENTER
document.addEventListener('keydown', function(e) {
  if (e.key !== 'Enter') return;
  if (document.getElementById('panelLogin').classList.contains('active')) document.getElementById('doLogin').click();
  else document.getElementById('doRegister').click();
});
</script>
</body>
</html>
