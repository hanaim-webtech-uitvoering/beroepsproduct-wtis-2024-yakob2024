<?php
require_once 'db_connectie.php';
session_start();


$ingelogd = isset($_SESSION['username']);
$rol = $_SESSION['role'] ?? '';

$producten = [];
$foutmelding = '';


try {
    $db = maakVerbinding();

    
    $sql = "SELECT name, price FROM dbo.Product ORDER BY name";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $producten = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $foutmelding = "Er ging iets mis bij het ophalen van het menu. Probeer het later opnieuw.";
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Pizzeria Sole Machina - Menu</title>
</head>
<body>

<h1>Menu - Pizzeria Sole Machina</h1>

<p>
    Op deze pagina zie je alle producten uit het menu.<br>
    <?php if ($ingelogd): ?>
        Je bent ingelogd als <strong><?= htmlspecialchars($_SESSION['username'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong>.
        Je kunt producten aan je <a href="winkelmandje.php">winkelmandje</a> toevoegen.
    <?php else: ?>
        Je bent niet ingelogd. Je kunt het menu wel bekijken, maar om te bestellen moet je eerst
        <a href="index.php">inloggen of registreren</a>.
    <?php endif; ?>
</p>

<hr>

<?php if ($foutmelding !== ''): ?>
    <p style="color: red;">
        <?= htmlspecialchars($foutmelding, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
    </p>
<?php endif; ?>

<?php if (empty($producten) && $foutmelding === ''): ?>
    <p>Er zijn momenteel geen producten beschikbaar.</p>
<?php else: ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
        <tr>
            <th>Product</th>
            <th>Prijs (&euro;)</th>
            <?php if ($ingelogd): ?>
                <th>Bestellen</th>
            <?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($producten as $product): ?>
            <tr>
                <td>
                    <?= htmlspecialchars($product['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </td>
                <td>
                    <?= number_format((float)$product['price'], 2, ',', '.') ?>
                </td>

                <?php if ($ingelogd): ?>
                    <td>
                        <form method="post" action="winkelmandje.php">
                            <input type="hidden" name="product_name"
                                   value="<?= htmlspecialchars($product['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                            <input type="hidden" name="price"
                                   value="<?= htmlspecialchars($product['price'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                            Aantal:
                            <input type="number" name="quantity" value="1" min="1" required>
                            <input type="submit" value="Voeg toe aan winkelmandje">
                        </form>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<p>
    <a href="index.php">Terug naar startpagina</a> |
    <a href="privacy.php">Privacyverklaring</a>
    <?php if ($ingelogd): ?>
        | <a href="winkelmandje.php">Bekijk winkelmandje</a>
    <?php endif; ?>
</p>

</body>
</html>
