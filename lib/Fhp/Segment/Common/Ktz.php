<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\Common;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Kontoverbindung ZV international (Version 1)
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX01lc3NhZ2VzX0dlc2NoYWVmdHN2b3JmYWVsbGVfMjAyMi0wNC0xNV9maW5hbF92ZXJzaW9uLnBkZiIsInBhZ2UiOjEyN30.nQ1tJDZlRp30Fh2ZXZK147v2xOOrEHIrmTu-gjeHHMQ/FinTS_3.0_Messages_Geschaeftsvorfaelle_2022-04-15_final_version.pdf
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

    /** {@inheritdoc} */
    public function getAccountNumber(): string
    {
        return $this->iban ?? $this->kontonummer;
    }

    /** {@inheritdoc} */
    public function getBankIdentifier(): ?string
    {
        return $this->bic ?? $this->kreditinstitutskennung->kreditinstitutscode;
    }
}
