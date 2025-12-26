<?php
// Personeeloverzicht tonen (presentatie) met herbruikbare header/footer

$pageTitle = 'Actieve bestellingen - Personeel';

// Verwerklaag: actieve orders ophalen
require_once __DIR__ . '/../verwerk/overzichtpersoneel_verwerk.php';

// Layout
require_once __DIR__ . '/../herbruikbaar/header.php';
?>

<section>
    <h1>Actieve bestellingen</h1>

    <?php if (!empty($fout)): ?>
        <div>
            <h2>Er ging iets mis</h2>
            <p><?= htmlspecialchars($fout) ?></p>
        </div>

    <?php elseif (empty($orders)): ?>
        <p>Er zijn momenteel geen actieve bestellingen.</p>

    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Datum/tijd</th>
                    <th>Klant</th>
                    <th>Adres</th>
                    <th>Status</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                    <?php
                        $oid = (int)($o['order_id'] ?? 0);
                        $dt = (string)($o['datetime'] ?? '');
                        $clientName = (string)($o['client_name'] ?? '');
                        $addr = (string)($o['address'] ?? '');
                        $statusTxt = $statusLabels[$oid] ?? 'Onbekend';
                    ?>
                    <tr>
                        <td><?= $oid ?></td>
                        <td><?= htmlspecialchars($dt) ?></td>
                        <td><?= htmlspecialchars($clientName) ?></td>
                        <td><?= htmlspecialchars($addr) ?></td>
                        <td><?= htmlspecialchars($statusTxt) ?></td>
                        <td>
                            <a href="/view/personeel_bestelling_detail.php?order_id=<?= $oid ?>">Bekijk</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p style="margin-top: 12px;">
        <a href="/view/index.php">Terug naar home</a>
    </p>
</section>

<?php
require_once __DIR__ . '/../herbruikbaar/footer.php';
