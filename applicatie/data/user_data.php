<?php
// User gegevens ophalen en opslaan in de database (alleen data-laag)

require_once __DIR__ . '/db_connectie.php';

/**
 * Gebruiker ophalen op basis van gebruikersnaam
 * Retourneert: associative array met user-velden of null als niet gevonden
 */
function userFindByUsername(string $username): ?array
{
    // Databaseverbinding ophalen
    $db = maakVerbinding();

    // Gebruiker selecteren (prepared statement)
    $sql = "
        SELECT
            u.username,
            u.password,
            u.role,
            u.first_name,
            u.last_name,
            u.address
        FROM dbo.[User] u
        WHERE u.username = :username
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([':username' => $username]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user ?: null;
}

/**
 * Controleren of gebruikersnaam al bestaat
 * Retourneert: true als bestaat, anders false
 */
function userUsernameExists(string $username): bool
{
    // Databaseverbinding ophalen
    $db = maakVerbinding();

    // Bestaan controleren (prepared statement)
    $sql = "SELECT 1 AS bestaat FROM dbo.[User] WHERE username = :username";
    $stmt = $db->prepare($sql);
    $stmt->execute([':username' => $username]);

    return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Nieuwe gebruiker toevoegen
 * Verwacht: gehashte password string + verplichte profielvelden
 * Retourneert: true bij succes, false bij falen
 */
function userCreate(
    string $username,
    string $passwordHash,
    string $role,
    string $firstName,
    string $lastName,
    string $address
): bool {
    // Databaseverbinding ophalen
    $db = maakVerbinding();

    // Gebruiker opslaan (prepared statement)
    $sql = "
        INSERT INTO dbo.[User] (username, password, role, first_name, last_name, address)
        VALUES (:username, :password, :role, :first_name, :last_name, :address)
    ";

    $stmt = $db->prepare($sql);

    return $stmt->execute([
        ':username'    => $username,
        ':password'    => $passwordHash,
        ':role'        => $role,
        ':first_name'  => $firstName,
        ':last_name'   => $lastName,
        ':address'     => $address
    ]);
}
