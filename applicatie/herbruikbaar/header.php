<?php
// Basis HTML-header genereren voor alle presentatiepagina's (head + site header + navbar)
// Verwacht vanuit de view: $pageTitle (string)

if (!isset($pageTitle) || $pageTitle === '') {
    $pageTitle = 'Pizzeria Sole Machina';
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

    <!-- Centrale stylesheet (relatief vanaf /view naar /css) -->
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<header>
    <h1>Pizzeria Sole Machina</h1>
    <p>Welkom! Bekijk het menu en plaats eenvoudig je bestelling.</p>
</header>

<?php
// Navigatie genereren (presentatie). View start de sessie, navbar leest alleen sessie-waarden.
require_once __DIR__ . '/navbar.php';
?>

<main>
