<?php

/** @noinspection PhpUnused */

namespace Fhp\Segment\HITANS;

use Fhp\Segment\BaseDeg;

class VerfahrensparameterZweiSchrittVerfahrenV1 extends BaseDeg implements VerfahrensparameterZweiSchrittVerfahren
{
    /** @var int Allowed values: 900 through 997 */
    public $sicherheitsfunktion;
    /** @var int Allowed values: 1, 2, 3, 4; See specification for details */
    public $tanProzess;
    /** @var string */
    public $technischeIdentifikationTanVerfahren;
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
    /** @var int|null */
    public $anzahlUnterstuetzterAktiverTanListen;
    /** @var bool */
    public $mehrfachTanErlaubt;
    /** @var bool */
    public $tanZeitversetztDialoguebergreifendErlaubt;

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
        return false;
    }

    /** {@inheritdoc} */
    public function getAuftraggeberkontoErforderlich()
    {
        return false;
    }

    /** {@inheritdoc} */
    public function getChallengeKlasseErforderlich()
    {
        return false;
    }

    /** {@inheritdoc} */
    public function getAntwortHhdUcErforderlich()
    {
        return false;
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
        return false;
    }
}
