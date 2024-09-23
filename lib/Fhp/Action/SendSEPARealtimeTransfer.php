<?php

namespace Fhp\Action;

use Fhp\BaseAction;
use Fhp\Model\SEPAAccount;
use Fhp\Protocol\BPD;
use Fhp\Protocol\Message;
use Fhp\Protocol\UnexpectedResponseException;
use Fhp\Protocol\UPD;
use Fhp\Segment\Common\Kti;
use Fhp\Segment\HIRMS\Rueckmeldung;
use Fhp\Segment\HIRMS\Rueckmeldungscode;
use Fhp\Segment\IPZ\HIIPZSv1;
use Fhp\Segment\IPZ\HIIPZSv2;
use Fhp\Segment\IPZ\HKIPZv2;
use Fhp\Segment\SPA\HISPAS;
use Fhp\Syntax\Bin;
use Fhp\UnsupportedException;

/**
 * Initiates an outgoing realtime transfer in SEPA format (PAIN XML).
 */
class SendSEPARealtimeTransfer extends BaseAction
{
    /** @var SEPAAccount */
    private $account;
    /** @var string */
    private $painMessage;
    /** @var string */
    private $xmlSchema;

    private bool $allowConversionToSEPATransfer = true;

    /**
     * @param SEPAAccount $account The account from which the transfer will be sent.
     * @param string $painMessage An XML-formatted ISO 20022 message. You may want to use github.com/nemiah/phpSepaXml
     *     to create this.
     * @param bool $allowConversionToSEPATransfer If instant payment ist not possible, allow the bank to send as a regular transfer instead
     * @return SendSEPARealtimeTransfer A new action for executing this the given PAIN message.
     */
    public static function create(SEPAAccount $account, string $painMessage, bool $allowConversionToSEPATransfer = true): SendSEPARealtimeTransfer
    {
        if (preg_match('/xmlns="(.*?)"/', $painMessage, $match) === false) {
            throw new \InvalidArgumentException('xmlns not found in the PAIN message');
        }
        $result = new SendSEPARealtimeTransfer();
        $result->account = $account;
        $result->painMessage = $painMessage;
        $result->xmlSchema = $match[1];
        $result->allowConversionToSEPATransfer = $allowConversionToSEPATransfer;
        return $result;
    }

    /** {@inheritdoc} */
    protected function createRequest(BPD $bpd, ?UPD $upd)
    {
        /** @var HIIPZSv1|HIIPZSv2 $hiipzs */
        $hiipzs = $bpd->requireLatestSupportedParameters('HIIPZS');

        /** @var HISPAS $hispas */
        $hispas = $bpd->requireLatestSupportedParameters('HISPAS');
        $supportedSchemas = $hispas->getParameter()->getUnterstuetzteSepaDatenformate();
        if (!in_array($this->xmlSchema, $supportedSchemas)) {
            throw new UnsupportedException("The bank does not support the XML schema $this->xmlSchema, but only "
                . implode(', ', $supportedSchemas));
        }

        /** @var HKIPZv1|HKIPZv2 $hkipz */
        $hkipz = $hiipzs->createRequestSegment();
        $hkipz->kontoverbindungInternational = Kti::fromAccount($this->account);
        $hkipz->sepaDescriptor = $this->xmlSchema;
        $hkipz->sepaPainMessage = new Bin($this->painMessage);
        if ($hiipzs instanceof HIIPZSv2) {
            $hkipz->umwandlungNachSEPAUeberweisungZulaessig = $hiipzs->parameter->umwandlungNachSEPAUeberweisungZulaessigErlaubt && $this->allowConversionToSEPATransfer;
        }
        return $hkipz;
    }

    /** {@inheritdoc} */
    public function processResponse(Message $response)
    {
        parent::processResponse($response);

        // Was the instant payment converted to a regular transfer?
        $info = $response->findRueckmeldungen(3270);
        if (count($info) > 0) {
            $this->successMessage = implode("\n", array_map(function (Rueckmeldung $rueckmeldung) {
                return $rueckmeldung->rueckmeldungstext;
            }, $info));
            return;
        }

        if ($response->findRueckmeldung(Rueckmeldungscode::ENTGEGENGENOMMEN) === null &&
            $response->findRueckmeldung(Rueckmeldungscode::AUSGEFUEHRT) === null) {
            throw new UnexpectedResponseException('Bank did not confirm SEPATransfer execution');
        }
    }
}
