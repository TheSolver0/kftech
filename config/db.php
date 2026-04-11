<?php
// =====================================================
// config/db.php — Connexion locale à la base de données et helpers de session
// =====================================================

// Database configuration pour MySQL local
if (!defined('DB_HOST')) define('DB_HOST', '127.0.0.1');
if (!defined('DB_NAME')) define('DB_NAME', 'kftech');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

// URL de base du site (change sur hébergement)
define('BASE_URL', "https://api.kftech237.com/api/");

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

function getDB() {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    try {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        error_log('DB Connection failed: ' . $e->getMessage());
        return null;
    }
}
