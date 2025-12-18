<?php
// Uitloggen verwerken (sessie beëindigen, cookie opruimen en redirect)

require_once __DIR__ . '/autorisatie.php';

ensureSessionStarted();

// Als iemand niet is ingelogd, sturen we gewoon netjes terug naar home
if (!isLoggedIn()) {
    header('Location: ../view/index.php');
    exit;
}

// Sessiedata leegmaken
$_SESSION = [];

// Sessiecookies opruimen (indien gebruikt)
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        (bool)$params['secure'],
        (bool)$params['httponly']
    );
}

// Sessieserver-side beëindigen
session_destroy();

// Redirect naar home
header('Location: ../view/index.php');
exit;
