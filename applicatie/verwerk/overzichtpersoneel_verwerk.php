<?php
// Personeeloverzicht actieve + afgeronde bestellingen ophalen en voorbereiden voor presentatie (verwerklaag)

require_once __DIR__ . '/sessie.php';
require_once __DIR__ . '/autorisatie.php';
require_once __DIR__ . '/../data/personeel_bestellingen_data.php';
require_once __DIR__ . '/../herbruikbaar/status.php';

startSecureSession();
requirePersonnel('/view/login.php');

// Output voor view
$orders = [];             // actief
$completedOrders = [];    // afgerond
$statusLabels = [];       // [order_id => label]
$fout = null;

try {
    // Actief (1/2)
    $orders = personeelGetActiveOrders();

    // Afgerond (3)
    $completedOrders = personeelGetCompletedOrders();

    // Labels voor beide lijsten (centrale mapping)
    foreach ($orders as $o) {
        $oid = (int)($o['order_id'] ?? 0);
        if ($oid <= 0) {
            continue;
        }
        $statusLabels[$oid] = orderStatusLabel(isset($o['status']) ? (int)$o['status'] : null);
    }

    foreach ($completedOrders as $o) {
        $oid = (int)($o['order_id'] ?? 0);
        if ($oid <= 0) {
            continue;
        }
        $statusLabels[$oid] = orderStatusLabel(isset($o['status']) ? (int)$o['status'] : null);
    }

} catch (Throwable $e) {
    $orders = [];
    $completedOrders = [];
    $statusLabels = [];
    $fout = 'Bestellingen konden niet worden geladen.';
}
