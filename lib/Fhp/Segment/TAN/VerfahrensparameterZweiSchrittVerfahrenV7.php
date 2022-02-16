<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\TAN;

use Fhp\Model\TanMode;
use Fhp\Segment\BaseDeg;

class VerfahrensparameterZweiSchrittVerfahrenV7 extends BaseDeg implements TanMode
{
    /** @var int Allowed values: 900 through 997 */
    public $sicherheitsfunktion;
    /** @var string Allowed values: 1, 2; See specification or {@link HKTANv7::$$tanProzess} for details. */
    public $tanProzess;
    /** @var string */
    public $technischeIdentifikationTanVerfahren;
    /**
     * Allowed values:
     * - HHD
     * - HHDUC
     * - HHDOPT1
     * - mobileTAN
     * - App
     * - Decoupled
     * - DecoupledPush
     * @var string|null Max length: 32
     */
    public $dkTanVerfahren;
    /** @var string|null Max length: 10 */
    public $versionDkTanVerfahren;
    /** @var string Max length: 30 */
    public $nameDesZweiSchrittVerfahrens;
    /** @var int|null Present iff !isDecoupled. */
    public $maximaleLaengeDesTanEingabewertes;
    /** @var int|null Present iff !isDecoupled. Allowed values: 1 = numerisch, 2 = alfanumerisch */
    public $erlaubtesFormat;
    /** @var string */
    public $textZurBelegungDesRueckgabewertes;
    /** @var int Allowed values: 1 through 256 */
    public $maximaleLaengeDesRueckgabewertes;
    /** @var bool */
    public $mehrfachTanErlaubt;
    /**
     * In case of multi-TAN (see {@link $mehrfachTanErlaubt}), this specifies whether all TANs must be entered in the
     * same dialog and at the same time, or not.
     * 1 TAN nicht zeitversetzt / dialogübergreifend erlaubt
     * 2 TAN zeitversetzt / dialogübergreifend erlaubt
     * 3 beide Verfahren unterstützt
     * 4 nicht zutreffend
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
    /** @var int|null Present iff isDecoupled. 0 means infinity. */
    public $maximaleAnzahlStatusabfragen;
    /** @var int|null Present iff isDecoupled. In seconds. */
    public $wartezeitVorErsterStatusabfrage;
    /** @var int|null Present iff isDecoupled. In seconds. */
    public $wartezeitVorNaechsterStatusabfrage;
    /** @var bool|null Maybe present if isDecoupled. */
    public $manuelleBestaetigungMoeglich;
    /** @var bool|null Maybe present if isDecoupled. */
    public $automatisierteStatusabfragenErlaubt;

    /** {@inheritdoc} */
    public function getId(): int
    {
        return $this->sicherheitsfunktion;
    }

    /** {@inheritdoc} */
    public function getName(): string
    {
        return $this->nameDesZweiSchrittVerfahrens;
    }

    /** {@inheritdoc} */
    public function isProzessvariante2(): bool
    {
        return $this->tanProzess === HKTAN::TAN_PROZESS_2;
    }

    /** {@inheritdoc} */
    public function isDecoupled(): bool
    {
        return $this->dkTanVerfahren === 'Decoupled' || $this->dkTanVerfahren === 'DecoupledPush';
    }

    /** {@inheritdoc} */
    public function getSmsAbbuchungskontoErforderlich(): bool
    {
        return $this->smsAbbuchungskontoErforderlich === 2;
    }

    /** {@inheritdoc} */
    public function getAuftraggeberkontoErforderlich(): bool
    {
        return $this->auftraggeberkontoErforderlich === 2;
    }

    /** {@inheritdoc} */
    public function getChallengeKlasseErforderlich(): bool
    {
        return $this->challengeKlasseErforderlich;
    }

    /** {@inheritdoc} */
    public function getAntwortHhdUcErforderlich(): bool
    {
        return $this->antwortHhdUcErforderlich;
    }

    /** {@inheritdoc} */
    public function getChallengeLabel(): string
    {
        return $this->textZurBelegungDesRueckgabewertes;
    }

    /** {@inheritdoc} */
    public function getMaxChallengeLength(): int
    {
        return $this->maximaleLaengeDesRueckgabewertes;
    }

    /** {@inheritdoc} */
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

    /** {@inheritdoc} */
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

    /** {@inheritdoc} */
    public function needsTanMedium(): bool
    {
        return $this->bezeichnungDesTanMediumsErforderlich === 2 && $this->anzahlUnterstuetzterAktiverTanMedien > 0;
    }

    /** {@inheritdoc} */
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

    /** {@inheritdoc} */
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

    /** {@inheritdoc} */
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

    /** {@inheritdoc} */
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

    /** {@inheritdoc} */
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

    /** {@inheritdoc} */
    public function createHKTAN(): HKTAN
    {
        return HKTANv7::createEmpty();
    }
}
