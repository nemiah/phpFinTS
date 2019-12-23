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
use Fhp\Segment\DSE\HIDSESv2;
use Fhp\Segment\DSE\HKDSEv1;
use Fhp\Segment\SPA\HISPAS;
use Fhp\UnsupportedException;

class SendSEPADirectDebit extends BaseAction
{
    /** @var SEPAAccount */
    private $account;

    /** @var string */
    private $painMessage;

    /** @var string */
    private $xmlSchema;

    /** @var float */
    private $ctrlSum;

    /** @var bool */
    private $singleDirectDebit = false;

    public static function create(SEPAAccount $account, string $painMessage): SendSEPADirectDebit
    {
        if (preg_match('/xmlns="(?<namespace>[^"]+)".*<GrpHdr>.*<CtrlSum>(?<ctrlsum>[.0-9]+)<\/CtrlSum>.*<\/GrpHdr>/s', $painMessage, $matches) === 1) {
            $painUrn = $matches['namespace'];
            $ctrlSum = $matches['ctrlsum'];
        } else {
            throw new \InvalidArgumentException('PAIN-Namespace and or <GrpHdr><CtrlSum>xx</CtrlSum></GrpHdr> missing in PAIN message');
        }

        // Check wether it is one DirectDebit or multiple, should match <NbOfTxs>xx</NbOfTxs> in the XML
        $nbOfTxs = substr_count($painMessage, '<DrctDbtTxInf>');

        $result = new SendSEPADirectDebit();
        $result->account = $account;
        $result->painMessage = $painMessage;
        $result->xmlSchema = $painUrn;
        $result->ctrlSum = $ctrlSum;
        $result->singleDirectDebit = $nbOfTxs == 1;
        return $result;
    }

    public function createRequest(BPD $bpd, ?UPD $upd)
    {
        $type = $this->singleDirectDebit ? 'S' : 'M';

        /** @var HIDXES|BaseSegment $hidxes */
        $hidxes = $bpd->requireLatestSupportedParameters('HID' . $type . 'ES');

        $supportedSchemas = null;

        if ($hidxes->getVersion() == 2) {
            /** @var HIDMESv2|HIDSESv2 $hidxes */
            $supportedSchemas = $hidxes->getParameter()->unterstuetzteSEPADatenformate;
        }

        // If there are no SEPA formats available in the HIDXES Parameters, we look to the general formats
        if (!is_array($supportedSchemas) || count($supportedSchemas) == 0) {
            /** @var HISPAS $hispas */
            $hispas = $bpd->requireLatestSupportedParameters('HISPAS');
            $supportedSchemas = $hispas->getParameter()->getUnterstuetzteSepaDatenformate();
        }

        if (!in_array($this->xmlSchema, $supportedSchemas)) {
            throw new UnsupportedException("The bank does not support the XML schema $this->xmlSchema, but only "
                . implode(', ', $supportedSchemas));
        }

        /** @var HKDMEv1|HKDSEv1 $hkdxe */
        $hkdxe = ('Fhp\Segment\D' . $type . 'E\HKD' . $type . 'Ev' . $hidxes->getVersion())::createEmpty();

        $hkdxe->kontoverbindungInternational = Kti::fromAccount($this->account);
        $hkdxe->sepaDescriptor = $this->xmlSchema;
        $hkdxe->sepaPainMessage = new Bin($this->painMessage);

        if (!$this->singleDirectDebit) {
            $hkdxe->einzelbuchungGewuenscht = false;

            /** @var HIDMESv1 $hidxes */
            if ($hidxes->getParameter()->summenfeldBenoetigt) {
                $hkdxe->summenfeld = new Btg($this->ctrlSum);
            }
        }

        return $hkdxe;
    }
}
