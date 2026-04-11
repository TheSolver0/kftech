<?php
// api/favoris.php
// GET  ?action=liste                → favoris de l'utilisateur connecté
// POST ?action=toggle { produit_id }→ ajoute ou retire des favoris
// GET  ?action=check&id=X           → vérifie si un produit est en favori
session_start();
require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../config/db.php';

$action = $_GET['action'] ?? '';
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

if (!isLoggedIn()) {
    jsonResponse(['succes' => false, 'connecte' => false, 'message' => 'Connexion requise.'], 401);
}

$user = getCurrentUser();
$userId = $user['id'];
$cookieKey = 'kftech_favoris_' . $userId;

function loadFavorisIds(string $key): array {
    $raw = $_COOKIE[$key] ?? '[]';
    $ids = json_decode($raw, true);
    if (!is_array($ids)) {
        return [];
    }
    return array_values(array_unique(array_filter($ids, function($item) {
        return $item !== null && $item !== '';
    })));
}

function saveFavorisIds(string $key, array $ids): void {
    setcookie($key, json_encode(array_values($ids), JSON_UNESCAPED_UNICODE), time() + 30 * 24 * 3600, '/');
}

$storedIds = loadFavorisIds($cookieKey);

switch ($action) {

    case 'liste':
        jsonResponse(['succes' => true, 'favoris' => $storedIds, 'total' => count($storedIds)]);
        break;

    case 'toggle':
        $produitId = $body['produit_id'] ?? $_GET['id'] ?? null;
        if (!$produitId) {
            jsonResponse(['succes' => false, 'message' => 'Produit invalide.'], 400);
        }
        $produitId = (string)$produitId;
        $found = array_search($produitId, $storedIds, true);
        if ($found !== false) {
            array_splice($storedIds, $found, 1);
            saveFavorisIds($cookieKey, $storedIds);
            jsonResponse(['succes' => true, 'action' => 'retire', 'message' => 'Retiré des favoris.']);
        }
        $storedIds[] = $produitId;
        saveFavorisIds($cookieKey, $storedIds);
        jsonResponse(['succes' => true, 'action' => 'ajoute', 'message' => 'Ajouté aux favoris !']);
        break;

    case 'check':
        $produitId = $_GET['id'] ?? null;
        if (!$produitId) {
            jsonResponse(['favori' => false]);
        }
        $produitId = (string)$produitId;
        jsonResponse(['favori' => in_array($produitId, $storedIds, true)]);
        break;

    case 'ids':
        jsonResponse(['ids' => $storedIds]);
        break;

    default:
        jsonResponse(['erreur' => 'Action inconnue.'], 400);
}
