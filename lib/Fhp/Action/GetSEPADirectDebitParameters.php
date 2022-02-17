<?php

namespace Fhp\Action;

use Fhp\BaseAction;
use Fhp\Protocol\BPD;
use Fhp\Protocol\UPD;
use Fhp\Segment\DSE\HIDXES;
use Fhp\Segment\DSE\MinimaleVorlaufzeitSEPALastschrift;

/**
 * Retrieves information about SEPA Direct Debit Requests
 */
class GetSEPADirectDebitParameters extends BaseAction
{
    public const SEQUENCE_TYPES = ['FRST', 'OOFF', 'FNAL', 'RCUR'];
    public const DIRECT_DEBIT_TYPES = ['CORE', 'COR1', 'B2B'];

    /** @var string */
    private $directDebitType;

    /** @var string */
    private $seqType;

    /** @var bool */
    private $singleDirectDebit;

    /** @var HIDXES */
    private $hidxes;

    public static function create(string $seqType, bool $singleDirectDebit, string $directDebitType = 'CORE')
    {
        if (!in_array($directDebitType, self::DIRECT_DEBIT_TYPES)) {
            throw new \InvalidArgumentException('Unknown CORE type, possible values are ' . implode(', ', self::DIRECT_DEBIT_TYPES));
        }
        if (!in_array($seqType, self::SEQUENCE_TYPES)) {
            throw new \InvalidArgumentException('Unknown SEPA sequence type, possible values are ' . implode(', ', self::SEQUENCE_TYPES));
        }
        $result = new GetSEPADirectDebitParameters();
        $result->directDebitType = $directDebitType;
        $result->seqType = $seqType;
        $result->singleDirectDebit = $singleDirectDebit;
        return $result;
    }

    public static function getHixxesSegmentName(string $directDebitType, bool $singleDirectDebit): string
    {
        switch ($directDebitType) {
            case 'CORE':
            case 'COR1':
                return $singleDirectDebit ? 'HIDSES' : 'HIDMES';
            case 'B2B':
                return $singleDirectDebit ? 'HIBSES' : 'HIBMES';
            default:
                throw new \InvalidArgumentException('Unknown DirectDebitTypes type, possible values are ' . implode(', ', self::DIRECT_DEBIT_TYPES));
        }
    }

    /** {@inheritdoc} */
    protected function createRequest(BPD $bpd, ?UPD $upd)
    {
        $this->hidxes = $bpd->requireLatestSupportedParameters(static::getHixxesSegmentName($this->directDebitType, $this->singleDirectDebit));
        $this->isDone = true;
        return []; // No request to the bank required
    }

    /**
     * @return MinimaleVorlaufzeitSEPALastschrift|null The information about the lead time for the given Sequence Type and Direct Debit Type
     */
    public function getMinimalLeadTime(): ?MinimaleVorlaufzeitSEPALastschrift
    {
        $parsed = $this->hidxes->getParameter()->getMinimalLeadTime($this->seqType);
        if ($parsed instanceof MinimaleVorlaufzeitSEPALastschrift) {
            return $parsed;
        }
        return $parsed[$this->directDebitType] ?? null;
    }
}
