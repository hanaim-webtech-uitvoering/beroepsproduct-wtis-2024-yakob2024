<?php
// Navigatiebalk genereren (presentatie)
// Links tonen op basis van sessie (rol en loginstatus)

$ingelogd = isset($_SESSION['username']) && $_SESSION['username'] !== '';
$rol = $_SESSION['role'] ?? null;
?>
<nav>
    <ul>
        <!-- Publiek -->
        <li><a href="/view/index.php">Home</a></li>
        <li><a href="/view/menu.php">Menu</a></li>
        <li><a href="/view/privacy.php">Privacy</a></li>

        <?php if (!$ingelogd): ?>
            <!-- Niet ingelogd -->
            <li><a href="/view/login.php">Inloggen</a></li>

        <?php else: ?>
            <?php if ($rol === 'Personnel'): ?>
                <!-- Personeel -->
                <li><a href="/view/overzichtpersoneel.php">Bestellingen</a></li>

            <?php elseif ($rol === 'Customer' || $rol === 'Client'): ?>
                <!-- Klant -->
                <li><a href="/view/winkelmandje.php">Winkelmandje</a></li>
                <li><a href="/view/profiel.php">Profiel</a></li>

            <?php else: ?>
                <!-- Onbekende rol: minimale navigatie -->
                <!-- (Bewust geen extra links tonen) -->
            <?php endif; ?>

            <!-- Altijd voor ingelogde gebruikers -->
            <li><a href="/verwerk/loguit_verwerk.php">Uitloggen</a></li>
        <?php endif; ?>
    </ul>
</nav>
