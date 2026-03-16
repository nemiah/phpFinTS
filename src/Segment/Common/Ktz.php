<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\Common;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Kontoverbindung ZV international (Version 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: B.3.3
 */
class Ktz extends BaseDeg implements AccountInfo
{
    /** Whether it's a SEPA account that has IBAN/BIC, or not (e.g. a stock depot) */
    public bool $kontoverwendungSepa;
    /** Max length: 34 */
    public ?string $iban = null;
    /** Max length: 11, required if IBAN is present. */
    public ?string $bic = null;
    /** Also known as Depotnummer. */
    public string $kontonummer;
    public ?string $unterkontomerkmal = null;
    public Kik $kreditinstitutskennung;

    public function getAccountNumber(): string
    {
        return $this->iban ?? $this->kontonummer;
    }

    public function getBankIdentifier(): ?string
    {
        return $this->bic ?? $this->kreditinstitutskennung->kreditinstitutscode;
    }
}
