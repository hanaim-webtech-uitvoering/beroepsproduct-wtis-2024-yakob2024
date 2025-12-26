<?php
// Basis HTML-header + globale layout (presentatie)
// - opent <html>, <body> en <main>
// - toont ingelogde gebruiker
// - toont eenmalige autorisatie-meldingen (auth_flash)

// Verwacht vanuit de view: $pageTitle (string)
if (!isset($pageTitle) || $pageTitle === '') {
    $pageTitle = 'Pizzeria Sole Machina';
}

// Ingelogde gebruiker (alleen tonen, geen logica)
$ingelogdeGebruiker = '';
if (isset($_SESSION['username']) && (string)$_SESSION['username'] !== '') {
    $ingelogdeGebruiker = (string)$_SESSION['username'];
}

// Flash melding bij autorisatie (1x tonen)
$authFlash = null;
if (isset($_SESSION['auth_flash']) && (string)$_SESSION['auth_flash'] !== '') {
    $authFlash = (string)$_SESSION['auth_flash'];
    unset($_SESSION['auth_flash']);
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">

    <!-- Responsive layout -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Pagina beschrijving -->
    <meta name="description" content="Pizzeria Sole Machina - Online pizza bestellen">

    <!-- Dynamische paginatitel -->
    <title><?= htmlspecialchars($pageTitle) ?></title>

    <!-- Centrale stylesheet (relatief t.o.v. /view/*) -->
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<header>
    <h1>Pizzeria Sole Machina</h1>

    <?php if ($ingelogdeGebruiker !== ''): ?>
        <p>Ingelogd als: <?= htmlspecialchars($ingelogdeGebruiker) ?></p>
    <?php endif; ?>
</header>

<?php
// Globale navigatie (rol-afhankelijk)
require_once __DIR__ . '/navbar.php';
?>

<main>

<?php if ($authFlash !== null): ?>
    <div style="margin-bottom: 15px; padding: 10px; border: 1px solid #ccc;">
        <?= htmlspecialchars($authFlash) ?>
    </div>
<?php endif; ?>
