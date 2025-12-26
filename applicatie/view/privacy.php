<?php
// Privacy/AVG pagina tonen (presentatie) met herbruikbare header/footer

$pageTitle = 'Privacy (AVG) - Pizzeria Sole Machina';

// Layout
require_once __DIR__ . '/../herbruikbaar/header.php';
?>

<section>
    <h1>Privacy (AVG)</h1>

    <p>
        Pizzeria Sole Machina verwerkt persoonsgegevens om bestellingen te kunnen aannemen, bezorgen en de status van bestellingen
        te tonen. In deze privacyverklaring leggen we uit welke gegevens we verwerken en waarom.
    </p>

    <h2>Welke gegevens verwerken wij?</h2>
    <ul>
        <li>Gebruikersnaam (voor het inloggen en koppelen van bestellingen)</li>
        <li>Voornaam en achternaam (voor communicatie en bezorging)</li>
        <li>Adres (voor bezorging van bestellingen)</li>
        <li>Bestelgegevens (producten, aantallen, datum/tijd en status)</li>
        <li>Rol (klant of personeel) voor autorisatie binnen de applicatie</li>
    </ul>

    <h2>Waarom verwerken wij deze gegevens?</h2>
    <ul>
        <li>Om accounts te kunnen aanmaken en gebruikers te laten inloggen</li>
        <li>Om bestellingen te kunnen plaatsen en afleveren</li>
        <li>Om bestelhistorie en orderstatus te tonen</li>
        <li>Om personeel bestellingen te laten verwerken (status aanpassen)</li>
        <li>Om de applicatie te beveiligen (autoriseringscontroles en sessies)</li>
    </ul>

    <h2>Wettelijke grondslag</h2>
    <p>
        Wij verwerken gegevens omdat dit noodzakelijk is voor het uitvoeren van de overeenkomst (het leveren van de bestelling)
        en voor het goed functioneren en beveiligen van de webapplicatie.
    </p>

    <h2>Bewaartermijnen</h2>
    <p>
        We bewaren gegevens niet langer dan nodig is voor het uitvoeren van de dienstverlening en het kunnen tonen van orderhistorie.
        Testdata in de database kan afwijken van productie-instellingen.
    </p>

    <h2>Beveiliging</h2>
    <p>
        We nemen passende maatregelen om gegevens te beschermen, zoals:
    </p>
    <ul>
        <li>Prepared statements om SQL-injectie te voorkomen</li>
        <li>Wachtwoord hashing bij registratie en login</li>
        <li>Session-beveiliging en rolgebaseerde autorisatie</li>
        <li>Server-side validatie op invoer</li>
    </ul>

    <h2>Rechten van gebruikers</h2>
    <p>
        Je hebt het recht om je gegevens in te zien, te laten corrigeren of te laten verwijderen, voor zover dit mogelijk is binnen
        de context van deze applicatie. Neem hiervoor contact op met de beheerder van Pizzeria Sole Machina.
    </p>

    <h2>Contact</h2>
    <p>
        Voor vragen over privacy kun je contact opnemen met de beheerder van Pizzeria Sole Machina.
    </p>
</section>

<?php
require_once __DIR__ . '/../herbruikbaar/footer.php';
