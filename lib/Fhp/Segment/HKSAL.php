<?php


namespace Fhp\Segment;


use Fhp\DataTypes\Kik;
use Fhp\DataTypes\Kti;
use Fhp\DataTypes\Ktv;
use Fhp\Model\Account;

/**
 * Class HKSAL (Saldenabfrage)
 * Segment type: Geschäftsvorfall
 *
 * @link: http://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.2.1.2
 *
 * @package Fhp\Segment
 */
class HKSAL extends AbstractSegment
{
    const NAME = 'HKSAL';
    const VERSION = 7;
    const ALL_ACCOUNTS_N = 'N';
    const ALL_ACCOUNTS_Y = 'J';

    public function __construct($version, $segmentNumber, $ktv, $allAccounts)
    {
        parent::__construct(
            static::NAME,
            $segmentNumber,
            $version,
            array(
                $ktv,
                $allAccounts,
            )
        );
    }

    public function getName()
    {
        return static::NAME;
    }
}
