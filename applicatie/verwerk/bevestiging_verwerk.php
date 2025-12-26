<?php
// Bestelling plaatsen verwerken (verwerklaag): adres valideren + order opslaan in database (pizzeria schema)

require_once __DIR__ . '/sessie.php';
require_once __DIR__ . '/autorisatie.php';
require_once __DIR__ . '/../data/db_connectie.php';

startSecureSession();
requireCustomer('/view/login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /view/bevestiging.php');
    exit;
}

$cart = $_SESSION['cart'] ?? [];
if (!is_array($cart) || empty($cart)) {
    $_SESSION['confirm_errors'] = ['Je winkelmandje is leeg.'];
    header('Location: /view/bevestiging.php');
    exit;
}

$address = trim($_POST['address'] ?? '');
$errors = [];

if ($address === '') {
    $errors[] = 'Adres is verplicht.';
} elseif (mb_strlen($address) < 5) {
    $errors[] = 'Adres is te kort.';
} elseif (mb_strlen($address) > 200) {
    $errors[] = 'Adres is te lang.';
}

if (!empty($errors)) {
    $_SESSION['confirm_errors'] = $errors;
    $_SESSION['confirm_old'] = ['address' => $address];
    header('Location: /view/bevestiging.php');
    exit;
}

$db = null;

try {
    $db = maakVerbinding();

    $username = (string)($_SESSION['username'] ?? '');
    if ($username === '') {
        throw new RuntimeException('Gebruiker niet ingelogd.');
    }

    // client_name is NOT NULL: ophalen uit User (fallback = username)
    $clientName = $username;
    $sqlUser = "SELECT TOP 1 first_name, last_name FROM dbo.[User] WHERE username = :username";
    $stmtUser = $db->prepare($sqlUser);
    $stmtUser->execute([':username' => $username]);
    $u = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if ($u) {
        $fn = trim((string)($u['first_name'] ?? ''));
        $ln = trim((string)($u['last_name'] ?? ''));
        $full = trim($fn . ' ' . $ln);
        if ($full !== '') {
            $clientName = $full;
        }
    }

    // personnel_username is NOT NULL -> tijdelijk vullen met ingelogde username (bestaat zeker)
    // Later (personeelmodule) kan personnel_username aangepast worden bij toewijzing.
    $personnelUsername = $username;

    // status is INT. Default: 1 = nieuw/open (consistent met bestaande dataset)
    $status = 1;

    $db->beginTransaction();

    $sqlOrder = "
        INSERT INTO dbo.[Pizza_Order]
            ([client_username], [client_name], [personnel_username], [datetime], [status], [address])
        OUTPUT INSERTED.[order_id] AS new_id
        VALUES
            (:client_username, :client_name, :personnel_username, :dt, :status, :address);
    ";
    $stmtOrder = $db->prepare($sqlOrder);
    $stmtOrder->execute([
        ':client_username' => $username,
        ':client_name' => $clientName,
        ':personnel_username' => $personnelUsername,
        ':dt' => date('Y-m-d H:i:s'),
        ':status' => $status,
        ':address' => $address
    ]);

    $row = $stmtOrder->fetch(PDO::FETCH_ASSOC);
    if (!$row || !isset($row['new_id'])) {
        throw new RuntimeException('Order aanmaken mislukt: geen order_id teruggekregen.');
    }
    $orderId = (int)$row['new_id'];

    // Orderregels opslaan
    $sqlLine = "
        INSERT INTO dbo.[Pizza_Order_Product]
            ([order_id], [product_name], [quantity])
        VALUES
            (:order_id, :product_name, :qty);
    ";
    $stmtLine = $db->prepare($sqlLine);

    foreach ($cart as $productName => $qty) {
        $productName = trim((string)$productName);
        $qty = (int)$qty;

        if ($productName === '' || $qty <= 0) {
            continue;
        }

        $stmtLine->execute([
            ':order_id' => $orderId,
            ':product_name' => $productName,
            ':qty' => $qty
        ]);
    }

    $db->commit();

    // Cart opruimen
    $_SESSION['cart'] = [];
    unset($_SESSION['pending_order']);

    // Flash melding
    $_SESSION['cart_flash'] = 'Bestelling geplaatst. Ordernummer: ' . $orderId;

    // Redirect (tijdelijk naar profiel; later naar mijnbestellingen als die klaar is)
    header('Location: /view/profiel.php');
    exit;

} catch (Throwable $e) {
    if ($db instanceof PDO && $db->inTransaction()) {
        $db->rollBack();
    }

    // Alleen nette melding (geen technische details i.v.m. OWASP)
    $_SESSION['confirm_errors'] = ['Bestelling plaatsen is mislukt. Probeer het later opnieuw.'];
    $_SESSION['confirm_old'] = ['address' => $address];
    header('Location: /view/bevestiging.php');
    exit;
}
