<?php
// Mijn bestellingen ophalen en voorbereiden voor presentatie (verwerklaag)

require_once __DIR__ . '/sessie.php';
require_once __DIR__ . '/autorisatie.php';
require_once __DIR__ . '/../data/bestellingen_data.php';
require_once __DIR__ . '/../herbruikbaar/status.php';

// Veilige sessie
startSecureSession();

// Alleen klant mag eigen bestellingen bekijken
requireCustomer('/view/login.php');

// Output voor view
$orders = [];
$orderTotals = [];     // [order_id => float]
$statusLabels = [];    // [order_id => 'Tekst']
$fout = null;

try {
    $username = (string)($_SESSION['username'] ?? '');
    if ($username === '') {
        $fout = 'Je bent niet ingelogd.';
    } else {
        $orders = bestellingenGetOrdersByClient($username);

        foreach ($orders as $o) {
            $oid = (int)($o['order_id'] ?? 0);
            if ($oid <= 0) {
                continue;
            }

            // totaal per order
            $orderTotals[$oid] = bestellingenGetOrderTotal($oid);

            // status label via herbruikbaar
            $statusLabels[$oid] = orderStatusLabel(isset($o['status']) ? (int)$o['status'] : null);
        }
    }
} catch (Throwable $e) {
    $orders = [];
    $orderTotals = [];
    $statusLabels = [];
    $fout = 'Bestellingen konden niet worden geladen.';
}
