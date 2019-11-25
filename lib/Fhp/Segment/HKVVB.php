<?php

namespace Fhp\Segment;

/**
 * Class HKVVB (Verarbeitungsvorbereitung)
 * Segment type: Administration
 *
 * @link: http://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2011-06-14_final_version.pdf
 * Section: C.3.1.3
 *
 * @package Fhp\Segment
 */
class HKVVB extends AbstractSegment
{
    const NAME = 'HKVVB';
    const VERSION = 3;

    const DEFAULT_BPD_VERSION = 0;
    const DEFAULT_UPD_VERSION = 0;

    const LANG_DEFAULT = 0;
    const LANG_DE = 1;
    const LANG_EN = 2;
    const LANG_FR = 3;

    const DEFAULT_PRODUCT_NAME = 'fints-hbci-php';
    const DEFAULT_PRODUCT_VERSION = '1.0';

    /**
     * HKVVB constructor.
     * @param int $segmentNumber
     * @param int $bpdVersion
     * @param int $updVersion
     * @param int $dialogLanguage
     * @param string $productName
     * @param string $productVersion
     */
    public function __construct(
        $segmentNumber,
        $bpdVersion = self::DEFAULT_BPD_VERSION,
        $updVersion = self::DEFAULT_UPD_VERSION,
        $dialogLanguage = self::LANG_DEFAULT,
        $productName = self::DEFAULT_PRODUCT_NAME,
        $productVersion = self::DEFAULT_PRODUCT_VERSION
    ) {
        parent::__construct(
            static::NAME,
            $segmentNumber,
            static::VERSION,
            array(
                $bpdVersion,
                $updVersion,
                $dialogLanguage,
                $productName,
                $productVersion
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
