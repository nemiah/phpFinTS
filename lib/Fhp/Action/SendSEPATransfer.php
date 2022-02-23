<?php

namespace Fhp\Action;

use Fhp\BaseAction;
use Fhp\Model\SEPAAccount;
use Fhp\Protocol\BPD;
use Fhp\Protocol\Message;
use Fhp\Protocol\UnexpectedResponseException;
use Fhp\Protocol\UPD;
use Fhp\Segment\CCS\HKCCSv1;
use Fhp\Segment\Common\Kti;
use Fhp\Segment\HIRMS\Rueckmeldungscode;
use Fhp\Segment\SPA\HISPAS;
use Fhp\Syntax\Bin;
use Fhp\UnsupportedException;

/**
 * Initiates an outgoing wire transfer in SEPA format (PAIN XML).
 */
class SendSEPATransfer extends BaseAction
{
    /** @var SEPAAccount */
    private $account;
    /** @var string */
    private $painMessage;
    /** @var string */
    private $xmlSchema;
	/** @var float */
	protected $ctrlSum;
	/** @var bool */
	protected $singleTransfer = false;
	/** @var bool */
	protected $requestSingleBooking = false;

    /**
     * @param SEPAAccount $account The account from which the transfer will be sent.
     * @param string $painMessage An XML-formatted ISO 20022 message. You may want to use github.com/nemiah/phpSepaXml
     *     to create this.
     * @return SendSEPATransfer A new action for executing this the given PAIN message.
     */
    public static function create(SEPAAccount $account, string $painMessage): SendSEPATransfer
    {
        if (preg_match('/xmlns="(.*?)"/', $painMessage, $xmlns) === false) {
            throw new \InvalidArgumentException('xmlns not found in the PAIN message');
        }

        // Check whether the PAIN message contains the correct message root
        if (strstr($painMessage, '<CstmrCdtTrfInitn>') === false) {
            throw new \InvalidArgumentException('Pain message contains a wrong message root');
        }

        // Check whether the PAIN message contains multiple or only one transfer, should match <NbOfTxs>xx</NbOfTxs> in the XML
        $nbOfTxs = substr_count($painMessage, '<CdtTrfTxInf>');
        if ($nbOfTxs === 0) {
            throw new \InvalidArgumentException('PAIN message contains no credit transfer transactions');
        }

        $ctrlSum = null;
        if (preg_match('@<GrpHdr>.*<CtrlSum>(?<ctrlsum>[.0-9]+)</CtrlSum>.*</GrpHdr>@s', $painMessage, $matches) === 1) {
            $ctrlSum = $matches['ctrlsum'];
        }

        // Check whether a <PmtInf> block sets <BtchBookg> to false: This means that Single Booking is to be requested
        $singleBooking = preg_match('@<PmtInf>.*<BtchBookg>false</BtchBookg>.*</PmtInf>@', $painMessage) === 1;

        $result = new SendSEPATransfer();
        $result->account = $account;
        $result->painMessage = $painMessage;
        $result->xmlSchema = $xmlns[1];
        $result->ctrlSum = $ctrlSum;
        $result->singleTransfer = $nbOfTxs === 1;
        $result->requestSingleBooking = $singleBooking;
        return $result;
    }

    /** {@inheritdoc} */
    protected function createRequest(BPD $bpd, ?UPD $upd)
    {
        /** @var string $segment */
        $segment = $this->singleTransfer ? 'HKCCS' : 'HKCCM';
        /** @var string $bankparams */
        $bankparams = $this->singleTransfer ? 'HICCSS' : 'HICCMS';

        if (!$bpd->supportsParameters($bankparams, 1)) {
            throw new UnsupportedException('The bank does not support ' . $segment . 'v1');
        }

        /** @var HISPAS $hispas */
        $hispas = $bpd->requireLatestSupportedParameters('HISPAS');
        $supportedSchemas = $hispas->getParameter()->getUnterstuetzteSepaDatenformate();
        if (!in_array($this->xmlSchema, $supportedSchemas)) {
            throw new UnsupportedException("The bank does not support the XML schema $this->xmlSchema, but only "
                . implode(', ', $supportedSchemas));
        }

       	/** @var BaseSegment $hiccxs */
      	$hiccxs = $bpd->requireLatestSupportedParameters($bankparams);

      	$hkccx = $hiccxs->createRequestSegment();
        $hkccx->kontoverbindungInternational = Kti::fromAccount($this->account);
        $hkccx->sepaDescriptor = $this->xmlSchema;
        $hkccx->sepaPainMessage = new Bin($this->painMessage);

        if (!$this->singleTransfer) {
            // Just always send the control sum (may be optional)
            $hkccx->summenfeld = Btg::create($this->ctrlSum);

            // Request Single booking only if bank allows
            /** @var HICCMSv1 $hiccxs */
            if ($hiccxs->getParameter()->einzelbuchungErlaubt) {
                $hkccx->einzelbuchungGewuenscht = $this->requestSingleBooking;
            }
        }

        return $hkccx;
    }

    /** {@inheritdoc} */
    public function processResponse(Message $response)
    {
        parent::processResponse($response);
        if ($response->findRueckmeldung(Rueckmeldungscode::ENTGEGENGENOMMEN) === null && $response->findRueckmeldung(Rueckmeldungscode::AUSGEFUEHRT) === null) {
            throw new UnexpectedResponseException('Bank did not confirm SEPATransfer execution');
        }
    }
}
