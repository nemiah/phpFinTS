<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\Common;

use Fhp\Model\SEPAAccount;
use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Kontoverbindung international (Version 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: B.3.2
 */
class Kti extends BaseDeg
{
    /** @var string|null Max length: 34 */
    public $iban;
    /** @var string|null Max length: 11, required if IBAN is present. */
    public $bic;

    // The following fields can only be set if the BPD parameters allow it. If they are set, the fields above become
    // optional.
    /** @var string|null Also known as Depotnummer. */
    public $kontonummer;
    /** @var string|null */
    public $unterkontomerkmal;
    /** @var Kik|null */
    public $kreditinstitutskennung;

    /**
     * @param string $iban
     * @param string $bic
     * @return Kti
     */
    public static function create($iban, $bic)
    {
        $result = new Kti();
        $result->iban = $iban;
        $result->bic = $bic;
        return $result;
    }

    /**
     * @param SEPAAccount $account
     * @return Kti
     */
    public static function fromAccount($account)
    {
        return static::create($account->getIban(), $account->getBic());
    }
}
