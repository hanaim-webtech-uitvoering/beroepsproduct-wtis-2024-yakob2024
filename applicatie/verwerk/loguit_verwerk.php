<?php
// Uitloggen verwerken (verwerklaag): sessie beëindigen en terugsturen naar home (PRG)

require_once __DIR__ . '/sessie.php';

startSecureSession();

// Sessie leegmaken
$_SESSION = [];

// Sessiecookie verwijderen (belangrijk)
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Sessie vernietigen
session_destroy();

// Terug naar home
header('Location: /view/index.php');
exit;
