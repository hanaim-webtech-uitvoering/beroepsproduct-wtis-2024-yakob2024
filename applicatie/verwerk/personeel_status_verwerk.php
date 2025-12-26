<?php
// Personeel: status wijzigen verwerken (verwerklaag) + PRG redirect

require_once __DIR__ . '/sessie.php';
require_once __DIR__ . '/autorisatie.php';
require_once __DIR__ . '/../data/personeel_bestellingen_data.php';

startSecureSession();
requirePersonnel('/view/login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /view/overzichtpersoneel.php');
    exit;
}

$orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$newStatus = isset($_POST['status']) ? (int)$_POST['status'] : 0;

$allowedStatuses = [1, 2, 3];

if ($orderId <= 0 || !in_array($newStatus, $allowedStatuses, true)) {
    $_SESSION['personeel_flash'] = 'Ongeldige invoer. Status is niet aangepast.';
    header('Location: /view/overzichtpersoneel.php');
    exit;
}

$personnelUsername = (string)($_SESSION['username'] ?? '');
if ($personnelUsername === '') {
    $_SESSION['personeel_flash'] = 'Je bent niet ingelogd.';
    header('Location: /view/login.php');
    exit;
}

try {
    $ok = personeelUpdateOrderStatus($orderId, $newStatus, $personnelUsername);

    if ($ok) {
        $_SESSION['personeel_flash'] = 'Status is aangepast.';
    } else {
        $_SESSION['personeel_flash'] = 'Status aanpassen is mislukt.';
    }

    header('Location: /view/personeel_bestelling_detail.php?order_id=' . $orderId);
    exit;

} catch (Throwable $e) {
    $_SESSION['personeel_flash'] = 'Status aanpassen is mislukt.';
    header('Location: /view/personeel_bestelling_detail.php?order_id=' . $orderId);
    exit;
}
