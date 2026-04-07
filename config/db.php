<?php
// =============================================
// config/db.php — Connexion MySQL
// Modifie DB_PASS si tu as mis un mot de passe
// =============================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'kftech');
define('DB_USER', 'root');       // utilisateur XAMPP par défaut
define('DB_PASS', '');           // mot de passe vide sur XAMPP par défaut
define('DB_CHARSET', 'utf8mb4');

// URL de base du site (change sur hébergement)
define('BASE_URL', 'http://localhost/kftech');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // En production, ne jamais afficher l'erreur brute
            http_response_code(500);
            die(json_encode(['erreur' => 'Connexion base de données impossible.']));
        }
    }
    return $pdo;
}

// Helper : réponse JSON propre pour les APIs
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Helper : vérifier si utilisateur connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper : récupérer l'utilisateur connecté
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT id, prenom, nom, email, telephone, role FROM utilisateurs WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}
