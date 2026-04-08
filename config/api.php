<?php

define('APIURL', "https://api.kftech237.com/api/ecom");

function apiGet(string $path): array {
    $url = APIURL . '/' . ltrim($path, '/');
    $ctx = stream_context_create(['http' => [
        'timeout'       => 10,
        'ignore_errors' => true,
        'header'        => 'Accept: application/json',
    ]]);
    
    $raw = @file_get_contents($url, false, $ctx);
    
    if ($raw === false) {
        error_log("apiGet failed: $url");
        return [];
    }
    
    $decoded = json_decode($raw, true);
    
    // Si l'API retourne un objet avec une clé data/items/results
    if (isset($decoded['data'])) return $decoded['data'];
    if (isset($decoded['items'])) return $decoded['items'];
    
    return is_array($decoded) ? $decoded : [];
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
