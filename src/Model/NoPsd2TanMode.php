<?php

namespace Fhp\Model;

use Fhp\Segment\TAN\HKTAN;

/**
 * This is a placeholder used instead of a real {@link TanMode} in order to signal that the bank's HBCI interface
 * supports no strong authentication whatsoever and thus also no TAN modes. While it should still support the
 * PIN/TAN authentication scheme (that's the only one that this library implements), not supporting the TAN part of
 * means, in times of PSD2 regulations, that the HBCI interface is limited to read-only operations (like reading
 * accounts and statements) and a separate login (through an app or web UI) is required regularly for the HBCI
 * access to keep working.
 */
final class NoPsd2TanMode implements TanMode
{
    public const ID = -1;

    public function getId(): int
    {
        return self::ID;
    }

    public function getName(): string
    {
        return 'No PSD2/TANs supported';
    }

    public function isProzessvariante2(): bool
    {
        return false;
    }

    public function isDecoupled(): bool
    {
        return false;
    }

    public function getChallengeLabel(): string
    {
        return '';
    }

    public function getMaxChallengeLength(): int
    {
        return 0;
    }

    public function getMaxTanLength(): int
    {
        return 0;
    }

    public function getTanFormat(): int
    {
        return 0;
    }

    public function needsTanMedium(): bool
    {
        return false;
    }

    public function getSmsAbbuchungskontoErforderlich(): bool
    {
        return false;
    }

    public function getAuftraggeberkontoErforderlich(): bool
    {
        return false;
    }

    public function getChallengeKlasseErforderlich(): bool
    {
        return false;
    }

    public function getAntwortHhdUcErforderlich(): bool
    {
        return false;
    }

    public function getMaxDecoupledChecks(): int
    {
        throw new \RuntimeException('Only allowed for decoupled TAN modes');
    }

    public function getFirstDecoupledCheckDelaySeconds(): int
    {
        throw new \RuntimeException('Only allowed for decoupled TAN modes');
    }

    public function getPeriodicDecoupledCheckDelaySeconds(): int
    {
        throw new \RuntimeException('Only allowed for decoupled TAN modes');
    }

    public function allowsManualConfirmation(): bool
    {
        throw new \RuntimeException('Only allowed for decoupled TAN modes');
    }

    public function allowsAutomatedPolling(): bool
    {
        throw new \RuntimeException('Only allowed for decoupled TAN modes');
    }

    public function createHKTAN(): HKTAN
    {
        throw new \AssertionError('HKTAN should not be needed when the bank does not support PSD2');
    }
}
