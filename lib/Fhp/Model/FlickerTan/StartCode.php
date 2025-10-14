<?php

namespace Fhp\Model\FlickerTan;

/**
 * Represents a startcode in the TAN Flicker Challenge. Shortens the given challenge
 * @see https://www.hbci-zka.de/dokumente/spezifikation_deutsch/hhd/Belegungsrichtlinien%20TANve1.5%20FV%20vom%202018-04-16.pdf
 */
class StartCode extends DataElement
{
    /**
     * @var string[] of the control bytes in hex representation
     */
    private $controlBytes;

    /**
     * Parses Header information, control bytes and start code
     * @param string the rest of the given challenge from the bank to parse
     * @return array [string, FlickerTanStartCode]
     */
    public static function parseNextBlock(string $challenge): array
    {
        $header = substr($challenge, 0, 2);
        $rest = substr($challenge, 2);
        $byte = self::hexToByte($header);
        /* LS encoded base 16, bit idx:
         * 0: 0=without ctrl byte 1=with ctrl byte
         * 1: 0=BCD 1=ASC // never set
         * 2 - 7: intval: start code length
         */
        $hasControl = $byte[0] === '1';
        $length = (int) base_convert(substr($byte, 2, 6), 2, 10);
        [$ctrlBytes, $rest] = self::parseControlBytes($rest, $hasControl);
        $data = substr($rest, 0, $length);
        $rest = substr($rest, $length);
        return [$rest, new self($ctrlBytes, $data)];
    }

    /**
     * Helper function to parse the control bytes
     * @param string $challenge the unparsed rest of the challenge string
     * @param bool $hasControl is a controlbyte expected
     * @return array [string[] of controlbytes, string unparsed rest of the challenge]
     */
    private static function parseControlBytes(string $challenge, bool $hasControl): array
    {
        $controlBytes = [];
        $rest = $challenge;
        while ($hasControl) {
            $ctrl = substr($challenge, 0, 2);
            $controlBytes[] = $ctrl;
            $rest = substr($challenge, 2);
            $hasControl = self::hexToByte($ctrl)[0] === '1';
        }
        return [$controlBytes, $rest];
    }

    /**
     * @throws \InvalidArgumentException if $ctrlBytes are unequal to ['01'] -> old version not supported so far
     */
    protected function __construct(array $ctrlBytes, string $data)
    {
        if ($ctrlBytes !== ['01']) {
            throw new \InvalidArgumentException('Other versions then 1.4 are not supported');
        }
        parent::__construct($data);
        $this->controlBytes = $ctrlBytes;
        $this->headerHighBit = '1';
    }

    public function toHex(): string
    {
        return $this->getHeaderHex() . implode('', $this->controlBytes) . $this->getDataHex();
    }

    public function getLuhnChecksum(): int
    {
        $luhn = 0;
        foreach ($this->controlBytes as $ctrl) {
            $luhn = self::calcLuhn($ctrl);
        }
        $luhn += parent::getLuhnChecksum(); // Luhn from the data (of the startcode)
        return $luhn;
    }

    public function __debugInfo(): ?array
    {
        return [
            'header' => $this->getHeaderHex(),
            'ctrl' => $this->controlBytes,
            'data' => $this->data,
            'hex-data' => $this->getDataHex(),
            'luhn' => $this->getLuhnChecksum(),
        ];
    }
}
