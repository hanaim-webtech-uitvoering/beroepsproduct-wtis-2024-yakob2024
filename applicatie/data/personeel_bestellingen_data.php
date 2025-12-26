<?php
// Personeel: bestellingen ophalen en status wijzigen - alleen data-laag

require_once __DIR__ . '/db_connectie.php';

/**
 * Actieve bestellingen ophalen.
 * Afspraak: status 1 = Nieuw, 2 = In behandeling, 3 = Afgerond.
 * "Actief" = status in (1,2).
 */
function personeelGetActiveOrders(): array
{
    // Actieve bestellingen ophalen uit database
    $db = maakVerbinding();

    $sql = "
        SELECT
            order_id,
            client_username,
            client_name,
            personnel_username,
            [datetime],
            status,
            address
        FROM dbo.Pizza_Order
        WHERE status IN (1, 2)
        ORDER BY [datetime] ASC, order_id ASC
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Afgeronde bestellingen ophalen (status = 3).
 */
function personeelGetCompletedOrders(): array
{
    // Afgeronde bestellingen ophalen uit database
    $db = maakVerbinding();

    $sql = "
        SELECT
            order_id,
            client_username,
            client_name,
            personnel_username,
            [datetime],
            status,
            address
        FROM dbo.Pizza_Order
        WHERE status = 3
        ORDER BY [datetime] DESC, order_id DESC
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Eén bestelling ophalen (header) op order_id.
 */
function personeelGetOrder(int $orderId): ?array
{
    // Bestelling header ophalen uit database
    $db = maakVerbinding();

    $sql = "
        SELECT
            order_id,
            client_username,
            client_name,
            personnel_username,
            [datetime],
            status,
            address
        FROM dbo.Pizza_Order
        WHERE order_id = :order_id
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([':order_id' => $orderId]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

/**
 * Orderregels ophalen voor personeel (JOIN met Product voor prijs).
 */
function personeelGetOrderLinesWithPrice(int $orderId): array
{
    // Orderregels + prijs ophalen via JOIN
    $db = maakVerbinding();

    $sql = "
        SELECT
            op.product_name,
            op.quantity,
            p.price
        FROM dbo.Pizza_Order_Product op
        INNER JOIN dbo.Product p
            ON p.name = op.product_name
        WHERE op.order_id = :order_id
        ORDER BY op.product_name ASC
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([':order_id' => $orderId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Status updaten voor een order.
 * Optioneel: personeel_username toewijzen (wordt gebruikt omdat kolom NOT NULL is in jouw database).
 */
function personeelUpdateOrderStatus(int $orderId, int $newStatus, string $personnelUsername): bool
{
    // Order status wijzigen in database
    $db = maakVerbinding();

    $sql = "
        UPDATE dbo.Pizza_Order
        SET status = :status,
            personnel_username = :personnel_username
        WHERE order_id = :order_id
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':status' => $newStatus,
        ':personnel_username' => $personnelUsername,
        ':order_id' => $orderId
    ]);

    return $stmt->rowCount() > 0;
}
