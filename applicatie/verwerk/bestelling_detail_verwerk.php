<?php
// Bestelling detail ophalen en beveiligen (verwerklaag): klant mag alleen eigen order bekijken

require_once __DIR__ . '/sessie.php';
require_once __DIR__ . '/autorisatie.php';
require_once __DIR__ . '/../data/bestelling_detail_data.php';
require_once __DIR__ . '/../herbruikbaar/status.php';

startSecureSession();
requireCustomer('/view/login.php');

// Output voor view
$order = null;
$lines = [];
$total = 0.0;
$statusLabel = 'Onbekend';
$fout = null;

try {
    $username = (string)($_SESSION['username'] ?? '');
    if ($username === '') {
        $fout = 'Je bent niet ingelogd.';
        return;
    }

    $orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
    if ($orderId <= 0) {
        $fout = 'Ongeldig ordernummer.';
        return;
    }

    $order = bestellingDetailGetOrder($orderId);
    if ($order === null) {
        $fout = 'Bestelling niet gevonden.';
        return;
    }

    // Ownership check: klant mag alleen eigen order bekijken
    $clientUsername = (string)($order['client_username'] ?? '');
    if ($clientUsername !== $username) {
        $order = null;
        $fout = 'Je hebt geen toegang tot deze bestelling.';
        return;
    }

    $lines = bestellingDetailGetLinesWithPrice($orderId);
    $total = bestellingDetailGetTotal($orderId);

    // status label via herbruikbaar
    $statusLabel = orderStatusLabel(isset($order['status']) ? (int)$order['status'] : null);

} catch (Throwable $e) {
    $order = null;
    $lines = [];
    $total = 0.0;
    $statusLabel = 'Onbekend';
    $fout = 'Bestelling kon niet worden geladen.';
}
