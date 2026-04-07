<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

define('API_BASE', 'http://localhost:5273/api/'); 

function apiGet(string $path): mixed {
    $ctx = stream_context_create(['http' => [
        'timeout'       => 5,
        'ignore_errors' => true,
    ]]);
    $raw = @file_get_contents(API_BASE . $path, false, $ctx);
    if ($raw === false) {
        http_response_code(503);
        echo json_encode(['erreur' => 'API indisponible']);
        exit;
    }
    return json_decode($raw, true) ?? [];
}

function jsonResponse(mixed $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

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

    default:
        jsonResponse(['erreur' => 'Action inconnue'], 400);
}