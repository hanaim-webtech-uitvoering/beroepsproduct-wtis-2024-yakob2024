<?php
require_once 'db_connectie.php';
session_start();


if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'Personnel') {
    header("Location: index.php");
    exit;
}

$personeelsNaam = $_SESSION['first_name'] ?? $_SESSION['username'];

$updateMelding = "";
$foutmelding   = "";


function statusNaarTekst($status)
{
    switch ((int)$status) {
        case 0: return "In behandeling";
        case 1: return "Onderweg";
        case 2: return "Bezorgd";
        default: return "Onbekend";
    }
}


if (isset($_POST['update_status']) && isset($_POST['order_id'], $_POST['status'])) {
    $orderId      = (int) $_POST['order_id'];
    $nieuweStatus = (int) $_POST['status'];

    
    if ($orderId > 0 && in_array($nieuweStatus, [0, 1, 2], true)) {
        try {
            $db = maakVerbinding();

            $sqlUpdate = "UPDATE Pizza_Order
                          SET status = :status,
                              personnel_username = :personeel
                          WHERE order_id = :order_id";

            $stmtUpdate = $db->prepare($sqlUpdate);
            $stmtUpdate->execute([
                ':status'   => $nieuweStatus,
                ':personeel'=> $_SESSION['username'],
                ':order_id' => $orderId
            ]);

            $updateMelding = "Status van bestelling #" . $orderId . " is bijgewerkt.";
        } catch (PDOException $e) {
            $foutmelding = "Er ging iets mis bij het bijwerken van de status. Probeer het later opnieuw.";
        }
    }
}


$bestellingen = [];

try {
    $db = maakVerbinding();

    
    $sql = "SELECT order_id,
                   client_username,
                   client_name,
                   datetime,
                   status,
                   address,
                   personnel_username
            FROM Pizza_Order
            WHERE status IN (0,1)
            ORDER BY datetime DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $bestellingen = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $foutmelding = "Er ging iets mis bij het ophalen van de bestellingen.";
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Bestellingsoverzicht personeel - Pizzeria Sole Machina</title>
</head>
<body>

<h1>Bestellingsoverzicht personeel</h1>

<p>
    Ingelogd als 
    <strong><?= htmlspecialchars($personeelsNaam, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong>.
</p>

<p>
    <a href="menu.php">Menu</a> |
    <a href="loguit.php">Uitloggen</a>
</p>

<hr>

<?php if ($updateMelding !== ""): ?>
    <p style="color: green;">
        <?= htmlspecialchars($updateMelding, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </p>
<?php endif; ?>

<?php if ($foutmelding !== ""): ?>
    <p style="color: red;">
        <?= htmlspecialchars($foutmelding, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </p>
<?php endif; ?>

<?php if (empty($bestellingen) && $foutmelding === ""): ?>

    <p>Er zijn momenteel geen actieve bestellingen (in behandeling of onderweg).</p>

<?php else: ?>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
        <tr>
            <th>Bestelnummer</th>
            <th>Klant</th>
            <th>Datum / tijd</th>
            <th>Status</th>
            <th>Afleveradres</th>
            <th>Toegewezen personeelslid</th>
            <th>Nieuwe status instellen</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($bestellingen as $bestelling): ?>
            <tr>
                <td>
                    <?= htmlspecialchars($bestelling['order_id'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </td>
                <td>
                    <?= htmlspecialchars($bestelling['client_name'] ?? $bestelling['client_username'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </td>
                <td>
                    <?= htmlspecialchars($bestelling['datetime'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </td>
                <td>
                    <?= htmlspecialchars(statusNaarTekst($bestelling['status']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </td>
                <td>
                    <?= htmlspecialchars($bestelling['address'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </td>
                <td>
                    <?= htmlspecialchars($bestelling['personnel_username'] ?? 'Nog niet toegewezen', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </td>
                <td>
                    <form method="post" action="overzichtpersoneel.php">
                        <input type="hidden" name="order_id"
                               value="<?= htmlspecialchars($bestelling['order_id'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">

                        <select name="status">
                            <option value="0" <?= (int)$bestelling['status'] === 0 ? 'selected' : '' ?>>
                                In behandeling
                            </option>
                            <option value="1" <?= (int)$bestelling['status'] === 1 ? 'selected' : '' ?>>
                                Onderweg
                            </option>
                            <option value="2" <?= (int)$bestelling['status'] === 2 ? 'selected' : '' ?>>
                                Bezorgd
                            </option>
                        </select>

                        <input type="submit" name="update_status" value="Status bijwerken">
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

<?php endif; ?>

<p><a href="index.php">Terug naar startpagina</a></p>

</body>
</html>
