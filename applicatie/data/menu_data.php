<?php
// Menu data ophalen uit database (producten + categorie + optioneel ingrediënten) - alleen data-laag

require_once __DIR__ . '/db_connectie.php';

/**
 * Kolomnamen ophalen voor een tabel (SQL Server standaard: INFORMATION_SCHEMA)
 */
function menuDbGetColumns(string $schema, string $table): array
{
    // Kolomnamen ophalen uit database
    $db = maakVerbinding();

    $sql = "
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':schema' => $schema,
        ':table'  => $table
    ]);

    return array_map(
        fn($r) => $r['COLUMN_NAME'],
        $stmt->fetchAll(PDO::FETCH_ASSOC)
    );
}

/**
 * Eerste bestaande kolom kiezen uit een lijst met kandidaten
 */
function menuPickFirstColumn(array $available, array $candidates): ?string
{
    // Juiste kolom kiezen zonder te gokken
    foreach ($candidates as $c) {
        if (in_array($c, $available, true)) {
            return $c;
        }
    }
    return null;
}

/**
 * Producten + categorie ophalen (JOIN Product -> ProductType)
 * Retourneert: lijst met [product_name, price, category]
 */
function menuGetProductsWithCategory(): array
{
    // Producten + categorie ophalen uit database
    $db = maakVerbinding();

    $productCols = menuDbGetColumns('dbo', 'Product');
    $typeCols    = menuDbGetColumns('dbo', 'ProductType');

    // Kandidaten: pasbaar bij varianten die we eerder hebben gezien in projecten
    $productNameCol = menuPickFirstColumn($productCols, ['name', 'product_name', 'productName']);
    $priceCol       = menuPickFirstColumn($productCols, ['price', 'unit_price', 'unitPrice']);
    $productTypeFk  = menuPickFirstColumn($productCols, [
        'type_id', 'type', 'type_name', 'typeName', 'product_type', 'product_type_id', 'producttype', 'producttype_id'
    ]);

    $typePkCol      = menuPickFirstColumn($typeCols, ['name', 'type_name', 'id', 'type_id', 'product_type_id']);
    $typeNameCol    = menuPickFirstColumn($typeCols, ['name', 'type_name']);

    if ($productNameCol === null || $priceCol === null || $productTypeFk === null || $typePkCol === null || $typeNameCol === null) {
        throw new RuntimeException('Menu schema mismatch: Product/ProductType kolommen konden niet worden bepaald.');
    }

    // Geen user-input in kolomnamen; deze komen uit INFORMATION_SCHEMA
    $sql = "
        SELECT
            p.[$productNameCol] AS product_name,
            p.[$priceCol]       AS price,
            pt.[$typeNameCol]   AS category
        FROM dbo.Product p
        INNER JOIN dbo.ProductType pt
            ON p.[$productTypeFk] = pt.[$typePkCol]
        ORDER BY pt.[$typeNameCol], p.[$productNameCol];
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Ingrediënten per product ophalen
 * Retourneert map: [product_name => [ingredient1, ingredient2, ...]]
 *
 * Opmerking: niet elk product hoeft ingrediënten te hebben (bijv. dranken / knoflookbrood in jouw DB).
 */
function menuGetIngredientsMap(): array
{
    // Ingrediënten per product ophalen uit database
    $db = maakVerbinding();

    // Tabellen uit casus: Product_Ingredient + Ingredient
    $piCols = menuDbGetColumns('dbo', 'Product_Ingredient');
    $iCols  = menuDbGetColumns('dbo', 'Ingredient');

    $piProductCol = menuPickFirstColumn($piCols, ['product_name', 'product', 'product_id', 'productId', 'name']);
    $piIngrCol    = menuPickFirstColumn($piCols, ['ingredient_name', 'ingredient', 'ingredient_id', 'ingredientId']);

    $ingrPkCol    = menuPickFirstColumn($iCols, ['name', 'ingredient_name', 'id', 'ingredient_id']);
    $ingrNameCol  = menuPickFirstColumn($iCols, ['name', 'ingredient_name']);

    // Als schema anders is: ingrediënten niet tonen (menu werkt dan nog steeds)
    if ($piProductCol === null || $piIngrCol === null || $ingrPkCol === null || $ingrNameCol === null) {
        return [];
    }

    $sql = "
        SELECT
            pi.[$piProductCol] AS product_name,
            i.[$ingrNameCol]   AS ingredient_name
        FROM dbo.Product_Ingredient pi
        INNER JOIN dbo.Ingredient i
            ON pi.[$piIngrCol] = i.[$ingrPkCol]
        ORDER BY pi.[$piProductCol], i.[$ingrNameCol];
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $map = [];
    foreach ($rows as $r) {
        $p = (string)($r['product_name'] ?? '');
        $ing = (string)($r['ingredient_name'] ?? '');
        if ($p === '' || $ing === '') {
            continue;
        }
        if (!isset($map[$p])) {
            $map[$p] = [];
        }
        $map[$p][] = $ing;
    }

    return $map;
}
