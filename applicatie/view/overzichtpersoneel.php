<?php
// Personeel overzicht tonen (presentatie) - toegang via verwerklaag afgedwongen

session_start();

$pageTitle = 'Bestellingen (Personeel) - Pizzeria Sole Machina';

// Verwerklaag: autorisatie afdwingen + (later) data klaarzetten
require_once __DIR__ . '/../verwerk/personeel_overzicht_verwerk.php';

// Layout
require_once __DIR__ . '/../herbruikbaar/header.php';
?>

<section>
    <h1>Actieve bestellingen</h1>

    <?php if (empty($actieveBestellingen)): ?>
        <p>Er zijn momenteel geen actieve bestellingen (koppeling met database volgt bij personeel-orders).</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Ordernummer</th>
                    <th>Klant</th>
                    <th>Status</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($actieveBestellingen as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars((string)($order['order_id'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($order['username'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($order['status'] ?? '')) ?></td>
                        <td>
                            <a href="/view/bestelling_detail.php?order_id=<?= urlencode((string)($order['order_id'] ?? '')) ?>">
                                Details
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php
require_once __DIR__ . '/../herbruikbaar/footer.php';
