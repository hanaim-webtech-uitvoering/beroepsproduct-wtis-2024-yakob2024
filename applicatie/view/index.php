<?php
// Homepagina tonen (presentatie) met herbruikbare header/navbar/footer

session_start();

$pageTitle = 'Home - Pizzeria Sole Machina';

require_once __DIR__ . '/../herbruikbaar/header.php';

// Alleen UI-variabelen, geen verwerking
$ingelogd = isset($_SESSION['username']);
$rol = $_SESSION['role'] ?? null;
?>

<h1> Home </h1>


<?php
require_once __DIR__ . '/../herbruikbaar/footer.php';
