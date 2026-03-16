<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HIPINS;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Geschäftsvorfallspezifische PIN/TAN-Informationen
 *
 * Informs the application which business requests it can send to the server. The mere presence of this DEG for a
 * particular request means that it can be sent (through PIN/TAN, which is the only mode supported by this library).
 * The $tanErforderlich flag additionally specifies whether a TAN is needed or not.
 */
class GeschaeftsvorfallspezifischePinTanInformationen extends BaseDeg
{
    /** Max length: 6; The segment name of the potential client request. */
    public string $segmentkennung;
    /** Whether a TAN is needed. */
    public bool $tanErforderlich;
}
