<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/api.php';

$action = $_GET['action'] ?? 'liste';

switch ($action) {

    case 'liste':
        $cat   = isset($_GET['cat'])   ? '&cat='   . urlencode($_GET['cat'])   : '';
        $page  = isset($_GET['page'])  ? '&page='  . intval($_GET['page'])     : '';
        $limit = isset($_GET['limit']) ? '&limit=' . intval($_GET['limit'])    : '';
        jsonResponse(apiGet("/products?1=1{$cat}{$page}{$limit}"));

    case 'recherche':
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 2) jsonResponse(['produits' => [], 'message' => 'Requête trop courte']);
        jsonResponse(apiGet('/products?q=' . urlencode($q)));

    case 'single':
        $id = intval($_GET['id'] ?? 0);
        if (!$id) jsonResponse(['erreur' => 'ID manquant'], 400);
        jsonResponse(apiGet('/products/' . $id));

    case 'categories':
        jsonResponse(apiGet('/categories'));

    case 'hero':
        jsonResponse(apiGet('/hero'));

    case 'tendance':
        jsonResponse(apiGet('/products/tendance'));

    case 'meilleurs':
        jsonResponse(apiGet('/products/meilleurs'));

    case 'recent':
        // Récupérer les produits récents par catégorie (2 par catégorie)
        $categories = apiGet('/categories');
        if (!is_array($categories)) {
            jsonResponse(['produits' => [], 'message' => 'Erreur récupération catégories']);
        }
        
        $allRecentProducts = [];
        foreach ($categories as $cat) {
            $catSlug = $cat['slug'] ?? '';
            if (!$catSlug) continue;
            
            // Récupérer les 2 premiers produits de chaque catégorie
            $products = apiGet('/products?cat=' . urlencode($catSlug) . '&limit=2');
            if (is_array($products) && isset($products['produits'])) {
                foreach ($products['produits'] as $product) {
                    $product['categorie_nom'] = $cat['nom'] ?? 'Catégorie';
                    $product['categorie_slug'] = $catSlug;
                    $allRecentProducts[] = $product;
                }
            }
        }
        
        // Mélanger et limiter à 10 produits maximum
        shuffle($allRecentProducts);
        $finalProducts = array_slice($allRecentProducts, 0, 10);
        
        jsonResponse(['produits' => $finalProducts]);

    default:
        jsonResponse(['erreur' => 'Action inconnue'], 400);
}