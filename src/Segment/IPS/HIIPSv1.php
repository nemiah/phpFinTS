<?php

namespace Fhp\Segment\IPS;

use Fhp\Segment\BaseSegment;
use Fhp\Segment\Common\Kti;
use Fhp\Syntax\Bin;

/**
 * Segment: SEPA-Instant Payment Status rückmelden (Version 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2022-04-15_final_version.pdf
 * Section: C.10.2.9.2 b)
 */
class HIIPSv1 extends BaseSegment
{
    public Kti $kontoverbindungInternational;
    /** Max length: 256 */
    public ?string $sepaDescriptor = null;
    public ?Bin $sepaPainMessage = null;
    /** Max length: 99 */
    public string $auftragsidentifikation;

    /**
     * SEPA C-Code („C“ for Cancellation)
     * Possible values
     * 3: Delete
     * 4: Recall
     */
    public ?int $sepaCCode = null;

    /**
     * Possible values: see {@link \Fhp\Segment\IPZ\HIIPZv1::$statusSepaAuftrag}.
     */
    public ?int $statusSepaAuftrag = null;
}
