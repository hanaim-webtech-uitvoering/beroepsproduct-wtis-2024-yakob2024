<?php
// Personeel overzicht tonen (presentatie) met autorisatie via verwerklaag

session_start();

// Verwerklaag aanroepen (afdwingt role personeel + zet $actieveBestellingen klaar)
require_once __DIR__ . '/../verwerk/personeel_overzicht_verwerk.php';

$pageTitle = 'Personeel - Actieve bestellingen';

require_once __DIR__ . '/../herbruikbaar/header.php';
require_once __DIR__ . '/../herbruikbaar/navbar.php';
?>
<main>
    <section>
        <h1>Actieve bestellingen</h1>

        <?php if (empty($actieveBestellingen)): ?>
            <p>Er zijn momenteel geen actieve bestellingen (of de koppeling met de database komt in Hoofdstap 9).</p>
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
                                <a href="/applicatie/view/bestelling_detail.php?order_id=<?= urlencode((string)($order['order_id'] ?? '')) ?>">
                                    Details
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</main>
<?php
require_once __DIR__ . '/../herbruikbaar/footer.php';
