<?php

namespace Fhp\Tests\Unit\Action;

use Fhp\BaseAction;
use Fhp\Protocol\BPD;
use Fhp\Protocol\Message;
use Fhp\Protocol\UPD;
use Fhp\Segment\BaseSegment;

/**
 * Shared helpers for tests that drive a single action without a {@link \Fhp\FinTs} instance.
 */
abstract class ActionTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Requests the next page the way FinTs::execute() does, which includes assigning segment numbers. Without those
     * the request segments cannot be serialized.
     *
     * @return BaseSegment[]
     */
    protected static function nextRequest(BaseAction $action, BPD $bpd, ?UPD $upd = null): array
    {
        $requestSegments = $action->getNextRequest($bpd, $upd);
        // In a real message the request follows the message head (HNHBK) and the signature head (HNSHK).
        Message::setSegmentNumbers($requestSegments, 3);
        $action->setRequestSegmentNumbers(array_map(function (BaseSegment $segment) {
            return $segment->getSegmentNumber();
        }, $requestSegments));
        return $requestSegments;
    }

    /**
     * A BPD that offers exactly the given parameter segments, each filed under its own name and version.
     */
    protected static function createBpd(BaseSegment ...$parameterSegments): BPD
    {
        $bpd = new class extends BPD {
            public function getBankName()
            {
                return 'Testbank';
            }
        };
        foreach ($parameterSegments as $segment) {
            $bpd->parameters[$segment->getName()][$segment->getVersion()] = $segment;
        }
        return $bpd;
    }
}
