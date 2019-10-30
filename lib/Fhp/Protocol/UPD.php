<?php

namespace Fhp\Protocol;

use Fhp\Segment\HIUPA\HIUPAv4;
use Fhp\Segment\HIUPD\HIUPD;
use Fhp\Segment\HIUPD\HIUPDv4;

/**
 * Contains the "Userparameterdaten" (UPD), i.e. configuration information that was retrieved from the bank server
 * during dialog initialization and that is user-specific. This library currently does not store persisted UPD, so it
 * just retrieves them freshly every time.
 */
class UPD
{
    /** @var HIUPAv4 The HIBPA segment received from the server, which contains most of the UPD data. */
    public $hiupa;
    /** @var HIUPDv4[] All HIUPD segments from the server, which contain per-account information. */
    public $hiupd;

    public function getVersion()
    {
        return $this->hiupa->updVersion;
    }

    /**
     * @param Message $response A dialog initialization response from the server.
     * @return boolean True if the UPD data is contained in the response and {@link #extractFromResponse()} would
     *     succeed.
     */
    public static function containedInResponse($response)
    {
        return $response->hasSegment(HIUPAv4::class);
    }

    /**
     * @param Message $response The dialog initialization response from the server, which should contain the UPD
     *     data.
     * @return UPD A new UPD instance with the extracted configuration data.
     */
    public static function extractFromResponse($response)
    {
        $upd = new UPD();
        $upd->hiupa = $response->requireSegment(HIUPAv4::class);
        $upd->hiupd = $response->findSegments(HIUPD::class);
        return $upd;
    }
}
