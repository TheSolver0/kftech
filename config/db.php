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

// Helper : récupérer l'utilisateur depuis le cookie de session locale
function getCookieUser() {
    if (empty($_COOKIE['kftech_user'])) {
        return null;
    }
    $decoded = json_decode($_COOKIE['kftech_user'], true);
    if (!is_array($decoded) || empty($decoded['id']) || empty($decoded['email'])) {
        return null;
    }
    return [
        'id' => $decoded['id'],
        'prenom' => $decoded['prenom'] ?? 'Utilisateur',
        'nom' => $decoded['nom'] ?? '',
        'email' => $decoded['email'],
        'telephone' => $decoded['telephone'] ?? '',
        'role' => $decoded['role'] ?? 'client',
        'mot_de_passe_hash' => $decoded['mot_de_passe_hash'] ?? null,
    ];
}

// Helper : vérifier si utilisateur connecté (via session ou cookie local)
function isLoggedIn() {
    return isset($_SESSION['user_id']) || getCookieUser() !== null;
}

// Helper : récupérer l'utilisateur connecté (depuis la session ou le cookie)
function getCurrentUser() {
    if (isset($_SESSION['user_id'])) {
        return [
            'id' => $_SESSION['user_id'],
            'prenom' => $_SESSION['user_prenom'] ?? 'Utilisateur',
            'nom' => $_SESSION['user_nom'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'telephone' => $_SESSION['user_telephone'] ?? '',
            'role' => $_SESSION['user_role'] ?? 'client',
        ];
    }
    return getCookieUser();
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
