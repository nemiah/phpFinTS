<?php

namespace Fhp\Segment;

use Fhp\DataTypes\Kik;

/**
 * Class HKIDN (Identifikation)
 * Segment type: Administration
 *
 * @link: http://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2011-06-14_final_version.pdf
 * Section: C.3.1.2
 *
 * @package Fhp\Segment
 */
class HKIDN extends AbstractSegment
{
    const NAME = 'HKIDN';
    const VERSION = 2;
    const COUNTRY_CODE = 280; // Germany

    /**
     * HKIDN constructor.
     * @param int $segmentNumber
     * @param string $bankCode
     * @param string $userName
     * @param int $systemId
     */
    public function __construct($segmentNumber, $bankCode, $userName, $systemId = 0)
    {
        parent::__construct(
            static::NAME,
            $segmentNumber,
            static::VERSION,
            array(
                new Kik(static::COUNTRY_CODE, $bankCode),
                $userName,
                $systemId,
                1   // Kunden-ID
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
