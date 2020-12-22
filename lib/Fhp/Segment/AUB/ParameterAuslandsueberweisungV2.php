<?php

namespace Fhp\Segment\AUB;

use Fhp\Segment\BaseDeg;

class ParameterAuslandsueberweisungV2 extends BaseDeg
{
    /** @var int */
    public $DTAZVHandbuch;

    /** @var int */
    public $maximaleAnzahlTSaetze;

    /** @var float */
    public $meldepflichtgrenzbetrag;

    /** @var string|null */
    public $unterstuetzteMeldesaetze;

    /** @var string|null */
    public $zugelasseneWeisungsschluessel;

    /** @var string|null */
    public $maximaleAnzahlDerZugelassenenWeisungschluessel;

    /** @var string|null */
    public $erlaubteZahlungsarten;
}
