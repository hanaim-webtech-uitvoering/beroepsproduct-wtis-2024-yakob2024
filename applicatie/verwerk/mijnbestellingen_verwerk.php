<?php
// Mijn bestellingen ophalen en voorbereiden voor presentatie (verwerklaag)

require_once __DIR__ . '/sessie.php';
require_once __DIR__ . '/autorisatie.php';
require_once __DIR__ . '/../data/bestellingen_data.php';

// Veilige sessie
startSecureSession();

// Alleen klant mag eigen bestellingen bekijken
requireCustomer('/view/login.php');

// Output voor view
$orders = [];
$orderTotals = [];     // [order_id => float]
$statusLabels = [];    // [order_id => 'Tekst']
$fout = null;

/**
 * Status mapping (int -> tekst)
 * Deze mapping maken we expliciet zodat het assesbaar is.
 * Als jouw docent andere statuscodes verwacht, passen we dit later aan.
 */
function mapStatusToLabel(?int $status): string
{
    // Order status label bepalen
    if ($status === null) {
        return 'Onbekend';
    }

    switch ($status) {
        case 1:
            return 'Nieuw';
        case 2:
            return 'In behandeling';
        case 3:
            return 'Afgerond';
        default:
            return 'Onbekend';
    }
}

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

            // totaal en statuslabel per order
            $orderTotals[$oid] = bestellingenGetOrderTotal($oid);
            $statusLabels[$oid] = mapStatusToLabel(isset($o['status']) ? (int)$o['status'] : null);
        }
    }
} catch (Throwable $e) {
    $orders = [];
    $orderTotals = [];
    $statusLabels = [];
    $fout = 'Bestellingen konden niet worden geladen.';
}
