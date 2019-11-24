<?php

/** @noinspection PhpUnused */

namespace Fhp\Segment\HITANS;

use Fhp\Segment\BaseDeg;

class VerfahrensparameterZweiSchrittVerfahrenV6 extends BaseDeg implements VerfahrensparameterZweiSchrittVerfahren
{
    /** @var int Allowed values: 900 through 997 */
    public $sicherheitsfunktion;
    /** @var int Allowed values: 1, 2; See specification for details */
    public $tanProzess;
    /** @var string */
    public $technischeIdentifikationTanVerfahren;
    /** @var string|null Max length: 32 */
    public $zkaTanVerfahren;
    /** @var string|null Max length: 10 */
    public $versionZkaTanVerfahren;
    /** @var string Max length: 30 */
    public $nameDesZweiSchrittVerfahrens;
    /** @var int */
    public $maximaleLaengeDesTanEingabewertes;
    /** @var int Allowed values: 1 = numerisch, 2 = alfanumerisch */
    public $erlaubtesFormat;
    /** @var string */
    public $textZurBelegungDesRueckgabewertes;
    /** @var int Allowed values: 1 through 256 */
    public $maximaleLaengeDesRueckgabewertes;
    /** @var bool */
    public $mehrfachTanErlaubt;
    /**
     * 1 TAN nicht zeitversetzt / dialogübergreifend erlaubt
     * 2 TAN zeitversetzt / dialogübergreifend erlaubt
     * 3 beide Verfahren unterstützt
     * 4 nicht zutreffend.
     *
     * @var int
     */
    public $tanZeitUndDialogbezug;
    /** @var bool */
    public $auftragsstornoErlaubt;
    /** @var int Allowed values: 0 (cannot), 2 (must) */
    public $smsAbbuchungskontoErforderlich;
    /** @var int Allowed values: 0 (cannot), 2 (must) */
    public $auftraggeberkontoErforderlich;
    /** @var bool */
    public $challengeKlasseErforderlich;
    /** @var bool */
    public $challengeStrukturiert;
    /** @var string Allowed values: 00 (cleartext PIN, no TAN), 01 (Schablone 01, encrypted PIN), 02 (reserved) */
    public $initialisierungsmodus;
    /** @var int Allowed values: 0 (cannot), 2 (must) */
    public $bezeichnungDesTanMediumsErforderlich;
    /** @var bool */
    public $antwortHhdUcErforderlich;
    /** @var int|null */
    public $anzahlUnterstuetzterAktiverTanMedien;

    /** {@inheritdoc} */
    public function getId()
    {
        return $this->sicherheitsfunktion;
    }

    /** {@inheritdoc} */
    public function getName()
    {
        return $this->nameDesZweiSchrittVerfahrens;
    }

    /** {@inheritdoc} */
    public function getSmsAbbuchungskontoErforderlich()
    {
        return 2 === $this->smsAbbuchungskontoErforderlich;
    }

    /** {@inheritdoc} */
    public function getAuftraggeberkontoErforderlich()
    {
        return 2 === $this->auftraggeberkontoErforderlich;
    }

    /** {@inheritdoc} */
    public function getChallengeKlasseErforderlich()
    {
        return $this->challengeKlasseErforderlich;
    }

    /** {@inheritdoc} */
    public function getAntwortHhdUcErforderlich()
    {
        return $this->antwortHhdUcErforderlich;
    }

    /** {@inheritdoc} */
    public function getChallengeLabel()
    {
        return $this->textZurBelegungDesRueckgabewertes;
    }

    /** {@inheritdoc} */
    public function getMaxChallengeLength()
    {
        return $this->maximaleLaengeDesRueckgabewertes;
    }

    /** {@inheritdoc} */
    public function getMaxTanLength()
    {
        return $this->maximaleLaengeDesTanEingabewertes;
    }

    /** {@inheritdoc} */
    public function getTanFormat()
    {
        return $this->erlaubtesFormat;
    }

    /** {@inheritdoc} */
    public function needsTanMedium()
    {
        return 2 === $this->bezeichnungDesTanMediumsErforderlich && $this->anzahlUnterstuetzterAktiverTanMedien > 0;
    }
}
