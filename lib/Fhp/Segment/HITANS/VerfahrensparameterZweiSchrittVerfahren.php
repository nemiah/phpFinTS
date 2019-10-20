<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HITANS;

use Fhp\Model\TanMode;

interface VerfahrensparameterZweiSchrittVerfahren extends TanMode
{
    /** @return integer */
    public function getId();

    /** @return string */
    public function getName();
}
