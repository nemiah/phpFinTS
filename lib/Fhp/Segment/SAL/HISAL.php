<?php

namespace Fhp\Segment\SAL;

use Fhp\Segment\Common\AccountInfo;
use Fhp\Segment\Common\Btg;
use Fhp\Segment\Common\Sdo;
use Fhp\Segment\Common\Tsp;

/**
 * Segment: Saldenabfrage Rückmeldung
 */
interface HISAL
{
    public function getAccountInfo(): AccountInfo;

    public function getKontoproduktbezeichnung(): string;

    public function getGebuchterSaldo(): Sdo;

    public function getSaldoDerVorgemerktenUmsaetze(): ?Sdo;

    public function getKreditlinie(): ?Btg;

    // This is essentially max(0, gebuchterSaldo + kreditlinie - bereitsVerfuegterBetrag), i.e. how much the account
    // owner can use from their positive balance plus their allowance to go negative, minus what they already used.
    public function getVerfuegbarerBetrag(): ?Btg;

    public function getBereitsVerfuegterBetrag(): ?Btg;

    // Note: Also consider getGebuchterSaldo()->getTimestamp().
    public function getBuchungszeitpunkt(): ?Tsp;

    public function getFaelligkeit(): ?string;  // JJJJMMTT gemäß ISO 8601
}
