<?php

namespace Fhp\Model\FlickerTan;

use InvalidArgumentException;

/**
 * inspired by @see https://github.com/willuhn/hbci4java/blob/master/src/org/kapott/hbci/manager/FlickerCode.java
 * documentation @see tan_hhd_uc_v14.pdf e.g. https://github.com/willuhn/hbci4java/blob/master/doc/tan_hhd_uc_v14.pdf
 */
class SvgRenderer
{
    private $svg;

    /**
     * @var string[] the code in half-bit representation (string has length 4)
     */
    private $bitPattern;

    /**
     * @var int blink frequency in Hz [1/s] should be between 2 and 20 Hz by documentation, but many TAN Generators are able to fetch 40 Hz as well
     */
    private $frequency;

    /**
     * @param string[] $bitPattern a bit pattern in the format from {@see TanRequestChallengeFlicker::getFlickerPattern()}
     * @param int $flickerFrequenz in Hz [1/s] between 2 and 40 Hz allowed
     * @param int $width width of the svg, aspect ratio around 2:1 is recommended, but not enforced, default 210
     * @param int $height height of the svg (will not adapt to width automatic), default 130
     * @param string $id DOM id of the svg, which can be used as a selector via JS, e.g. to change height or width on clientside
     * @throws InvalidArgumentException thrown if $flickerFrequenz or $bitPattern are faulty formed
     */
    public function __construct(array $bitPattern, int $flickerFrequenz = 10, int $width = 210, int $height = 130, string $id = 'flickerTanSVG')
    {
        $this->validate($bitPattern, $flickerFrequenz);
        $this->frequency = $flickerFrequenz;
        // prefix sync identifier
        $this->bitPattern = $bitPattern;
        // do the svg
        $doc = [];
        // init black background rect with rounded corners
        $doc[] = $this->buildNode('rect', [
            'width' => 210,
            'height' => 105,
            'rx' => 7.5,
            'ry' => 7.5,
            'fill' => 'black',
        ]);
        // triangle for aiming help for hardware tan generator
        $doc[] = $this->buildNode('polygon', [
            'points' => [
                '25,18', // middle bottom
                '32,5', // top right
                '18,5', // top left
            ],
            'fill' => 'grey',
        ]);
        $doc[] = $this->buildNode('polygon', [
            'points' => [ // x shifted by 160
                '185,18', // middle bottom
                '192,5', // top right
                '178,5', // top left
            ],
            'fill' => 'grey',
        ]);

        // init flicker rectangles
        for ($i = 0; $i < 5; ++$i) {
            $doc[] = $this->buildNode('rect', [
                'x' => 40 * $i + 10,
                'y' => 20,
                'width' => 30,
                'height' => 75,
            ], [$this->getAnimation($i)]);
        }
        $docAttr = [
            'xmlns' => 'http://www.w3.org/2000/svg',
            'width' => $width,
            'height' => $height,
            'viewBox' => '0 0 210 105',
            'preserveAspectRatio' => 'none',
            'id' => $id,
        ];
        $this->svg = $this->buildNode('svg', $docAttr, $doc);
    }

    /**
     * @param int $channelNumber flickerRectangles numbered from left to right, 0 is clock, 1 is 2^0, ..., 4 is 2^3 half byte representation
     */
    private function getAnimation(int $channelNumber): string
    {
        $timePerHalfByte = 1 / ($this->frequency) * 2;
        $attr = [
            'attributeName' => 'fill',
            'calcMode' => 'discrete',
            'repeatCount' => 'indefinite',
        ];
        if ($channelNumber === 0) {
            // first rectangle is the clock
            $attr['values'] = 'white;black;white';
            $attr['keyFrames'] = '0;0.5;1';
            $attr['dur'] = $timePerHalfByte . 's';
        } else {
            // arrange keyframes and colors
            $colors = array_map(static function (string $pattern) use ($channelNumber) {
                return $pattern[$channelNumber - 1] === '1' ? 'white' : 'black';
            }, $this->bitPattern);
            $keyFrames = range(0, 1, 1.0 / count($this->bitPattern));

            $attr['values'] = implode(';', $colors);
            $attr['keyFrames'] = implode(';', $keyFrames);
            $attr['dur'] = ($timePerHalfByte * count($this->bitPattern)) . 's';
        }
        return $this->buildNode('animate', $attr);
    }

    /**
     * @param string $tag the tag of the node
     * @param array $attributes name-value pairs of the node attributes
     * @param string[] $childs of the node
     * @return string the string representation of the whole node with cilds
     */
    private function buildNode(string $tag, array $attributes = [], array $childs = []): string
    {
        $attr = [];
        foreach ($attributes as $name => $value) {
            switch ($name) {
                case 'fill':
                    $attr[] = "style='fill: $value'";
                    break;
                case 'points':
                    $attr[] = "points='" . implode(' ', $value) . "'";
                    break;
                default:
                    $attr[] = "$name='$value'";
            }
        }
        $attrStr = implode(' ', $attr);
        $childStr = implode(PHP_EOL, $childs);
        return "<$tag $attrStr>$childStr</$tag>";
    }

    public function getImage(): string
    {
        return $this->svg;
    }

    public function __toString()
    {
        return $this->svg;
    }

    /**
     * Validates input for frequency and bit pattern
     * @throws InvalidArgumentException
     */
    private function validate(array $bitPattern, int $frequency): void
    {
        if ($frequency < 2 || $frequency > 40) {
            throw new InvalidArgumentException('Frequency is not between 2 and 40 Hz');
        }
        foreach ($bitPattern as $idx => $pattern) {
            // detect if a string is not length 4 with only 0 and 1 chars
            if (!preg_match('/^[01]{4}$/', $pattern)) {
                throw new InvalidArgumentException("Bit Pattern at index $idx is faulty, only 0 and 1 are allowed with length 4");
            }
        }
    }
}
