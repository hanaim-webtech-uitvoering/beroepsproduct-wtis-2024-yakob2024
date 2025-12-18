<?php
// Login verwerken (validatie, gebruiker ophalen, wachtwoord checken, sessie starten, redirect)
// Sessiehygiëne: centrale sessie-helper gebruiken + sessie-id regenereren

require_once __DIR__ . '/sessie.php';
require_once __DIR__ . '/../data/user_data.php';

// Veilige sessiestart
startSecureSession();

// Alleen POST verzoeken toestaan
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /view/login.php');
    exit;
}

// Invoer ophalen en opschonen
$username = trim($_POST['username'] ?? '');
$password = (string)($_POST['password'] ?? '');

// Validatie: basis checks (server-side)
$errors = [];

if ($username === '') {
    $errors[] = 'Gebruikersnaam is verplicht.';
}
if ($password === '') {
    $errors[] = 'Wachtwoord is verplicht.';
}

// Bij validatiefouten terug
if (!empty($errors)) {
    $_SESSION['login_errors'] = $errors;
    $_SESSION['login_old'] = ['username' => $username];
    header('Location: /view/login.php');
    exit;
}

// Gebruiker ophalen uit database
$user = userFindByUsername($username);

// Generieke foutmelding (geen user enumeration)
if ($user === null) {
    $_SESSION['login_errors'] = ['Onjuiste gebruikersnaam of wachtwoord.'];
    $_SESSION['login_old'] = ['username' => $username];
    header('Location: /view/login.php');
    exit;
}

// Wachtwoord controleren
$hash = (string)($user['password'] ?? '');

if ($hash === '' || !password_verify($password, $hash)) {
    $_SESSION['login_errors'] = ['Onjuiste gebruikersnaam of wachtwoord.'];
    $_SESSION['login_old'] = ['username' => $username];
    header('Location: /view/login.php');
    exit;
}

// Sessiebeveiliging: nieuw sessie-id na login
regenerateSessionId();

// Rol uit database halen
$role = $user['role'] ?? null;
if ($role === null || $role === '') {
    $_SESSION['login_errors'] = ['Account heeft geen geldige rol. Neem contact op met beheer.'];
    $_SESSION['login_old'] = ['username' => $username];
    header('Location: /view/login.php');
    exit;
}

// Sessie vullen
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $role;

// Oude meldingen opruimen
unset($_SESSION['login_errors'], $_SESSION['login_old']);

// Redirect op rol
if ($role === 'Personnel') {
    header('Location: /view/overzichtpersoneel.php');
    exit;
}

header('Location: /view/index.php');
exit;
