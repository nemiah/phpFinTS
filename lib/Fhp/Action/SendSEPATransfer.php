<?php

namespace Fhp\Action;

use Fhp\BaseAction;
use Fhp\Model\SEPAAccount;
use Fhp\Protocol\BPD;
use Fhp\Protocol\Message;
use Fhp\Protocol\UnexpectedResponseException;
use Fhp\Protocol\UPD;
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
    // Request (if you add a field here, update __serialize() and __unserialize() as well).
    /** @var SEPAAccount */
    private $account;
    /** @var string */
    private $painMessage;
    /** @var string */
    private $xmlSchema;

    // There are no result fields. This action is simply marked as done to indicate that the transfer was executed.

    /**
     * @param SEPAAccount $account The account from which the transfer will be sent.
     * @param string $painMessage An XML-formatted ISO 20022 message. You may want to use github.com/nemiah/phpSepaXml
     *     to create this.
     * @return SendSEPATransfer A new action for executing this the given PAIN message.
     */
    public static function create(SEPAAccount $account, string $painMessage): SendSEPATransfer
    {
        if (preg_match('/xmlns="(.*?)"/', $painMessage, $match) === false) {
            throw new \InvalidArgumentException('xmlns not found in the PAIN message');
        }
        $result = new SendSEPATransfer();
        $result->account = $account;
        $result->painMessage = $painMessage;
        $result->xmlSchema = $match[1];
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
            $this->account, $this->painMessage, $this->xmlSchema,
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
            $this->account, $this->painMessage, $this->xmlSchema,
        ) = $serialized;

        is_array($parentSerialized) ?
            parent::__unserialize($parentSerialized) :
            parent::unserialize($parentSerialized);
    }

    protected function createRequest(BPD $bpd, ?UPD $upd)
    {
        // ANALYSE XML FOR RECEIPTS AND PAYMENT DATE
        $xmlAsObject = simplexml_load_string($this->painMessage, 'SimpleXMLElement', LIBXML_NOCDATA);
        $numberOfTransactions = $xmlAsObject->CstmrCdtTrfInitn->GrpHdr->NbOfTxs;
        $hasReqdExDates = false;
        foreach ($xmlAsObject->CstmrCdtTrfInitn?->PmtInf as $pmtInfo) {
            // Checks for both, <ReqdExctnDt>1999-01-01</ReqdExctnDt> and <ReqdExctnDt><Dt>1999-01-01</Dt></ReqdExctnDt>
            if (isset($pmtInfo->ReqdExctnDt) && ($pmtInfo->ReqdExctnDt->Dt ?? $pmtInfo->ReqdExctnDt) != '1999-01-01') {
                $hasReqdExDates = true;
                break;
            }
        }

        // NOW READ OUT, WICH SEGMENT SHOULD BE USED:
        if ($numberOfTransactions > 1 && $hasReqdExDates) {
            // Terminierte SEPA-Sammelüberweisung (Segment HKCME / Kennung HICMES)
            $segmentID = 'HICMES';
            $segment = \Fhp\Segment\CME\HKCMEv1::createEmpty();
        } elseif ($numberOfTransactions == 1 && $hasReqdExDates) {
            // Terminierte SEPA-Überweisung (Segment HKCSE / Kennung HICSES)
            $segmentID = 'HICSES';
            $segment = \Fhp\Segment\CSE\HKCSEv1::createEmpty();
        } elseif ($numberOfTransactions > 1 && !$hasReqdExDates) {
            // SEPA-Sammelüberweisungen (Segment HKCCM / Kennung HICSES)
            $segmentID = 'HICSES';
            $segment = \Fhp\Segment\CCM\HKCCMv1::createEmpty();
        } else {
            // SEPA Einzelüberweisung (Segment HKCCS / Kennung HICCSS).
            $segmentID = 'HICCSS';
            $segment = \Fhp\Segment\CCS\HKCCSv1::createEmpty();
        }

        if (!$bpd->supportsParameters($segmentID, 1)) {
            throw new UnsupportedException('The bank does not support ' . $segmentID . 'v1');
        }

        /** @var HISPAS $hispas */
        $hispas = $bpd->requireLatestSupportedParameters('HISPAS');
        $supportedSchemas = $hispas->getParameter()->getUnterstuetzteSEPADatenformate();

        // Sometimes the Bank reports supported schemas with a "_GBIC_X" postfix.
        // GIBC_X stands for German Banking Industry Committee and a version counter.
        $xmlSchema = $this->xmlSchema;
        $matchingSchemas = array_filter($supportedSchemas, function ($value) use ($xmlSchema) {
            // For example urn:iso:std:iso:20022:tech:xsd:pain.001.001.09 from the xml matches
            // urn:iso:std:iso:20022:tech:xsd:pain.001.001.09_GBIC_4
            return str_starts_with($value, $xmlSchema);
        });

        if (count($matchingSchemas) === 0) {
            throw new UnsupportedException("The bank does not support the XML schema $this->xmlSchema, but only "
                . implode(', ', $supportedSchemas));
        }

        $segment->kontoverbindungInternational = Kti::fromAccount($this->account);
        $segment->sepaDescriptor = $this->xmlSchema;
        $segment->sepaPainMessage = new Bin($this->painMessage);
        return $segment;
    }

    public function processResponse(Message $response)
    {
        parent::processResponse($response);
        if ($response->findRueckmeldung(Rueckmeldungscode::ENTGEGENGENOMMEN) === null && $response->findRueckmeldung(Rueckmeldungscode::AUSGEFUEHRT) === null) {
            throw new UnexpectedResponseException('Bank did not confirm SEPATransfer execution');
        }
    }
}
