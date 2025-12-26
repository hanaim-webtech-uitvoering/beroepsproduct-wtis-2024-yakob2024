<?php
// Autorisatie functies voor verwerklaag (login/rol afdwingen en onbevoegde toegang blokkeren)

// Directe toegang via URL blokkeren (dit bestand is bedoeld om te includen)
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    header('Location: /view/index.php');
    exit;
}

/**
 * Sessie veilig beschikbaar maken (zonder dubbele session_start warnings)
 */
function ensureSessionStarted(): void
{
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
 * Rollen volgens database (consistent)
 */
function isPersonnelRole(?string $role): bool
{
    return $role === 'Personnel';
}

function isCustomerRole(?string $role): bool
{
    // In jouw dataset komen Customer/Client voor én legacy testdata 'klant'
    return $role === 'Customer' || $role === 'Client' || $role === 'klant';
}

/**
 * Login verplicht stellen (anders redirect)
 */
function requireLogin(string $redirectTo = '/view/login.php'): void
{
    if (!isLoggedIn()) {
        header('Location: ' . $redirectTo);
        exit;
    }
}

/**
 * Specifieke rol verplicht stellen (anders redirect)
 */
function requireRole(string $role, string $redirectTo = '/view/index.php'): void
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
function requireAnyRole(array $roles, string $redirectTo = '/view/index.php'): void
{
    requireLogin();

    $current = currentRole();
    if ($current === null || !in_array($current, $roles, true)) {
        header('Location: ' . $redirectTo);
        exit;
    }
}

/**
 * Personeel verplicht stellen (Personnel)
 */
function requirePersonnel(string $redirectTo = '/view/index.php'): void
{
    requireLogin();

    if (!isPersonnelRole(currentRole())) {
        header('Location: ' . $redirectTo);
        exit;
    }
}

/**
 * Klant verplicht stellen (Customer, Client of legacy: klant)
 */
function requireCustomer(string $redirectTo = '/view/index.php'): void
{
    requireLogin();

    if (!isCustomerRole(currentRole())) {
        header('Location: ' . $redirectTo);
        exit;
    }
}
