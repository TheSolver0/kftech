<?php
// api/avis.php — proxy vers POST /api/ecom/reviews
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/api.php';

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['produit_id']) || empty($input['nom_auteur'])
    || empty($input['note'])    || empty($input['commentaire'])) {
    http_response_code(400);
    echo json_encode(['succes' => false, 'message' => 'Champs manquants']);
    exit;
}

// Envoyer à l'API .NET
$payload = json_encode([
    'productId' => (int)$input['produit_id'],
    'author'    => $input['nom_auteur'],
    'rating'    => (float)$input['note'],
    'comment'   => $input['commentaire'],
]);

$ctx = stream_context_create(['http' => [
    'method'  => 'POST',
    'header'  => 'Content-Type: application/json',
    'content' => $payload,
    'timeout' => 5,
    'ignore_errors' => true,
]]);

$raw  = @file_get_contents(APIURL . 'reviews', false, $ctx);
$data = $raw ? json_decode($raw, true) : null;

if ($data && !isset($data['erreur'])) {
    echo json_encode(['succes' => true]);
} else {
    http_response_code(500);
    echo json_encode(['succes' => false, 'message' => 'Erreur lors de la publication']);
}