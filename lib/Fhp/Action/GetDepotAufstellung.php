<?php

namespace Fhp\Action;

use Fhp\Model\SEPAAccount;
use Fhp\Model\StatementOfHoldings\StatementOfHoldings;
use Fhp\MT535\MT535;
use Fhp\MT535\MT535Exception;
use Fhp\PaginateableAction;
use Fhp\Protocol\BPD;
use Fhp\Protocol\Message;
use Fhp\Protocol\UnexpectedResponseException;
use Fhp\Protocol\UPD;
use Fhp\Segment\Common\KtvV3;
use Fhp\Segment\HIRMS\Rueckmeldungscode;
use Fhp\Segment\WPD\HIWPDv5;
use Fhp\Segment\WPD\HKWPDv5;
use Fhp\UnsupportedException;

/**
 * Depotaufstellung HKWPD
 * MT535
 * ZwenAusZwota
 */
class GetDepotAufstellung extends PaginateableAction
{
    // Request (not available after serialization, i.e. not available in processResponse()).
    /** @var SEPAAccount */
    private $account;

    // Information from the BPD needed to interpret the response.
    /** @var string */
    private $bankName;

    // Response
    /** @var string */
    private $rawMT535 = '';

    /** @var array */
    protected $parsedMT535 = [];

    /** @var Holding */
    private $statement;

    /**
     * @param SEPAAccount $account The account to get the statement for. This can be constructed based on information
     *     that the user entered, or it can be {@link SEPAAccount} instance retrieved from {@link getAccounts()}.
     * @return GetDepot A new action instance.
     */
    public static function create(SEPAAccount $account): GetDepotAufstellung
    {
        $result = new GetDepotAufstellung();
        $result->account = $account;
        return $result;
    }

    public function serialize(): string
    {
        return serialize([
            parent::serialize(),
            $this->account,
        ]);
    }

    public function unserialize($serialized)
    {
        list(
            $parentSerialized,
            $this->account) = unserialize($serialized);
        parent::unserialize($parentSerialized);
    }

    /**
     * @return string The raw MT535 data received from the server.
     * @noinspection PhpUnused
     */
    public function getRawMT535(): string
    {
        $this->ensureDone();
        return $this->rawMT535;
    }

    /**
     * @return array The parsed MT535 data.
     */
    public function getParsedMT535(): array
    {
        $this->ensureDone();
        return $this->parsedMT535;
    }

    /**
     * @return StatementOfHolding
     */
    public function getStatement()
    {
        $this->ensureDone();
        return $this->statement;
    }

    /** {@inheritdoc} */
    protected function createRequest(BPD $bpd, ?UPD $upd)
    {
        /** @var HIWPDS $hiwpds */
        $hiwpds = $bpd->requireLatestSupportedParameters('HIWPDS');

        switch ($hiwpds->getVersion()) {
            case 5:
                return HKWPDv5::create(KtvV3::fromAccount($this->account));
            default:
                throw new UnsupportedException('Unsupported HKWPD version: ' . $hiwpds->getVersion());
        }
    }

    /** {@inheritdoc} */
    public function processResponse(Message $response)
    {
        parent::processResponse($response);

        $isUnavailable = $response->findRueckmeldung(Rueckmeldungscode::NICHT_VERFUEGBAR) !== null;
        $responseHiwpd = $response->findSegments(HIWPDv5::class);

        $numResponseSegments = count($responseHiwpd);
        if (!$isUnavailable && $numResponseSegments < count($this->getRequestSegmentNumbers())) {
            throw new UnexpectedResponseException("Only got $numResponseSegments HIWPD response segments!");
        }

        /** @var HIWPD $hiwpd */
        foreach ($responseHiwpd as $hiwpd) {
            $this->rawMT535 .= $hiwpd->getDepotaufstellung()->getData();
        }

        // Note: Pagination boundaries may cut in the middle of the MT535 data, so it is not possible to parse a partial
        // reponse before having received all pages.
        if (!$this->hasMorePages()) {
            $this->parseMt535();
        }
    }

    private function parseMt535()
    {
        $parser = new MT535();

        try {
            // Note: Some banks encode their MT 535 data as SWIFT/ISO-8859 like it should be according to the
            // specification, others just send UTF-8, so we try to detect it here.
            $rawMT535 = mb_detect_encoding($this->rawMT535, 'UTF-8', true) === false
                ? utf8_encode($this->rawMT535) : $this->rawMT535;
            $this->parsedMT535 = $parser->parse($rawMT535);
            $this->statement = StatementOfHoldings::fromMT535Object($this->parsedMT535->blockB);
        } catch (MT535Exception $e) {
            throw new \InvalidArgumentException('Invalid MT535 data', 0, $e);
        }
    }

    public function getDepotWert()
    {
        $ret = new \StdClass();
        $ret->betrag = 0.0;
        preg_match('/EUR(.*)/sm', $this->parsedMT535->blockC[1], $matches);
        $ret->betrag = floatval($matches[1]);
        return $ret;
    }
}
