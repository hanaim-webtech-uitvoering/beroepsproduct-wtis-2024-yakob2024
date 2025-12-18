<?php
// Sessie veilig starten en basishygiëne toepassen (cookie flags, timeout, regeneratie helpers)

// Directe toegang via URL blokkeren (dit bestand is bedoeld om te includen)
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    header('Location: /view/index.php');
    exit;
}

/**
 * Veilige sessiestart met cookie-flags.
 * - HttpOnly: voorkomt toegang via JS
 * - SameSite: vermindert CSRF risico
 * - Secure: alleen via HTTPS (als HTTPS actief is)
 */
function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

    $cookieParams = session_get_cookie_params();

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => $cookieParams['path'] ?? '/',
        'domain' => $cookieParams['domain'] ?? '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

/**
 * Regeneratie helper voor login/privilege change.
 */
function regenerateSessionId(): void
{
    startSecureSession();
    session_regenerate_id(true);
}

/**
 * Basis sessie-timeout (in seconden).
 */
function enforceSessionTimeout(int $timeoutSeconds = 1800, string $redirectTo = '/view/login.php'): void
{
    startSecureSession();

    $now = time();
    $last = $_SESSION['last_activity'] ?? $now;

    if (($now - (int)$last) > $timeoutSeconds) {
        $_SESSION = [];
        session_destroy();
        header('Location: ' . $redirectTo);
        exit;
    }

    $_SESSION['last_activity'] = $now;
}
