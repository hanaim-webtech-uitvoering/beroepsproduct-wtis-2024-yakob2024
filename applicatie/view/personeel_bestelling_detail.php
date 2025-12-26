<?php
// Personeel bestelling detail tonen (presentatie) met herbruikbare header/footer

$pageTitle = 'Bestelling detail (personeel) - Pizzeria Sole Machina';

// Verwerklaag: order ophalen
require_once __DIR__ . '/../verwerk/personeel_bestelling_detail_verwerk.php';

// Layout
require_once __DIR__ . '/../herbruikbaar/header.php';

// Flash melding (na status update)
$flash = $_SESSION['personeel_flash'] ?? null;
unset($_SESSION['personeel_flash']);
?>

<section>
    <h1>Bestelling detail (personeel)</h1>

    <?php if (!empty($flash)): ?>
        <p><?= htmlspecialchars($flash) ?></p>
    <?php endif; ?>

    <?php if (!empty($fout)): ?>
        <div>
            <h2>Er ging iets mis</h2>
            <p><?= htmlspecialchars($fout) ?></p>
        </div>
        <p><a href="/view/overzichtpersoneel.php">Terug naar overzicht</a></p>

    <?php elseif (!$order): ?>
        <p>Bestelling niet beschikbaar.</p>
        <p><a href="/view/overzichtpersoneel.php">Terug naar overzicht</a></p>

    <?php else: ?>
        <?php
            $orderId = (int)($order['order_id'] ?? 0);
            $dt = (string)($order['datetime'] ?? '');
            $clientName = (string)($order['client_name'] ?? '');
            $addr = (string)($order['address'] ?? '');
            $currentStatus = isset($order['status']) ? (int)$order['status'] : 1;
        ?>

        <h2>Order #<?= $orderId ?></h2>

        <table>
            <tr>
                <th>Datum/tijd</th>
                <td><?= htmlspecialchars($dt) ?></td>
            </tr>
            <tr>
                <th>Klant</th>
                <td><?= htmlspecialchars($clientName) ?></td>
            </tr>
            <tr>
                <th>Adres</th>
                <td><?= htmlspecialchars($addr) ?></td>
            </tr>
        </table>

        <h2>Status wijzigen</h2>

        <form method="post" action="/verwerk/personeel_status_verwerk.php" autocomplete="off">
            <input type="hidden" name="order_id" value="<?= $orderId ?>">

            <label for="status">Nieuwe status</label><br>
            <select id="status" name="status">
                <?php foreach ($statusOptions as $value => $label): ?>
                    <option value="<?= (int)$value ?>" <?= ($currentStatus === (int)$value) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div style="margin-top: 10px;">
                <button type="submit">Opslaan</button>
            </div>
        </form>

        <h2>Producten</h2>

        <?php if (empty($lines)): ?>
            <p>Geen orderregels gevonden.</p>
        <?php else: ?>
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
                    <?php foreach ($lines as $line): ?>
                        <?php
                            $pname = (string)($line['product_name'] ?? '');
                            $qty = (int)($line['quantity'] ?? 0);
                            $price = (float)($line['price'] ?? 0);
                            $sub = $qty * $price;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($pname) ?></td>
                            <td><?= $qty ?></td>
                            <td>€ <?= number_format($price, 2, ',', '.') ?></td>
                            <td>€ <?= number_format($sub, 2, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p><strong>Totaal:</strong> € <?= number_format((float)$total, 2, ',', '.') ?></p>
        <?php endif; ?>

        <p style="margin-top: 12px;">
            <a href="/view/overzichtpersoneel.php">Terug naar overzicht</a>
        </p>
    <?php endif; ?>
</section>

<?php
require_once __DIR__ . '/../herbruikbaar/footer.php';
