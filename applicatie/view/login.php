<?php
// Loginpagina tonen (presentatie) met herbruikbare header/footer

session_start();

$pageTitle = 'Inloggen - Pizzeria Sole Machina';

require_once __DIR__ . '/../herbruikbaar/header.php';

// Fouten en oude invoer ophalen uit sessie (alleen presentatie)
$errors = $_SESSION['login_errors'] ?? [];
$old = $_SESSION['login_old'] ?? ['username' => ''];

// Na uitlezen opruimen (zodat refresh niet blijft tonen)
unset($_SESSION['login_errors'], $_SESSION['login_old']);
?>

<section>
    <h1>Inloggen</h1>

    <?php if (!empty($errors)): ?>
        <div>
            <h2>Er ging iets mis</h2>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="../verwerk/login_verwerk.php" autocomplete="off">
        <div>
            <label for="username">Gebruikersnaam</label><br>
            <input
                type="text"
                id="username"
                name="username"
                value="<?= htmlspecialchars($old['username'] ?? '') ?>"
            >
        </div>

        <div>
            <label for="password">Wachtwoord</label><br>
            <input
                type="password"
                id="password"
                name="password"
            >
        </div>

        <div>
            <button type="submit">Inloggen</button>
        </div>
    </form>

    <p>
        Nog geen account?
        <a href="/view/registratie.php">Registreer hier</a>.
    </p>
</section>

<?php
require_once __DIR__ . '/../herbruikbaar/footer.php';
