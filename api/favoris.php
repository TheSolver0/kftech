<?php
// api/favoris.php
// GET  ?action=liste                → favoris de l'utilisateur connecté
// POST ?action=toggle { produit_id }→ ajoute ou retire des favoris
// GET  ?action=check&id=X           → vérifie si un produit est en favori
session_start();
require_once __DIR__ . '/../config/api.php';

// Créer la table si elle n'existe pas
$db = getDB();
$db->query("CREATE TABLE IF NOT EXISTS favoris (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    produit_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_favori (utilisateur_id, produit_id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (produit_id) REFERENCES produits(id) ON DELETE CASCADE
) ENGINE=InnoDB");

$action = $_GET['action'] ?? '';
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

// Toutes les actions nécessitent d'être connecté
if (!isLoggedIn()) {
    jsonResponse(['succes' => false, 'connecte' => false, 'message' => 'Connexion requise.'], 401);
}

$userId = $_SESSION['user_id'];

switch ($action) {

    // ---- LISTE des favoris ----
    case 'liste':
        $stmt = $db->prepare("
            SELECT p.*, c.nom AS cat_nom, c.slug AS cat_slug
            FROM favoris f
            JOIN produits p ON f.produit_id = p.id
            JOIN categories c ON p.categorie_id = c.id
            WHERE f.utilisateur_id = ? AND p.actif = 1
            ORDER BY f.created_at DESC
        ");
        $stmt->execute([$userId]);
        $favoris = $stmt->fetchAll();
        jsonResponse(['succes' => true, 'favoris' => $favoris, 'total' => count($favoris)]);
        break;

    // ---- TOGGLE (ajouter ou retirer) ----
    case 'toggle':
        $produitId = (int)($body['produit_id'] ?? $_GET['id'] ?? 0);
        if (!$produitId) jsonResponse(['succes' => false, 'message' => 'Produit invalide.'], 400);

        // Vérifier si déjà en favori
        $check = $db->prepare("SELECT id FROM favoris WHERE utilisateur_id=? AND produit_id=?");
        $check->execute([$userId, $produitId]);
        $existe = $check->fetch();

        if ($existe) {
            // Retirer
            $db->prepare("DELETE FROM favoris WHERE utilisateur_id=? AND produit_id=?")->execute([$userId, $produitId]);
            jsonResponse(['succes' => true, 'action' => 'retire', 'message' => 'Retiré des favoris.']);
        } else {
            // Ajouter
            $db->prepare("INSERT INTO favoris (utilisateur_id, produit_id) VALUES (?,?)")->execute([$userId, $produitId]);
            jsonResponse(['succes' => true, 'action' => 'ajoute', 'message' => 'Ajouté aux favoris !']);
        }
        break;

    // ---- VÉRIFIER si un produit est en favori ----
    case 'check':
        $produitId = (int)($_GET['id'] ?? 0);
        if (!$produitId) jsonResponse(['favori' => false]);
        $check = $db->prepare("SELECT id FROM favoris WHERE utilisateur_id=? AND produit_id=?");
        $check->execute([$userId, $produitId]);
        jsonResponse(['favori' => (bool)$check->fetch()]);
        break;

    // ---- LISTE des IDs en favori (pour marquer les coeurs sur les pages) ----
    case 'ids':
        $stmt = $db->prepare("SELECT produit_id FROM favoris WHERE utilisateur_id=?");
        $stmt->execute([$userId]);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        jsonResponse(['ids' => array_map('intval', $ids)]);
        break;

    default:
        jsonResponse(['erreur' => 'Action inconnue.'], 400);
}
