<?php
// Profielgegevens ophalen uit database (User tabel) - alleen data-laag

require_once __DIR__ . '/db_connectie.php';

/**
 * Haal profielgegevens op voor een username.
 * Retourneert associative array of null als gebruiker niet bestaat.
 */
function profielGetUserByUsername(string $username): ?array
{
    // Gebruiker gegevens ophalen uit database
    $db = maakVerbinding();

    $sql = "
        SELECT
            username,
            first_name,
            last_name,
            address,
            role
        FROM dbo.[User]
        WHERE username = :username
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([':username' => $username]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}
