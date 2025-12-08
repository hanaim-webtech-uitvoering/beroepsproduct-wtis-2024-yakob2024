<?php
require_once 'db_connectie.php';
session_start();


if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'Client') {
    header("Location: index.php");
    exit;
}

$username   = $_SESSION['username'];
$voornaam   = $_SESSION['first_name'] ?? $username;

$bestellingen = [];
$foutmelding  = "";


function statusNaarTekst($status)
{
    switch ((int)$status) {
        case 0: return "In behandeling";
        case 1: return "Onderweg";
        case 2: return "Bezorgd";
        default: return "Onbekend";
    }
}

try {
    $db = maakVerbinding();

    
    $sql = "SELECT 
                po.order_id,
                po.datetime,
                po.status,
                po.personnel_username,
                pop.product_name,
                pop.quantity
            FROM Pizza_Order po
            INNER JOIN Pizza_Order_Product pop 
                ON po.order_id = pop.order_id
            WHERE po.client_username = :username
            ORDER BY po.datetime DESC, po.order_id DESC, pop.product_name ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute([':username' => $username]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    foreach ($rows as $rij) {
        $orderId = $rij['order_id'];

        if (!isset($bestellingen[$orderId])) {
            $bestellingen[$orderId] = [
                'datetime'  => $rij['datetime'],
                'status'    => $rij['status'],
                'personeel' => $rij['personnel_username'],
                'producten' => []
            ];
        }

        $bestellingen[$orderId]['producten'][] = [
            'naam'   => $rij['product_name'],
            'aantal' => $rij['quantity']
        ];
    }

} catch (PDOException $e) {
    $foutmelding = "Er ging iets mis bij het ophalen van je bestellingen. Probeer het later opnieuw.";
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Mijn profiel - Pizzeria Sole Machina</title>
</head>
<body>

<h1>Mijn profiel</h1>

<p>
    Ingelogd als 
    <strong><?= htmlspecialchars($voornaam, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong>.
</p>


<p>
    <a href="menu.php">Menu bekijken</a> | 
    <a href="winkelmandje.php">Winkelmandje</a> | 
    <a href="loguit.php">Uitloggen</a>
</p>

<hr>

<h2>Mijn bestellingen</h2>

<?php if ($foutmelding !== ""): ?>
    <p style="color: red;">
        <?= htmlspecialchars($foutmelding, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </p>
<?php endif; ?>

<?php if (empty($bestellingen) && $foutmelding === ""): ?>
    <p>Je hebt nog geen bestellingen geplaatst.</p>
<?php elseif (!empty($bestellingen)): ?>

    <?php foreach ($bestellingen as $id => $info): ?>
        <h3>
            Bestelling #<?= htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            - <?= htmlspecialchars($info['datetime'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </h3>
        <p>
            <strong>Status:</strong>
            <?= htmlspecialchars(statusNaarTekst($info['status']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?><br>
            <strong>Personeelslid:</strong>
            <?= htmlspecialchars($info['personeel'] ?? 'Nog niet toegewezen', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </p>

        <?php if (!empty($info['producten'])): ?>
            <table border="1" cellpadding="5" cellspacing="0">
                <thead>
                <tr>
                    <th>Product</th>
                    <th>Aantal</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($info['producten'] as $product): ?>
                    <tr>
                        <td><?= htmlspecialchars($product['naam'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($product['aantal'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Geen producten gevonden voor deze bestelling.</p>
        <?php endif; ?>

        <br>
    <?php endforeach; ?>

<?php endif; ?>

<p><a href="index.php">Terug naar startpagina</a></p>

</body>
</html>
