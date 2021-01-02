<?php

namespace Fhp\Action;

use Fhp\Model\SEPAAccount;
use Fhp\PaginateableAction;
use Fhp\Protocol\BPD;
use Fhp\Protocol\Message;
use Fhp\Protocol\UnexpectedResponseException;
use Fhp\Protocol\UPD;
use Fhp\Segment\BaseSegment;
use Fhp\Segment\Common\Kti;
use Fhp\Segment\Common\Kto;
use Fhp\Segment\Common\KtvV3;
use Fhp\Segment\SAL\HISAL;
use Fhp\Segment\SAL\HKSALv4;
use Fhp\Segment\SAL\HKSALv5;
use Fhp\Segment\SAL\HKSALv6;
use Fhp\Segment\SAL\HKSALv7;
use Fhp\UnsupportedException;

/**
 * Runs an HKSAL request the current balance of the given account.
 */
class GetBalance extends PaginateableAction
{
    // Request (not available after serialization, i.e. not available in processResponse()).
    /** @var SEPAAccount */
    private $account;
    /** @var bool */
    private $allAccounts;

    // Response
    /** @var HISAL[] */
    private $response = [];

    /**
     * @param SEPAAccount $account The account to get the balance for. This can be constructed based on information
     *     that the user entered, or it can be {@link SEPAAccount} instance retrieved from {@link GetSEPAAccounts}.
     * @param bool $allAccounts If set to true, will return balances for all accounts of the user. You still need to
     *     pass one of the accounts into $account, though.
     */
    public static function create(SEPAAccount $account, bool $allAccounts = false): GetBalance
    {
        $result = new GetBalance();
        $result->account = $account;
        $result->allAccounts = $allAccounts;
        return $result;
    }

    public function serialize(): string
    {
        return serialize([
            parent::serialize(),
            $this->account, $this->allAccounts,
        ]);
    }

    public function unserialize($serialized)
    {
        list(
            $parentSerialized,
            $this->account, $this->allAccounts
            ) = unserialize($serialized);
        parent::unserialize($parentSerialized);
    }

    /**
     * @return HISAL[]
     */
    public function getBalances()
    {
        $this->ensureDone();
        return $this->response;
    }

    /** {@inheritdoc} */
    protected function createRequest(BPD $bpd, ?UPD $upd)
    {
        /** @var BaseSegment $hisals */
        $hisals = $bpd->requireLatestSupportedParameters('HISALS');
        switch ($hisals->getVersion()) {
            case 4:
                return HKSALv4::create(Kto::fromAccount($this->account));
            case 5:
                return HKSALv5::create(KtvV3::fromAccount($this->account), $this->allAccounts);
            case 6:
                return HKSALv6::create(KtvV3::fromAccount($this->account), $this->allAccounts);
            case 7:
                return HKSALv7::create(Kti::fromAccount($this->account), $this->allAccounts);
            default:
                throw new UnsupportedException('Unsupported HKSAL version: ' . $hisals->getVersion());
        }
    }

    /** {@inheritdoc} */
    public function processResponse(Message $response)
    {
        parent::processResponse($response);

        $responseSegments = $response->findSegments(HISAL::class);
        if (count($responseSegments) === 0) {
            throw new UnexpectedResponseException('No HISAL segments received!');
        }
        $this->response = array_merge($this->response, $responseSegments);
    }
}
