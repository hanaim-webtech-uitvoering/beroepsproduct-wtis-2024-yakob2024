<?php
// Personeel overzicht voorbereiden (autorisatie afdwingen en data klaarzetten voor de view)

require_once __DIR__ . '/autorisatie.php';

// Alleen personeel mag dit uitvoeren
requireRole('personeel', '../view/login.php');

// Placeholder dataset (wordt gevuld in Hoofdstap 9 via order_data.php)
$actieveBestellingen = [];
