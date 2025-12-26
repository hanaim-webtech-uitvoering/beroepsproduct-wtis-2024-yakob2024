<?php
// Mijn bestellingen tonen (presentatie) met herbruikbare header/footer

session_start();

$pageTitle = 'Mijn bestellingen - Pizzeria Sole Machina';

// Verwerklaag: bestellingen ophalen + totals + status labels
require_once __DIR__ . '/../verwerk/mijnbestellingen_verwerk.php';

// Layout
require_once __DIR__ . '/../herbruikbaar/header.php';
?>

<section>
    <h1>Mijn bestellingen</h1>

    <?php if (!empty($fout)): ?>
        <div>
            <h2>Er ging iets mis</h2>
            <p><?= htmlspecialchars($fout) ?></p>
        </div>

    <?php elseif (empty($orders)): ?>
        <p>Je hebt nog geen bestellingen geplaatst.</p>
        <p><a href="/view/menu.php">Ga naar het menu</a></p>

    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Datum/tijd</th>
                    <th>Status</th>
                    <th>Totaal</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                    <?php
                        $oid = (int)($o['order_id'] ?? 0);
                        $dt = (string)($o['datetime'] ?? '');
                        $statusTxt = $statusLabels[$oid] ?? 'Onbekend';
                        $total = $orderTotals[$oid] ?? 0;
                    ?>
                    <tr>
                        <td><?= $oid ?></td>
                        <td><?= htmlspecialchars($dt) ?></td>
                        <td><?= htmlspecialchars($statusTxt) ?></td>
                        <td>€ <?= number_format((float)$total, 2, ',', '.') ?></td>
                        <td>
                            <a href="/view/bestelling_detail.php?order_id=<?= $oid ?>">Bekijk</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p style="margin-top: 12px;">
        <a href="/view/profiel.php">Terug naar profiel</a>
    </p>
</section>

<?php
require_once __DIR__ . '/../herbruikbaar/footer.php';
