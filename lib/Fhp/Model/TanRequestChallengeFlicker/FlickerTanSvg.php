<?php

namespace Fhp\Model\TanRequestChallengeFlicker;

use SVG\Nodes\Presentation\SVGAnimate;
use SVG\Nodes\Shapes\SVGPolygon;
use SVG\Nodes\Shapes\SVGRect;
use SVG\SVG;

/**
 * inspired by @see https://github.com/willuhn/hbci4java/blob/master/src/org/kapott/hbci/manager/FlickerCode.java
 * documentation @see tan_hhd_uc_v14.pdf
 */
class FlickerTanSvg extends SVG
{
    /**
     * @var string[] the code in half-bit representation (string has length 4)
     */
    private $bitPatterns;

    /**
     * @var int blink frequency in Hz [1/s] should be between 2 and 20 Hz by documentation, but many TAN Generators are able to fetch 40 Hz as well
     */
    private $frequency;

    /**
     * @param int $flickerFrequenz in Hz [1/s]
     * @param int $width width of the svg, aspect ratio 2:1 is recommended, but not enforced
     * @param int $height height of the svg
     */
    public function __construct(string $hexCode, int $flickerFrequenz = 10, int $width = 210, int $height = 130)
    {
        $this->frequency = $flickerFrequenz;
        // prefix sync identifier
        $this->bitPatterns[] = '1111';
        $this->bitPatterns[] = '0000';
        $this->bitPatterns[] = '1111';
        $this->bitPatterns[] = '1111';
        // convert hex code to flicker pattern
        $len = strlen($hexCode);
        for ($i = 0; $i < $len; $i += 2) {
            $byte = base_convert(substr($hexCode, $i, 2), 16, 2);
            // add missing zeros to the left
            $byte = str_pad($byte, 8, '0', STR_PAD_LEFT);
            // reverse order of half-bytes;  flicker pattern is | clock | 2^0 | 2^1 | 2^2 | 2^3 |
            $firstHalfByte = strrev(substr($byte, 0, 4));
            $secondHalfByte = strrev(substr($byte, 4, 4));
            // change order from first and second half byte (@see C.2)
            $this->bitPatterns[] = $secondHalfByte;
            $this->bitPatterns[] = $firstHalfByte;
        }

        // do the svg
        parent::__construct($width, $height);
        $doc = $this->getDocument();
        // set relative coordinates, which are not dependent on render size
        $doc->setAttribute('viewBox', '0 0 210 105');
        $doc->setAttribute('preserveAspectRatio', 'none');
        // init background rect
        $bg = (new SVGRect(0, 0, 210, 105))
            ->setRX(7.5)
            ->setRY(7.5)
            ->setStyle('fill', 'black');
        $doc->addChild($bg);
        $triLeft = (new SVGPolygon([
            [25, 18], // middle bottom
            [32, 5], // top right
            [18, 5], // top left
        ]))->setStyle('fill', 'grey');
        $doc->addChild($triLeft);
        $triRight = (new SVGPolygon([
            [160 + 25, 18], // middle bottom
            [160 + 32, 5], // top right
            [160 + 18, 5], // top left
        ]))->setStyle('fill', 'grey');
        $doc->addChild($triRight);

        // init flicker rectangles
        for ($i = 0; $i < 5; ++$i) {
            $rect = new SVGRect(40 * $i + 10, 20, 30, 75);
            $animation = $this->getAnimation($i);
            $rect->addChild($animation);
            $doc->addChild($rect);
        }
        //var_dump($this->bitPatterns);
    }

    /**
     * @param int $channelNumber flickerRectangles numbered from left to right, 0 is clock, 1 is 2^0, ..., 4 is 2^3 half byte representation
     * @return SVGAnimate
     */
    public function getAnimation(int $channelNumber): SVGAnimate
    {
        $timePerHalfByte = 1 / ($this->frequency) * 2;
        $animation = (new SVGAnimate())
            ->setAttribute('attributeName', 'fill')
            ->setAttribute('calcMode', 'discrete')
            ->setAttribute('repeatCount', 'indefinite');
        if ($channelNumber === 0) {
            // first rectangle is the clock
            return $animation
                ->setAttribute('values', 'white;black;white')
                ->setAttribute('keyFrames', '0;0.5;1')
                ->setAttribute('dur', $timePerHalfByte . 's');
        }
        $colors = array_map(static function (string $pattern) use ($channelNumber) {
            return $pattern[$channelNumber - 1] === '1' ? 'white' : 'black';
        }, $this->bitPatterns);
        $keyFrames = range(0, 1, 1.0 / count($this->bitPatterns));

        return $animation
            ->setAttribute('dur', ($timePerHalfByte * count($this->bitPatterns)) . 's')
            ->setAttribute('values', implode(';', $colors))
            ->setAttribute('keyFrames', implode(';', $keyFrames));
    }
}
