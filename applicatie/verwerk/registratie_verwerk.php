<?php
// Registratie verwerken (validatie, username-check, wachtwoord hashen, opslaan, redirect)

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

// Validatie: basis checks (server-side, geen HTML checks)
$errors = [];

// Voornaam / achternaam / adres (minimale checks, DB NOT NULL afdwingen)
if ($firstName === '') {
    $errors[] = 'Voornaam is verplicht.';
} elseif (strlen($firstName) > 100) {
    $errors[] = 'Voornaam is te lang.';
}

if ($lastName === '') {
    $errors[] = 'Achternaam is verplicht.';
} elseif (strlen($lastName) > 100) {
    $errors[] = 'Achternaam is te lang.';
}

if ($address === '') {
    $errors[] = 'Adres is verplicht.';
} elseif (strlen($address) > 255) {
    $errors[] = 'Adres is te lang.';
}

// Username / wachtwoord
if ($username === '') {
    $errors[] = 'Gebruikersnaam is verplicht.';
} elseif (strlen($username) < 3) {
    $errors[] = 'Gebruikersnaam moet minimaal 3 tekens zijn.';
} elseif (strlen($username) > 50) {
    $errors[] = 'Gebruikersnaam mag maximaal 50 tekens zijn.';
}

if ($password === '') {
    $errors[] = 'Wachtwoord is verplicht.';
} elseif (strlen($password) < 8) {
    $errors[] = 'Wachtwoord moet minimaal 8 tekens zijn.';
}

if ($password2 === '') {
    $errors[] = 'Bevestig je wachtwoord.';
} elseif ($password !== $password2) {
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
    $_SESSION['reg_errors'] = ['Registreren is niet gelukt. Probeer opnieuw.'];
    header('Location: /view/registratie.php');
    exit;
}

// Succes: naar login sturen
$_SESSION['reg_success'] = 'Account aangemaakt. Je kunt nu inloggen.';

// Oude registratie-state opruimen
unset($_SESSION['reg_errors'], $_SESSION['reg_old']);

header('Location: /view/login.php');
exit;
