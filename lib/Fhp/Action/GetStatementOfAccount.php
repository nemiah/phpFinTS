<?php

namespace Fhp\Action;

use Fhp\Model\SEPAAccount;
use Fhp\Model\StatementOfAccount\StatementOfAccount;
use Fhp\MT940\Dialect\ComdirectMT940;
use Fhp\MT940\Dialect\PostbankMT940;
use Fhp\MT940\Dialect\SpardaMT940;
use Fhp\MT940\MT940;
use Fhp\MT940\MT940Exception;
use Fhp\PaginateableAction;
use Fhp\Protocol\BPD;
use Fhp\Protocol\Message;
use Fhp\Protocol\UnexpectedResponseException;
use Fhp\Protocol\UPD;
use Fhp\Segment\Common\Kti;
use Fhp\Segment\Common\Kto;
use Fhp\Segment\Common\KtvV3;
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
class GetStatementOfAccount extends PaginateableAction
{
    // Request (not available after serialization, i.e. not available in processResponse()).
    /** @var SEPAAccount */
    private $account;
    /** @var \DateTime */
    private $from;
    /** @var \DateTime */
    private $to;
    /** @var bool */
    private $allAccounts;

    // Information from the BPD needed to interpret the response.
    /** @var string */
    private $bankName;

    // Response
    /** @var string */
    private $rawMT940 = '';

    /** @var array */
    protected $parsedMT940 = [];

    /** @var StatementOfAccount */
    private $statement;

    /**
     * @param SEPAAccount $account The account to get the statement for. This can be constructed based on information
     *     that the user entered, or it can be {@link SEPAAccount} instance retrieved from {@link getAccounts()}.
     * @param \DateTime|null $from If set, only transactions after this date (inclusive) are returned.
     * @param \DateTime|null $to If set, only transactions before this date (inclusive) are returned.
     * @param bool $allAccounts If set to true, will return statements for all accounts of the user. You still need to
     *     pass one of the accounts into $account, though.
     * @return GetStatementOfAccount A new action instance.
     */
    public static function create(SEPAAccount $account, ?\DateTime $from = null, ?\DateTime $to = null, bool $allAccounts = false): GetStatementOfAccount
    {
        if ($from !== null && $to !== null && $from > $to) {
            throw new \InvalidArgumentException('From-date must be before to-date');
        }

        $result = new GetStatementOfAccount();
        $result->account = $account;
        $result->from = $from;
        $result->to = $to;
        $result->allAccounts = $allAccounts;
        return $result;
    }

    public function serialize(): string
    {
        return serialize([
            parent::serialize(),
            $this->account, $this->from, $this->to, $this->allAccounts,
            $this->bankName,
        ]);
    }

    public function unserialize($serialized)
    {
        list(
            $parentSerialized,
            $this->account, $this->from, $this->to, $this->allAccounts,
            $this->bankName
            ) = unserialize($serialized);
        parent::unserialize($parentSerialized);
    }

    /**
     * @return string The raw MT940 data received from the server.
     * @noinspection PhpUnused
     */
    public function getRawMT940(): string
    {
        $this->ensureDone();
        return $this->rawMT940;
    }

    /**
     * @return array The parsed MT940 data.
     */
    public function getParsedMT940(): array
    {
        $this->ensureDone();
        return $this->parsedMT940;
    }

    /**
     * @return StatementOfAccount
     */
    public function getStatement()
    {
        $this->ensureDone();
        return $this->statement;
    }

    /** {@inheritdoc} */
    protected function createRequest(BPD $bpd, ?UPD $upd)
    {
        $this->bankName = $bpd->getBankName();

        /** @var HIKAZS $hikazs */
        $hikazs = $bpd->requireLatestSupportedParameters('HIKAZS');
        if ($this->allAccounts && !$hikazs->getParameter()->getAlleKontenErlaubt()) {
            throw new \InvalidArgumentException('The bank do not permit the use of allAccounts=true');
        }
        switch ($hikazs->getVersion()) {
            case 4:
                return HKKAZv4::create(Kto::fromAccount($this->account), $this->from, $this->to);
            case 5:
                return HKKAZv5::create(KtvV3::fromAccount($this->account), $this->allAccounts, $this->from, $this->to);
            case 6:
                return HKKAZv6::create(KtvV3::fromAccount($this->account), $this->allAccounts, $this->from, $this->to);
            case 7:
                return HKKAZv7::create(Kti::fromAccount($this->account), $this->allAccounts, $this->from, $this->to);
            default:
                throw new UnsupportedException('Unsupported HKKAZ version: ' . $hikazs->getVersion());
        }
    }

    /** {@inheritdoc} */
    public function processResponse(Message $response)
    {
        parent::processResponse($response);

        // Banks send just 3010 and no HIKAZ in case there are no transactions.
        $isUnavailable = $response->findRueckmeldung(Rueckmeldungscode::NICHT_VERFUEGBAR) !== null;
        $responseHikaz = $response->findSegments(HIKAZ::class);
        $numResponseSegments = count($responseHikaz);
        if (!$isUnavailable && $numResponseSegments < count($this->getRequestSegmentNumbers())) {
            throw new UnexpectedResponseException("Only got $numResponseSegments HIKAZ response segments!");
        }

        /** @var HIKAZ $hikaz */
        foreach ($responseHikaz as $hikaz) {
            $this->rawMT940 .= $hikaz->getGebuchteUmsaetze()->getData();
        }

        // Note: Pagination boundaries may cut in the middle of the MT940 data, so it is not possible to parse a partial
        // reponse before having received all pages.
        if (!$this->hasMorePages()) {
            $this->parseMt940();
        }
    }

    private function parseMt940()
    {
        if (strpos(strtolower($this->bankName), 'sparda') !== false) {
            $parser = new SpardaMT940();
        } elseif (strpos(strtolower($this->bankName), 'postbank') !== false) {
            $parser = new PostbankMT940();
        /*}else if(strpos(strtolower($this->bankName),'comdirect')!== false){

            $parser = new ComdirectMT940();*/
        } else {
            $parser = new MT940();
        }

        try {
            // Note: Some banks encode their MT 940 data as SWIFT/ISO-8859 like it should be according to the
            // specification (e.g. DKB), others just send UTF-8 (e.g. Consorsbank), so we try to detect it here.
            $rawMT940 = mb_detect_encoding($this->rawMT940, 'UTF-8', true) === false
                ? utf8_encode($this->rawMT940) : $this->rawMT940;
            $this->parsedMT940 = $parser->parse($rawMT940);
            $this->statement = StatementOfAccount::fromMT940Array($this->parsedMT940);
        } catch (MT940Exception $e) {
            throw new \InvalidArgumentException('Invalid MT940 data', 0, $e);
        }
    }
}
