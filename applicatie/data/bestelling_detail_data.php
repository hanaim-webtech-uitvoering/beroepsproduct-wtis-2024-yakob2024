<?php
// Bestelling details ophalen uit database (Pizza_Order + Pizza_Order_Product) - alleen data-laag

require_once __DIR__ . '/db_connectie.php';

/**
 * Orderheader ophalen op order_id.
 * Retourneert associative array of null.
 */
function bestellingDetailGetOrder(int $orderId): ?array
{
    // Orderheader ophalen uit database
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
 * Orderregels ophalen op order_id.
 */
function bestellingDetailGetLines(int $orderId): array
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
 * Orderregels met prijs ophalen (JOIN met Product) voor subtotaalberekening.
 */
function bestellingDetailGetLinesWithPrice(int $orderId): array
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
 * Totaalbedrag berekenen voor order.
 */
function bestellingDetailGetTotal(int $orderId): float
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
    return (float)($row['total'] ?? 0);
}
 