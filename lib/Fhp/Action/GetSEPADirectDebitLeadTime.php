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
class GetSEPADirectDebitLeadTime extends BaseAction
{
    const SEQUENCE_TYPES = ['FRST', 'OOFF', 'FNAL', 'RCUR'];
    const CORE_TYPES = ['CORE', 'COR1'];

    /** @var string */
    private $coreType;

    /** @var string */
    private $seqType;

    /** @var bool */
    private $singleDirectDebit;

    /** @var MinimaleVorlaufzeitSEPALastschrift|null */
    private $minimalLeadTime;

    public static function create(string $seqType, bool $singleDirectDebit, string $coreType = 'CORE')
    {
        if (!in_array($coreType, self::CORE_TYPES)) {
            throw new \InvalidArgumentException('Unknown CORE Type');
        }
        if (!in_array($seqType, self::SEQUENCE_TYPES)) {
            throw new \InvalidArgumentException('Unknown SEPA Sequence Type');
        }
        $result = new GetSEPADirectDebitLeadTime();
        $result->coreType = $coreType;
        $result->seqType = $seqType;
        $result->singleDirectDebit = $singleDirectDebit;

        return $result;
    }

    /** {@inheritdoc} */
    public function createRequest($bpd, $upd)
    {
        $type = $this->singleDirectDebit ? 'HIDSES' : 'HIDMES';

        $hidxes = $bpd->requireLatestSupportedParameters($type);

        switch ($hidxes->getVersion()) {
            case 1:
                /* @var HIDMESv1|HIDSESv1 $hidxes */
                $leadTime = in_array($this->seqType, ['FRST', 'OOFF']) ? $hidxes->parameter->minimaleVorlaufzeitFRSTOOFF : $hidxes->parameter->minimaleVorlaufzeitFNALRCUR;
                $this->minimalLeadTime = MinimaleVorlaufzeitSEPALastschrift::create($leadTime, '235959');
                break;
            case 2:
                /* @var HIDMESv2|HIDSESv2 $hidxes */
                $this->minimalLeadTime = $hidxes->parameter->getMinimaleVorlaufzeit($this->seqType, $this->coreType);
                break;
        }
        // No request to the bank required
        return [];
    }

    /**
     * @return MinimaleVorlaufzeitSEPALastschrift|null The information about the lead time for the given Sequence Type and Core Type
     */
    public function getMinimalLeadTime(): ?MinimaleVorlaufzeitSEPALastschrift
    {
        //$this->ensureSuccess();
        return $this->minimalLeadTime;
    }
}
