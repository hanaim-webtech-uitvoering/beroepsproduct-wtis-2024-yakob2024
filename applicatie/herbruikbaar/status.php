<?php
// Orderstatus labels (herbruikbaar): int -> tekst, gebruikt door klant én personeel

/**
 * Zet een statuscode om naar een leesbaar label.
 * Afspraak: 1 = Nieuw, 2 = In behandeling, 3 = Afgerond.
 */
function orderStatusLabel(?int $status): string
{
    // Status label bepalen
    if ($status === null) {
        return 'Onbekend';
    }

    switch ($status) {
        case 1:
            return 'Nieuw';
        case 2:
            return 'In behandeling';
        case 3:
            return 'Afgerond';
        default:
            return 'Onbekend';
    }
}
