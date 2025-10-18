<?php

namespace Fhp\Action;

use Fhp\BaseAction;
use Fhp\Model\SEPAAccount;
use Fhp\Protocol\BPD;
use Fhp\Protocol\UPD;
use Fhp\Segment\AUB\HIAUBSv9;
use Fhp\Segment\AUB\HKAUBv9;
use Fhp\Segment\Common\Kti;
use Fhp\Syntax\Bin;

class SendInternationalCreditTransfer extends BaseAction
{
    // Request (if you add a field here, update __serialize() and __unserialize() as well).
    /** @var SEPAAccount */
    protected $account;
    /** @var string */
    protected $dtavzData;
    /** @var string|null */
    protected $dtavzVersion;

    /**
     * @param SEPAAccount $account The account of the creditor (the sender of the money)
     * @param string $dtavzData The details of the transfer(s) in DTAZV Format (Datenträgeraustauschverfahren Auslandszahlungsverkehr)
     * @param string|null $dtavzVersion If null the value the bank expects is used.
     */
    public static function create(SEPAAccount $account, string $dtavzData, ?string $dtavzVersion = null): SendInternationalCreditTransfer
    {
        $result = new SendInternationalCreditTransfer();
        $result->account = $account;
        $result->dtavzVersion = $dtavzVersion;
        $result->dtavzData = $dtavzData;
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
            $this->account, $this->dtavzData, $this->dtavzVersion,
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
            $this->account, $this->dtavzData, $this->dtavzVersion,
        ) = $serialized;

        is_array($parentSerialized) ?
            parent::__unserialize($parentSerialized) :
            parent::unserialize($parentSerialized);
    }

    protected function createRequest(BPD $bpd, ?UPD $upd)
    {
        /** @var HIAUBSv9 $hiaubs */
        $hiaubs = $bpd->requireLatestSupportedParameters('HIAUBS');

        $hkaub = HKAUBv9::createEmpty();
        $hkaub->kontoverbindungInternational = Kti::fromAccount($this->account);
        $hkaub->DTAZVHandbuch = $this->dtavzVersion ?? $hiaubs->parameter->DTAZVHandbuch;
        $hkaub->DTAZVDatensatz = new Bin($this->dtavzData);
        return $hkaub;
    }
}
