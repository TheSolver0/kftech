<?php
// api/avis.php — Soumettre un avis produit
session_start();
require_once __DIR__ . '/../config/db.php';

$body       = json_decode(file_get_contents('php://input'), true) ?? [];
$produit_id = (int)($body['produit_id'] ?? 0);
$nom        = trim($body['nom_auteur'] ?? 'Anonyme');
$note       = (int)($body['note'] ?? 0);
$commentaire = trim($body['commentaire'] ?? '');

if (!$produit_id || $note < 1 || $note > 5 || !$commentaire) {
    jsonResponse(['succes' => false, 'message' => 'Données invalides.'], 400);
}

$db = getDB();
$stmt = $db->prepare("INSERT INTO avis (produit_id, nom_auteur, note, commentaire, verifie) VALUES (?,?,?,?,0)");
$stmt->execute([$produit_id, $nom, $note, $commentaire]);

// Mettre à jour la note moyenne du produit
$avg = $db->prepare("SELECT AVG(note), COUNT(*) FROM avis WHERE produit_id = ?");
$avg->execute([$produit_id]);
[$newNote, $nbAvis] = $avg->fetch(PDO::FETCH_NUM);
$db->prepare("UPDATE produits SET note=?, nb_avis=? WHERE id=?")->execute([round($newNote,1), $nbAvis, $produit_id]);

jsonResponse(['succes' => true, 'message' => 'Avis publié ! Merci.']);
