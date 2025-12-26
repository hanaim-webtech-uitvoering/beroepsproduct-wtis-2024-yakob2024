<?php
// Bestellingen opslaan in database (Pizza_Order + Pizza_Order_Product) - alleen data-laag

require_once __DIR__ . '/db_connectie.php';

/**
 * Kolommen ophalen voor een tabel
 */
function orderDbGetColumns(string $schema, string $table): array
{
    $db = maakVerbinding();

    $sql = "
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([':schema' => $schema, ':table' => $table]);

    return array_map(fn($r) => $r['COLUMN_NAME'], $stmt->fetchAll(PDO::FETCH_ASSOC));
}

/**
 * Eerste bestaande kolom kiezen uit kandidaten
 */
function orderPickFirstColumn(array $available, array $candidates): ?string
{
    foreach ($candidates as $c) {
        if (in_array($c, $available, true)) {
            return $c;
        }
    }
    return null;
}

/**
 * Order aanmaken in Pizza_Order en het nieuwe order-id teruggeven.
 *
 * Verwacht minimaal:
 * - gebruikersnaam
 * - adres
 * - status (bijv. 'Open' of 'Nieuw')
 */
function orderCreate(string $username, string $address, string $status = 'Open'): int
{
    $db = maakVerbinding();

    $orderCols = orderDbGetColumns('dbo', 'Pizza_Order');

    // PK kolom (OUTPUT INSERTED.<pk>)
    $pkCol = orderPickFirstColumn($orderCols, ['id', 'order_id', 'pizza_order_id', 'Pizza_Order_Id']);

    // username / user fk kolom
    $userCol = orderPickFirstColumn($orderCols, [
        'username', 'user_username', 'UserUsername', 'user', 'user_id', 'UserId'
    ]);

    // adres kolom
    $addressCol = orderPickFirstColumn($orderCols, [
        'address', 'delivery_address', 'DeliveryAddress', 'adres'
    ]);

    // status kolom
    $statusCol = orderPickFirstColumn($orderCols, [
        'status', 'order_status', 'OrderStatus'
    ]);

    if ($pkCol === null || $userCol === null || $addressCol === null || $statusCol === null) {
        throw new RuntimeException('Order schema mismatch: Pizza_Order kolommen konden niet worden bepaald.');
    }

    // INSERT met OUTPUT zodat we het nieuwe ID krijgen (SQL Server)
    $sql = "
        INSERT INTO dbo.Pizza_Order ([$userCol], [$addressCol], [$statusCol])
        OUTPUT INSERTED.[$pkCol] AS new_id
        VALUES (:username, :address, :status);
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':username' => $username,
        ':address'  => $address,
        ':status'   => $status
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row || !isset($row['new_id'])) {
        throw new RuntimeException('Order aanmaken mislukt: geen order-id teruggekregen.');
    }

    return (int)$row['new_id'];
}

/**
 * Orderregel toevoegen aan Pizza_Order_Product.
 *
 * We slaan product + qty op gekoppeld aan order_id.
 * Product kan op jouw DB via product_name of product_id lopen; we detecteren dat.
 */
function orderAddProduct(int $orderId, string $productName, int $qty): void
{
    $db = maakVerbinding();

    $opCols = orderDbGetColumns('dbo', 'Pizza_Order_Product');
    $pCols  = orderDbGetColumns('dbo', 'Product');

    // FK naar order
    $orderFkCol = orderPickFirstColumn($opCols, [
        'order_id', 'pizza_order_id', 'Pizza_Order_Id', 'PizzaOrderId'
    ]);

    // qty kolom
    $qtyCol = orderPickFirstColumn($opCols, [
        'quantity', 'qty', 'aantal'
    ]);

    // Product referentie in orderregel: product_id of product_name
    $opProductIdCol = orderPickFirstColumn($opCols, [
        'product_id', 'ProductId'
    ]);
    $opProductNameCol = orderPickFirstColumn($opCols, [
        'product_name', 'ProductName', 'name'
    ]);

    // Product tabel: id + naam
    $productIdCol = orderPickFirstColumn($pCols, ['id', 'product_id', 'ProductId']);
    $productNameCol = orderPickFirstColumn($pCols, ['name', 'product_name', 'ProductName']);

    if ($orderFkCol === null || $qtyCol === null) {
        throw new RuntimeException('Order schema mismatch: Pizza_Order_Product mist order/qty kolommen.');
    }

    // Eerst bepalen of we product_id route kunnen doen
    $useProductId = ($opProductIdCol !== null && $productIdCol !== null && $productNameCol !== null);

    if ($useProductId) {
        // product_id ophalen op basis van productnaam
        $sqlFind = "SELECT TOP 1 [$productIdCol] AS pid FROM dbo.Product WHERE [$productNameCol] = :pname";
        $stmtFind = $db->prepare($sqlFind);
        $stmtFind->execute([':pname' => $productName]);
        $row = $stmtFind->fetch(PDO::FETCH_ASSOC);

        if (!$row || !isset($row['pid'])) {
            throw new RuntimeException('Product niet gevonden in database: ' . $productName);
        }

        $pid = (int)$row['pid'];

        $sql = "
            INSERT INTO dbo.Pizza_Order_Product ([$orderFkCol], [$opProductIdCol], [$qtyCol])
            VALUES (:orderId, :productId, :qty);
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':orderId'   => $orderId,
            ':productId' => $pid,
            ':qty'       => $qty
        ]);
        return;
    }

    // Fallback: opslaan op productnaam in orderregel (als schema zo is)
    if ($opProductNameCol !== null) {
        $sql = "
            INSERT INTO dbo.Pizza_Order_Product ([$orderFkCol], [$opProductNameCol], [$qtyCol])
            VALUES (:orderId, :productName, :qty);
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':orderId'     => $orderId,
            ':productName' => $productName,
            ':qty'         => $qty
        ]);
        return;
    }

    throw new RuntimeException('Order schema mismatch: product-koppeling in Pizza_Order_Product kon niet worden bepaald.');
}
