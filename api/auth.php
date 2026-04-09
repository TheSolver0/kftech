<?php
// api/auth.php
session_start();
require_once __DIR__ . '/../config/db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Déconnexion : pas de JSON, juste une redirection propre
if ($action === 'deconnexion') {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    session_start();
    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Vous avez été déconnecté avec succès.'];
    // URL absolue fiable depuis n'importe quel sous-dossier
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'];
    // SCRIPT_NAME = /kftech/api/auth.php → remonter 2 niveaux
    $base     = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');
    header('Location: ' . $protocol . '://' . $host . $base . '/index.php');
    exit;
}

// Vérifier session : pas besoin de JSON content-type
if ($action === 'session') {
    header('Content-Type: application/json; charset=utf-8');
    if (isLoggedIn()) {
        $user = getCurrentUser();
        echo json_encode(['connecte' => true, 'user' => $user]);
    } else {
        echo json_encode(['connecte' => false, 'user' => null]);
    }
    exit;
}

// Les autres actions (connexion, inscription) retournent du JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$body = [];
$raw  = file_get_contents('php://input');
if ($raw) $body = json_decode($raw, true) ?? [];
$data = array_merge($_POST, $body);

switch ($action) {

    // ---- CONNEXION ----
    case 'connexion':
        $email = strtolower(trim($data['email'] ?? ''));
        $pass  = $data['mot_de_passe'] ?? '';

        if (!$email || !$pass) {
            jsonResponse(['succes' => false, 'message' => 'Veuillez remplir tous les champs.'], 400);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['succes' => false, 'message' => 'Adresse email invalide.'], 400);
        }

        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE LOWER(email) = ? AND actif = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Utilisateur inexistant
        if (!$user) {
            jsonResponse([
                'succes'  => false,
                'message' => 'Aucun compte trouvé avec cet email. Veuillez créer un compte.',
                'action'  => 'inscrire' // indice pour le JS
            ], 401);
        }

        // Mot de passe incorrect
        if (!password_verify($pass, $user['mot_de_passe'])) {
            jsonResponse(['succes' => false, 'message' => 'Mot de passe incorrect.'], 401);
        }

        // Connexion réussie
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_nom']  = $user['prenom'] . ' ' . $user['nom'];
        $_SESSION['user_role'] = $user['role'];

        jsonResponse([
            'succes'  => true,
            'message' => 'Connexion réussie ! Bienvenue ' . $user['prenom'] . ' !',
            'user'    => [
                'id'        => $user['id'],
                'prenom'    => $user['prenom'],
                'nom'       => $user['nom'],
                'email'     => $user['email'],
                'telephone' => $user['telephone'],
                'role'      => $user['role'],
            ]
        ]);
        break;

    // ---- INSCRIPTION ----
    case 'inscription':
        $prenom    = trim($data['prenom']      ?? '');
        $nom       = trim($data['nom']         ?? '');
        $email     = strtolower(trim($data['email'] ?? ''));
        $telephone = trim($data['telephone']   ?? '');
        $pass      = $data['mot_de_passe']     ?? '';

        // Validations
        if (!$prenom || !$nom || !$email || !$pass) {
            jsonResponse(['succes' => false, 'message' => 'Tous les champs obligatoires doivent être remplis.'], 400);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['succes' => false, 'message' => 'Adresse email invalide.'], 400);
        }
        if (strlen($pass) < 8) {
            jsonResponse(['succes' => false, 'message' => 'Le mot de passe doit faire au moins 8 caractères.'], 400);
        }

        $db = getDB();

        // Vérifier si email déjà utilisé (SEULEMENT les comptes clients normaux)
        $check = $db->prepare("SELECT id, prenom FROM utilisateurs WHERE LOWER(email) = ?");
        $check->execute([$email]);
        $existant = $check->fetch();

        if ($existant) {
            jsonResponse([
                'succes'  => false,
                'message' => 'Un compte existe déjà avec cet email. Veuillez vous connecter.',
                'action'  => 'connecter'
            ], 409);
        }

        // Créer le compte
        $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
        $insert = $db->prepare("
            INSERT INTO utilisateurs (prenom, nom, email, telephone, mot_de_passe, role, actif)
            VALUES (?, ?, ?, ?, ?, 'client', 1)
        ");
        $insert->execute([$prenom, $nom, $email, $telephone, $hash]);
        $newId = (int)$db->lastInsertId();

        // Créer la session
        session_regenerate_id(true);
        $_SESSION['user_id']   = $newId;
        $_SESSION['user_nom']  = "$prenom $nom";
        $_SESSION['user_role'] = 'client';

        jsonResponse([
            'succes'  => true,
            'message' => "Compte créé ! Bienvenue $prenom !",
            'user'    => [
                'id'        => $newId,
                'prenom'    => $prenom,
                'nom'       => $nom,
                'email'     => $email,
                'telephone' => $telephone,
                'role'      => 'client',
            ]
        ]);
        break;

    default:
        jsonResponse(['erreur' => 'Action inconnue.'], 400);
}