<?php
// Registratiepagina tonen (presentatie) met herbruikbare header/footer

session_start();

$pageTitle = 'Registreren - Pizzeria Sole Machina';

require_once __DIR__ . '/../herbruikbaar/header.php';

// Fouten en oude invoer ophalen uit sessie (alleen presentatie)
$errors = $_SESSION['reg_errors'] ?? [];
$old = $_SESSION['reg_old'] ?? [
    'username'    => '',
    'first_name'  => '',
    'last_name'   => '',
    'address'     => ''
];
$success = $_SESSION['reg_success'] ?? null;

// Na uitlezen opruimen
unset($_SESSION['reg_errors'], $_SESSION['reg_old'], $_SESSION['reg_success']);
?>

<section>
    <h1>Registreren</h1>

    <?php if ($success): ?>
        <div>
            <p><?= htmlspecialchars($success) ?></p>
        </div>
    <?php endif; ?>

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

    <form method="post" action="../verwerk/registratie_verwerk.php" autocomplete="off">
        <div>
            <label for="first_name">Voornaam</label><br>
            <input
                type="text"
                id="first_name"
                name="first_name"
                value="<?= htmlspecialchars($old['first_name'] ?? '') ?>"
            >
        </div>

        <div>
            <label for="last_name">Achternaam</label><br>
            <input
                type="text"
                id="last_name"
                name="last_name"
                value="<?= htmlspecialchars($old['last_name'] ?? '') ?>"
            >
        </div>

        <div>
            <label for="address">Adres</label><br>
            <input
                type="text"
                id="address"
                name="address"
                value="<?= htmlspecialchars($old['address'] ?? '') ?>"
            >
        </div>

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
            <label for="password_confirm">Wachtwoord bevestigen</label><br>
            <input
                type="password"
                id="password_confirm"
                name="password_confirm"
            >
        </div>

        <div>
            <button type="submit">Account aanmaken</button>
        </div>
    </form>

    <p>
        Heb je al een account?
        <a href="/view/login.php">Log hier in</a>.
    </p>
</section>

<?php
require_once __DIR__ . '/../herbruikbaar/footer.php';
