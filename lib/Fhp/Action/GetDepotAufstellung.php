<?php

namespace Fhp\Action;

use Fhp\Model\SEPAAccount;
use Fhp\Model\StatementOfHoldings\StatementOfHoldings;
use Fhp\MT535\MT535;
use Fhp\PaginateableAction;
use Fhp\Protocol\BPD;
use Fhp\Protocol\Message;
use Fhp\Protocol\UnexpectedResponseException;
use Fhp\Protocol\UPD;
use Fhp\Segment\Common\KtvV3;
use Fhp\Segment\HIRMS\Rueckmeldungscode;
use Fhp\Segment\WPD\HIWPD;
use Fhp\Segment\WPD\HIWPDS;
use Fhp\Segment\WPD\HIWPDv5;
use Fhp\Segment\WPD\HKWPDv5;
use Fhp\UnsupportedException;

/**
 * Depotaufstellung HKWPD
 * MT535
 */
class GetDepotAufstellung extends PaginateableAction
{
    // Request (not available after serialization, i.e. not available in processResponse()).
    /** @var SEPAAccount */
    private $account;

    // Response
    /** @var string */
    private $rawMT535 = '';

    /** @var StatementOfHoldings */
    private $statement;

    /** @var float */
    private $depotWert;

    /**
     * @param SEPAAccount $account The account to get the statement for. This can be constructed based on information
     *     that the user entered, or it can be {@link SEPAAccount} instance retrieved from {@link getAccounts()}.
     * @return GetDepotAufstellung A new action instance.
     */
    public static function create(SEPAAccount $account): GetDepotAufstellung
    {
        $result = new GetDepotAufstellung();
        $result->account = $account;
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
            $this->account,
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
        $this->__unserialize(unserialize($serialized));
    }

    public function __unserialize(array $serialized): void
    {
        list(
            $parentSerialized,
            $this->account
            ) = $serialized;

        is_array($parentSerialized) ?
            parent::__unserialize($parentSerialized) :
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

    public function getStatement(): StatementOfHoldings
    {
        $this->ensureDone();
        return $this->statement;
    }

    public function getDepotWert(): float
    {
        $this->ensureDone();
        return $this->depotWert;
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
        try {
            // Note: Some banks encode their MT 535 data as SWIFT/ISO-8859 like it should be according to the
            // specification, others just send UTF-8, so we try to detect it here.
            $rawMT535 = mb_detect_encoding($this->rawMT535, 'UTF-8', true) === false
                ? utf8_encode($this->rawMT535) : $this->rawMT535;
            $parser = new MT535($rawMT535);
            $this->statement = $parser->parseHoldings();
            $this->depotWert = $parser->parseDepotWert();
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid MT535 data', 0, $e);
        }
    }
}
