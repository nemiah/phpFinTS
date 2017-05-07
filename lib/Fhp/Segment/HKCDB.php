<?php

namespace Fhp\Segment;

use Fhp\Deg;

/**
 * Class HKCDB (SEPA-Dauerauftragsbestand anfordern)
 * Segment type: Geschäftsvorfall
 *
 * @link: http://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.10.2.3.4
 *
 * @author Nena Furtmeier <support@furtmeier.it>
 * @package Fhp\Segment
 */
class HKCDB extends AbstractSegment
{
    const NAME = 'HKCDB';
    const VERSION = 1;

    /**
     * HKCDB constructor.
     * @param int $version
     * @param int $segmentNumber
     * @param Kti $kti
     * @param array $supportedPain
     */
    public function __construct($version, $segmentNumber, $kti, $supportedPain)
    {
		$deg = new Deg();
		foreach ($supportedPain AS $pain)
			$deg->addDataElement($pain);
		
        parent::__construct(
            static::NAME,
            $segmentNumber,
            $version,
            array(
                $kti,
                $deg
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
