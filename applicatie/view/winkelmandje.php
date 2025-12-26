<?php
// Winkelmandje tonen (presentatie) met herbruikbare header/footer

session_start();

$pageTitle = 'Winkelmandje - Pizzeria Sole Machina';

// Verwerklaag: autorisatie + cart berekening + POST acties
require_once __DIR__ . '/../verwerk/winkelmand_verwerk.php';

// Layout
require_once __DIR__ . '/../herbruikbaar/header.php';
?>

<section>
    <h1>Winkelmandje</h1>

    <?php if (!empty($flash)): ?>
        <p><?= htmlspecialchars($flash) ?></p>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div>
            <h2>Er ging iets mis</h2>
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (empty($cartItems)): ?>
        <p>Je winkelmandje is leeg.</p>
        <p><a href="/view/menu.php">Terug naar het menu</a></p>

    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Categorie</th>
                    <th>Prijs</th>
                    <th>Aantal</th>
                    <th>Subtotaal</th>
                    <th>Actie</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartItems as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                        <td><?= htmlspecialchars($item['category']) ?></td>
                        <td>€ <?= number_format((float)$item['price'], 2, ',', '.') ?></td>

                        <td>
                            <form method="post" action="/view/winkelmandje.php">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_name" value="<?= htmlspecialchars($item['product_name']) ?>">
                                <input type="number" name="qty" value="<?= (int)$item['qty'] ?>" min="0" max="99">
                                <button type="submit">Bijwerken</button>
                            </form>
                        </td>

                        <td>€ <?= number_format((float)$item['subtotal'], 2, ',', '.') ?></td>

                        <td>
                            <form method="post" action="/view/winkelmandje.php">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_name" value="<?= htmlspecialchars($item['product_name']) ?>">
                                <button type="submit">Verwijderen</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p><strong>Totaal:</strong> € <?= number_format((float)$cartTotal, 2, ',', '.') ?></p>

        <form method="post" action="/view/winkelmandje.php">
            <input type="hidden" name="action" value="clear">
            <button type="submit">Winkelmandje leegmaken</button>
        </form>

        <p><a href="/view/menu.php">Verder winkelen</a></p>

        <!-- Volgende stap in hoofdstap 7: bestelling bevestigen + adres (mandje.php/bevestiging) -->
        <p>
            <a href="/view/bevestiging.php">Doorgaan naar bestellen</a>
        </p>
    <?php endif; ?>
</section>

<?php
require_once __DIR__ . '/../herbruikbaar/footer.php';
