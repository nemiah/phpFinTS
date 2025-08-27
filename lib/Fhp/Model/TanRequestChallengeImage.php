<?php

namespace Fhp\Model;

use Fhp\Syntax\Bin;

class TanRequestChallengeImage
{
    private $mimeType;
    private $data;

    public function __construct(Bin $bin)
    {
        $data = $bin->getData();

        // Documentation: https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMjAyNjQsImV4cCI6MTc1NjQxMDI2NCwidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2hoZC9CZWxlZ3VuZ3NyaWNodGxpbmllbiBUQU52ZTEuNSBGViB2b20gMjAxOC0wNC0xNi5wZGYiLCJwYWdlIjoxMzB9.wlNrEBrCjpmqX8zHQ4vZkFyq3u4n2I-nyiYyQB14wK4/Belegungsrichtlinien%20TANve1.5%20FV%20vom%202018-04-16.pdf
        // II.3

        // Matrix-Format:
        // 2 bytes = length of mime type
        // mime type as string
        // 2 bytes = length of data

        $dataLength = strlen($data);
        if ($dataLength < 2) {
            throw new \InvalidArgumentException(
                "Invalid TAN challenge. Expected image MIME type but only found $dataLength bytes. ");
        }
        $mimeTypeLengthString = substr($data, 0, 2);
        $mimeTypeLength = ord($mimeTypeLengthString[0]) * 256 + ord($mimeTypeLengthString[1]);

        if ($dataLength < 2 + $mimeTypeLength + 2) {
            throw new \InvalidArgumentException(
                "Invalid TAN challenge. Expected image MIME type of length $mimeTypeLength but only found $dataLength bytes. " .
                'Maybe the challenge is not an image but rather a URL or a flicker code.');
        }
        $this->mimeType = substr($data, 2, $mimeTypeLength);

        $data = substr($data, 2 + $mimeTypeLength);

        $dataLengthString = substr($data, 0, 2);
        $expectedDataLength = ord($dataLengthString[0]) * 256 + ord($dataLengthString[1]);
        $actualDataLength = strlen($data) - 2;

        if ($expectedDataLength != $actualDataLength) {
            // This exception is thrown, if there is an encoding problem
            // f.e.: the serialized action was saved as a string, but not base64 encoded
            throw new \InvalidArgumentException(
                "Unexpected data length, expected $expectedDataLength but found $actualDataLength bytes.");
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
