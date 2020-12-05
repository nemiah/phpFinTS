<?php

namespace Fhp\Segment\TAN;

interface HKTAN
{
    public function setTanProzess(int $tanProzess): void;

    public function setSegmentkennung(?string $segmentkennung): void;

    public function setBezeichnungDesTanMediums(?string $bezeichnungDesTanMediums): void;

    public function setAuftragsreferenz(?string $auftragsreferenz): void;

    public function setWeitereTanFolgt(?bool $weitereTanFolgt): void;
}
