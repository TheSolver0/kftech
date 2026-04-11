<?php
// api/auth.php
session_start();
require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../config/db.php';

function persistSessionCookie() {
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            session_id(),
            time() + 30 * 24 * 3600,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
}

function setPersistentUserCookie(array $user): void {
    $cookieUser = [
        'id' => $user['id'],
        'prenom' => $user['prenom'],
        'nom' => $user['nom'],
        'email' => $user['email'],
        'telephone' => $user['telephone'] ?? '',
        'role' => $user['role'] ?? 'client',
        'mot_de_passe_hash' => $user['mot_de_passe_hash'] ?? null,
    ];
    setcookie('kftech_user', json_encode($cookieUser, JSON_UNESCAPED_UNICODE), time() + 30 * 24 * 3600, '/', '', false, true);
}

function clearPersistentUserCookie(): void {
    setcookie('kftech_user', '', time() - 42000, '/');
}

function setUserSession(array $user): void {
    session_regenerate_id(true);
    persistSessionCookie();
    $_SESSION['user_id']        = $user['id'];
    $_SESSION['user_prenom']    = $user['prenom'];
    $_SESSION['user_nom']       = trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''));
    $_SESSION['user_email']     = $user['email'];
    $_SESSION['user_telephone'] = $user['telephone'] ?? '';
    $_SESSION['user_role']      = $user['role'] ?? 'client';
}

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
    clearPersistentUserCookie();
    session_destroy();
    session_start();
    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Vous avez été déconnecté avec succès.'];

    header('Location: ../index.php');
    exit;
}

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

        $storedUser = getCurrentUser();
        if (!$storedUser || strcasecmp($storedUser['email'], $email) !== 0) {
            jsonResponse([
                'succes'  => false,
                'message' => 'Aucun compte trouvé avec cet email. Veuillez créer un compte.',
                'action'  => 'inscrire'
            ], 401);
        }

        if (empty($storedUser['mot_de_passe_hash']) || !password_verify($pass, $storedUser['mot_de_passe_hash'])) {
            jsonResponse(['succes' => false, 'message' => 'Mot de passe incorrect.'], 401);
        }

        setUserSession($storedUser);
        setPersistentUserCookie($storedUser);

        jsonResponse([
            'succes'  => true,
            'message' => 'Connexion réussie ! Bienvenue ' . $storedUser['prenom'] . ' !',
            'user'    => [
                'id'        => $storedUser['id'],
                'prenom'    => $storedUser['prenom'],
                'nom'       => $storedUser['nom'],
                'email'     => $storedUser['email'],
                'telephone' => $storedUser['telephone'],
                'role'      => $storedUser['role'],
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

        if (!$prenom || !$nom || !$email || !$pass) {
            jsonResponse(['succes' => false, 'message' => 'Tous les champs obligatoires doivent être remplis.'], 400);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['succes' => false, 'message' => 'Adresse email invalide.'], 400);
        }
        if (strlen($pass) < 8) {
            jsonResponse(['succes' => false, 'message' => 'Le mot de passe doit faire au moins 8 caractères.'], 400);
        }

        $storedUser = getCurrentUser();
        if ($storedUser && strcasecmp($storedUser['email'], $email) === 0) {
            jsonResponse([
                'succes'  => false,
                'message' => 'Un compte existe déjà avec cet email. Veuillez vous connecter.',
                'action'  => 'connecter'
            ], 409);
        }

        $newId = 'local_' . bin2hex(random_bytes(5));
        $hash  = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
        $newUser = [
            'id' => $newId,
            'prenom' => $prenom,
            'nom' => $nom,
            'email' => $email,
            'telephone' => $telephone,
            'role' => 'client',
            'mot_de_passe_hash' => $hash,
        ];

        setUserSession($newUser);
        setPersistentUserCookie($newUser);

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

    // ---- SUPPRESSION DE COMPTE ----
    case 'delete_account':
        $storedUser = getCurrentUser();
        if (!$storedUser) {
            jsonResponse(['succes' => false, 'message' => 'Connexion requise.'], 401);
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        clearPersistentUserCookie();
        session_destroy();

        jsonResponse(['succes' => true, 'message' => 'Votre compte a bien été supprimé.']);
        break;

    default:
        jsonResponse(['erreur' => 'Action inconnue.'], 400);
}