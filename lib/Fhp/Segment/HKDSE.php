<?php

namespace Fhp\Segment;

use \Fhp\DataTypes\Bin;

/**
 * Class HKDSE (Terminierte SEPA-Einzellastschrift einreichen)
 * Segment type: Geschäftsvorfall
 *
 * @link: http://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.10.2.5.4.1
 *
 * @author Nena Furtmeier <support@furtmeier.it>
 * @package Fhp\Segment
 */
class HKDSE extends AbstractSegment
{
    const NAME = 'HKDSE';
    const VERSION = 1;

    /**
     * HKDSE constructor.
     * @param int $version
     * @param int $segmentNumber
     * @param Kti $kti
     * @param string $SEPADescriptor
	 * @param string $painMessage
     */
    public function __construct($version, $segmentNumber, $kti, $SEPADescriptor, $painMessage)
    {
        parent::__construct(
            static::NAME,
            $segmentNumber,
            $version,
            array(
                $kti,
                $SEPADescriptor,
				new Bin($painMessage)
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return static::NAME;
    }
}
