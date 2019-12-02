<?php

namespace Fhp\Action;

use Fhp\BaseAction;
use Fhp\Model\SEPAAccount;
use Fhp\Model\StatementOfAccount\StatementOfAccount;
use Fhp\MT940\Dialect\PostbankMT940;
use Fhp\MT940\Dialect\SpardaMT940;
use Fhp\MT940\MT940;
use Fhp\MT940\MT940Exception;
use Fhp\Protocol\UnexpectedResponseException;
use Fhp\Segment\CAZ\HICAZSv1;
use Fhp\Segment\CAZ\HICAZv1;
use Fhp\Segment\CAZ\HKCAZv1;
use Fhp\Segment\CAZ\UnterstuetzteCamtMessages;
use Fhp\Segment\Common\Kti;
use Fhp\Segment\Common\Kto;
use Fhp\Segment\Common\KtvV3;
use Fhp\Segment\HIRMS\HIRMSv2;
use Fhp\Segment\HIRMS\Rueckmeldungscode;
use Fhp\Segment\KAZ\HIKAZ;
use Fhp\Segment\KAZ\HIKAZS;
use Fhp\Segment\KAZ\HKKAZv4;
use Fhp\Segment\KAZ\HKKAZv5;
use Fhp\Segment\KAZ\HKKAZv6;
use Fhp\Segment\KAZ\HKKAZv7;
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
    /** @var string */
    private $xml;

    /**
     * @param SEPAAccount $account The account to get the statement for. This can be constructed based on information
     *     that the user entered, or it can be {@link SEPAAccount} instance retrieved from {@link #getAccounts()}.
     * @param string $camtURN The URN/descriptor of the CAMT XML format you want the bank to return. Needs to be one of the reported URNs the bank supports. For example urn:iso:std:iso:20022:tech:xsd:camt.052.001.02
     * @param \DateTime|null $from If set, only transactions after this date (inclusive) are returned.
     * @param \DateTime|null $to If set, only transactions before this date (inclusive) are returned.
     * @param bool $allAccounts If set to true, will return statements for all accounts of the user. You still need to
     *     pass one of the accounts into $account, though.
     * @return GetStatementOfAccountXML A new action instance.
     */
    public static function create(SEPAAccount $account, string $camtURN, $from = null, $to = null, $allAccounts = false): GetStatementOfAccountXML
    {
        if (isset($from) && isset($to) && $from > $to) {
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
     * @return string
     * @throws \Exception See {@link #ensureSuccess()}.
     */
    public function getBookedXML()
    {
        $this->ensureSuccess();
        return $this->xml;
    }

    /** {@inheritdoc} */
    public function createRequest($bpd, $upd)
    {
        /** @var HICAZSv1 $hicazs */
        $hicazs = $bpd->requireLatestSupportedParameters('HICAZS');
        $camtURNs = $hicazs->getParameter()->getUnterstuetzteCamtMessages()->camtDescriptor;
        if (!in_array($this->camtURN, $camtURNs)) {
            throw new \InvalidArgumentException('The bank does not support the CAMT format' . $this->camtURN . '. The following formats are supported: ' . implode(', ', $camtURNs));
        }
        if ($this->allAccounts && !$hicazs->getParameter()->getAlleKontenErlaubt()) {
            throw new \InvalidArgumentException('The bank do not permit the use of allAccounts=true');
        }
        switch ($hicazs->getVersion()) {
            case 1:
                $unterstuetzteCamtMessages = new UnterstuetzteCamtMessages();
                $unterstuetzteCamtMessages->camtDescriptor = [$this->camtURN];
                return [HKCAZv1::create(Kti::fromAccount($this->account), $unterstuetzteCamtMessages, $this->allAccounts, $this->from, $this->to)];
            default:
                throw new UnsupportedException('Unsupported HKCAZ version: ' . $hicazs->getVersion());
        }
    }

    /** {@inheritdoc} */
    public function processResponse($response, $bpd, $upd)
    {
        parent::processResponse($response, $bpd, $upd);

        // Banks send just 3010 and no HIKAZ in case there are no transactions.
        $isUnavailable = false;
        $responseHirms = $response->findSegments(HIRMSv2::class);
        /** @var HIRMSv2 $hirms */
        foreach ($responseHirms as $hirms) {
            if ($hirms->findRueckmeldung(Rueckmeldungscode::UNAVAILABLE) !== null) {
                $isUnavailable = true;
            }
        }

        if ($isUnavailable) {
            return;
        }

        /** @var HICAZv1[] $responseHicaz */
        $responseHicaz = $response->findSegments(HICAZv1::class);

        #dump($responseHicaz);
        $numResponseSegments = count($responseHicaz);
        if (!$isUnavailable && $numResponseSegments < count($this->getRequestSegmentNumbers())) {
            throw new UnexpectedResponseException("Only got $numResponseSegments HIKAZ response segments!");
        }

        if ($numResponseSegments > 1) {
            throw new UnexpectedResponseException("More than 1 HIKAZ response segment is not supported!");
        }
        $this->xml = $responseHicaz[0]->getGebuchteUmsaetze()->getData();

        // TODO Implement pagination somewhere, not necessarily here.
    }
}
