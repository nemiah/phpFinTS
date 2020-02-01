<?php

namespace Fhp\Model;

use Fhp\DataTypes\Bin;

class TanRequestChallengeImage
{
    private $mimeType;
    private $data;

    public function __construct(Bin $bin)
    {
        $data = $bin->getData();

        // Documentation: https://www.hbci-zka.de/dokumente/spezifikation_deutsch/hhd/Belegungsrichtlinien%20TANve1.5%20FV%20vom%202018-04-16.pdf
        // II.3

        // Matrix-Format:
        // 2 bytes = length of mime type
        // mime type as string
        // 2 bytes = length of data

        $mimeTypeLengthString = substr($data, 0, 2);
        $mimeTypeLength = ord($mimeTypeLengthString[0]) * 256 + ord($mimeTypeLengthString[1]);

        $this->mimeType = substr($data, 2, $mimeTypeLength);

        $data = substr($data, 2 + $mimeTypeLength);

        $dataLengthString = substr($data, 0, 2);
        $expectedDataLength = ord($dataLengthString[0]) * 256 + ord($dataLengthString[1]);
        $actualDataLength = strlen($data) - 2;

        if ($expectedDataLength != $actualDataLength) {
            // This exception is thrown, if there is an encoding problem
            // f.e.: the serialized action was saved as a string, but not base64 encoded
            throw new \RuntimeException(
            'Unexpected data length ' .
                '- expected: ' . $expectedDataLength .
                ' - is: ' . $actualDataLength
            );
        }

        $this->data = substr($data, 2, $expectedDataLength);
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getData(): string
    {
        return $this->data;
    }
}
