<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\Common;

use Fhp\Model\SEPAAccount;
use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Kontoverbindung international (Version 1)
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX01lc3NhZ2VzX0dlc2NoYWVmdHN2b3JmYWVsbGVfMjAyMi0wNC0xNV9maW5hbF92ZXJzaW9uLnBkZiIsInBhZ2UiOjEyN30.nQ1tJDZlRp30Fh2ZXZK147v2xOOrEHIrmTu-gjeHHMQ/FinTS_3.0_Messages_Geschaeftsvorfaelle_2022-04-15_final_version.pdf
 * Section: B.3.2
 */
class Kti extends BaseDeg implements AccountInfo
{
    /** Max length: 34 */
    public ?string $iban = null;
    /** Max length: 11, required if IBAN is present. */
    public ?string $bic = null;

    // The following fields can only be set if the BPD parameters allow it. If they are set, the fields above become
    // optional.
    /** Also known as Depotnummer. */
    public ?string $kontonummer = null;
    public ?string $unterkontomerkmal = null;
    public ?Kik $kreditinstitutskennung = null;

    /** {@inheritdoc} */
    public function validate()
    {
        parent::validate();
        if ($this->iban !== null) {
            if ($this->bic == null) {
                throw new \InvalidArgumentException('Kti cannot have IBAN without BIC');
            }
        } else {
            if ($this->kontonummer === null || $this->kreditinstitutskennung === null) {
                throw new \InvalidArgumentException('Kti must have IBAN+BIC or Kontonummer+Kik or both');
            }
        }
    }

    public static function create(?string $iban, ?string $bic): Kti
    {
        $result = new Kti();
        $result->iban = $iban;
        $result->bic = $bic;
        return $result;
    }

    public static function fromAccount(SEPAAccount $account): Kti
    {
        $result = static::create($account->getIban(), $account->getBic());
        $result->kontonummer = $account->getAccountNumber();
        $result->unterkontomerkmal = $account->getSubAccount();
        $result->kreditinstitutskennung = Kik::create($account->getBlz());
        return $result;
    }

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
