<?php
// Personeel: bestelling detail ophalen en voorbereiden voor presentatie (verwerklaag)

require_once __DIR__ . '/sessie.php';
require_once __DIR__ . '/autorisatie.php';
require_once __DIR__ . '/../data/personeel_bestellingen_data.php';
require_once __DIR__ . '/../herbruikbaar/status.php';

startSecureSession();
requirePersonnel('/view/login.php');

// Output voor view
$order = null;
$lines = [];
$total = 0.0;
$fout = null;

// Status label (centrale mapping)
$statusLabel = 'Onbekend';

// Status opties voor dropdown (int => label)
$statusOptions = [
    1 => 'Nieuw',
    2 => 'In behandeling',
    3 => 'Afgerond'
];

try {
    $orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

    // UX: geen (geldige) order_id -> terug naar overzicht
    if ($orderId <= 0) {
        $_SESSION['auth_flash'] = 'Selecteer eerst een bestelling uit het overzicht.';
        header('Location: /view/overzichtpersoneel.php');
        exit;
    }

    $order = personeelGetOrder($orderId);

    // UX: order bestaat niet -> terug naar overzicht
    if ($order === null) {
        $_SESSION['auth_flash'] = 'Bestelling niet gevonden.';
        header('Location: /view/overzichtpersoneel.php');
        exit;
    }

    // Status label klaarzetten
    $statusLabel = orderStatusLabel(isset($order['status']) ? (int)$order['status'] : null);

    $lines = personeelGetOrderLinesWithPrice($orderId);

    // Totaal berekenen op basis van lines (geen extra query nodig)
    $sum = 0.0;
    foreach ($lines as $l) {
        $qty = (int)($l['quantity'] ?? 0);
        $price = (float)($l['price'] ?? 0);
        $sum += ($qty * $price);
    }
    $total = $sum;

} catch (Throwable $e) {
    $_SESSION['auth_flash'] = 'Bestelling kon niet worden geladen.';
    header('Location: /view/overzichtpersoneel.php');
    exit;
}
