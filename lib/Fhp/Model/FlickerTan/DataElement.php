<?php

namespace Fhp\Model\FlickerTan;

/**
 * Represents a Data Element which is part of the Flicker Tan Challenge. Shortens the whole challenge.
 * @see https://www.hbci-zka.de/dokumente/spezifikation_deutsch/hhd/Belegungsrichtlinien%20TANve1.5%20FV%20vom%202018-04-16.pdf
 */
class DataElement
{
    public const ENC_ASCII = '1';
    public const ENC_ASC = self::ENC_ASCII;
    public const ENC_BCD = '0';

    /**
     * @var string the encoding (either self::ENC_ASC or self::ENC_BCD)
     */
    protected $enc;

    /**
     * @var string the raw data string
     */
    protected $data;

    /**
     * @var string the highest bit of the generated header
     */
    protected $headerHighBit;

    /**
     * @param $challenge string raw challenge text
     * @return array [string $reducedChallenge, FlickerTanDataElement $dataElementObject]
     * @see https://www.hbci-zka.de/dokumente/spezifikation_deutsch/hhd/Belegungsrichtlinien%20TANve1.5%20FV%20vom%202018-04-16.pdf
     */
    public static function parseNextBlock(string $challenge): array
    {
        if (empty($challenge)) {
            return [$challenge, new self('')];
        }
        $length = (int) substr($challenge, 0, 2);
        $data = substr($challenge, 2, $length);
        if (strlen($data) !== $length) {
            throw new \InvalidArgumentException('Parsing went wrong');
        }
        $rest = substr($challenge, 2 + $length);
        return [$rest, new self($data)];
    }

    /**
     * The needed encoding will be automatically determined by the type of data
     * @param string $data the raw data
     */
    protected function __construct(string $data)
    {
        $this->data = $data;
        $this->headerHighBit = 0;
        if (is_numeric($this->data) || empty($this->data)) {
            $this->enc = self::ENC_BCD;
        } else {
            $this->enc = self::ENC_ASC;
        }
    }

    /**
     * @return int amount of bytes in data
     */
    protected function getLength(): int
    {
        if ($this->enc === self::ENC_BCD) {
            return ceil(strlen($this->data) / 2);
        }
        return strlen($this->data);
    }

    /**
     * @return string returns the hex representation from the header of the data element as string with length 2
     */
    public function getHeaderHex(): string
    {
        $lengthBin = str_pad(base_convert($this->getLength(), 10, 2), 6, '0', STR_PAD_LEFT);
        $headerHex = base_convert($this->headerHighBit . $this->enc . $lengthBin, 2, 16);
        return str_pad($headerHex, 2, '0', STR_PAD_LEFT);
    }

    /**
     * @return string returns the hex representation of the data, depending on the set encoding
     */
    public function getDataHex(): string
    {
        if ($this->enc === self::ENC_BCD) {
            // base 10 and hex BCD encoded numbers are the same in range 0 to 9
            $hexData = $this->data;
            // Pad on Byte lenght
            if (strlen($hexData) % 2 === 1) {
                $hexData .= 'F';
            }
            return $hexData;
        }
        // ASCII encoding
        $hexData = '';
        foreach (str_split($this->data) as $char) {
            $hexData .= base_convert(ord($char), 10, 16);
        }
        return $hexData;
    }

    /**
     * @return string returns the hex representation of the Data Element incl header information
     */
    public function toHex(): string
    {
        if (empty($this->data)) {
            return '';
        }
        return $this->getHeaderHex() . $this->getDataHex();
    }

    /**
     * @param string $hex which will be converted
     * @param int $length to which the byte will be padded (default: 8)
     * @return string binary representation of hex value as string with length $length
     */
    public static function hexToByte(string $hex, int $length = 8): string
    {
        $byte = base_convert($hex, 16, 2);
        return str_pad($byte, $length, '0', STR_PAD_LEFT);
    }

    /**
     * @return int calculates Luhn Checksum of this object
     */
    public function getLuhnChecksum(): int
    {
        return self::calcLuhn($this->getDataHex());
    }

    /**
     * @return int calculates the Luhn checksum of a given hex string
     */
    protected static function calcLuhn(string $hex): int
    {
        $sum = 0;
        $doubleIt = false;
        foreach (str_split($hex) as $char) {
            $number = (int) base_convert($char, 16, 10);
            if ($doubleIt) {
                $number *= 2;
                $decRep = str_split($number);
                foreach ($decRep as $value) {
                    $sum += (int) $value;
                }
            } else {
                $sum += $number;
            }
            $doubleIt = !$doubleIt;
        }
        return $sum;
    }

    /**
     * Some handy debug info
     */
    public function __debugInfo(): ?array
    {
        return [
            'header' => $this->getHeaderHex(),
            'data' => $this->data,
            'hex-data' => $this->getDataHex(),
            'hex' => $this->toHex(),
            'luhn' => $this->getLuhnChecksum(),
        ];
    }

    /**
     * @return string hex representation of object
     */
    public function __toString()
    {
        return $this->toHex();
    }
}
