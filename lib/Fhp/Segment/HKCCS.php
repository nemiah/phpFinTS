<?php

namespace Fhp\Segment;

use Fhp\Syntax\Bin;

/**
 * Class HKCCS (SEPA Einzelüberweisung)
 * Segment type: Geschäftsvorfall
 *
 * @link: http://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.10.2.1
 *
 * @author Nena Furtmeier <support@furtmeier.it>
 */
class HKCCS extends AbstractSegment
{
    const NAME = 'HKCCS';
    const VERSION = 1;

    /**
     * HKCCS constructor.
     */
    public function __construct(int $version, int $segmentNumber, \Fhp\DataTypes\Kti $kti, string $SEPADescriptor, string $painMessage)
    {
        parent::__construct(
            static::NAME,
            $segmentNumber,
            $version,
            [
                $kti,
                $SEPADescriptor,
                new Bin($painMessage),
            ]
        );
    }

    public function getName(): string
    {
        return static::NAME;
    }
}
