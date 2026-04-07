<?php
// =====================================================
// config/api.php — Configuration API centralisée
// SEULE CONSTANTE À MODIFIER POUR CHANGER L'URL DE L'API
// =====================================================

// 🔴 IMPORTANTE : URL de base de l'API - À MODIFIER SI NÉCESSAIRE
define('APIURL', 'http://localhost:5273/api/');

/**
 * Appel GET à l'API
 * @param string $path Chemin relatif (ex: /products, /categories, etc)
 * @return array Les données JSON décódées ou array vide en cas d'erreur
 */
function apiGet(string $path): array {
    $ctx = stream_context_create(['http' => [
        'timeout'       => 5,
        'ignore_errors' => true,
    ]]);
    $raw = @file_get_contents(APIURL . ltrim($path, '/'), false, $ctx);
    if ($raw === false) return [];
    return json_decode($raw, true) ?? [];
}

/**
 * Appel POST à l'API
 * @param string $path Chemin relatif (ex: /auth/login)
 * @param array $data Données à envoyer
 * @return array Les données JSON décódées ou array vide en cas d'erreur
 */
function apiPost(string $path, array $data): array {
    $opts = [
        'http' => [
            'method'        => 'POST',
            'timeout'       => 5,
            'ignore_errors' => true,
            'header'        => 'Content-Type: application/json',
            'content'       => json_encode($data),
        ]
    ];
    $ctx = stream_context_create($opts);
    $raw = @file_get_contents(APIURL . ltrim($path, '/'), false, $ctx);
    if ($raw === false) return [];
    return json_decode($raw, true) ?? [];
}

/**
 * Réponse JSON propre pour les APIs
 */
function jsonResponse($data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
