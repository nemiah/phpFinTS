<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HITANS;

use Fhp\Segment\BaseDeg;

class VerfahrensparameterZweiSchrittVerfahrenV6 extends BaseDeg implements VerfahrensparameterZweiSchrittVerfahren
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
    /** @var boolean */
    public $antwortHhdUcErforderlich;
    /** @var integer|null */
    public $anzahlUnterstuetzterAktiverTanMedien;

    /** @inheritDoc */
    public function getId()
    {
        return $this->sicherheitsfunktion;
    }

    /** @inheritDoc */
    public function getName()
    {
        return $this->nameDesZweiSchrittVerfahrens;
    }

    /** @inheritDoc */
    public function getSmsAbbuchungskontoErforderlich()
    {
        return $this->smsAbbuchungskontoErforderlich === 2;
    }

    /** @inheritDoc */
    public function getAuftraggeberkontoErforderlich()
    {
        return $this->auftraggeberkontoErforderlich === 2;
    }

    /** @inheritDoc */
    public function getChallengeKlasseErforderlich()
    {
        return $this->challengeKlasseErforderlich;
    }

    /** @inheritDoc */
    public function getAntwortHhdUcErforderlich()
    {
        return $this->antwortHhdUcErforderlich;
    }

    /** @inheritDoc */
    public function getChallengeLabel()
    {
        return $this->textZurBelegungDesRueckgabewertes;
    }

    /** @inheritDoc */
    public function getMaxChallengeLength()
    {
        return $this->maximaleLaengeDesRueckgabewertes;
    }

    /** @inheritDoc */
    public function getMaxTanLength()
    {
        return $this->maximaleLaengeDesTanEingabewertes;
    }

    /** @inheritDoc */
    public function getTanFormat()
    {
        return $this->erlaubtesFormat;
    }

    /** @inheritDoc */
    public function needsTanDevice()
    {
        return $this->bezeichnungDesTanMediumsErforderlich === 2 && $this->anzahlUnterstuetzterAktiverTanMedien > 0;
    }
}
