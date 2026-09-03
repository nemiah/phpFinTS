<?php

/** @noinspection PhpUnused */

namespace Fhp\Action;

use Fhp\CAMT\CAMT;
use Fhp\Model\SEPAAccount;
use Fhp\Model\StatementOfAccount\StatementOfAccount;
use Fhp\Protocol\BPD;
use Fhp\Protocol\Message;
use Fhp\Protocol\UnexpectedResponseException;
use Fhp\Protocol\UPD;
use Fhp\Segment\CAZ\HICAZSv1;
use Fhp\Segment\CAZ\HICAZv1;
use Fhp\Segment\CAZ\HKCAZv1;
use Fhp\Segment\CAZ\UnterstuetzteCamtMessages;
use Fhp\Segment\Common\Kti;
use Fhp\Segment\HIRMS\Rueckmeldungscode;
use Fhp\Segment\SPA\HISPAS;
use Fhp\UnsupportedException;

/**
 * Retrieves statements in the CAMT XML format (HKCAZ), which supersedes the MT 940 format (see
 * {@link GetStatementOfAccountMT940}). Use this action directly if your application needs the raw XML documents,
 * otherwise you probably want {@link GetStatementOfAccount}, which picks whichever format the bank supports.
 */
class GetStatementOfAccountXML extends AbstractGetStatementOfAccount
{
    // Request (if you add a field here, update __serialize() and __unserialize() as well).
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
    /** @var bool */
    private $includeUnbooked;

    // Response
    /** @var string[] */
    protected $xml = [];

    /** @var string[] */
    protected $unbookedXml = [];

    /**
     * @param SEPAAccount $account The account to get the statement for. This can be constructed based on information
     *     that the user entered, or it can be {@link SEPAAccount} instance retrieved from {@link getAccounts()}.
     * @param \DateTime|null $from If set, only transactions after this date (inclusive) are returned.
     * @param \DateTime|null $to If set, only transactions before this date (inclusive) are returned.
     * @param string|null $camtURN The URN/descriptor of the CAMT XML format you want the bank to return.
     *     Use null to just let the bank decide. Otherwise needs to be one of the reported URNs the bank supports.
     *     For example urn:iso:std:iso:20022:tech:xsd:camt.052.001.02
     * @param bool $allAccounts If set to true, will return statements for all accounts of the user. You still need to
     *     pass one of the accounts into $account, though.
     * @param bool $includeUnbooked If set to true, transactions that the bank has received but not booked yet are
     *     included in {@link getStatement()} and {@link getRawResponse()}. Note that the bank decides whether to send
     *     them at all: they are always absent for a time range that lies in the past, and {@link getUnbookedXML()}
     *     exposes them regardless of this flag if the bank did send them.
     * @return GetStatementOfAccountXML A new action instance.
     */
    public static function create(SEPAAccount $account, ?\DateTime $from = null, ?\DateTime $to = null, ?string $camtURN = null, bool $allAccounts = false, bool $includeUnbooked = false): GetStatementOfAccountXML
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
        $result->includeUnbooked = $includeUnbooked;
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
            $this->account, $this->camtURN, $this->from, $this->to, $this->allAccounts,
            $this->includeUnbooked,
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
            $this->account, $this->camtURN, $this->from, $this->to, $this->allAccounts,
            $this->includeUnbooked,
        ) = $serialized;

        is_array($parentSerialized) ?
            parent::__unserialize($parentSerialized) :
            parent::unserialize($parentSerialized);
    }

    public function getRawResponse(): array
    {
        return $this->includeUnbooked
            ? array_merge($this->getBookedXML(), $this->getUnbookedXML())
            : $this->getBookedXML();
    }

    /**
     * @return string[] The XML-Document(s) received from the bank, or empty array if the statement is unavailable/empty.
     */
    public function getBookedXML(): array
    {
        $this->ensureDone();
        return $this->xml;
    }

    /**
     * @return string[] The XML-Document that contains the transactions which the bank has received but not booked yet,
     *     or an empty array if the bank did not send any. This is independent of the $includeUnbooked flag, which only
     *     determines whether these transactions are part of {@link getStatement()} and {@link getRawResponse()}.
     * @noinspection PhpUnused
     */
    public function getUnbookedXML(): array
    {
        $this->ensureDone();
        return $this->unbookedXml;
    }

    /**
     * @return StatementOfAccount The transactions from the CAMT XML document(s), for applications that don't want to
     *     parse the XML themselves. Use {@link getBookedXML()} to access the raw documents. Note that this conversion
     *     is lossy, see {@link CAMT}.
     */
    public function getStatement(): StatementOfAccount
    {
        $xmlStrings = $this->getRawResponse();
        if (empty($xmlStrings)) {
            // No transactions available
            return new StatementOfAccount();
        }

        try {
            $parser = new CAMT();
            $parsedCAMT = $parser->parse($xmlStrings);
            return StatementOfAccount::fromCAMTArray($parsedCAMT);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid CAMT XML data', 0, $e);
        }
    }

    protected function createRequest(BPD $bpd, ?UPD $upd)
    {
        if ($upd === null) {
            throw new UnsupportedException('The UPD is needed to be able to create a request for GetStatementOfAccountXML.');
        }

        if (!$upd->isRequestSupportedForAccount($this->account, 'HKCAZ')) {
            throw new UnsupportedException('The bank (or the given account/user combination) does not support GetStatementOfAccountXML.');
        }

        /** @var HICAZSv1 $hicazs */
        $hicazs = $bpd->requireLatestSupportedParameters('HICAZS');
        $supportedCamtURNs = $hicazs->getParameter()->getUnterstuetzteCamtMessages()->camtDescriptor;
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

        /** @var HISPAS $hispas */
        $hispas = $bpd->requireLatestSupportedParameters('HISPAS');
        $kti = Kti::fromAccount($this->account, $hispas->getParameter()->getNationaleKontoverbindungErlaubt());

        switch ($hicazs->getVersion()) {
            case 1:
                $unterstuetzteCamtMessages = UnterstuetzteCamtMessages::create($camtURNs);
                return HKCAZv1::create($kti, $unterstuetzteCamtMessages, $this->allAccounts, $this->from, $this->to);
            default:
                throw new UnsupportedException('Unsupported HKCAZ version: ' . $hicazs->getVersion());
        }
    }

    public function processResponse(Message $response)
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
        foreach ($responseHicaz[0]->getGebuchteUmsaetze() as $xml_string) {
            $this->xml[] = $xml_string;
        }
        // Banks only send this in case the requested time range reaches into the present.
        $nichtGebuchteUmsaetze = $responseHicaz[0]->getNichtGebuchteUmsaetze();
        if ($nichtGebuchteUmsaetze !== null) {
            $this->unbookedXml[] = $nichtGebuchteUmsaetze;
        }
    }
}
