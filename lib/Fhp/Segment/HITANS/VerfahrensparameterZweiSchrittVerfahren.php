<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HITANS;

use Fhp\Model\TanMode;

interface VerfahrensparameterZweiSchrittVerfahren extends TanMode
{
    /** @return int */
    public function getId(): int;

    /** @return string */
    public function getName(): string;

    /** @return bool */
    public function getSmsAbbuchungskontoErforderlich(): bool;

    /** @return bool */
    public function getAuftraggeberkontoErforderlich(): bool;

    /** @return bool */
    public function getChallengeKlasseErforderlich(): bool;

    /** @return bool */
    public function getAntwortHhdUcErforderlich(): bool;
}
