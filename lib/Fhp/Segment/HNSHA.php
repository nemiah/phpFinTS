<?php

namespace Fhp\Segment;

use Fhp\Deg;

/**
 * Class HNSHA (Signaturabschluss)
 * Segment type: Administration
 *
 * @link: http://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_HBCI_Rel_20130718_final_version.pdf
 * Section: B.5.2
 */
class HNSHA extends AbstractSegment
{
    const NAME = 'HNSHA';
    const VERSION = 2;

    /**
     * HNSHA constructor.
     */
    public function __construct(
        int $segmentNumber,
        string $securityControlReference,
        string $pin,
        $tan = null
    ) {
        $deg = new Deg();
        $deg->addDataElement($pin);
        if ($tan) {
            $deg->addDataElement($tan);
        }

        parent::__construct(
            static::NAME,
            $segmentNumber,
            static::VERSION,
            [
                $securityControlReference,
                '',
                $deg,
            ]
        );
    }

    public function getName(): string
    {
        return static::NAME;
    }
}
