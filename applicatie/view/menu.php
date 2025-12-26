<?php
// Menu tonen (presentatie) met herbruikbare header/footer

session_start();

$pageTitle = 'Menu - Pizzeria Sole Machina';

// Verwerklaag: menu ophalen
require_once __DIR__ . '/../verwerk/menu_verwerk.php';

// Layout
require_once __DIR__ . '/../herbruikbaar/header.php';

// Rol bepalen voor UI: alleen klant mag toevoegen
$rol = $_SESSION['role'] ?? null;
$isCustomer = in_array($rol, ['Customer', 'Client', 'klant'], true);

// Producten groeperen per categorie
$perCategorie = [];
foreach ($producten as $p) {
    $categorie = (string)($p['category'] ?? 'Overig');
    if (!isset($perCategorie[$categorie])) {
        $perCategorie[$categorie] = [];
    }
    $perCategorie[$categorie][] = $p;
}
?>

<section>
    <h1>Menu</h1>

    <?php if ($menuFout): ?>
        <p><?= htmlspecialchars($menuFout) ?></p>

    <?php elseif (empty($perCategorie)): ?>
        <p>Het menu is momenteel niet beschikbaar.</p>

    <?php else: ?>

        <?php if ($isCustomer): ?>
            <p><a href="/view/winkelmandje.php">Ga naar winkelmandje</a></p>
        <?php endif; ?>

        <?php foreach ($perCategorie as $categorie => $items): ?>
            <h2><?= htmlspecialchars($categorie) ?></h2>

            <ul>
                <?php foreach ($items as $item): ?>
                    <?php
                        $naam  = (string)($item['product_name'] ?? '');
                        $prijs = (float)($item['price'] ?? 0);
                        $ingrediënten = $ingredMap[$naam] ?? [];

                        // Unieke id voor input (werkt ook bij spaties/specials)
                        $qtyId = 'qty_' . md5($naam);
                    ?>
                    <li style="margin-bottom: 14px;">
                        <div>
                            <strong><?= htmlspecialchars($naam) ?></strong>
                            – € <?= number_format($prijs, 2, ',', '.') ?>
                        </div>

                        <?php if (!empty($ingrediënten)): ?>
                            <div>
                                <small>
                                    Ingrediënten:
                                    <?= htmlspecialchars(implode(', ', $ingrediënten)) ?>
                                </small>
                            </div>
                        <?php endif; ?>

                        <?php if ($isCustomer): ?>
                            <form method="post" action="/view/winkelmandje.php" style="margin-top: 8px;">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_name" value="<?= htmlspecialchars($naam) ?>">

                                <label for="<?= htmlspecialchars($qtyId) ?>">Aantal:</label>
                                <input
                                    id="<?= htmlspecialchars($qtyId) ?>"
                                    type="number"
                                    name="qty"
                                    value="1"
                                    min="1"
                                    max="99"
                                >

                                <button type="submit">Toevoegen aan winkelmandje</button>
                            </form>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>

    <?php endif; ?>
</section>

<?php
require_once __DIR__ . '/../herbruikbaar/footer.php';
