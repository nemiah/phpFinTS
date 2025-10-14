<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\Common;

use Fhp\Model\SEPAAccount;
use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Kontoverbindung (Version 3)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: B.3.1
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/archiv/HBCI_V2.x_FV.zip
 * File: HBCI22 Final.pdf
 * Section: II.5.3.3
 * Note that this older specification document contains no version number and has the Kik inlined, which is equivalent.
 */
class KtvV3 extends BaseDeg implements AccountInfo
{
    public ?string $kontonummer = null;  // Officially it's mandatory, but in practice it can be missing.
    public ?string $unterkontomerkmal = null;
    public ?Kik $kik = null;  // Officially it's mandatory, but in practice it can be missing.

    public static function create(string $kontonummer, ?string $unterkontomerkmal, Kik $kik): KtvV3
    {
        $result = new KtvV3();
        $result->kontonummer = $kontonummer;
        $result->unterkontomerkmal = $unterkontomerkmal;
        $result->kik = $kik;
        return $result;
    }

    public static function fromAccount(SEPAAccount $account): KtvV3
    {
        return static::create($account->getAccountNumber(), $account->getSubAccount(), Kik::create($account->getBlz()));
    }

    public function getAccountNumber(): string
    {
        return $this->kontonummer ?: '';
    }

    public function getBankIdentifier(): ?string
    {
        return $this->kik->kreditinstitutscode;
    }
}
