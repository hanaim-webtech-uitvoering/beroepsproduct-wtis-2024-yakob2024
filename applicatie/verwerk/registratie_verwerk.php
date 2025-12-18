<?php
// Registratie verwerken (validatie, username-check, wachtwoord hashen, opslaan, sessie starten, redirect)
// Finetune: nette foutafhandeling bij databasefouten (geen fatal errors tonen)

session_start();

require_once __DIR__ . '/../data/user_data.php';

// Als gebruiker al is ingelogd: registratie is niet toegestaan
if (isset($_SESSION['username']) && $_SESSION['username'] !== '') {
    header('Location: /view/index.php');
    exit;
}

// Alleen POST verzoeken toestaan
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /view/registratie.php');
    exit;
}

// Invoer ophalen en opschonen
$firstName = trim($_POST['first_name'] ?? '');
$lastName  = trim($_POST['last_name'] ?? '');
$address   = trim($_POST['address'] ?? '');

$username  = trim($_POST['username'] ?? '');
$password  = (string)($_POST['password'] ?? '');
$password2 = (string)($_POST['password_confirm'] ?? '');

// Validatie: basis checks (server-side)
$errors = [];

// Voornaam / achternaam / adres
if ($firstName === '') {
    $errors[] = 'Voornaam is verplicht.';
}
if ($lastName === '') {
    $errors[] = 'Achternaam is verplicht.';
}
if ($address === '') {
    $errors[] = 'Adres is verplicht.';
}

// Username / wachtwoord
if ($username === '') {
    $errors[] = 'Gebruikersnaam is verplicht.';
}
if ($password === '') {
    $errors[] = 'Wachtwoord is verplicht.';
}
if ($password !== $password2) {
    $errors[] = 'Wachtwoorden komen niet overeen.';
}

// Oude invoer bewaren (geen wachtwoord)
$_SESSION['reg_old'] = [
    'first_name' => $firstName,
    'last_name'  => $lastName,
    'address'    => $address,
    'username'   => $username
];

// Bij validatiefouten terug
if (!empty($errors)) {
    $_SESSION['reg_errors'] = $errors;
    header('Location: /view/registratie.php');
    exit;
}

try {
    // Gebruikersnaam uniek houden
    if (userUsernameExists($username)) {
        $_SESSION['reg_errors'] = ['Gebruikersnaam is al in gebruik. Kies een andere.'];
        header('Location: /view/registratie.php');
        exit;
    }

    // Wachtwoord hashen
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Gebruiker opslaan (standaard rol: Customer)
    $ok = userCreate($username, $passwordHash, 'Customer', $firstName, $lastName, $address);

    if (!$ok) {
        throw new Exception('Insert mislukt.');
    }

    // Sessiebeveiliging + auto-login
    session_regenerate_id(true);
    $_SESSION['username'] = $username;
    $_SESSION['role'] = 'Customer';

    // Opruimen
    unset($_SESSION['reg_errors'], $_SESSION['reg_old']);

    // Succes → home
    header('Location: /view/index.php');
    exit;

} catch (Throwable $e) {
    // Geen technische details tonen aan gebruiker
    $_SESSION['reg_errors'] = [
        'Er is iets misgegaan bij het aanmaken van het account. Probeer het later opnieuw.'
    ];

    header('Location: /view/registratie.php');
    exit;
}
