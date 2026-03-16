<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\TAN;

use Fhp\Model\TanMode;
use Fhp\Segment\BaseDeg;

class VerfahrensparameterZweiSchrittVerfahrenV7 extends BaseDeg implements TanMode
{
    /** Allowed values: 900 through 997 */
    public int $sicherheitsfunktion;
    /** Allowed values: 1, 2; See specification or {@link HKTANv7::$$tanProzess} for details. */
    public string $tanProzess;
    public string $technischeIdentifikationTanVerfahren;
    /**
     * Allowed values:
     * - HHD
     * - HHDUC
     * - HHDOPT1
     * - mobileTAN
     * - App
     * - Decoupled
     * - DecoupledPush
     * Max length: 32
     */
    public ?string $dkTanVerfahren = null;
    /** Max length: 10 */
    public ?string $versionDkTanVerfahren = null;
    /** Max length: 30 */
    public string $nameDesZweiSchrittVerfahrens;
    /** Present iff !isDecoupled. */
    public ?int $maximaleLaengeDesTanEingabewertes = null;
    /** Present iff !isDecoupled. Allowed values: 1 = numerisch, 2 = alfanumerisch */
    public ?int $erlaubtesFormat = null;
    public string $textZurBelegungDesRueckgabewertes;
    /** Allowed values: 1 through 256 */
    public int $maximaleLaengeDesRueckgabewertes;
    public bool $mehrfachTanErlaubt;
    /**
     * In case of multi-TAN (see {@link $mehrfachTanErlaubt}), this specifies whether all TANs must be entered in the
     * same dialog and at the same time, or not.
     * 1 TAN nicht zeitversetzt / dialogübergreifend erlaubt
     * 2 TAN zeitversetzt / dialogübergreifend erlaubt
     * 3 beide Verfahren unterstützt
     * 4 nicht zutreffend
     */
    public int $tanZeitUndDialogbezug;
    public bool $auftragsstornoErlaubt;
    /** Allowed values: 0 (cannot), 2 (must) */
    public int $smsAbbuchungskontoErforderlich;
    /** Allowed values: 0 (cannot), 2 (must) */
    public int $auftraggeberkontoErforderlich;
    public bool $challengeKlasseErforderlich;
    public bool $challengeStrukturiert;
    /** Allowed values: 00 (cleartext PIN, no TAN), 01 (Schablone 01, encrypted PIN), 02 (reserved) */
    public string $initialisierungsmodus;
    /** Allowed values: 0 (cannot), 2 (must) */
    public int $bezeichnungDesTanMediumsErforderlich;
    public bool $antwortHhdUcErforderlich;
    public ?int $anzahlUnterstuetzterAktiverTanMedien = null;
    /** Present iff isDecoupled. 0 means infinity. */
    public ?int $maximaleAnzahlStatusabfragen = null;
    /** Present iff isDecoupled. In seconds. */
    public ?int $wartezeitVorErsterStatusabfrage = null;
    /** Present iff isDecoupled. In seconds. */
    public ?int $wartezeitVorNaechsterStatusabfrage = null;
    /** Maybe present if isDecoupled. */
    public ?bool $manuelleBestaetigungMoeglich = null;
    /** Maybe present if isDecoupled. */
    public ?bool $automatisierteStatusabfragenErlaubt = null;

    public function getId(): int
    {
        return $this->sicherheitsfunktion;
    }

    public function getName(): string
    {
        return $this->nameDesZweiSchrittVerfahrens;
    }

    public function isProzessvariante2(): bool
    {
        return $this->tanProzess === HKTAN::TAN_PROZESS_2;
    }

    public function isDecoupled(): bool
    {
        return $this->dkTanVerfahren === 'Decoupled' || $this->dkTanVerfahren === 'DecoupledPush';
    }

    public function getSmsAbbuchungskontoErforderlich(): bool
    {
        return $this->smsAbbuchungskontoErforderlich === 2;
    }

    public function getAuftraggeberkontoErforderlich(): bool
    {
        return $this->auftraggeberkontoErforderlich === 2;
    }

    public function getChallengeKlasseErforderlich(): bool
    {
        return $this->challengeKlasseErforderlich;
    }

    public function getAntwortHhdUcErforderlich(): bool
    {
        return $this->antwortHhdUcErforderlich;
    }

    public function getChallengeLabel(): string
    {
        return $this->textZurBelegungDesRueckgabewertes;
    }

    public function getMaxChallengeLength(): int
    {
        return $this->maximaleLaengeDesRueckgabewertes;
    }

    public function getMaxTanLength(): int
    {
        if ($this->isDecoupled()) {
            throw new \RuntimeException('getMaxTanLength is not available for decoupled TAN modes');
        }
        if ($this->maximaleLaengeDesTanEingabewertes === null) {
            throw new \AssertionError('maximaleLaengeDesTanEingabewertes is unexpectedly absent');
        }
        return $this->maximaleLaengeDesTanEingabewertes;
    }

    public function getTanFormat(): int
    {
        if ($this->isDecoupled()) {
            throw new \RuntimeException('getTanFormat is not available for decoupled TAN modes');
        }
        if ($this->erlaubtesFormat === null) {
            throw new \AssertionError('erlaubtesFormat is unexpectedly absent');
        }
        return $this->erlaubtesFormat;
    }

    public function needsTanMedium(): bool
    {
        return $this->bezeichnungDesTanMediumsErforderlich === 2 && $this->anzahlUnterstuetzterAktiverTanMedien > 0;
    }

    public function getMaxDecoupledChecks(): int
    {
        if (!$this->isDecoupled()) {
            throw new \RuntimeException('Only allowed for decoupled TAN modes');
        }
        if ($this->maximaleAnzahlStatusabfragen === null) {
            throw new \AssertionError('maximaleAnzahlStatusabfragen is unexpectedly absent');
        }
        return $this->maximaleAnzahlStatusabfragen;
    }

    public function getFirstDecoupledCheckDelaySeconds(): int
    {
        if (!$this->isDecoupled()) {
            throw new \RuntimeException('Only allowed for decoupled TAN modes');
        }
        if ($this->wartezeitVorErsterStatusabfrage === null) {
            throw new \AssertionError('wartezeitVorErsterStatusabfrage is unexpectedly absent');
        }
        return $this->wartezeitVorErsterStatusabfrage;
    }

    public function getPeriodicDecoupledCheckDelaySeconds(): int
    {
        if (!$this->isDecoupled()) {
            throw new \RuntimeException('Only allowed for decoupled TAN modes');
        }
        if ($this->wartezeitVorNaechsterStatusabfrage === null) {
            throw new \AssertionError('wartezeitVorNaechsterStatusabfrage is unexpectedly absent');
        }
        return $this->wartezeitVorNaechsterStatusabfrage;
    }

    public function allowsManualConfirmation(): bool
    {
        if (!$this->isDecoupled()) {
            throw new \RuntimeException('Only allowed for decoupled TAN modes');
        }
        if ($this->manuelleBestaetigungMoeglich === null) {
            throw new \AssertionError('manuelleBestaetigungMoeglich is unexpectedly absent');
        }
        return $this->manuelleBestaetigungMoeglich;
    }

    public function allowsAutomatedPolling(): bool
    {
        if (!$this->isDecoupled()) {
            throw new \RuntimeException('Only allowed for decoupled TAN modes');
        }
        if ($this->automatisierteStatusabfragenErlaubt === null) {
            throw new \AssertionError('automatisierteStatusabfragenErlaubt is unexpectedly absent');
        }
        return $this->automatisierteStatusabfragenErlaubt;
    }

    public function createHKTAN(): HKTAN
    {
        return HKTANv7::createEmpty();
    }
}
