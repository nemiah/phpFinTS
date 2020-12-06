<?php

namespace Fhp\Segment\TAN;

interface HKTAN
{
    // Note: TAN Prozess 1 is for Prozessvariante 1, which is not implemented at all in this library.
    const TAN_PROZESS_2 = '2'; // Prozessvariante 2 step 2
    const TAN_PROZESS_4 = '4'; // Prozessvariante 2 step 1 (yes, four is one!)

    public function setTanProzess(string $tanProzess): void;

    public function setSegmentkennung(?string $segmentkennung): void;

    public function setBezeichnungDesTanMediums(?string $bezeichnungDesTanMediums): void;

    public function setAuftragsreferenz(?string $auftragsreferenz): void;

    public function setWeitereTanFolgt(?bool $weitereTanFolgt): void;
}
