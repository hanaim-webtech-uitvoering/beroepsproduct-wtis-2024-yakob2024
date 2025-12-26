<?php
// Bestelling bevestigen (presentatie): winkelmand-samenvatting + bezorgadres invullen

session_start();

$pageTitle = 'Bestelling bevestigen - Pizzeria Sole Machina';

// Verwerklaag winkelmand: autorisatie klant + items/totaal klaarzetten
require_once __DIR__ . '/../verwerk/winkelmand_verwerk.php';

// Layout
require_once __DIR__ . '/../herbruikbaar/header.php';

// Fouten/old values uit sessie (voor na POST in verwerklaag)
$errors = $_SESSION['confirm_errors'] ?? [];
$old = $_SESSION['confirm_old'] ?? ['address' => ''];
unset($_SESSION['confirm_errors'], $_SESSION['confirm_old']);
?>

<section>
    <h1>Bestelling bevestigen</h1>

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
        <p>Je winkelmandje is leeg. Je kunt geen bestelling plaatsen.</p>
        <p><a href="/view/menu.php">Terug naar het menu</a></p>

    <?php else: ?>
        <h2>Samenvatting</h2>

        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Aantal</th>
                    <th>Prijs</th>
                    <th>Subtotaal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartItems as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                        <td><?= (int)$item['qty'] ?></td>
                        <td>€ <?= number_format((float)$item['price'], 2, ',', '.') ?></td>
                        <td>€ <?= number_format((float)$item['subtotal'], 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p><strong>Totaal:</strong> € <?= number_format((float)$cartTotal, 2, ',', '.') ?></p>

        <h2>Bezorgadres</h2>

        <!-- Geen HTML required: validatie doen we in verwerklaag -->
        <form method="post" action="/verwerk/bevestiging_verwerk.php" autocomplete="off">
            <div>
                <label for="address">Adres</label><br>
                <input
                    type="text"
                    id="address"
                    name="address"
                    value="<?= htmlspecialchars($old['address'] ?? '') ?>"
                >
            </div>

            <div style="margin-top: 10px;">
                <button type="submit">Bestelling plaatsen</button>
            </div>
        </form>

        <p style="margin-top: 10px;">
            <a href="/view/winkelmandje.php">Terug naar winkelmandje</a>
        </p>
    <?php endif; ?>
</section>

<?php
require_once __DIR__ . '/../herbruikbaar/footer.php';
