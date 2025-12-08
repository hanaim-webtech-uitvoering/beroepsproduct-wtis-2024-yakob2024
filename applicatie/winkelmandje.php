<?php
require_once 'db_connectie.php';
session_start();


if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'Client') {
    header("Location: index.php");
    exit;
}


if (!isset($_SESSION['winkelmandje']) || !is_array($_SESSION['winkelmandje'])) {
    $_SESSION['winkelmandje'] = [];
}


$adresFout  = "";
$mandjeFout = "";


$standaardAdres = "";
$adresUitPost   = null;


if (isset($_POST['adres'])) {
    $adresUitPost = trim($_POST['adres']);
}


try {
    $dbPrefill = maakVerbinding();

    $sqlLaatsteAdres = "SELECT TOP 1 address
                        FROM Pizza_Order
                        WHERE client_username = :username
                          AND address IS NOT NULL
                          AND address <> ''
                        ORDER BY datetime DESC";

    $stmtLaatste = $dbPrefill->prepare($sqlLaatsteAdres);
    $stmtLaatste->execute([':username' => $_SESSION['username']]);
    $resAdres = $stmtLaatste->fetchColumn();

    if ($resAdres !== false) {
        $standaardAdres = $resAdres;
    }
} catch (PDOException $e) {
}


if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['product_name']) &&
    isset($_POST['price']) &&
    !isset($_POST['afrekenen']) &&
    !isset($_POST['update'])
) {
    $product  = trim($_POST['product_name'] ?? '');
    $price    = isset($_POST['price']) ? (float) $_POST['price'] : 0;
    $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 0;

    if ($product !== "" && $price > 0 && $quantity > 0) {
        if (isset($_SESSION['winkelmandje'][$product])) {
            $_SESSION['winkelmandje'][$product]['quantity'] += $quantity;
        } else {
            $_SESSION['winkelmandje'][$product] = [
                'price'    => $price,
                'quantity' => $quantity
            ];
        }
    }
    header("Location: winkelmandje.php");
    exit;
}


if (isset($_GET['verwijder'])) {
    $teVerwijderen = $_GET['verwijder'];

    if (isset($_SESSION['winkelmandje'][$teVerwijderen])) {
        unset($_SESSION['winkelmandje'][$teVerwijderen]);
    }

    header("Location: winkelmandje.php");
    exit;
}


if (isset($_POST['update']) && isset($_POST['update_quantity']) && is_array($_POST['update_quantity'])) {
    foreach ($_POST['update_quantity'] as $product => $aantal) {
        $nieuwAantal = (int) $aantal;
        if ($nieuwAantal <= 0) {
            unset($_SESSION['winkelmandje'][$product]);
        } else {
            if (isset($_SESSION['winkelmandje'][$product])) {
                $_SESSION['winkelmandje'][$product]['quantity'] = $nieuwAantal;
            }
        }
    }
}


if (isset($_POST['afrekenen'])) {
    if (empty($_SESSION['winkelmandje'])) {
        $mandjeFout = "Je winkelmandje is leeg. Voeg eerst producten toe.";
    }

    $invoerAdres = trim($_POST['adres'] ?? '');
    $adresUitPost = $invoerAdres; 

    if ($invoerAdres === '') {
        $adresFout = "Vul een afleveradres in.";
    }

    if ($mandjeFout === "" && $adresFout === "") {
        try {
            $db = maakVerbinding();

            
            $sqlPers = "SELECT TOP 1 username FROM [user] WHERE role = 'Personnel' ORDER BY username";
            $stmtPers = $db->prepare($sqlPers);
            $stmtPers->execute();
            $personnelUsername = $stmtPers->fetchColumn();

            if ($personnelUsername === false) {
                $personnelUsername = null; 
            }

            
            $sqlOrder = "INSERT INTO Pizza_Order (client_username, client_name, personnel_username, datetime, status, address)
                         VALUES (:client_username, :client_name, :personnel_username, GETDATE(), :status, :address)";

            $stmtOrder = $db->prepare($sqlOrder);
            $stmtOrder->execute([
                ':client_username'    => $_SESSION['username'],
                ':client_name'        => $_SESSION['first_name'] ?? $_SESSION['username'],
                ':personnel_username' => $personnelUsername,
                ':status'             => 0,            
                ':address'            => $invoerAdres
            ]);

            
            $orderId = $db->lastInsertId();

            
            $sqlItem = "INSERT INTO Pizza_Order_Product (order_id, product_name, quantity)
                        VALUES (:order_id, :product_name, :quantity)";
            $stmtItem = $db->prepare($sqlItem);

            foreach ($_SESSION['winkelmandje'] as $productNaam => $gegevens) {
                $stmtItem->execute([
                    ':order_id'     => $orderId,
                    ':product_name' => $productNaam,
                    ':quantity'     => (int) $gegevens['quantity']
                ]);
            }

            
            $_SESSION['winkelmandje'] = [];

          
            $_SESSION['laatste_bestelling_id'] = $orderId;

            
            header("Location: Bevestiging.php");
            exit;

        } catch (PDOException $e) {
            $mandjeFout = "Er ging iets mis bij het plaatsen van je bestelling. Probeer het later opnieuw.";
        }
    }
}


$totaal = 0.0;
foreach ($_SESSION['winkelmandje'] as $productNaam => $gegevens) {
    $totaal += ((float) $gegevens['price']) * ((int) $gegevens['quantity']);
}


$adresVeldWaarde = '';
if ($adresUitPost !== null && $adresUitPost !== '') {
    $adresVeldWaarde = $adresUitPost;      
} elseif ($standaardAdres !== '') {
    $adresVeldWaarde = $standaardAdres;    
}

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Winkelmandje - Pizzeria Sole Machina</title>
</head>
<body>

<h1>Winkelmandje</h1>

<p>
    Ingelogd als 
    <strong><?= htmlspecialchars($_SESSION['first_name'] ?? $_SESSION['username'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong>.
</p>

<p>
    <a href="menu.php">Verder winkelen</a> | 
    <a href="profiel.php">Mijn profiel</a> | 
    <a href="loguit.php">Uitloggen</a>
</p>

<hr>

<?php if ($mandjeFout !== ""): ?>
    <p style="color: red;">
        <?= htmlspecialchars($mandjeFout, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </p>
<?php endif; ?>

<?php if (empty($_SESSION['winkelmandje'])): ?>

    <p>Je winkelmandje is leeg.</p>
    <p><a href="menu.php">Ga terug naar het menu om producten toe te voegen.</a></p>

<?php else: ?>

    <form method="post" action="winkelmandje.php">
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
            <tr>
                <th>Product</th>
                <th>Prijs (&euro;)</th>
                <th>Aantal</th>
                <th>Subtotaal (&euro;)</th>
                <th>Verwijderen</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($_SESSION['winkelmandje'] as $productNaam => $gegevens): 
                $subtotaal = ((float) $gegevens['price']) * ((int) $gegevens['quantity']);
            ?>
                <tr>
                    <td><?= htmlspecialchars($productNaam, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                    <td><?= number_format((float) $gegevens['price'], 2, ',', '.') ?></td>
                    <td>
                        <input
                            type="number"
                            name="update_quantity[<?= htmlspecialchars($productNaam, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>]"
                            value="<?= (int) $gegevens['quantity'] ?>"
                            min="1"
                            required
                        >
                    </td>
                    <td><?= number_format($subtotaal, 2, ',', '.') ?></td>
                    <td>
                        <a href="winkelmandje.php?verwijder=<?= urlencode($productNaam) ?>">Verwijder</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <p><strong>Totaal: &euro; <?= number_format($totaal, 2, ',', '.') ?></strong></p>

        <p>
            <label for="adres"><strong>Afleveradres (verplicht):</strong></label><br>
            <input
                type="text"
                name="adres"
                id="adres"
                style="width: 320px;"
                value="<?= htmlspecialchars($adresVeldWaarde, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                required
            >
        </p>

        <?php if ($adresFout !== ""): ?>
            <p style="color: red;">
                <?= htmlspecialchars($adresFout, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </p>
        <?php endif; ?>

        <p>
            <input type="submit" name="update" value="Hoeveelheden bijwerken">
            <input type="submit" name="afrekenen" value="Afrekenen">
        </p>
    </form>

<?php endif; ?>

<p><a href="index.php">Terug naar startpagina</a></p>

</body>
</html>
