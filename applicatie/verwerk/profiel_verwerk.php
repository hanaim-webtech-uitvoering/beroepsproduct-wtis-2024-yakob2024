<?php
// Profielgegevens ophalen en klaarzetten voor presentatie (verwerklaag)

require_once __DIR__ . '/sessie.php';
require_once __DIR__ . '/autorisatie.php';
require_once __DIR__ . '/../data/profiel_data.php';

// Veilige sessie
startSecureSession();

// Iedereen die ingelogd is mag profiel zien (klant én personeel)
requireLogin('/view/login.php');

// Output voor view
$user = null;
$profielFout = null;

try {
    $username = (string)($_SESSION['username'] ?? '');
    if ($username === '') {
        $profielFout = 'Je bent niet ingelogd.';
    } else {
        $user = profielGetUserByUsername($username);
        if ($user === null) {
            $profielFout = 'Profielgegevens konden niet worden geladen.';
        }
    }
} catch (Throwable $e) {
    $user = null;
    $profielFout = 'Profielgegevens konden niet worden geladen.';
}
