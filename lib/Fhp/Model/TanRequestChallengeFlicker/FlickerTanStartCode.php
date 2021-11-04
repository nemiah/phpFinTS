<?php

namespace Fhp\Model\TanRequestChallengeFlicker;

use InvalidArgumentException;

class FlickerTanStartCode extends FlickerTanDataElement
{
    /**
     * @var string[] of the control bytes in hex representation
     */
    private $controlBytes;

    /**
     * Parses Header information, control bytes and start code
     * @return array [string, FlickerTanStartCode]
     */
    public static function parseNextBlock(string $challenge): array
    {
        $header = substr($challenge, 0, 2);
        $rest = substr($challenge, 2);
        $byte = self::hexToByte($header);
        /* LS encoded base 16 idx:
         * 2 - 7: length start code
         * 1: 0=BCD 1=ASC // never set
         * 0: 0=without ctrl byte 1=with ctrl byte */
        $hasControl = $byte[0] === '1';
        $length = (int) base_convert(substr($byte, 2, 6), 2, 10);
        [$ctrlBytes, $rest] = self::parseControlBytes($rest, $hasControl);
        $data = substr($rest, 0, $length);
        $rest = substr($rest, $length);
        return [$rest, new self($ctrlBytes, $data)];
    }

    /**
     * @param array $ctrlBytes
     * @param string $data
     * @throws InvalidArgumentException if $ctrlBytes are unequal to ['01'] -> old version not supported so far
     */
    protected function __construct(array $ctrlBytes, string $data)
    {
        if ($ctrlBytes !== ['01']) {
            throw new InvalidArgumentException('Other versions then 1.4 are not supported');
        }
        parent::__construct($data);
        $this->controlBytes = $ctrlBytes;
        $this->headerHighBit = '1';
    }

    /**
     * {@inheritDoc}
     */
    public function toHex(): string
    {
        return $this->getHeaderHex() . implode('', $this->controlBytes) . $this->getDataHex();
    }

    /**
     * Helper function to parse the control bytes
     * @param $challenge
     * @param $hasControl
     * @return array
     */
    private static function parseControlBytes($challenge, $hasControl): array
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
     * {@inheritDoc}
     */
    public function getLuhnChecksum(): int
    {
        $luhn = 0;
        foreach ($this->controlBytes as $ctrl) {
            $luhn = $this->calcLuhn($ctrl);
        }
        $luhn += parent::getLuhnChecksum(); // Luhn from Startcode data
        return $luhn;
    }

    /**
     * {@inheritDoc}
     */
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
