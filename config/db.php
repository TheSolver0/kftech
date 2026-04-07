<?php
// =====================================================
// config/db.php — Helpers de session (dépréciés)
// Note: Ce fichier n'est plus utilisé pour les requêtes BD
// Toutes les données proviennent maintenant de l'API
// =====================================================

// URL de base du site (change sur hébergement)
define('BASE_URL', 'http://localhost/kftech');

// Helper : vérifier si utilisateur connecté (via session)
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper : récupérer l'utilisateur connecté (depuis la session)
// Note: Les infos complètes doivent être stockées dans les variables de session
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    return [
        'id' => $_SESSION['user_id'],
        'prenom' => $_SESSION['user_prenom'] ?? 'Utilisateur',
        'nom' => $_SESSION['user_nom'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'telephone' => $_SESSION['user_telephone'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'client',
    ];
}

// DEPRECATED: getDB() - plus utilisé, l'API remplace la BD
function getDB() {
    trigger_error('getDB() est dépréciée. Utilisez l\'API à la place.', E_USER_DEPRECATED);
    return null;
}
