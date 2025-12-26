<?php
// Menu ophalen en klaarzetten voor presentatie (verwerklaag)

require_once __DIR__ . '/../data/menu_data.php';

// Output variabelen voor de view
$producten = [];
$ingredMap = [];
$menuFout = null;

try {
    $producten = menuGetProductsWithCategory();
    $ingredMap = menuGetIngredientsMap();
} catch (Throwable $e) {
    // Geen technische details tonen in de view
    $menuFout = 'Fout bij laden van menu.';
    $producten = [];
    $ingredMap = [];
}
