<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\Common;

use Fhp\Model\SEPAAccount;
use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Kontoverbindung
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/archiv/HBCI_V2.x_FV.zip
 * File: Gesamtdok_HBCI210.pdf
 * Section: II.5.3.3
 */
class Kto extends BaseDeg implements AccountInfo
{
    public string $kontonummer; // Aka Depotnummer
    public Kik $kik;

    public static function create(string $kontonummer, Kik $kik): Kto
    {
        $result = new Kto();
        $result->kontonummer = $kontonummer;
        $result->kik = $kik;
        return $result;
    }

    public static function fromAccount(SEPAAccount $account): Kto
    {
        return static::create($account->getAccountNumber(), Kik::create($account->getBlz()));
    }

    public function getAccountNumber(): string
    {
        return $this->kontonummer;
    }

    public function getBankIdentifier(): ?string
    {
        return $this->kik->kreditinstitutscode;
    }
}
