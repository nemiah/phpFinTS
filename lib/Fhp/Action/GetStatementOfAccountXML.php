<?php

/** @noinspection PhpUnused */

namespace Fhp\Action;

use Fhp\BaseAction;
use Fhp\Model\SEPAAccount;
use Fhp\Protocol\UnexpectedResponseException;
use Fhp\Segment\CAZ\HICAZSv1;
use Fhp\Segment\CAZ\HICAZv1;
use Fhp\Segment\CAZ\HKCAZv1;
use Fhp\Segment\CAZ\UnterstuetzteCamtMessages;
use Fhp\Segment\Common\Kti;
use Fhp\Segment\HIRMS\Rueckmeldungscode;
use Fhp\UnsupportedException;

/**
 * Retrieves statements for one specific account or for all accounts that the user has access to. A statement is a
 * series of financial transactions that pertain to the account, grouped by day.
 */
class GetStatementOfAccountXML extends BaseAction
{
    // Request (not available after serialization, i.e. not available in processResponse()).
    /** @var SEPAAccount */
    private $account;
    /** @var \DateTime */
    private $from;
    /** @var \DateTime */
    private $to;
    /** @var string */
    private $camtURN;
    /** @var bool */
    private $allAccounts;

    // Response
    /** @var string[] */
    protected $xml = [];

    /**
     * @param SEPAAccount $account The account to get the statement for. This can be constructed based on information
     *     that the user entered, or it can be {@link SEPAAccount} instance retrieved from {@link #getAccounts()}.
     * @param \DateTime|null $from If set, only transactions after this date (inclusive) are returned.
     * @param \DateTime|null $to If set, only transactions before this date (inclusive) are returned.
     * @param string|null $camtURN The URN/descriptor of the CAMT XML format you want the bank to return.
     *     Use null to just let the bank decide. Otherwise needs to be one of the reported URNs the bank supports.
     *     For example urn:iso:std:iso:20022:tech:xsd:camt.052.001.02
     * @param bool $allAccounts If set to true, will return statements for all accounts of the user. You still need to
     *     pass one of the accounts into $account, though.
     * @return GetStatementOfAccountXML A new action instance.
     */
    public static function create(SEPAAccount $account, $from = null, $to = null, ?string $camtURN = null, $allAccounts = false): GetStatementOfAccountXML
    {
        if ($from !== null && $to !== null && $from > $to) {
            throw new \InvalidArgumentException('From-date must be before to-date');
        }

        $result = new GetStatementOfAccountXML();
        $result->account = $account;
        $result->camtURN = $camtURN;
        $result->from = $from;
        $result->to = $to;
        $result->allAccounts = $allAccounts;
        return $result;
    }

    /**
     * @return string[] The XML-Document(s) received from the bank, or empty array if the statement is unavailable/empty.
     * @throws \Exception See {@link #ensureSuccess()}.
     */
    public function getBookedXML(): array
    {
        $this->ensureSuccess();
        return $this->xml;
    }

    /** {@inheritdoc} */
    public function createRequest($bpd, $upd)
    {
        if (!$upd->isRequestSupportedForAccount($this->account, 'HKCAZ')) {
            throw new UnsupportedException('The bank (or the given account/user combination) does not allow this type of request.');
        }

        /** @var HICAZSv1 $hicazs */
        $hicazs = $bpd->requireLatestSupportedParameters('HICAZS');
        $supportedCamtURNs = $hicazs->getParameter()->getUnterstuetzteCamtMessages()->camtDescriptor;
        $camtURNs = [];
        if (is_null($this->camtURN)) {
            $camtURNs = $supportedCamtURNs;
        } elseif (!in_array($this->camtURN, $supportedCamtURNs)) {
            throw new \InvalidArgumentException('The bank does not support the CAMT format' . $this->camtURN . '. The following formats are supported: ' . implode(', ', $supportedCamtURNs));
        } else {
            $camtURNs = [$this->camtURN];
        }

        if ($this->allAccounts && !$hicazs->getParameter()->getAlleKontenErlaubt()) {
            throw new \InvalidArgumentException('The bank do not permit the use of allAccounts=true');
        }
        switch ($hicazs->getVersion()) {
            case 1:
                $unterstuetzteCamtMessages = UnterstuetzteCamtMessages::create($camtURNs);
                return HKCAZv1::create(Kti::fromAccount($this->account), $unterstuetzteCamtMessages, $this->allAccounts, $this->from, $this->to, $this->getPaginationToken());
            default:
                throw new UnsupportedException('Unsupported HKCAZ version: ' . $hicazs->getVersion());
        }
    }

    /** {@inheritdoc} */
    public function processResponse($response)
    {
        parent::processResponse($response);

        // Banks send just 3010 and no HICAZ in case there are no transactions.
        if ($response->findRueckmeldung(Rueckmeldungscode::NICHT_VERFUEGBAR) !== null) {
            return;
        }

        /** @var HICAZv1[] $responseHicaz */
        $responseHicaz = $response->findSegments(HICAZv1::class);
        $numResponseSegments = count($responseHicaz);
        if ($numResponseSegments < count($this->getRequestSegmentNumbers())) {
            throw new UnexpectedResponseException("Only got $numResponseSegments HICAZ response segments!");
        }
        if ($numResponseSegments > 1) {
            throw new UnsupportedException('More than 1 HICAZ response segment is not supported at the moment!');
        }
        // It seems that paginated responses, always contain a whole XML Document
        $this->xml[] = $responseHicaz[0]->getGebuchteUmsaetze()->getData();
    }
}
