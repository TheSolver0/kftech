/* ===== KF TECH - main.js ===== */

var API_BASE = 'api/produits.php';
var AUTH_API = 'api/auth.php';

function getLocalUser() {
  try {
    return JSON.parse(localStorage.getItem('kftech_user') || 'null');
  } catch (e) {
    return null;
  }
}
function setLocalUser(user) {
  if (user && user.id) {
    localStorage.setItem('kftech_user', JSON.stringify(user));
  }
}
function clearLocalUser() {
  localStorage.removeItem('kftech_user');
}
function getStoredFavoris(userId) {
  if (!userId) return [];
  try {
    return JSON.parse(localStorage.getItem('kftech_favoris_' + userId) || '[]');
  } catch (e) {
    return [];
  }
}
function saveStoredFavoris(userId, ids) {
  if (!userId) return;
  localStorage.setItem('kftech_favoris_' + userId, JSON.stringify(ids));
}

// ================================================
// PANIER — stocké en localStorage
// ================================================
function getCart() {
  return JSON.parse(localStorage.getItem('kftech_cart') || '[]');
}
function saveCart(c) {
  localStorage.setItem('kftech_cart', JSON.stringify(c));
}

function getProductFromButton(btn) {
  if (!btn) return null;
  var id = btn.dataset.id;
  var name = btn.dataset.name;
  var price = btn.dataset.price;
  var image = btn.dataset.image;
  if (id && name && price) {
    return {
      id: id,
      nom: name,
      prix: parseFloat(price) || 0,
      image: image || ''
    };
  }
  var card = btn.closest('.prod-card');
  if (!card) return null;
  var title = card.querySelector('.prod-name');
  var priceEl = card.querySelector('.prod-price-new');
  var imgEl = card.querySelector('img');
  var parsedPrice = 0;
  if (priceEl) {
    parsedPrice = parseFloat(priceEl.textContent.replace(/[^0-9]/g, '')) || 0;
  }
  return {
    id: id || btn.dataset.wishId || null,
    nom: title ? title.textContent.trim() : '',
    prix: parsedPrice,
    image: imgEl ? imgEl.src : ''
  };
}

function addToCart(produit, qty) {
  if (!qty) qty = 1;
  var cart = getCart();
  var existing = cart.find(function(x) { return String(x.id) === String(produit.id); });
  if (existing) {
    existing.qty += qty;
  } else {
    cart.push({
      id:    produit.id,
      nom:   produit.nom,
      prix:  parseFloat(produit.prix),
      image: produit.image || '',
      qty:   qty
    });
  }
  saveCart(cart);
  updateCartUI();
  showToast('"' + produit.nom + '" ajouté au panier !', 'success');
}

function updateCartUI() {
  var cart     = getCart();
  var totalQty = cart.reduce(function(s, i) { return s + i.qty; }, 0);
  var totalPrix= cart.reduce(function(s, i) { return s + i.prix * i.qty; }, 0);

  // Badge icône panier
  var badge = document.getElementById('cartBadge');
  if (badge) badge.textContent = totalQty;

  // Compteur dans le drawer
  var countEl = document.getElementById('cartCount');
  if (countEl) countEl.textContent = '(' + totalQty + ')';

  // Total dans le drawer
  var totalEl = document.getElementById('cartTotal');
  if (totalEl) totalEl.textContent = formatPrix(totalPrix);

  // Liste des articles dans le drawer
  var itemsEl = document.getElementById('cartItems');
  if (!itemsEl) return;

  if (!cart.length) {
    itemsEl.innerHTML = '<p class="empty">Votre panier est vide.</p>';
    return;
  }

  itemsEl.innerHTML = cart.map(function(item, idx) {
    return '<div class="cart-item">' +
      '<img src="' + item.image + '" alt="' + item.nom + '" ' +
           'onerror="this.src=\'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2260%22 height=%2260%22><rect fill=%22%23eee%22 width=%2260%22 height=%2260%22/></svg>\'"/>' +
      '<div class="ci-info">' +
        '<p>' + item.nom + '</p>' +
        '<div style="display:flex;align-items:center;gap:8px;margin-top:4px">' +
          '<button class="ci-qty-btn" data-idx="' + idx + '" data-op="minus">−</button>' +
          '<span style="font-weight:700">' + item.qty + '</span>' +
          '<button class="ci-qty-btn" data-idx="' + idx + '" data-op="plus">+</button>' +
          '<span style="color:var(--orange);font-weight:700;margin-left:6px">' + formatPrix(item.prix * item.qty) + '</span>' +
        '</div>' +
      '</div>' +
      '<button class="ci-remove" data-idx="' + idx + '" title="Supprimer"><i class="fas fa-trash"></i></button>' +
    '</div>';
  }).join('');

  // Boutons supprimer
  itemsEl.querySelectorAll('.ci-remove').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var c = getCart();
      c.splice(parseInt(btn.dataset.idx), 1);
      saveCart(c);
      updateCartUI();
    });
  });

  // Boutons +/- quantité
  itemsEl.querySelectorAll('.ci-qty-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var c   = getCart();
      var idx = parseInt(btn.dataset.idx);
      var op  = btn.dataset.op;
      if (op === 'plus') {
        c[idx].qty++;
      } else {
        c[idx].qty--;
        if (c[idx].qty <= 0) c.splice(idx, 1);
      }
      saveCart(c);
      updateCartUI();
    });
  });
}

function openCart() {
  var drawer  = document.getElementById('cartDrawer');
  var overlay = document.getElementById('cartOverlay');
  if (drawer)  drawer.classList.add('open');
  if (overlay) overlay.classList.add('show');
  document.body.style.overflow = 'hidden';
  updateCartUI();
}
function closeCartFn() {
  var drawer  = document.getElementById('cartDrawer');
  var overlay = document.getElementById('cartOverlay');
  if (drawer)  drawer.classList.remove('open');
  if (overlay) overlay.classList.remove('show');
  document.body.style.overflow = '';
}

// ================================================
// COMMANDER VIA WHATSAPP
// Génère un message WhatsApp avec le résumé du panier
// ================================================
function commanderWhatsApp() {
  var cart = getCart();
  if (!cart.length) {
    showToast('Votre panier est vide !', 'error');
    return;
  }

  var total = cart.reduce(function(s, i) { return s + i.prix * i.qty; }, 0);
  var lignes = cart.map(function(item) {
    return '• ' + item.nom + ' x' + item.qty + ' = ' + formatPrix(item.prix * item.qty);
  }).join('\n');

  var message =
    '🛒 *Nouvelle commande KF Tech*\n\n' +
    lignes + '\n\n' +
    '💰 *Total : ' + formatPrix(total) + '*\n\n' +
    'Bonjour, je souhaite commander ces articles. Pouvez-vous me confirmer la disponibilité et les modalités de livraison ? Merci !';

  var numero  = '237651271617'; // Numéro KF Tech sans + ni espaces
  var url     = 'https://wa.me/' + numero + '?text=' + encodeURIComponent(message);
  window.open(url, '_blank');
}

function generateInvoicePdf() {
  var cart = getCart();
  if (!cart.length) {
    showToast('Votre panier est vide !', 'error');
    return;
  }

  var total = cart.reduce(function(s, i) { return s + i.prix * i.qty; }, 0);
  var date = new Date();
  var invoiceNumber = Math.floor(100000 + Math.random() * 900000);
  var html = '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Facture KF TECH</title>' +
    '<style>body{margin:0;padding:0;background:#f2f4f8;color:#0f172a;font-family:Inter,system-ui,Arial,sans-serif;} .invoice-container{max-width:900px;margin:0 auto;padding:38px 24px 48px;} .invoice-card{background:#fff;border-radius:24px;overflow:hidden;box-shadow:0 18px 50px rgba(15,23,42,.08);} .invoice-header{display:flex;justify-content:space-between;flex-wrap:wrap;gap:20px;padding:32px 32px 24px;background:linear-gradient(135deg,#ff7a00,#ff9f1c);color:#fff;} .company-brand h1{margin:0;font-size:32px;letter-spacing:.03em;} .company-brand p{margin:10px 0 0;font-size:14px;line-height:1.6;opacity:.9;} .invoice-meta{min-width:240px;background:rgba(255,255,255,.12);padding:18px;border-radius:18px;} .invoice-meta h2{margin:0 0 12px;font-size:18px;letter-spacing:.02em;} .invoice-meta p{margin:8px 0;font-size:14px;line-height:1.7;} .invoice-body{padding:32px;} .section-title{margin:0 0 20px;font-size:18px;color:#111;font-weight:700;letter-spacing:.01em;} .table-wrapper{overflow-x:auto;} table{width:100%;border-collapse:collapse;color:#1f2937;} th,td{padding:18px 16px;border-bottom:1px solid #e5e7eb;text-align:left;font-size:14px;} th{background:#f8fafc;color:#0f172a;font-weight:700;} td:last-child, th:last-child{text-align:right;} tbody tr:last-child td{border-bottom:none;} .totals{margin-top:24px;display:flex;justify-content:flex-end;gap:16px;flex-wrap:wrap;} .totals .label{font-size:15px;color:#475569;} .totals .value{font-size:18px;font-weight:800;color:#111;} .footer-note{margin-top:32px;padding:24px 22px;border-radius:16px;background:#f8fafc;color:#475569;font-size:14px;line-height:1.9;} @media print{body{background:#fff;} .invoice-container{padding:0;} .invoice-card{box-shadow:none;} .no-print{display:none;} }</style></head><body>' +
    '<div class="invoice-container">' +
      '<div class="invoice-card">' +
        '<div class="invoice-header">' +
          '<div class="company-brand">' +
            '<h1>KF TECH SARL</h1>' +
            '<p>Douala, Cameroun<br>contact@kftech237.com<br>+237 6 51 27 16 17</p>' +
          '</div>' +
          '<div class="invoice-meta">' +
            '<h2>FACTURE</h2>' +
            '<p><strong>N° :</strong> ' + invoiceNumber + '</p>' +
            '<p><strong>Date :</strong> ' + date.toLocaleDateString('fr-FR') + '</p>' +
          '</div>' +
        '</div>' +
        '<div class="invoice-body">' +
          '<p class="section-title">Détails de la commande</p>' +
          '<div class="table-wrapper">' +
            '<table>' +
              '<thead><tr><th>Produit</th><th>Quantité</th><th>Prix unitaire</th><th>Total</th></tr></thead>' +
              '<tbody>';

  cart.forEach(function(item){
    html += '<tr><td>' + item.nom + '</td><td>' + item.qty + '</td><td>' + formatPrix(item.prix) + '</td><td>' + formatPrix(item.prix * item.qty) + '</td></tr>';
  });

  html += '</tbody></table>' +
          '</div>' +
          '<div class="totals">' +
            '<span class="label">Total général</span>' +
            '<span class="value">' + formatPrix(total) + '</span>' +
          '</div>' +
          '<div class="footer-note">' +
            'Merci pour votre achat chez KF TECH SARL. Cette facture correspond aux articles présents dans votre panier. Conservez-la pour le suivi de la livraison et la garantie du produit.' +
          '</div>' +
        '</div>' +
      '</div>' +
    '</div>' +
    '<script>window.onload=function(){window.print();};</script>' +
    '</body></html>';

  var invoiceWindow = window.open('', '_blank');
  invoiceWindow.document.write(html);
  invoiceWindow.document.close();
}

// ================================================
// ACHETER MAINTENANT — ouvre un mini-formulaire
// puis envoie sur WhatsApp avec les infos du client
// ================================================
function acheterMaintenant(produit, qty) {
  if (!qty) qty = 1;

  // Créer le modal s'il n'existe pas encore
  var existingModal = document.getElementById('modalAcheterNow');
  if (existingModal) existingModal.remove();

  var totalPrix = parseFloat(produit.prix) * qty;

  var modal = document.createElement('div');
  modal.id = 'modalAcheterNow';
  modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:2000;display:flex;align-items:center;justify-content:center;padding:16px';
  modal.innerHTML =
    '<div style="background:#fff;border-radius:14px;padding:28px;width:100%;max-width:440px;max-height:90vh;overflow-y:auto;position:relative">' +
      '<button id="closeAcheter" style="position:absolute;top:14px;right:16px;background:none;border:none;font-size:20px;cursor:pointer;color:#aaa">×</button>' +

      // Résumé produit
      '<div style="display:flex;gap:12px;align-items:center;background:#f9f9f9;border-radius:10px;padding:12px;margin-bottom:20px">' +
        '<img src="' + produit.image + '" style="width:60px;height:60px;object-fit:contain;border-radius:8px;background:#fff;padding:4px"/>' +
        '<div>' +
          '<p style="font-weight:700;font-size:14px">' + produit.nom + '</p>' +
          '<p style="color:var(--orange);font-weight:800;font-size:16px">' + formatPrix(totalPrix) + '</p>' +
          '<small style="color:#888">Quantité : ' + qty + '</small>' +
        '</div>' +
      '</div>' +

      '<h3 style="font-size:17px;font-weight:800;margin-bottom:16px">Vos informations</h3>' +

      '<div style="margin-bottom:12px">' +
        '<label style="display:block;font-size:11px;font-weight:700;color:#555;margin-bottom:5px;text-transform:uppercase">Nom complet *</label>' +
        '<input id="anNom" type="text" placeholder="Votre nom et prénom" style="width:100%;height:44px;border:1.5px solid #e8e8e8;border-radius:8px;padding:0 14px;font-size:14px;font-family:Barlow,sans-serif;outline:none"/>' +
      '</div>' +
      '<div style="margin-bottom:12px">' +
        '<label style="display:block;font-size:11px;font-weight:700;color:#555;margin-bottom:5px;text-transform:uppercase">Téléphone *</label>' +
        '<input id="anTel" type="tel" placeholder="+237 6 XX XX XX XX" style="width:100%;height:44px;border:1.5px solid #e8e8e8;border-radius:8px;padding:0 14px;font-size:14px;font-family:Barlow,sans-serif;outline:none"/>' +
      '</div>' +
      '<div style="margin-bottom:20px">' +
        '<label style="display:block;font-size:11px;font-weight:700;color:#555;margin-bottom:5px;text-transform:uppercase">Quartier / Adresse</label>' +
        '<input id="anAdresse" type="text" placeholder="Ex: Akwa, rue des Manguiers" style="width:100%;height:44px;border:1.5px solid #e8e8e8;border-radius:8px;padding:0 14px;font-size:14px;font-family:Barlow,sans-serif;outline:none"/>' +
      '</div>' +

      '<button id="btnEnvoyerWA" style="width:100%;height:50px;background:#25d366;color:#fff;border:none;border-radius:10px;font-size:15px;font-weight:800;font-family:Barlow,sans-serif;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px">' +
        '<i class="fab fa-whatsapp"></i> Envoyer ma commande sur WhatsApp' +
      '</button>' +
      '<p style="text-align:center;font-size:12px;color:#aaa;margin-top:10px">Vous serez redirigé vers WhatsApp pour confirmer</p>' +
    '</div>';

  document.body.appendChild(modal);
  document.getElementById('anNom').focus();

  // Fermer
  document.getElementById('closeAcheter').addEventListener('click', function() { modal.remove(); });
  modal.addEventListener('click', function(e) { if (e.target === modal) modal.remove(); });

  // Envoyer sur WhatsApp
  document.getElementById('btnEnvoyerWA').addEventListener('click', function() {
    var nom     = document.getElementById('anNom').value.trim();
    var tel     = document.getElementById('anTel').value.trim();
    var adresse = document.getElementById('anAdresse').value.trim();

    if (!nom || !tel) {
      document.getElementById('anNom').style.borderColor = !nom ? '#e63946' : '#e8e8e8';
      document.getElementById('anTel').style.borderColor = !tel ? '#e63946' : '#e8e8e8';
      showToast('Veuillez remplir votre nom et téléphone', 'error');
      return;
    }

    var message =
      '🛒 *Commande KF Tech*\n\n' +
      '📦 *Produit :* ' + produit.nom + '\n' +
      '🔢 *Quantité :* ' + qty + '\n' +
      '💰 *Prix :* ' + formatPrix(totalPrix) + '\n\n' +
      '👤 *Client :* ' + nom + '\n' +
      '📞 *Téléphone :* ' + tel + '\n' +
      (adresse ? '📍 *Adresse :* ' + adresse + '\n' : '') +
      '\nBonjour, je souhaite commander cet article. Merci de confirmer la disponibilité !';

    var numero = '237651271617';
    window.open('https://wa.me/' + numero + '?text=' + encodeURIComponent(message), '_blank');
    modal.remove();
  });
}

// ================================================
// INITIALISATION PANIER
// ================================================
function initCart() {
  // Bouton panier header
  var cartBtn = document.getElementById('cartBtn');
  if (cartBtn) cartBtn.addEventListener('click', function(e) { e.preventDefault(); openCart(); });

  // Fermer drawer
  var closeCart = document.getElementById('closeCart');
  if (closeCart) closeCart.addEventListener('click', closeCartFn);

  var overlay = document.getElementById('cartOverlay');
  if (overlay) overlay.addEventListener('click', function(e) {
    if (e.target === overlay) closeCartFn();
  });

  // Bouton "Télécharger la facture" dans le drawer
  var btnInvoice = document.getElementById('btnInvoice');
  if (btnInvoice) btnInvoice.addEventListener('click', function(e) {
    e.preventDefault();
    generateInvoicePdf();
  });

  // Bouton "Commander" dans le drawer → WhatsApp
  var btnCommander = document.getElementById('btnCommander');
  if (btnCommander) btnCommander.addEventListener('click', function(e) {
    e.preventDefault();
    commanderWhatsApp();
  });

  // ---- DÉLÉGATION : boutons "Ajouter au panier" ----
  document.addEventListener('click', function(e) {
    var btn = e.target.closest('.btn-add');
    if (!btn) return;

    // Empêcher la navigation vers la fiche produit
    e.preventDefault();
    e.stopPropagation();

    if (btn.disabled) return;
    btn.disabled = true;

    var product = getProductFromButton(btn);
    if (product && product.id && product.nom && product.prix) {
      addToCart(product, 1);
      btn.textContent = '✓ Ajouté !';
      btn.style.background = '#2ecc71';
      setTimeout(function() {
        btn.textContent = 'Ajouter au panier';
        btn.style.background = '';
        btn.disabled = false;
      }, 1500);
      return;
    }

    var id = btn.dataset.id;
    if (!id) {
      btn.disabled = false;
      return;
    }

    fetch(API_BASE + '?action=single&id=' + id)
      .then(function(r) { return r.json(); })
      .then(function(p) {
        if (p && p.id) {
          addToCart(p, 1);
          btn.textContent = '✓ Ajouté !';
          btn.style.background = '#2ecc71';
          setTimeout(function() {
            btn.textContent = 'Ajouter au panier';
            btn.style.background = '';
            btn.disabled = false;
          }, 1500);
        } else {
          btn.disabled = false;
        }
      })
      .catch(function() { btn.disabled = false; });
  }, true); // ← true = phase de capture, prioritaire sur les onclick inline
}

// ================================================
// HAMBURGER + MOBILE NAV
// ================================================
function initHamburger() {
  var btn   = document.querySelector('.nav-menu-btn');
  var links = document.querySelector('.nav-links');
  var nav   = document.getElementById('mainNav');
  if (!btn || !links) return;

  btn.addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    var isOpen = links.classList.toggle('open');
    btn.innerHTML = isOpen
      ? '<i class="fas fa-times"></i>'
      : '<i class="fas fa-bars"></i>';
   // Positionner le menu juste sous la navbar
    if (isOpen && nav) {
      var navBottom = nav.getBoundingClientRect().bottom;
      links.style.top = navBottom + 'px';
    }
  });

  // Fermer en cliquant un lien
  links.querySelectorAll('a').forEach(function(a) {
    a.addEventListener('click', function() {
      links.classList.remove('open');
      btn.innerHTML = '<i class="fas fa-bars"></i>';
    });
  });

  // Fermer en dehors
  document.addEventListener('click', function(e) {
    if (!btn.contains(e.target) && !links.contains(e.target)) {
      links.classList.remove('open');
      btn.innerHTML = '<i class="fas fa-bars"></i>';
    }
  });
}

// Calcule la hauteur totale header+navbar et définit --kf-offset
// pour que body padding-top compense le fixed sans espace blanc
function adjustOffset() {
  var header = document.getElementById('mainHeader');
  var nav    = document.getElementById('mainNav');
  if (!header || !nav) return;

  if (window.innerWidth <= 768) {
    // Mettre la navbar juste sous le header
    var headerH = header.offsetHeight;
    nav.style.top = headerH + 'px';

    // Définir l'offset total pour compenser le fixed
    var totalOffset = headerH + nav.offsetHeight;
    document.documentElement.style.setProperty('--kf-offset', totalOffset + 'px');

    // Mettre à jour le top du menu ouvert s'il est visible
    var links = document.querySelector('.nav-links.open');
    if (links) {
      links.style.top = (headerH + nav.offsetHeight) + 'px';
    }
  } else {
    nav.style.top = '';
    document.documentElement.style.setProperty('--kf-offset', '0px');
  }
}

// ================================================
// RECHERCHE
// ================================================
function initSearch() {
  var input  = document.getElementById('searchInput');
  var btn    = document.getElementById('searchBtn');
  var select = document.getElementById('searchCatSelect');

  function doSearch() {
    var q   = input ? input.value.trim() : '';
    var cat = select ? select.value : '';
    if (q.length < 2) { showToast('Tapez au moins 2 caractères', ''); return; }
    var url = 'catalog.php?q=' + encodeURIComponent(q);
    if (cat) url += '&cat=' + encodeURIComponent(cat);
    window.location.href = url;
  }

  if (btn)   btn.addEventListener('click', doSearch);
  if (input) input.addEventListener('keydown', function(e) { if (e.key === 'Enter') doSearch(); });
}

// ================================================
// STICKY NAVBAR + BACK TO TOP
// ================================================
function initScroll() {
  var nav    = document.getElementById('mainNav');
  var backBtn= document.getElementById('backTop');

  window.addEventListener('scroll', function() {
    if (nav && window.innerWidth > 768) {
      nav.style.boxShadow = window.scrollY > 60 ? '0 4px 12px rgba(0,0,0,.15)' : 'none';
    }
    if (backBtn) backBtn.classList.toggle('show', window.scrollY > 400);
  });

// Recalculer l'offset au resize (rotation écran, etc.)
  window.addEventListener('resize', function() {
    adjustOffset();
  });

  // Calculer au chargement — après que le DOM soit rendu
  adjustOffset();
  // Recalculer après les fonts/images chargées (hauteur header peut changer)
  window.addEventListener('load', adjustOffset);

  if (backBtn) backBtn.addEventListener('click', function(e) {
    e.preventDefault();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}

// ================================================
// HELPERS
// ================================================
function formatPrix(p) {
  return 'XAF ' + parseInt(p).toLocaleString('fr-FR');
}

function renderStars(n) {
  n = Math.round(n);
  return '★'.repeat(n) + '☆'.repeat(5 - n);
}

function showToast(msg, type) {
  var existing = document.getElementById('kf-toast');
  if (existing) existing.remove();
  var t = document.createElement('div');
  t.id = 'kf-toast';
  t.className = 'toast' + (type ? ' ' + type : '');
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(function() { t.classList.add('show'); }, 10);
  setTimeout(function() {
    t.classList.remove('show');
    setTimeout(function() { if (t.parentNode) t.remove(); }, 400);
  }, 3500);
}

// ================================================
// INIT GLOBAL
// ================================================
document.addEventListener('DOMContentLoaded', function() {
  updateCartUI();
  initCart();
  initHamburger();
  initSearch();
  initScroll();
});

// ================================================
// FAVORIS
// ================================================
var FAV_IDS = []; // IDs des produits en favori pour cet utilisateur

// Charger les IDs favoris si connecté
function loadFavorisIds() {
  fetch('api/favoris.php?action=ids')
    .then(function(r) { return r.json(); })
    .then(function(d) {
      if (d.ids) {
        FAV_IDS = d.ids;
        updateAllWishButtons();
        updateWishBadge();
      }
    })
    .catch(function() {
      // Si l'API favoris n'est pas disponible, on utilise le stockage local
      var user = getLocalUser();
      if (user && user.id) {
        FAV_IDS = getStoredFavoris(user.id);
        updateAllWishButtons();
        updateWishBadge();
      }
    });
}

// Mettre à jour l'apparence de tous les boutons favori sur la page
function updateAllWishButtons() {
  document.querySelectorAll('.btn-wish, .btn-wish-prod, [data-wish-id]').forEach(function(btn) {
    var id = parseInt(btn.dataset.wishId || btn.dataset.id);
    if (!id) return;
    var estFavori = FAV_IDS.indexOf(id) !== -1;
    btn.classList.toggle('active', estFavori);
    btn.title = estFavori ? 'Retirer des favoris' : 'Ajouter aux favoris';
  });
}

// Mettre à jour le badge coeur dans le header
function updateWishBadge() {
  var badge = document.getElementById('wishBadge');
  if (badge) badge.textContent = FAV_IDS.length || 0;
}

// Toggle favori — demande connexion si non connecté
function toggleFavori(produitId, btnEl) {
  fetch('api/auth.php?action=session')
    .then(function(r) { return r.json(); })
    .then(function(session) {
      if (!session.connecte) {
        showModalConnexionFavoris(produitId);
        return;
      }
      var userId = session.user && session.user.id ? session.user.id : null;
      fetch('api/favoris.php?action=toggle', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ produit_id: produitId })
      })
      .then(function(r) { return r.json(); })
      .then(function(d) {
        if (d.succes) {
          if (d.action === 'ajoute') {
            if (FAV_IDS.indexOf(produitId) === -1) FAV_IDS.push(produitId);
            showToast('❤ Ajouté aux favoris !', 'success');
          } else {
            FAV_IDS = FAV_IDS.filter(function(id) { return id !== produitId; });
            showToast('Retiré des favoris', '');
          }
          if (userId) saveStoredFavoris(userId, FAV_IDS);
          updateAllWishButtons();
          updateWishBadge();
        }
      })
      .catch(function() {
        // API favoris indisponible, fallback sur storage local
        if (!userId) {
          showModalConnexionFavoris(produitId);
          return;
        }
        var stored = getStoredFavoris(userId);
        var action = stored.indexOf(produitId) === -1 ? 'ajoute' : 'retire';
        if (action === 'ajoute') {
          stored.push(produitId);
          if (FAV_IDS.indexOf(produitId) === -1) FAV_IDS.push(produitId);
          showToast('❤ Ajouté aux favoris !', 'success');
        } else {
          stored = stored.filter(function(id) { return id !== produitId; });
          FAV_IDS = FAV_IDS.filter(function(id) { return id !== produitId; });
          showToast('Retiré des favoris', '');
        }
        saveStoredFavoris(userId, stored);
        updateAllWishButtons();
        updateWishBadge();
      });
    })
    .catch(function() {
      var user = getLocalUser();
      if (!user || !user.id) {
        showModalConnexionFavoris(produitId);
        return;
      }
      var stored = getStoredFavoris(user.id);
      var action = stored.indexOf(produitId) === -1 ? 'ajoute' : 'retire';
      if (action === 'ajoute') {
        stored.push(produitId);
        if (FAV_IDS.indexOf(produitId) === -1) FAV_IDS.push(produitId);
        showToast('❤ Ajouté aux favoris !', 'success');
      } else {
        stored = stored.filter(function(id) { return id !== produitId; });
        FAV_IDS = FAV_IDS.filter(function(id) { return id !== produitId; });
        showToast('Retiré des favoris', '');
      }
      saveStoredFavoris(user.id, stored);
      updateAllWishButtons();
      updateWishBadge();
    });
}

// Modal : demander connexion pour les favoris
function showModalConnexionFavoris(produitId) {
  var existingModal = document.getElementById('modalFavorisLogin');
  if (existingModal) existingModal.remove();

  var modal = document.createElement('div');
  modal.id = 'modalFavorisLogin';
  modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:2000;display:flex;align-items:center;justify-content:center;padding:16px';
  modal.innerHTML =
    '<div style="background:#fff;border-radius:16px;padding:36px 32px;width:100%;max-width:400px;text-align:center;position:relative">' +
      '<button id="closeFavModal" style="position:absolute;top:14px;right:16px;background:none;border:none;font-size:22px;cursor:pointer;color:#aaa;line-height:1">×</button>' +

      // Icône coeur animé
      '<div style="width:72px;height:72px;background:#fff0f0;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;font-size:32px">❤️</div>' +

      '<h3 style="font-size:20px;font-weight:800;margin-bottom:10px;color:#1a1a1a">Sauvegardez vos favoris !</h3>' +
      '<p style="font-size:14px;color:#666;line-height:1.6;margin-bottom:24px">' +
        'Connectez-vous pour sauvegarder ce produit dans vos favoris et le retrouver facilement quand vous serez prêt à acheter.' +
      '</p>' +

      '<div style="display:flex;flex-direction:column;gap:10px">' +
        '<a href="login.php?redirect=favoris" style="display:block;height:48px;background:var(--orange);color:#fff;border-radius:10px;font-size:15px;font-weight:700;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:8px;font-family:Barlow,sans-serif;transition:background .2s">' +
          '<i class="fas fa-sign-in-alt"></i> Se connecter' +
        '</a>' +
        '<a href="login.php?tab=register&redirect=favoris" style="display:flex;height:44px;border:2px solid var(--orange);color:var(--orange);border-radius:10px;font-size:14px;font-weight:700;text-decoration:none;align-items:center;justify-content:center;gap:8px;font-family:Barlow,sans-serif">' +
          '<i class="fas fa-user-plus"></i> Créer un compte gratuit' +
        '</a>' +
      '</div>' +

      '<p style="font-size:12px;color:#aaa;margin-top:16px">Inscription gratuite en moins d\'une minute</p>' +
    '</div>';

  document.body.appendChild(modal);

  document.getElementById('closeFavModal').addEventListener('click', function() { modal.remove(); });
  modal.addEventListener('click', function(e) { if (e.target === modal) modal.remove(); });
}

// Délégation : clics sur tous les boutons favori
function initFavoris() {
  // Icône coeur dans le header → aller sur la page favoris
  var wishIcon = document.querySelector('.action-icon .fa-heart');
  if (wishIcon) {
    var wishLink = wishIcon.closest('a');
    if (wishLink) {
      wishLink.addEventListener('click', function(e) {
        e.preventDefault();
        fetch('api/auth.php?action=session')
          .then(function(r) { return r.json(); })
          .then(function(d) {
            if (d.connecte) {
              window.location.href = 'favoris.php';
            } else {
              showModalConnexionFavoris(null);
            }
          });
      });
    }
  }

  // Délégation sur tous les boutons .btn-wish et .btn-wish-prod
  document.addEventListener('click', function(e) {
    var btn = e.target.closest('.btn-wish, .btn-wish-prod, [data-wish-id]');
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();
    var id = parseInt(btn.dataset.wishId || btn.dataset.id);
    if (id) toggleFavori(id, btn);
  });

  // Charger les IDs au démarrage (si connecté)
  loadFavorisIds();
}

// Ajouter initFavoris au DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
  initFavoris();
});