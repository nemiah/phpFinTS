<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HITANS;

use Fhp\Segment\BaseDeg;

class VerfahrensparameterZweiSchrittVerfahrenV5 extends BaseDeg implements VerfahrensparameterZweiSchrittVerfahren
{
    /** @var integer Allowed values: 900 through 997 */
    public $sicherheitsfunktion;
    /** @var integer Allowed values: 1, 2; See specification for details */
    public $tanProzess;
    /** @var string */
    public $technischeIdentifikationTanVerfahren;
    /** @var string|null Max length: 32 */
    public $zkaTanVerfahren;
    /** @var string|null Max length: 10 */
    public $versionZkaTanVerfahren;
    /** @var string Max length: 30 */
    public $nameDesZweiSchrittVerfahrens;
    /** @var integer */
    public $maximaleLaengeDesTanEingabewertes;
    /** @var integer Allowed values: 1 = numerisch, 2 = alfanumerisch */
    public $erlaubtesFormat;
    /** @var string */
    public $textZurBelegungDesRueckgabewertes;
    /** @var integer Allowed values: 1 through 256 */
    public $maximaleLaengeDesRueckgabewertes;
    /** @var integer|null */
    public $anzahlUnterstuetzterAktiverTanListen;
    /** @var boolean */
    public $mehrfachTanErlaubt;
    /**
     * 1 TAN nicht zeitversetzt / dialogübergreifend erlaubt
     * 2 TAN zeitversetzt / dialogübergreifend erlaubt
     * 3 beide Verfahren unterstützt
     * 4 nicht zutreffend
     * @var integer
     */
    public $tanZeitUndDialogbezug;
    /** @var integer Allowed values: 0 (cannot), 2 (must) */
    public $tanListennummerErforderlich;
    /** @var boolean */
    public $auftragsstornoErlaubt;
    /** @var integer Allowed values: 0 (cannot), 2 (must) */
    public $smsAbbuchungskontoErforderlich;
    /** @var integer Allowed values: 0 (cannot), 2 (must) */
    public $auftraggeberkontoErforderlich;
    /** @var boolean */
    public $challengeKlasseErforderlich;
    /** @var boolean */
    public $challengeStrukturiert;
    /** @var string Allowed values: 00 (cleartext PIN, no TAN), 01 (Schablone 01, encrypted PIN), 02 (reserved) */
    public $initialisierungsmodus;
    /** @var integer Allowed values: 0 (cannot), 2 (must) */
    public $bezeichnungDesTanMediumsErforderlich;
    /** @var integer|null */
    public $anzahlUnterstuetzterAktiverTanMedien;

    public function getSicherheitsfunktion()
    {
        return $this->sicherheitsfunktion;
    }

    public function getNameDesZweiSchrittVerfahrens()
    {
        return $this->nameDesZweiSchrittVerfahrens;
    }
}
