<?php

namespace Fhp\Segment\AUB;

use Fhp\Segment\BaseDeg;

class ParameterAuslandsueberweisungV2 extends BaseDeg
{
    public int $DTAZVHandbuch;
    public int $maximaleAnzahlTSaetze;
    public float $meldepflichtgrenzbetrag;
    public ?string $unterstuetzteMeldesaetze = null;
    public ?string $zugelasseneWeisungsschluessel = null;
    public ?string $maximaleAnzahlDerZugelassenenWeisungschluessel = null;
    public ?string $erlaubteZahlungsarten = null;
}
