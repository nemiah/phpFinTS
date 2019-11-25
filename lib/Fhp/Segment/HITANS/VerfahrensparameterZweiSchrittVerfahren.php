<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HITANS;

use Fhp\Model\TanMode;

interface VerfahrensparameterZweiSchrittVerfahren extends TanMode
{
    /** @return integer */
    public function getId();

    /** @return string */
    public function getName();

    /** @return boolean */
    public function getSmsAbbuchungskontoErforderlich();

    /** @return boolean */
    public function getAuftraggeberkontoErforderlich();

    /** @return boolean */
    public function getChallengeKlasseErforderlich();

    /** @return boolean */
    public function getAntwortHhdUcErforderlich();
}
