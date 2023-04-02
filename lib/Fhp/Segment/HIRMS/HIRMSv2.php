<?php

namespace Fhp\Segment\HIRMS;

use Fhp\Segment\BaseSegment;

/**
 * Segment: Rückmeldungen zu Segmenten (Version 2)
 * Sender: Kreditinstitut
 *
 * Contains reponse code(s) pertain to an individual segment in the request message. The request segment is referenced
 * inside the Segmentkopf (see the BaseSegment super class). If the request segment consisted of multiple data elements
 * (or DEGs), the Rueckmeldung.bezugsdatenelement will point to the intended one.
 * The HIRMS segment itself is repeated. There is one HIRMS per request segment, and one Rueckmeldung per original DE(G)
 * that needs to be referenced.
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section B.7.2
 */
class HIRMSv2 extends BaseSegment implements RueckmeldungContainer
{
    use FindRueckmeldungTrait; // For RueckmeldungContainer.

    /** @var Rueckmeldung[] @Max(99) */
    public array $rueckmeldung;
}
