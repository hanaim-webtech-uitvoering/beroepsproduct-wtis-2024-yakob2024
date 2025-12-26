<?php
// Profiel tonen (presentatie) met herbruikbare header/footer

session_start();

$pageTitle = 'Profiel - Pizzeria Sole Machina';

// Verwerklaag: profieldata ophalen
require_once __DIR__ . '/../verwerk/profiel_verwerk.php';

// Layout
require_once __DIR__ . '/../herbruikbaar/header.php';

// Rol bepalen voor UI
$rol = $_SESSION['role'] ?? null;
$isCustomer = in_array($rol, ['Customer', 'Client', 'klant'], true);

// Flash melding (bijv. na bestellen)
$flash = $_SESSION['cart_flash'] ?? null;
unset($_SESSION['cart_flash']);
?>

<section>
    <h1>Profiel</h1>

    <?php if (!empty($flash)): ?>
        <p><?= htmlspecialchars($flash) ?></p>
    <?php endif; ?>

    <?php if (!empty($profielFout)): ?>
        <div>
            <h2>Er ging iets mis</h2>
            <p><?= htmlspecialchars($profielFout) ?></p>
        </div>

    <?php elseif (!$user): ?>
        <p>Profiel niet beschikbaar.</p>

    <?php else: ?>
        <table>
            <tr>
                <th>Gebruikersnaam</th>
                <td><?= htmlspecialchars($user['username'] ?? '') ?></td>
            </tr>
            <tr>
                <th>Voornaam</th>
                <td><?= htmlspecialchars($user['first_name'] ?? '') ?></td>
            </tr>
            <tr>
                <th>Achternaam</th>
                <td><?= htmlspecialchars($user['last_name'] ?? '') ?></td>
            </tr>
            <tr>
                <th>Adres</th>
                <td><?= htmlspecialchars($user['address'] ?? '') ?></td>
            </tr>
            <tr>
                <th>Rol</th>
                <td><?= htmlspecialchars($user['role'] ?? '') ?></td>
            </tr>
        </table>

        <?php if ($isCustomer): ?>
            <p style="margin-top: 12px;">
                <a href="/view/mijnbestellingen.php">Mijn bestellingen bekijken</a>
            </p>
        <?php endif; ?>
    <?php endif; ?>
</section>

<?php
require_once __DIR__ . '/../herbruikbaar/footer.php';
