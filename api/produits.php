<?php
// =============================================
// api/produits.php
// Appels possibles :
//   GET ?action=liste&cat=laptops&page=1
//   GET ?action=recherche&q=samsung
//   GET ?action=single&id=3
//   GET ?action=categories
//   GET ?action=hero
//   GET ?action=tendance
//   GET ?action=meilleurs
// =============================================

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$db     = getDB();
$action = $_GET['action'] ?? 'liste';

switch ($action) {

    // ---- LISTE avec filtre catégorie + pagination ----
    case 'liste':
        $cat   = $_GET['cat']  ?? '';
        $page  = max(1, (int)($_GET['page'] ?? 1));
        $limit = (int)($_GET['limit'] ?? 8);
        $offset = ($page - 1) * $limit;

        $where  = "WHERE p.actif = 1";
        $params = [];

        if ($cat) {
            $where   .= " AND c.slug = ?";
            $params[] = $cat;
        }

        // Total pour pagination
        $countSql = "SELECT COUNT(*) FROM produits p
                     JOIN categories c ON p.categorie_id = c.id
                     $where";
        $total = $db->prepare($countSql);
        $total->execute($params);
        $totalProduits = $total->fetchColumn();

        // Produits
        $sql = "SELECT p.*, c.nom AS categorie_nom, c.slug AS categorie_slug
                FROM produits p
                JOIN categories c ON p.categorie_id = c.id
                $where
                ORDER BY p.created_at DESC
                LIMIT $limit OFFSET $offset";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $produits = $stmt->fetchAll();

        jsonResponse([
            'produits'   => $produits,
            'total'      => (int)$totalProduits,
            'page'       => $page,
            'pages'      => ceil($totalProduits / $limit),
        ]);
        break;

    // ---- RECHERCHE ----
    case 'recherche':
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 2) {
            jsonResponse(['produits' => [], 'message' => 'Requête trop courte']);
        }

        $stmt = $db->prepare("
            SELECT p.*, c.nom AS categorie_nom, c.slug AS categorie_slug
            FROM produits p
            JOIN categories c ON p.categorie_id = c.id
            WHERE p.actif = 1
              AND (p.nom LIKE ? OR p.description LIKE ? OR p.marque LIKE ? OR c.nom LIKE ?)
            ORDER BY p.note DESC, p.nb_avis DESC
            LIMIT 20
        ");
        $like = "%$q%";
        $stmt->execute([$like, $like, $like, $like]);
        $produits = $stmt->fetchAll();

        jsonResponse(['produits' => $produits, 'total' => count($produits), 'query' => $q]);
        break;

    // ---- PRODUIT UNIQUE ----
    case 'single':
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) jsonResponse(['erreur' => 'ID manquant'], 400);

        $stmt = $db->prepare("
            SELECT p.*, c.nom AS categorie_nom, c.slug AS categorie_slug
            FROM produits p
            JOIN categories c ON p.categorie_id = c.id
            WHERE p.id = ? AND p.actif = 1
        ");
        $stmt->execute([$id]);
        $produit = $stmt->fetch();

        if (!$produit) jsonResponse(['erreur' => 'Produit introuvable'], 404);

        // Avis du produit
        $avisStmt = $db->prepare("
            SELECT * FROM avis WHERE produit_id = ? ORDER BY created_at DESC LIMIT 10
        ");
        $avisStmt->execute([$id]);
        $produit['avis'] = $avisStmt->fetchAll();

        // Produits similaires (même catégorie)
        $simStmt = $db->prepare("
            SELECT p.*, c.slug AS categorie_slug
            FROM produits p JOIN categories c ON p.categorie_id = c.id
            WHERE p.categorie_id = ? AND p.id != ? AND p.actif = 1
            LIMIT 4
        ");
        $simStmt->execute([$produit['categorie_id'], $id]);
        $produit['similaires'] = $simStmt->fetchAll();

        jsonResponse($produit);
        break;

    // ---- CATEGORIES ----
    case 'categories':
        $stmt = $db->query("
            SELECT c.*, COUNT(p.id) AS nb_produits
            FROM categories c
            LEFT JOIN produits p ON p.categorie_id = c.id AND p.actif = 1
            GROUP BY c.id
            ORDER BY c.ordre
        ");
        jsonResponse($stmt->fetchAll());
        break;

    // ---- HERO SLIDES ----
    case 'hero':
        $stmt = $db->query("
            SELECT * FROM hero_slides WHERE actif = 1 ORDER BY ordre ASC
        ");
        jsonResponse($stmt->fetchAll());
        break;

    // ---- PRODUITS TENDANCE ----
    case 'tendance':
        $stmt = $db->query("
            SELECT p.*, c.slug AS categorie_slug
            FROM produits p JOIN categories c ON p.categorie_id = c.id
            WHERE p.actif = 1
            ORDER BY p.nb_avis DESC, p.note DESC
            LIMIT 8
        ");
        jsonResponse($stmt->fetchAll());
        break;

    // ---- MEILLEURS PRODUITS (sidebar hero) ----
    case 'meilleurs':
        $stmt = $db->query("
            SELECT p.*, c.slug AS categorie_slug
            FROM produits p JOIN categories c ON p.categorie_id = c.id
            WHERE p.actif = 1
            ORDER BY p.note DESC, p.nb_avis DESC
            LIMIT 5
        ");
        jsonResponse($stmt->fetchAll());
        break;

    default:
        jsonResponse(['erreur' => 'Action inconnue'], 400);
}
