<?php /** @noinspection PhpUnused */

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
class Kto extends BaseDeg
{
    /** @var string */
    public $kontonummer; // Aka Depotnummer
    /** @var Kik */
    public $kik;

    /**
     * @param string $kontonummer
     * @param Kik $kik
     * @return Kto
     */
    public static function create(string $kontonummer, Kik $kik): Kto
    {
        $result = new Kto();
        $result->kontonummer = $kontonummer;
        $result->kik = $kik;
        return $result;
    }

    /**
     * @param SEPAAccount $account
     * @return Kto
     */
    public static function fromAccount(SEPAAccount $account): Kto
    {
        return static::create($account->getAccountNumber(), Kik::create($account->getBlz()));
    }
}
