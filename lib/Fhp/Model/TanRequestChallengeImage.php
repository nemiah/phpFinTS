<?php

namespace Fhp\Model;

use Fhp\DataTypes\Bin;

class TanRequestChallengeImage {

    private $mimeType, $data;

    public function __construct(Bin $bin) {

        $data = $bin->getData();

        // Documentation: https://www.hbci-zka.de/dokumente/spezifikation_deutsch/hhd/Belegungsrichtlinien%20TANve1.5%20FV%20vom%202018-04-16.pdf
        // II.3

        // Matrix-Format:
        // 2 bytes = length of mime type
        // mime type as string
        // 2 bytes = length of data

        $mimeTypeLengthString = substr($data, 0, 2);
        $mimeTypeLength = ord($mimeTypeLengthString[0])*256 + ord($mimeTypeLengthString[1]);

        $this->mimeType = substr($data, 2, $mimeTypeLength);
        $this->data = substr($data, 2 + $mimeTypeLength + 2);
    }

    public function getMimeType() {
        return $this->mimeType;
    }

    public function getData() {
        return $this->data;
    }
}