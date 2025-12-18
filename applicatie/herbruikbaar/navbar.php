<?php
// Navigatiebalk genereren voor alle presentatiepagina's (alleen UI, geen autorisatie)

// We lezen alleen sessiewaarden; sessie wordt gestart in de view
$ingelogd = isset($_SESSION['username']);
$rol = $_SESSION['role'] ?? null;
$username = $_SESSION['username'] ?? null;
?>
<nav>
    <ul>
        <!-- Publieke links -->
        <li><a href="/view/index.php">Home</a></li>
        <li><a href="/view/menu.php">Menu</a></li>
        <li><a href="/view/privacy.php">Privacy</a></li>

        <?php if ($ingelogd): ?>
            <!-- Klant links -->
            <li><a href="/view/winkelmandje.php">Winkelmandje</a></li>
            <li><a href="/view/profiel.php">Profiel</a></li>

            <?php if ($rol === 'personeel'): ?>
                <!-- Personeel links -->
                <li><a href="/view/overzichtpersoneel.php">Bestellingen (Personeel)</a></li>
            <?php endif; ?>

            <!-- Account -->
            <li><a href="/verwerk/loguit_verwerk.php">Uitloggen</a></li>
        <?php else: ?>
            <!-- Niet ingelogd -->
            <li><a href="/view/login.php">Inloggen</a></li>
        <?php endif; ?>
    </ul>

    <?php if ($ingelogd && $username): ?>
        <div>
            <small>Ingelogd als: <?= htmlspecialchars($username) ?></small>
        </div>
    <?php endif; ?>
</nav>
