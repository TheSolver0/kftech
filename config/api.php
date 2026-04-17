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
    
    $result = is_array($decoded) ? $decoded : [];

    // Workaround for API category filter bugs when category filters return zero results
    if (strpos($path, 'products') !== false) {
        parse_str(parse_url(APIURL . '/' . ltrim($path, '/'), PHP_URL_QUERY), $query);
        $requestedCat = rawurldecode($query['cat'] ?? $query['categorie'] ?? $query['category'] ?? '');
        $items = $result['produits'] ?? $result['items'] ?? null;

        if ($requestedCat !== '' && is_array($items) && count($items) === 0) {
            $all = apiGet('/products?limit=500');
            $products = $all['produits'] ?? $all['items'] ?? [];
            if (is_array($products)) {
                $filtered = array_values(array_filter($products, function($p) use ($requestedCat) {
                    return isset($p['categorie_slug'], $p['categorie_nom']) && (
                        $p['categorie_slug'] === $requestedCat ||
                        strtolower(trim($p['categorie_nom'])) === strtolower(trim($requestedCat)) ||
                        strtolower(trim($p['categorie_nom'])) === strtolower(str_replace('-', ' ', $requestedCat))
                    );
                }));

                if (isset($result['produits'])) {
                    $result['produits'] = $filtered;
                    $result['total']    = count($filtered);
                    $result['pages']    = 1;
                } elseif (isset($result['items'])) {
                    $result['items'] = $filtered;
                } else {
                    $result = ['produits' => $filtered, 'total' => count($filtered), 'page' => 1, 'pages' => 1];
                }
            }
        }
    }

    return $result;
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

function categoryIconClass(array $cat): string {
    if (!empty($cat['icone']) && !in_array($cat['icone'], ['fas fa-tag', 'fa fa-tag', 'fa-tag'], true)) {
        return $cat['icone'];
    }

    $slug = strtolower(trim($cat['slug'] ?? ''));
    $name = strtolower(trim($cat['nom'] ?? ''));

    $normalize = function(string $value): string {
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/[\s_\/&]+/', '-', $value);
        $value = str_replace(['é','è','ê','à','ô','î','ù','û','ç','æ','œ'], ['e','e','e','a','o','i','u','u','c','ae','oe'], $value);
        return preg_replace('/[^a-z0-9\-]/', '', $value);
    };

    $key = $normalize($slug ?: $name);

    if (strpos($key, 'laptop') !== false || strpos($key, 'desktop') !== false) {
        return 'fas fa-laptop';
    }
    if (strpos($key, 'smartphone') !== false || strpos($key, 'telephone') !== false || strpos($key, 'mobile') !== false) {
        return 'fas fa-mobile-alt';
    }
    if (strpos($key, 'tablette') !== false || strpos($key, 'tablet') !== false) {
        return 'fas fa-tablet-alt';
    }
    if (strpos($key, 'accessoire') !== false || strpos($key, 'accessory') !== false) {
        return 'fas fa-headphones';
    }
    if (strpos($key, 'gaming') !== false || strpos($key, 'fun') !== false) {
        return 'fas fa-gamepad';
    }
    if (strpos($key, 'tv') !== false || strpos($key, 'audio') !== false || strpos($key, 'television') !== false) {
        return 'fas fa-tv';
    }
    if (strpos($key, 'photo') !== false || strpos($key, 'camera') !== false || strpos($key, 'cam') !== false) {
        return 'fas fa-camera';
    }

    return 'fas fa-tag';
}
