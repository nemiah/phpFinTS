<?php

namespace Fhp\Action;

use Fhp\BaseAction;
use Fhp\Model\SEPAAccount;
use Fhp\Protocol\BPD;
use Fhp\Protocol\UPD;
use Fhp\Segment\BaseSegment;
use Fhp\Segment\Common\Btg;
use Fhp\Segment\Common\Kti;
use Fhp\Segment\DME\HIDMESv1;
use Fhp\Segment\DME\HIDMESv2;
use Fhp\Segment\DSE\HIDSESv2;
use Fhp\Segment\DSE\HIDXES;
use Fhp\Segment\SPA\HISPAS;
use Fhp\Syntax\Bin;
use Fhp\UnsupportedException;

/**
 * Initiate one or multiple SEPA Direct Debits ("Lastschriften")
 */
class SendSEPADirectDebit extends BaseAction
{
    // Request (if you add a field here, update __serialize() and __unserialize() as well).
    /** @var SEPAAccount */
    protected $account;
    /** @var string */
    protected $painMessage;
    /** @var string */
    protected $painNamespace;
    /** @var float */
    protected $ctrlSum;
    /** @var bool */
    protected $singleDirectDebit = false;
    /** @var bool */
    protected $tryToUseControlSumForSingleTransactions = false;
    /** @var string */
    private $coreType;

    // There are no result fields. This action is simply marked as done to indicate that the transfer was executed.

    public static function create(SEPAAccount $account, string $painMessage, bool $tryToUseControlSumForSingleTransactions = false): SendSEPADirectDebit
    {
        if (preg_match('/xmlns="(?<namespace>[^"]+)"/s', $painMessage, $matches) === 1) {
            $painNamespace = $matches['namespace'];
        } else {
            throw new \InvalidArgumentException('The namespace aka "xmlns" is missing in PAIN message');
        }

        // Check whether the PAIN message contains multiple or only one Direct Debit, should match <NbOfTxs>xx</NbOfTxs> in the XML
        $nbOfTxs = substr_count($painMessage, '<DrctDbtTxInf>');
        $ctrlSum = null;

        if (preg_match('@<GrpHdr>.*?<CtrlSum>(?<ctrlsum>[0-9.]+)</CtrlSum>.*?</GrpHdr>@s', $painMessage, $matches) === 1) {
            $ctrlSum = $matches['ctrlsum'];
        }

        if (preg_match('@<PmtTpInf>.*?<LclInstrm>.*?<Cd>(?<coretype>CORE|COR1|B2B)</Cd>.*?</LclInstrm>.*?</PmtTpInf>@s', $painMessage, $matches) === 1) {
            $coreType = $matches['coretype'];
        } else {
            throw new \InvalidArgumentException('The type CORE/COR1/B2B is missing in PAIN message');
        }

        if ($nbOfTxs > 1 && is_null($ctrlSum)) {
            throw new \InvalidArgumentException('The control sum aka "<GrpHdr><CtrlSum>xx</CtrlSum></GrpHdr>" is missing in PAIN message');
        }

        $result = new SendSEPADirectDebit();
        $result->account = $account;
        $result->painMessage = $painMessage;
        $result->painNamespace = $painNamespace;
        $result->ctrlSum = $ctrlSum;
        $result->coreType = $coreType;

        $result->singleDirectDebit = $nbOfTxs === 1;

        $result->tryToUseControlSumForSingleTransactions = $tryToUseControlSumForSingleTransactions;

        return $result;
    }

    /**
     * @deprecated Beginning from PHP7.4 __unserialize is used for new generated strings, then this method is only used for previously generated strings - remove after May 2023
     */
    public function serialize(): string
    {
        return serialize($this->__serialize());
    }

    public function __serialize(): array
    {
        return [
            parent::__serialize(),
            $this->singleDirectDebit, $this->tryToUseControlSumForSingleTransactions, $this->ctrlSum, $this->coreType, $this->painMessage, $this->painNamespace, $this->account,
        ];
    }

    /**
     * @deprecated Beginning from PHP7.4 __unserialize is used for new generated strings, then this method is only used for previously generated strings - remove after May 2023
     *
     * @param string $serialized
     * @return void
     */
    public function unserialize($serialized)
    {
        self::__unserialize(unserialize($serialized));
    }

    public function __unserialize(array $serialized): void
    {
        list(
            $parentSerialized,
            $this->singleDirectDebit, $this->tryToUseControlSumForSingleTransactions, $this->ctrlSum, $this->coreType, $this->painMessage, $this->painNamespace, $this->account,
        ) = $serialized;

        is_array($parentSerialized) ?
            parent::__unserialize($parentSerialized) :
            parent::unserialize($parentSerialized);
    }

    protected function createRequest(BPD $bpd, ?UPD $upd)
    {
        $useSingleDirectDebit = $this->singleDirectDebit;

        // If the PAIN message contains a control sum, we should use it, if the bank also supports it
        if ($useSingleDirectDebit && $this->tryToUseControlSumForSingleTransactions && !is_null($this->ctrlSum) && !is_null($bpd->getLatestSupportedParameters('HIDMES'))) {
            $useSingleDirectDebit = false;
        }

        /* @var HIDXES|BaseSegment $hidxes */
        $hidxes = $bpd->requireLatestSupportedParameters(GetSEPADirectDebitParameters::getHixxesSegmentName($this->coreType, $useSingleDirectDebit));

        $supportedPainNamespaces = null;

        if ($hidxes->getVersion() === 2) {
            /** @var HIDMESv2|HIDSESv2 $hidxes */
            $supportedPainNamespaces = $hidxes->getParameter()->getUnterstuetzteSEPADatenformate();
        }

        // If there are no SEPA formats available in the HIDXES Parameters, we look to the general formats
        if (!is_array($supportedPainNamespaces) || count($supportedPainNamespaces) === 0) {
            /** @var HISPAS $hispas */
            $hispas = $bpd->requireLatestSupportedParameters('HISPAS');
            $supportedPainNamespaces = $hispas->getParameter()->getUnterstuetzteSEPADatenformate();
        }

        // Sometimes the Bank reports supported schemas with a "_GBIC_X" postfix.
        // GIBC_X stands for German Banking Industry Committee and a version counter.
        $xmlSchema = $this->painNamespace;
        $matchingSchemas = array_filter($supportedPainNamespaces, function ($value) use ($xmlSchema) {
            // For example urn:iso:std:iso:20022:tech:xsd:pain.008.001.08 from the xml matches
            // urn:iso:std:iso:20022:tech:xsd:pain.008.001.08_GBIC_4
            return str_starts_with($value, $xmlSchema);
        });

        if (count($matchingSchemas) === 0) {
            throw new UnsupportedException("The bank does not support the XML schema $this->painNamespace, but only "
                . implode(', ', $supportedPainNamespaces));
        }

        /** @var mixed $hkdxe */ // TODO Put a new interface type here.
        $hkdxe = $hidxes->createRequestSegment();
        $hkdxe->kontoverbindungInternational = Kti::fromAccount($this->account);
        $hkdxe->sepaDescriptor = $this->painNamespace;
        $hkdxe->sepaPainMessage = new Bin($this->painMessage);

        if (!$useSingleDirectDebit) {
            if ($hidxes->getParameter()->einzelbuchungErlaubt) {
                $hkdxe->einzelbuchungGewuenscht = false;
            }

            /* @var HIDMESv1 $hidxes */
            // Just always send the control sum
            // if ($hidxes->getParameter()->summenfeldBenoetigt) {
            $hkdxe->summenfeld = Btg::create($this->ctrlSum);
            // }
        }

        return $hkdxe;
    }
}
