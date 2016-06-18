<?php

namespace Fhp\Segment;

use Fhp\DataTypes\Bin;

/**
 * Class HNVSD (Verschlüsselte Daten)
 * Segment type: Administration
 *
 * @link: http://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_HBCI_Rel_20130718_final_version.pdf
 * Section: B.5.4
 *
 * @package Fhp\Segment
 */
class HNVSD extends AbstractSegment
{
    const NAME = 'HNVSD';
    const VERSION = 1;

    /**
     * HNVSD constructor.
     * @param int $segmentNumber
     * @param string $encodedData
     */
    public function __construct($segmentNumber, $encodedData)
    {
        parent::__construct(
            static::NAME,
            $segmentNumber,
            static::VERSION,
            array(new Bin($encodedData))
        );
    }

    /**
     * @return Bin
     */
    public function getEncodedData()
    {
        $des = $this->getDataElements();

        return $des[0];
    }

    /**
     * @param string $data
     */
    public function setEncodedData($data)
    {
        $this->setDataElements(array(new Bin($data)));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return static::NAME;
    }
}
