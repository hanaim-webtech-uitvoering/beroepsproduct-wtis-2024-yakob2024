<?php
// Autorisatie helpers (verwerklaag): login/rol checks + redirects

function isLoggedIn(): bool
{
    return isset($_SESSION['username']) && (string)$_SESSION['username'] !== '';
}

function currentRole(): string
{
    return (string)($_SESSION['role'] ?? '');
}

function requireLogin(string $redirectIfNotLoggedIn = '/view/login.php'): void
{
    // Login verplichten
    if (!isLoggedIn()) {
        header('Location: ' . $redirectIfNotLoggedIn);
        exit;
    }
}

function denyAccess(string $message = 'Geen toegang.', string $redirectTo = '/view/index.php'): void
{
    // Geen toegang -> naar home (ingelogd blijft ingelogd)
    $_SESSION['auth_flash'] = $message;
    header('Location: ' . $redirectTo);
    exit;
}

function requireCustomer(string $redirectIfNotLoggedIn = '/view/login.php'): void
{
    // Klantrol verplichten
    requireLogin($redirectIfNotLoggedIn);

    $role = currentRole();
    $isCustomer = in_array($role, ['Customer', 'Client', 'klant'], true);

    if (!$isCustomer) {
        denyAccess('Geen toegang: deze pagina is alleen voor klanten.', '/view/index.php');
    }
}

function requirePersonnel(string $redirectIfNotLoggedIn = '/view/login.php'): void
{
    // Personeelrol verplichten
    requireLogin($redirectIfNotLoggedIn);

    $role = currentRole();
    $isPersonnel = in_array($role, ['Personnel', 'personeel'], true);

    if (!$isPersonnel) {
        denyAccess('Geen toegang: deze pagina is alleen voor personeel.', '/view/index.php');
    }
}

/**
 * Voor login/registratie: als je al ingelogd bent, stuur door naar home.
 */
function redirectIfLoggedIn(string $redirectTo = '/view/index.php'): void
{
    if (isLoggedIn()) {
        header('Location: ' . $redirectTo);
        exit;
    }
}
