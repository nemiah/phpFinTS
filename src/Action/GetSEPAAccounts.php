<?php

declare(strict_types=1);

namespace Fhp\Action;

use Fhp\Model\SEPAAccount;
use Fhp\PaginateableAction;
use Fhp\Protocol\BPD;
use Fhp\Protocol\Message;
use Fhp\Protocol\UPD;
use Fhp\Segment\BaseSegment;
use Fhp\Segment\Common\Ktz;
use Fhp\Segment\HIRMS\Rueckmeldungscode;
use Fhp\Segment\SPA\HISPA;
use Fhp\Segment\SPA\HKSPAv1;
use Fhp\Segment\SPA\HKSPAv2;
use Fhp\Segment\SPA\HKSPAv3;
use Fhp\UnsupportedException;

/**
 * Runs an HKSPA request to retrieve account details about the accounts that the user can access through FinTs.
 *
 * The accounts are annotated with the per-account information from the UPD (holder name, product name, currency,
 * account type) where the bank sent a matching HIUPD segment, see {@link SEPAAccount}.
 *
 * TODO In future, once all banks populate the BIC in HIUPD.erweiterungKontobezogen, or if we force library users to
 * supply the BIC to us, we won't need to send an HKSPA anymore, but we can simply fulfil this action from the UPD.
 */
class GetSEPAAccounts extends PaginateableAction
{
    // Empty request, in order to retrieve all accounts.

    // Request state (if you add a field here, update __serialize() and __unserialize() as well).
    /**
     * The UPD as of createRequest(), needed again in processResponse() to annotate the accounts. Some banks require a
     * TAN for HKSPA, and the FinTs instance that completes it may have been restored from persist(true), which leaves
     * out the UPD, so the action keeps (and serializes) its own copy.
     */
    private ?UPD $upd = null;

    // Response
    /** @var SEPAAccount[] */
    private array $accounts = [];

    /**
     * @return GetSEPAAccounts A new action instance.
     */
    public static function create(): GetSEPAAccounts
    {
        return new GetSEPAAccounts();
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
            $this->upd,
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
        // Actions serialized before the UPD travelled along consist of the PaginateableAction state only.
        if (count($serialized) === 3) {
            parent::__unserialize($serialized);
            return;
        }

        list($parentSerialized, $this->upd) = $serialized;

        is_array($parentSerialized) ?
            parent::__unserialize($parentSerialized) :
            parent::unserialize($parentSerialized);
    }

    /**
     * @return SEPAAccount[]
     */
    public function getAccounts(): array
    {
        $this->ensureDone();
        return $this->accounts;
    }

    protected function createRequest(BPD $bpd, ?UPD $upd)
    {
        $this->upd = $upd;

        /** @var BaseSegment $hispas */
        $hispas = $bpd->requireLatestSupportedParameters('HISPAS');
        switch ($hispas->getVersion()) {
            case 1:
                return HKSPAv1::createEmpty();
            case 2:
                return HKSPAv2::createEmpty();
            case 3:
                return HKSPAv3::createEmpty();
            default:
                throw new UnsupportedException('Unsupported HKSPA version: ' . $hispas->getVersion());
        }
    }

    public function processResponse(Message $response)
    {
        parent::processResponse($response);

        // Banks send just 3010 and no HISPA in case there are no accounts (or at least none that the bank is able to
        // report through HISPA).
        if ($response->findRueckmeldung(Rueckmeldungscode::NICHT_VERFUEGBAR) !== null) {
            $this->accounts = [];
            return;
        }

        /** @var HISPA $hispa */
        $hispa = $response->requireSegment(HISPA::class);
        $this->accounts = array_map(function ($ktz) {
            /** @var Ktz $ktz */
            $account = new SEPAAccount();
            $account->setIban($ktz->iban);
            $account->setBic($ktz->bic);
            $account->setAccountNumber($ktz->kontonummer);
            $account->setSubAccount($ktz->unterkontomerkmal);
            $account->setBlz($ktz->kreditinstitutskennung->kreditinstitutscode);
            // HISPA and HIUPD describe the same accounts, but only the latter carries names, currency and type.
            if ($hiupd = $this->upd?->findHiupd($account)) {
                $account
                    ->setName($hiupd->getAccountHolderName())
                    ->setProductName($hiupd->getKontoproduktbezeichnung())
                    ->setCurrency($hiupd->getKontowaehrung())
                    ->setAccountType($hiupd->getKontoart());
            }
            return $account;
        }, $hispa->getSepaKontoverbindung());
    }
}
