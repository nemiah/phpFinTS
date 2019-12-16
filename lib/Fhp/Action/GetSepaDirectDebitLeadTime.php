<?php

namespace Fhp\Action;

use Fhp\BaseAction;
use Fhp\Segment\DME\HIDMESv1;
use Fhp\Segment\DME\HIDMESv2;
use Fhp\Segment\DME\MinimaleVorlaufzeitSEPALastschrift;
use Fhp\Segment\DSE\HIDSESv1;

/**
 * Retrieves information about lead times for SEPA Direct Debit Requests
 */
class GetSepaDirectDebitLeadTime extends BaseAction
{
    const SEQUENCE_TYPES = ['FRST', 'OOFF', 'FNAL', 'RCUR'];
    const CORE_TYPES = ['CORE', 'COR1'];

    /** @var MinimaleVorlaufzeitSEPALastschrift|null */
    private $leadTime;

    /** @var string */
    private $coreType;

    /** @var string */
    private $seqType;

    /** @var bool */
    private $singleDirectDebit;

    public static function create(string $seqType, bool $singleDirectDebit, string $coreType = 'CORE')
    {
        if (!in_array($coreType, self::CORE_TYPES)) {
            throw new \InvalidArgumentException('Unknown CORE Type');
        }
        if (!in_array($seqType, self::SEQUENCE_TYPES)) {
            throw new \InvalidArgumentException('Unknown SEPA Sequence Type');
        }
        $result = new GetSepaDirectDebitLeadTime();
        $result->coreType = $coreType;
        $result->seqType = $seqType;
        $result->singleDirectDebit = $singleDirectDebit;

        return $result;
    }

    /** {@inheritdoc} */
    public function createRequest($bpd, $upd)
    {
        $type = $this->singleDirectDebit ? 'HIDSES' : 'HIDMES';

        $hidxes = $bpd->getLatestSupportedParameters($type);

        switch ($hidxes->getVersion()) {
            case 1:
                /* @var HIDMESv1|HIDSESv1 $hidxes */
                $leadTime = in_array($this->seqType, ['FRST', 'OFF']) ? $hidxes->parameter->minimaleVorlaufzeitFRSTOOFF : $hidxes->parameter->minimaleVorlaufzeitFNALRCUR;
                $this->leadTime = MinimaleVorlaufzeitSEPALastschrift::create($leadTime, '235959');
                break;
            case 2:
                /* @var HIDMESv2|HIDSESv2 $hidxes */
                $this->leadTime = $hidxes->parameter->getMinimaleVorlaufzeit($this->seqType, $this->coreType);
                break;
        }
        // No request to the bank required
        return [];
    }

    /**
     * @return MinimaleVorlaufzeitSEPALastschrift|null The information about the lead time for the given Sequence Type and Core Type
     */
    public function getLeadTime(): ?MinimaleVorlaufzeitSEPALastschrift
    {
        return $this->leadTime;
    }
}
