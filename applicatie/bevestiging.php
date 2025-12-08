<?php
require_once 'db_connectie.php';
session_start();


if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'Client') {
    header("Location: index.php");
    exit;
}

$username = $_SESSION['username'];
$voornaam = $_SESSION['first_name'] ?? $username;


if (!isset($_SESSION['laatste_bestelling_id'])) {
    $orderId = null;
} else {
    $orderId = (int) $_SESSION['laatste_bestelling_id'];
}


function statusNaarTekst($status)
{
    switch ((int)$status) {
        case 0: return "In behandeling";
        case 1: return "Onderweg";
        case 2: return "Bezorgd";
        default: return "Onbekend";
    }
}

$bestelling = null;
$productregels = [];
$foutmelding = "";

if ($orderId !== null) {
    try {
        $db = maakVerbinding();

    
        $sqlOrder = "SELECT order_id, datetime, status, address, personnel_username
                     FROM Pizza_Order
                     WHERE order_id = :order_id
                       AND client_username = :username";

        $stmtOrder = $db->prepare($sqlOrder);
        $stmtOrder->execute([
            ':order_id' => $orderId,
            ':username' => $username
        ]);
        $bestelling = $stmtOrder->fetch(PDO::FETCH_ASSOC);

        if ($bestelling) {
            $sqlItems = "SELECT product_name, quantity
                         FROM Pizza_Order_Product
                         WHERE order_id = :order_id
                         ORDER BY product_name";

            $stmtItems = $db->prepare($sqlItems);
            $stmtItems->execute([':order_id' => $orderId]);
            $productregels = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $foutmelding = "De bestelling kon niet worden gevonden.";
        }

    } catch (PDOException $e) {
        $foutmelding = "Er ging iets mis bij het ophalen van de bestelling.";
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Bestelling bevestigd - Pizzeria Sole Machina</title>
</head>
<body>

<h1>Bestelling bevestigd</h1>

<p>
    Bedankt voor je bestelling,
    <strong><?= htmlspecialchars($voornaam, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong>!
</p>

<?php if ($foutmelding !== ""): ?>

    <p style="color: red;">
        <?= htmlspecialchars($foutmelding, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </p>
    <p>
        Ga naar je <a href="profiel.php">profiel</a> om je bestellingen te bekijken.
    </p>

<?php elseif ($bestelling === null): ?>

    <p>Er is geen recente bestelling gevonden in deze sessie.</p>
    <p>
        Ga naar je <a href="profiel.php">profiel</a> of bekijk het <a href="menu.php">menu</a>.
    </p>

<?php else: ?>

    <h2>Gegevens van je bestelling</h2>

    <p>
        <strong>Bestelnummer:</strong>
        <?= htmlspecialchars($bestelling['order_id'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?><br>
        <strong>Datum/tijd:</strong>
        <?= htmlspecialchars($bestelling['datetime'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?><br>
        <strong>Status:</strong>
        <?= htmlspecialchars(statusNaarTekst($bestelling['status']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?><br>
        <strong>Afleveradres:</strong>
        <?= htmlspecialchars($bestelling['address'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?><br>
    </p>

    <?php if (!empty($productregels)): ?>
        <h3>Bestelde producten</h3>
        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
            <tr>
                <th>Product</th>
                <th>Aantal</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($productregels as $regel): ?>
                <tr>
                    <td><?= htmlspecialchars($regel['product_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($regel['quantity'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p>
        Je kunt je bestelling later altijd terugzien onder
        <a href="profiel.php">Mijn profiel</a>.
    </p>

<?php endif; ?>

<p>
    <a href="menu.php">Verder bestellen</a> |
    <a href="profiel.php">Naar mijn profiel</a> |
    <a href="index.php">Terug naar startpagina</a>
</p>

</body>
</html>
