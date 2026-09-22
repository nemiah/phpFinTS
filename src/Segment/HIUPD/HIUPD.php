<?php

namespace Fhp\Segment\HIUPD;

use Fhp\Model\SEPAAccount;
use Fhp\Segment\Common\KtvV3;

/**
 * Segment: Kontoinformation
 * Bezugssegment: HKVVB
 * Sender: Kreditinstitut
 */
interface HIUPD
{
    /**
     * @param SEPAAccount $account An account.
     * @return bool True if this HIUPD segment pertains to the given account.
     */
    public function matchesAccount(SEPAAccount $account): bool;

    /**
     * @return ErlaubteGeschaeftsvorfaelle[]
     */
    public function getErlaubteGeschaeftsvorfaelle(): array;

    /** @return KtvV3|null The account this segment describes. */
    public function getKontoverbindung(): ?KtvV3;

    /**
     * @return int|null The account type, if the bank reported one (not available before HIUPD v6):
     *     1-9 Kontokorrent/Giro, 10-19 Spar, 20-29 Festgeld, 30-39 Wertpapierdepot,
     *     40-49 Kredit/Darlehen, 50-59 Kreditkarte, 60-69 Fonds-Depot, 70-79 Bauspar,
     *     80-89 Versicherung, 90-99 Sonstige.
     */
    public function getKontoart(): ?int;

    /** @return string|null The account holder name (usually the surname). */
    public function getName1(): ?string;

    /** @return string|null A second account holder name part (usually the given name). */
    public function getName2(): ?string;

    /**
     * @return string|null The account holder name, i.e. {@link getName1()} and {@link getName2()} joined by a space
     *     (e.g. "Mustermann Max"), or null if the bank sent neither.
     */
    public function getAccountHolderName(): ?string;

    /** @return string|null The bank's product name for this account. */
    public function getKontoproduktbezeichnung(): ?string;

    /** @return string|null The account currency. */
    public function getKontowaehrung(): ?string;
}
