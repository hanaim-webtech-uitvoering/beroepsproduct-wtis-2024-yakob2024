<?php
// Autorisatie functies voor verwerklaag (login/rol afdwingen en onbevoegde toegang blokkeren)

// Directe toegang via URL blokkeren (dit bestand is bedoeld om te includen)
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    header('Location: ../view/index.php');
    exit;
}

/**
 * Sessie veilig beschikbaar maken (zonder dubbele session_start warnings)
 */
function ensureSessionStarted(): void
{
    // Sessie starten als dat nog niet gebeurd is
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

/**
 * Controleren of gebruiker is ingelogd
 */
function isLoggedIn(): bool
{
    ensureSessionStarted();
    return isset($_SESSION['username']) && $_SESSION['username'] !== '';
}

/**
 * Huidige rol ophalen uit sessie
 */
function currentRole(): ?string
{
    ensureSessionStarted();
    return $_SESSION['role'] ?? null;
}

/**
 * Login verplicht stellen (anders redirect)
 */
function requireLogin(string $redirectTo = '/applicatie/view/login.php'): void
{
    if (!isLoggedIn()) {
        header('Location: ' . $redirectTo);
        exit;
    }
}

/**
 * Specifieke rol verplicht stellen (anders redirect)
 */
function requireRole(string $role, string $redirectTo = '/applicatie/view/index.php'): void
{
    requireLogin();

    $current = currentRole();
    if ($current !== $role) {
        header('Location: ' . $redirectTo);
        exit;
    }
}

/**
 * Een van meerdere rollen toestaan (anders redirect)
 */
function requireAnyRole(array $roles, string $redirectTo = '/applicatie/view/index.php'): void
{
    requireLogin();

    $current = currentRole();
    if ($current === null || !in_array($current, $roles, true)) {
        header('Location: ' . $redirectTo);
        exit;
    }
}
