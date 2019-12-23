<?php

namespace Fhp\Action;

use Fhp\BaseAction;
use Fhp\DataTypes\Bin;
use Fhp\Model\SEPAAccount;
use Fhp\Protocol\BPD;
use Fhp\Protocol\UPD;
use Fhp\Segment\BaseSegment;
use Fhp\Segment\Common\Btg;
use Fhp\Segment\Common\Kti;
use Fhp\Segment\DME\HIDMESv1;
use Fhp\Segment\DME\HIDMESv2;
use Fhp\Segment\DME\HIDXES;
use Fhp\Segment\DME\HKDMEv1;
use Fhp\Segment\DME\HKDMEv2;
use Fhp\Segment\DSE\HIDSESv2;
use Fhp\Segment\DSE\HKDSEv1;
use Fhp\Segment\DSE\HKDSEv2;
use Fhp\Segment\SPA\HISPAS;
use Fhp\UnsupportedException;

/**
 * Initiate one or multiple SEPA Direct Debits ("Lastschriften")
 */
class SendSEPADirectDebit extends BaseAction
{
    /** @var SEPAAccount */
    private $account;

    /** @var string */
    private $painMessage;

    /** @var string */
    private $painNamespace;

    /** @var float */
    private $ctrlSum;

    /** @var bool */
    private $singleDirectDebit = false;

    public static function create(SEPAAccount $account, string $painMessage): SendSEPADirectDebit
    {
        if (preg_match('/xmlns="(?<namespace>[^"]+)"/s', $painMessage, $matches) === 1) {
            $painNamespace = $matches['namespace'];
        } else {
            throw new \InvalidArgumentException('The namespace aka "xmlns" is missing in PAIN message');
        }

        // Check whether the PAIN message contains multiple or only one Direct Debit, should match <NbOfTxs>xx</NbOfTxs> in the XML
        $nbOfTxs = substr_count($painMessage, '<DrctDbtTxInf>');
        $ctrlSum = null;

        if (preg_match('/<GrpHdr>.*<CtrlSum>(?<ctrlsum>[.0-9]+)<\/CtrlSum>.*<\/GrpHdr>/s', $painMessage, $matches) === 1) {
            $ctrlSum = $matches['ctrlsum'];
        }

        if ($nbOfTxs > 1 && is_null($ctrlSum)) {
            throw new \InvalidArgumentException('The control sum aka "<GrpHdr><CtrlSum>xx</CtrlSum></GrpHdr>" is missing in PAIN message');
        }

        $result = new SendSEPADirectDebit();
        $result->account = $account;
        $result->painMessage = $painMessage;
        $result->painNamespace = $painNamespace;
        $result->ctrlSum = $ctrlSum;

        $result->singleDirectDebit = $nbOfTxs === 1;

        return $result;
    }

    public function createRequest(BPD $bpd, ?UPD $upd)
    {
        $useSingleDirectDebit = $this->singleDirectDebit;

        // If the PAIN message contains a control sum, we should use it, if the bank also supports it
        if ($useSingleDirectDebit && !is_null($this->ctrlSum) && !is_null($bpd->getLatestSupportedParameters('HIDMES'))) {
            $useSingleDirectDebit = false;
        }

        /** @var HIDXES|BaseSegment $hidxes */
        $hidxes = $bpd->requireLatestSupportedParameters($useSingleDirectDebit ? 'HIDSES' : 'HIDMES');

        $supportedPainNamespaces = null;

        if ($hidxes->getVersion() === 2) {
            /** @var HIDMESv2|HIDSESv2 $hidxes */
            $supportedPainNamespaces = $hidxes->getParameter()->unterstuetzteSEPADatenformate;
        }

        // If there are no SEPA formats available in the HIDXES Parameters, we look to the general formats
        if (!is_array($supportedPainNamespaces) || count($supportedPainNamespaces) === 0) {
            /** @var HISPAS $hispas */
            $hispas = $bpd->requireLatestSupportedParameters('HISPAS');
            $supportedPainNamespaces = $hispas->getParameter()->getUnterstuetzteSepaDatenformate();
        }

        if (!in_array($this->painNamespace, $supportedPainNamespaces)) {
            throw new UnsupportedException("The bank does not support the XML schema $this->painNamespace, but only "
                . implode(', ', $supportedPainNamespaces));
        }

        /** @var HKDMEv1|HKDSEv1 $hkdxe */
        $hkdxe = null;
        switch ($hidxes->getVersion()) {
            case 1:
                $hkdxe = $useSingleDirectDebit ? HKDSEv1::createEmpty() : HKDMEv1::createEmpty();
            break;
            case 2:
                $hkdxe = $useSingleDirectDebit ? HKDSEv2::createEmpty() : HKDMEv2::createEmpty();
            break;
            default:
                throw new UnsupportedException('Unsupported HKDME or HKDSE version: ' . $hidxes->getVersion());
        }

        $hkdxe->kontoverbindungInternational = Kti::fromAccount($this->account);
        $hkdxe->sepaDescriptor = $this->painNamespace;
        $hkdxe->sepaPainMessage = new Bin($this->painMessage);

        if ($useSingleDirectDebit) {
            $hkdxe->einzelbuchungGewuenscht = false;

            /* @var HIDMESv1 $hidxes */
            // Just always send the control sum
            //if ($hidxes->getParameter()->summenfeldBenoetigt) {
            $hkdxe->summenfeld = Btg::create($this->ctrlSum);
            //}
        }

        return $hkdxe;
    }
}
