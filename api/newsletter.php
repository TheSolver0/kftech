<?php
// =============================================
// api/newsletter.php
// =============================================

session_start();
require_once __DIR__ . '/../config/api.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Lire le body JSON si envoyé en JSON
$body = [];
$rawInput = file_get_contents('php://input');
if ($rawInput) {
    $body = json_decode($rawInput, true) ?? [];
}
// Fusion POST + JSON body
$data = array_merge($_POST, $body);

switch ($action) {

    // ---- CONNEXION ----
    case 'connexion':
        $email = trim($data['email'] ?? '');
        $pass  = $data['mot_de_passe'] ?? '';

        if (!$email || !$pass) {
            jsonResponse(['succes' => false, 'message' => 'Email et mot de passe requis.'], 400);
        }

        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE email = ? AND actif = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($pass, $user['mot_de_passe'])) {
            jsonResponse(['succes' => false, 'message' => 'Email ou mot de passe incorrect.'], 401);
        }

        // Créer la session
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_nom']  = $user['prenom'] . ' ' . $user['nom'];
        $_SESSION['user_role'] = $user['role'];

        jsonResponse([
            'succes'  => true,
            'message' => 'Connexion réussie ! Bienvenue ' . $user['prenom'] . ' !',
            'user'    => [
                'id'       => $user['id'],
                'prenom'   => $user['prenom'],
                'nom'      => $user['nom'],
                'email'    => $user['email'],
                'telephone'=> $user['telephone'],
                'role'     => $user['role'],
            ]
        ]);
        break;

    // ---- INSCRIPTION ----
    case 'inscription':
        $prenom    = trim($data['prenom']       ?? '');
        $nom       = trim($data['nom']          ?? '');
        $email     = trim($data['email']        ?? '');
        $telephone = trim($data['telephone']    ?? '');
        $pass      = $data['mot_de_passe']      ?? '';

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

        // Vérifier si email déjà utilisé
        $check = $db->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            jsonResponse(['succes' => false, 'message' => 'Cette adresse email est déjà utilisée.'], 409);
        }

        // Hash du mot de passe
        $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);

        $insert = $db->prepare("
            INSERT INTO utilisateurs (prenom, nom, email, telephone, mot_de_passe)
            VALUES (?, ?, ?, ?, ?)
        ");
        $insert->execute([$prenom, $nom, $email, $telephone, $hash]);
        $newId = $db->lastInsertId();

        // Connecter automatiquement après inscription
        $_SESSION['user_id']   = $newId;
        $_SESSION['user_nom']  = "$prenom $nom";
        $_SESSION['user_role'] = 'client';

        jsonResponse([
            'succes'  => true,
            'message' => "Compte créé avec succès ! Bienvenue $prenom !",
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

    // ---- DÉCONNEXION ----
    case 'deconnexion':
        session_destroy();
        jsonResponse(['succes' => true, 'message' => 'Déconnexion réussie.']);
        break;

    // ---- VÉRIFIER SESSION ----
    case 'session':
        if (isLoggedIn()) {
            $user = getCurrentUser();
            jsonResponse(['connecte' => true, 'user' => $user]);
        } else {
            jsonResponse(['connecte' => false, 'user' => null]);
        }
        break;

    default:
        jsonResponse(['erreur' => 'Action inconnue'], 400);
}
