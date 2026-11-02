<?php

declare(strict_types=1);

namespace Fhp\Action;

use Fhp\BaseAction;
use Fhp\Model\SEPAAccount;
use Fhp\Protocol\BPD;
use Fhp\Protocol\Message;
use Fhp\Protocol\UPD;
use Fhp\Segment\BaseSegment;
use Fhp\Segment\Common\Kti;
use Fhp\Segment\IPS\HIIPSSv1;
use Fhp\Segment\IPS\HIIPSv1;
use Fhp\Segment\IPS\HKIPSv1;
use Fhp\Segment\IPS\UnterstuetzteSEPAPainMessages;
use Fhp\Segment\SPA\HISPAS;

/**
 * Requests the status of a previously submitted SEPA instant payment (HKIPS).
 *
 * When the bank answers an instant payment ({@link SendSEPARealtimeTransfer}) with code 3045, the payee's bank has not
 * confirmed the payment yet. This action asks for the final status. The bank may answer with 3045 again, in which case
 * the status is still pending and the action can be executed again later. The bank announces how long a client should
 * wait before asking, see {@link getMinimumWaitingTime()}.
 */
class GetSEPARealtimeTransferStatus extends BaseAction
{
    /** Status SEPA-Auftrag: Auftrag ausgeführt; Geld für den Zahlungsempfänger verfügbar. */
    public const STATUS_EXECUTED = 7;
    /** Status SEPA-Auftrag codes that mean the payment was rejected or failed. */
    public const STATUS_FAILED = [2, 6, 8, 9];

    // Request (if you add a field here, update __serialize() and __unserialize() as well).
    private SEPAAccount $account;
    private string $orderId;
    private ?string $xmlSchema;

    // Response
    private bool $statusPending = false;
    private ?int $status = null;
    private ?int $sepaCCode = null;
    private ?string $painMessage = null;

    /**
     * @param SEPAAccount $account The account from which the instant payment was sent.
     * @param string $orderId The Auftragsidentifikation that the bank returned for the instant payment, see
     *     {@link SendSEPARealtimeTransfer::getOrderId()}.
     * @param string|null $xmlSchema The PAIN schema (XML namespace) of the original payment. If omitted, all formats
     *     announced by the bank are listed as supported.
     */
    public static function create(SEPAAccount $account, string $orderId, ?string $xmlSchema = null): GetSEPARealtimeTransferStatus
    {
        $result = new GetSEPARealtimeTransferStatus();
        $result->account = $account;
        $result->orderId = $orderId;
        $result->xmlSchema = $xmlSchema;
        return $result;
    }

    /**
     * @return int|null The minimum number of seconds the bank wants a client to wait before requesting the status,
     *     or null if the bank does not support the status request at all.
     */
    public static function getMinimumWaitingTime(BPD $bpd): ?int
    {
        $hiipss = $bpd->getLatestSupportedParameters('HIIPSS');
        return $hiipss instanceof HIIPSSv1 ? $hiipss->parameter->mindestwartezeit : null;
    }

    /** @return mixed[] */
    public function __serialize(): array
    {
        return [
            parent::__serialize(),
            $this->account, $this->orderId, $this->xmlSchema,
        ];
    }

    /** @param mixed[] $serialized */
    public function __unserialize(array $serialized): void
    {
        list(
            $parentSerialized,
            $this->account, $this->orderId, $this->xmlSchema,
        ) = $serialized;

        is_array($parentSerialized) ?
            parent::__unserialize($parentSerialized) :
            parent::unserialize($parentSerialized);
    }

    protected function createRequest(BPD $bpd, ?UPD $upd)
    {
        /** @var BaseSegment&HISPAS $hispas */
        $hispas = $bpd->requireLatestSupportedParameters('HISPAS');
        /** @var HIIPSSv1 $hiipss */
        $hiipss = $bpd->requireLatestSupportedParameters('HIIPSS');

        if ($this->xmlSchema !== null) {
            $schemas = [$this->xmlSchema];
        } else {
            $schemas = $hiipss->parameter->getUnterstuetzteSEPADatenformate();
            if (count($schemas) === 0) {
                $schemas = array_filter($hispas->getParameter()->getUnterstuetzteSEPADatenformate(), function ($schema) {
                    return str_contains($schema, 'pain.001.');
                });
            }
            $schemas = array_unique($schemas);
        }

        /** @var HKIPSv1 $hkips */
        $hkips = $hiipss->createRequestSegment();
        $hkips->kontoverbindungInternational = Kti::fromAccount(
            $this->account,
            $hispas->getParameter()->getNationaleKontoverbindungErlaubt()
        );
        $hkips->unterstuetzteSEPAPainMessages = UnterstuetzteSEPAPainMessages::create($schemas);
        $hkips->auftragsidentifikation = $this->orderId;
        return $hkips;
    }

    public function processResponse(Message $response): void
    {
        parent::processResponse($response);

        // 3045: The bank has no final status yet, the request can be repeated later.
        $this->statusPending = count($response->findRueckmeldungen(SendSEPARealtimeTransfer::CODE_STATUS_PENDING)) > 0;

        /** @var HIIPSv1|null $hiips */
        $hiips = $response->findSegment(HIIPSv1::class);
        if ($hiips !== null) {
            $this->status = $hiips->statusSepaAuftrag;
            $this->sepaCCode = $hiips->sepaCCode;
            $this->painMessage = $hiips->sepaPainMessage?->getData();
        }
    }

    /** @return bool True if the bank still has no final status (code 3045); the request may be repeated later. */
    public function isStatusPending(): bool
    {
        $this->ensureDone();
        return $this->statusPending;
    }

    /** @return int|null The Status SEPA-Auftrag (1-9) as reported by the bank, see {@link \Fhp\Segment\IPZ\HIIPZv1}. */
    public function getStatus(): ?int
    {
        $this->ensureDone();
        return $this->status;
    }

    /** @return int|null The SEPA C-Code, if the payment was deleted (3) or recalled (4). */
    public function getSepaCCode(): ?int
    {
        $this->ensureDone();
        return $this->sepaCCode;
    }

    /** @return string|null The PAIN message of the payment, if the bank returned it. */
    public function getPainMessage(): ?string
    {
        $this->ensureDone();
        return $this->painMessage;
    }

    /** @return bool True if the bank reports the money as available to the payee. */
    public function isExecuted(): bool
    {
        return $this->getStatus() === self::STATUS_EXECUTED;
    }

    /** @return bool True if the bank reports the payment as rejected or failed. */
    public function isFailed(): bool
    {
        return in_array($this->getStatus(), self::STATUS_FAILED, true);
    }
}
