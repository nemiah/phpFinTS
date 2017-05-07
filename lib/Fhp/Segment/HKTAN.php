<?php

namespace Fhp\Segment;

use \Fhp\DataTypes\Bin;

/**
 * Class HKCDL (SEPA-Dauerauftragsbestand löschen)
 * Segment type: Geschäftsvorfall
 *
 * @link: http://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.10.2.3.6
 *
 * @author Nena Furtmeier <support@furtmeier.it>
 * @package Fhp\Segment
 */
class HKTAN extends AbstractSegment
{
    const NAME = 'HKTAN';
    const VERSION = 5;

    /**
     * HKCDL constructor.
     * @param int $version
     * @param int $segmentNumber
     */
    public function __construct($version, $segmentNumber)
    {
        parent::__construct(
            static::NAME,
            $segmentNumber,
            $version,
            array(
				4
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
