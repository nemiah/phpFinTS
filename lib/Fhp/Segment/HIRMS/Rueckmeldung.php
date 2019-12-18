<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HIRMS;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Rückmeldung (Version 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: F (under letter R)
 */
class Rueckmeldung extends BaseDeg
{
    /** @var int See also the Rueckmeldungscode class/enum. */
    public $rueckmeldungscode;
    /**
     * O: bei Verwendung im Segment HIRMS
     * N: bei Verwendung im Segment HIRMG
     * @var string|null Max length: 7
     */
    public $bezugsdatenelement;
    /** @var string Max length: 80 */
    public $rueckmeldungstext;
    /** @var string[]|null @Max(10), max length each: 35 */
    public $rueckmeldungsparameter;

    /**
     * This is not part of the FinTS wire format, but for convenience we store it here. If this Rueckmeldung pertains to
     * a particular segment of the request, then this will be its segment number.
     * @var int|null @Ignore
     */
    public $referenceSegment;

    public function __toString()
    {
        $referenceSegment = $this->referenceSegment === null ? 'global' : "wrt seg $this->referenceSegment";
        $result = "$this->rueckmeldungscode ($referenceSegment): $this->rueckmeldungstext";
        if ($this->bezugsdatenelement !== null) {
            $result .= " (wrt DE $this->bezugsdatenelement)";
        }
        if ($this->rueckmeldungsparameter !== null && count($this->rueckmeldungsparameter) > 0) {
            $result .= ' [' . implode(', ', $this->rueckmeldungsparameter) . ']';
        }
        return $result;
    }
}
