<?php

namespace Fhp\Action;

use Fhp\BaseAction;
use Fhp\DataTypes\Bin;
use Fhp\Model\SEPAAccount;
use Fhp\Protocol\BPD;
use Fhp\Protocol\Message;
use Fhp\Protocol\UnexpectedResponseException;
use Fhp\Protocol\UPD;
use Fhp\Segment\CCS\HKCCSv1;
use Fhp\Segment\Common\Kti;
use Fhp\Segment\HIRMS\Rueckmeldungscode;
use Fhp\Segment\SPA\HISPAS;
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

    /** {@inheritdoc} */
    public function createRequest(BPD $bpd, ?UPD $upd)
    {
        if (!$bpd->supportsParameters('HICCSS', 1)) {
            throw new UnsupportedException('The bank does not support HKCCSv1');
        }

        /** @var HISPAS $hispas */
        $hispas = $bpd->requireLatestSupportedParameters('HISPAS');
        $supportedSchemas = $hispas->getParameter()->getUnterstuetzteSepaDatenformate();
        if (!in_array($this->xmlSchema, $supportedSchemas)) {
            throw new UnsupportedException("The bank does not support the XML schema $this->xmlSchema, but only "
                . implode(', ', $supportedSchemas));
        }

        $hkccs = HKCCSv1::createEmpty();
        $hkccs->kontoverbindungInternational = Kti::fromAccount($this->account);
        $hkccs->sepaDescriptor = $this->xmlSchema;
        $hkccs->sepaPainMessage = new Bin($this->painMessage);
        return $hkccs;
    }

    /** {@inheritdoc} */
    public function processResponse(Message $response)
    {
        parent::processResponse($response);
        if ($response->findRueckmeldung(Rueckmeldungscode::ENTGEGENGENOMMEN) === null) {
            throw new UnexpectedResponseException('Bank did not confirm SEPATransfer execution');
        }
    }
}
