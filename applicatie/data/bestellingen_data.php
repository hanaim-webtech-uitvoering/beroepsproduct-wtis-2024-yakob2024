<?php
// Bestellingen ophalen uit database (Pizza_Order + Pizza_Order_Product) - alleen data-laag

require_once __DIR__ . '/db_connectie.php';

/**
 * Bestellingen ophalen voor een klant (op client_username).
 * Retourneert lijst met basisgegevens.
 */
function bestellingenGetOrdersByClient(string $clientUsername): array
{
    // Bestellingen ophalen voor klant uit database
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
        WHERE client_username = :client_username
        ORDER BY [datetime] DESC, order_id DESC
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([':client_username' => $clientUsername]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Orderregels ophalen voor één order.
 */
function bestellingenGetOrderLines(int $orderId): array
{
    // Orderregels ophalen uit database
    $db = maakVerbinding();

    $sql = "
        SELECT
            order_id,
            product_name,
            quantity
        FROM dbo.Pizza_Order_Product
        WHERE order_id = :order_id
        ORDER BY product_name ASC
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([':order_id' => $orderId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Totaalbedrag berekenen voor één order via JOIN met Product.
 * Retourneert float.
 */
function bestellingenGetOrderTotal(int $orderId): float
{
    // Totaalbedrag berekenen via JOIN
    $db = maakVerbinding();

    $sql = "
        SELECT
            SUM(op.quantity * p.price) AS total
        FROM dbo.Pizza_Order_Product op
        INNER JOIN dbo.Product p
            ON p.name = op.product_name
        WHERE op.order_id = :order_id
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([':order_id' => $orderId]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total = $row['total'] ?? 0;

    return (float)$total;
}
