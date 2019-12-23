<?php

namespace Fhp\Segment;

/**
 * Class HKVVB (Verarbeitungsvorbereitung)
 * Segment type: Administration
 *
 * @link: http://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2011-06-14_final_version.pdf
 * Section: C.3.1.3
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
     */
    public function __construct(
        int $segmentNumber,
        int $bpdVersion = self::DEFAULT_BPD_VERSION,
        int $updVersion = self::DEFAULT_UPD_VERSION,
        int $dialogLanguage = self::LANG_DEFAULT,
        string $productName = self::DEFAULT_PRODUCT_NAME,
        string $productVersion = self::DEFAULT_PRODUCT_VERSION
    ) {
        parent::__construct(
            static::NAME,
            $segmentNumber,
            static::VERSION,
            [
                $bpdVersion,
                $updVersion,
                $dialogLanguage,
                $productName,
                $productVersion,
            ]
        );
    }

    public function getName(): string
    {
        return static::NAME;
    }
}
