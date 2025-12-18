<?php
// Personeel overzicht voorbereiden (autorisatie afdwingen en data klaarzetten)

require_once __DIR__ . '/autorisatie.php';

// Alleen personeel mag deze pagina bereiken
requirePersonnel('/view/login.php');

// Placeholder dataset (wordt later gevuld bij personeel-orders)
$actieveBestellingen = [];
