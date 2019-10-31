<?php

namespace Fhp\Segment\HIRMG;

use Fhp\Segment\BaseSegment;
use Fhp\Segment\HIRMS\RueckmeldungContainer;

/**
 * Segment: Rückmeldungen zur Gesamtnachricht (Version 2)
 * Sender: Kreditinstitut
 *
 * Contains response code(s) that pertain to the request message as a whole (as opposed to individual segments, see also
 * HIRMS).
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section B.7.2
 */
class HIRMGv2 extends BaseSegment
{
    use RueckmeldungContainer; // For utility functions.

	/** @var \Fhp\Segment\HIRMS\Rueckmeldung[] @Max(99) */
	public $rueckmeldung;
}
