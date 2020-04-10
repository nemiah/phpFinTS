<?php

namespace Fhp\Segment;

use Fhp\Model\SEPAStandingOrder;
use Fhp\Syntax\Bin;

/**
 * Class HKCDL (SEPA-Dauerauftragsbestand löschen)
 * Segment type: Geschäftsvorfall
 *
 * @link: http://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.10.2.3.6
 *
 * @author Nena Furtmeier <support@furtmeier.it>
 */
class HKCDL extends AbstractSegment
{
    const NAME = 'HKCDL';
    const VERSION = 1;

    /**
     * HKCDL constructor.
     */
    public function __construct(int $version, int $segmentNumber, Kti $kti, string $SEPADescriptor, SEPAStandingOrder $SEPAStandingOrder)
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
            [
                $kti,
                $SEPADescriptor,
                new Bin($SEPAStandingOrder->getXML()),
                '',
                $SEPAStandingOrder->getId(),
                $deg,
            ]
        );
    }

    public function getName(): string
    {
        return static::NAME;
    }
}
