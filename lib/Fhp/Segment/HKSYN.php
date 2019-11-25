<?php

namespace Fhp\Segment;

/**
 * Class HKSYN (Synchronisation)
 * Segment type: Administration
 *
 * @link: http://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2011-06-14_final_version.pdf
 * Section: C.8.1.2
 *
 * @package Fhp\Segment
 */
class HKSYN extends AbstractSegment
{
    const NAME = 'HKSYN';
    const VERSION = 3;

    const SYNC_MODE_NEW_CUSTOMER_ID = 0; // Neue Kundensystem-ID zurückmelden
    const SYNC_MODE_LAST_MSG_NUMBER = 1; // Letzte verarbeitete Nachrichtennummer zurückmelden
    const SYNC_MODE_SIGNATURE_ID = 2; // Signatur-ID zurückmelden

    /**
     * HKSYN constructor.
     * @param int $segmentNumber
     * @param int $syncMode
     */
    public function __construct(
        $segmentNumber,
        $syncMode = self::SYNC_MODE_NEW_CUSTOMER_ID
    ) {
        parent::__construct(
            static::NAME,
            $segmentNumber,
            static::VERSION,
            array($syncMode)
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
