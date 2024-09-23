<?php

namespace Fhp\Segment\IPZ;

use Fhp\Segment\BaseSegment;

/**
 * Segment: SEPA-Instant Payment Zahlung (Version 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2022-04-15_final_version.pdf
 * Section: C.10.2.9.1.1 b)
 */
class HIIPZv1 extends BaseSegment
{
    public string $auftragsidentifikation;

    /**
     * SEPA C-Code („C“ for Cancellation)
     * Possible values
     * 3: Delete
     * 4: Recall
     */
    public ?int $sepaCCode = null;

    /*
     * Possible values
     * 1: in Terminierung
     * 2: abgelehnt von erster Inkassostelle
     * 3: in Bearbeitung
     * 4: Creditoren-seitig verarbeitet, Buchung veranlasst
     * 5: R-Transaktion wurde veranlasst
     * 6: Auftrag fehlgeschagen
     * 7: Auftrag ausgeführt; Geld für den Zahlungsempfänger verfügbar
     * 8: Abgelehnt durch Zahlungsdienstleister des Zahlers
     * 9: Abgelehnt durch Zahlungsdienstleister des Zahlungsempfängers
     */
    public ?int $statusSepaAuftrag = null;
}
