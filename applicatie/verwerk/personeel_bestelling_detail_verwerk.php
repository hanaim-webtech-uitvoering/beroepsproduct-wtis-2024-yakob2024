<?php
// Personeel: bestelling detail ophalen en voorbereiden voor presentatie (verwerklaag)

require_once __DIR__ . '/sessie.php';
require_once __DIR__ . '/autorisatie.php';
require_once __DIR__ . '/../data/personeel_bestellingen_data.php';

startSecureSession();
requirePersonnel('/view/login.php');

// Output voor view
$order = null;
$lines = [];
$total = 0.0;
$fout = null;

// Status opties voor dropdown (int => label)
$statusOptions = [
    1 => 'Nieuw',
    2 => 'In behandeling',
    3 => 'Afgerond'
];

try {
    $orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
    if ($orderId <= 0) {
        $fout = 'Ongeldig ordernummer.';
        return;
    }

    $order = personeelGetOrder($orderId);
    if ($order === null) {
        $fout = 'Bestelling niet gevonden.';
        return;
    }

    $lines = personeelGetOrderLinesWithPrice($orderId);

    // totaal berekenen op basis van lines (geen extra query nodig)
    $sum = 0.0;
    foreach ($lines as $l) {
        $qty = (int)($l['quantity'] ?? 0);
        $price = (float)($l['price'] ?? 0);
        $sum += ($qty * $price);
    }
    $total = $sum;

} catch (Throwable $e) {
    $order = null;
    $lines = [];
    $total = 0.0;
    $fout = 'Bestelling kon niet worden geladen.';
}
