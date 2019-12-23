<?php

namespace Fhp\Segment;

use Fhp\DataTypes\Bin;

/**
 * Class HNVSD (Verschlüsselte Daten)
 * Segment type: Administration
 *
 * @link: http://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_HBCI_Rel_20130718_final_version.pdf
 * Section: B.5.4
 */
class HNVSD extends AbstractSegment
{
    const NAME = 'HNVSD';
    const VERSION = 1;

    /**
     * HNVSD constructor.
     */
    public function __construct(int $segmentNumber, string $encodedData)
    {
        parent::__construct(
            static::NAME,
            $segmentNumber,
            static::VERSION,
            [new Bin($encodedData)]
        );
    }

    public function getEncodedData(): Bin
    {
        $des = $this->getDataElements();

        return $des[0];
    }

    public function setEncodedData(string $data)
    {
        $this->setDataElements([new Bin($data)]);
    }

    public function getName(): string
    {
        return static::NAME;
    }
}
