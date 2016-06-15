<?php

namespace Fhp\Segment;

/**
 * Class HKEND (Dialogende)
 * Segment type: Administration
 *
 * @link: http://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2011-06-14_final_version.pdf
 * Section: C.4.1.2
 *
 * @package Fhp\Segment
 */
class HKEND extends AbstractSegment
{
    const NAME = 'HKEND';
    const VERSION = 1;

    /**
     * HKEND constructor.
     * @param $segmentNumber
     * @param $dialogId
     */
    public function __construct($segmentNumber, $dialogId)
    {
        parent::__construct(
            static::NAME,
            $segmentNumber,
            static::VERSION,
            array($dialogId)
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
