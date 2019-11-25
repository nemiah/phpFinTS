<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HITANS;

use Fhp\Model\TanMode;

interface VerfahrensparameterZweiSchrittVerfahren extends TanMode
{
    /** @return int */
    public function getId();

    /** @return string */
    public function getName();

    /** @return bool */
    public function getSmsAbbuchungskontoErforderlich();

    /** @return bool */
    public function getAuftraggeberkontoErforderlich();

    /** @return bool */
    public function getChallengeKlasseErforderlich();

    /** @return bool */
    public function getAntwortHhdUcErforderlich();
}
