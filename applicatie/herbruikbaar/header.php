<?php
// Basis HTML-header genereren voor alle presentatiepagina's (head + site header + main openen)
// Verwacht vanuit de view: $pageTitle (string)

if (!isset($pageTitle) || $pageTitle === '') {
    $pageTitle = 'Pizzeria Sole Machina';
}

// Alleen sessiewaarden lezen (sessie wordt gestart in de view)
$ingelogdeGebruiker = $_SESSION['username'] ?? null;
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
    <div>
        <h1>Pizzeria Sole Machina</h1>

        <?php if ($ingelogdeGebruiker): ?>
            <p>Ingelogd als: <?= htmlspecialchars($ingelogdeGebruiker) ?></p>
        <?php endif; ?>
    </div>
</header>

<?php
// Navigatie genereren (presentatie)
require_once __DIR__ . '/navbar.php';
?>

<main>
