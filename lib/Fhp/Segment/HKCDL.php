<?php

namespace Fhp\Segment;

use \Fhp\DataTypes\Bin;
use \Fhp\Model\SEPAStandingOrder;

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
class HKCDL extends AbstractSegment
{
    const NAME = 'HKCDL';
    const VERSION = 1;

    /**
     * HKCDL constructor.
     * @param int $version
     * @param int $segmentNumber
     * @param Kti $kti
     * @param string $SEPADescriptor
	 * @param SEPAStandingOrder $SEPAStandingOrder
     */
    public function __construct($version, $segmentNumber, $kti, $SEPADescriptor, SEPAStandingOrder $SEPAStandingOrder)
    {
		$deg = new \Fhp\Deg();
		$deg->addDataElement($SEPAStandingOrder->getFirstExecution());
		$deg->addDataElement($SEPAStandingOrder->getTimeUnit());
		$deg->addDataElement($SEPAStandingOrder->getInterval());
		$deg->addDataElement($SEPAStandingOrder->getExecutionDay());
		
        parent::__construct(
            static::NAME,
            $segmentNumber,
            $version,
            array(
                $kti,
                $SEPADescriptor,
				new Bin($SEPAStandingOrder->getXML()),
				"",
				$SEPAStandingOrder->getId(),
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
