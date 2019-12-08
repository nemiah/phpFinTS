<?php

namespace Fhp\Protocol;

use Fhp\BaseAction;
use Fhp\Segment\TAB\HITAB;
use Fhp\Segment\TAB\HKTABv4;
use Fhp\Segment\TAB\HKTABv5;
use Fhp\Segment\TAB\TanMediumListe;
use Fhp\UnsupportedException;

/**
 * Fetches the TAN media (e.g. different mobile phones or iTAN lists) that are available to the user (HTKAB).
 */
class GetTanMedia extends BaseAction
{
    /** @var TanMediumListe[]|null */
    private $tanMedia;

    /** {@inheritdoc} */
    public function createRequest($bpd, $upd)
    {
        // Prepare the HKTAB request.
        $hitabs = $bpd->requireLatestSupportedParameters('HITABS');
        switch ($hitabs->getVersion()) {
            case 4:
                return HKTABv4::createEmpty();
            case 5:
                return HKTABv5::createEmpty();
            default:
                throw new UnsupportedException('Unsupported HKTAB version: ' . $hitabs->getVersion());
        }
    }

    /** {@inheritdoc} */
    public function processResponse($response)
    {
        parent::processResponse($response);
        /** @var HITAB $hitab */
        $hitab = $response->requireSegment(HITAB::class);
        $this->tanMedia = $hitab->getTanMediumListe() === null ? [] : $hitab->getTanMediumListe();
    }

    /**
     * @return TanMediumListe[]|null
     * @throws \Exception See {@link #ensureSuccess()}.
     */
    public function getTanMedia(): ?array
    {
        $this->ensureSuccess();
        return $this->tanMedia;
    }
}
