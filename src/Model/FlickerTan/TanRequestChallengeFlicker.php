<?php

namespace Fhp\Model\FlickerTan;

use Fhp\Syntax\Bin;

/**
 * Parses the HHDUC Flicker Tan Challenge to a Flicker pattern with suffixed control sequence
 * @see https://www.hbci-zka.de/dokumente/spezifikation_deutsch/hhd/Belegungsrichtlinien%20TANve1.5%20FV%20vom%202018-04-16.pdf
 */
class TanRequestChallengeFlicker
{
    /**
     * @var string original challenge data
     */
    private $challenge;

    /**
     * @var StartCode holds and parses the startcode block of the challenge
     */
    private $startCode;

    /**
     * @var DataElement[] Holds and parses the first DataElement of the challenge, 3 max
     */
    private $dataElements;

    public function __construct(Bin $challengeBin)
    {
        $this->challenge = $challengeBin->getData();
        $this->parseChallenge();
    }

    private function parseChallenge(): void
    {
        $reducedChallenge = trim(str_replace(' ', '', $this->challenge));
        // length of whole challenge (without lc) max 255 | encoding: base 10
        $lc = (int) substr($reducedChallenge, 0, 3);
        $reducedChallenge = substr($reducedChallenge, 3);
        if (strlen($reducedChallenge) !== $lc) {
            throw new \InvalidArgumentException("Wrong length of TAN Challenge expected: $lc - found: ". strlen($reducedChallenge). ' - only Version 1.4 supported');
        }

        [$reducedChallenge, $this->startCode] = StartCode::parseNextBlock($reducedChallenge);
        for ($i = 0; $i < 3; ++$i) {
            [$reducedChallenge, $de] = DataElement::parseNextBlock($reducedChallenge);
            $this->dataElements[$i] = $de;
        }
        if (!empty($reducedChallenge)) {
            throw new \InvalidArgumentException("Challenge has unexpected ending $reducedChallenge");
        }
    }

    /**
     * @return string the xor checksum string in hex base
     */
    private function calcXorChecksum(): string
    {
        $xor = 0b0000; // bin Representation of 0
        $hex = str_split($this->getHexPayload());
        foreach ($hex as $hexChar) {
            $intVal = (int) base_convert($hexChar, 16, 10);
            $xor ^= $intVal; // xor operator
        }
        return base_convert($xor, 10, 16);
    }

    /**
     * @return string returns hex representation of the flicker code
     */
    private function getHexPayload(): string
    {
        $hex = $this->startCode->toHex();
        for ($i = 0; $i < 3; ++$i) {
            $hex .= $this->dataElements[$i]->toHex();
        }
        $lc = strlen($hex) / 2 + 1;
        $lc = str_pad(base_convert($lc, 10, 16), 2, '0', STR_PAD_LEFT);
        return $lc . $hex;
    }

    /**
     * calculates Luhn Checksum over the whole code
     */
    private function calcLuhnChecksum(): int
    {
        $luhn = $this->startCode->getLuhnChecksum();
        for ($i = 0; $i < 3; ++$i) {
            $luhn += $this->dataElements[$i]->getLuhnChecksum();
        }
        return (10 - ($luhn % 10)) % 10;
    }

    /**
     * @return string hex representation of challenge
     */
    public function getHex(): string
    {
        $payload = $this->getHexPayload();
        $luhn = $this->calcLuhnChecksum();
        $xor = $this->calcXorChecksum();

        return $payload . $luhn . $xor;
    }

    /**
     * takes Hex Representation and builds bit patterns from it. Notable differences to the hex code:
     * - prefixes 0FFF to the hex code (F0FF after swap)
     * - swaps half bytes e.g. 0F FF ... -> F0 FF ...
     * Hints for rendering:
     *  - 1 equals white, 0 equals black rectangle (other colors are possible, as long contrast is high enough, but unadvised)
     *  - The Tan Generator expects the following onscreen pattern: | clock | 2^0 | 2^1 | 2^2 | 2^3 |
     *  - Tan Generators read all 4 values on white to black flank, it is suggested to change the pattern on the black to white flank
     *  - each entry in the returned array will be hold for the whole clock cycle (both colors)
     * @return string[] integer indexed array with strings, each 4 chars long with 0 or 1, which represent the expected flicker patterns
     */
    public function getFlickerPattern(): array
    {
        $hexCode = $this->getHex();
        $bitPattern = [];
        // starting pattern - beginning of the pattern 0xFOFF
        $bitPattern[] = '1111';
        $bitPattern[] = '0000';
        $bitPattern[] = '1111';
        $bitPattern[] = '1111';
        // convert hex code to flicker pattern
        $len = strlen($hexCode);
        for ($i = 0; $i < $len; $i += 2) {
            // convert hex to bin representation of 1 byte at a time
            $byte = base_convert(substr($hexCode, $i, 2), 16, 2);
            // add missing zeros to the left
            $byte = str_pad($byte, 8, '0', STR_PAD_LEFT);
            // reverse order of half-bytes;  flicker pattern is | clock | 2^0 | 2^1 | 2^2 | 2^3 |
            $firstHalfByte = strrev(substr($byte, 0, 4));
            $secondHalfByte = strrev(substr($byte, 4, 4));
            // change order from first and second half byte (@see C.2)
            $bitPattern[] = $secondHalfByte;
            $bitPattern[] = $firstHalfByte;
        }
        return $bitPattern;
    }

    public function __debugInfo(): ?array
    {
        return [
            'startcode' => $this->startCode,
            'dataElements' => $this->dataElements,
            'payload' => $this->getHexPayload(),
            'luhn' => $this->calcLuhnChecksum(),
            'xor' => $this->calcXorChecksum(),
        ];
    }
}
