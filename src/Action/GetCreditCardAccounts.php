<?php

namespace Fhp\Action;

use Fhp\BaseAction;
use Fhp\Model\CreditCardAccount;
use Fhp\Protocol\BPD;
use Fhp\Protocol\Message;
use Fhp\Protocol\UPD;
use Fhp\Segment\HIUPD\HIUPD;

/**
 * Lists the credit card accounts that the user has access to.
 *
 * Credit card accounts have no IBAN, so they are not returned by {@link GetSEPAAccounts} (HKSPA).
 * They are however described by the HIUPD segments in the UPD, which the bank sends during dialog
 * initialization. This action therefore needs no request to the server at all; it just interprets
 * data that is already available after login.
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: E.3 "Kontoinformation" (the HIUPD segment this reads)
 */
class GetCreditCardAccounts extends BaseAction
{
    /**
     * The range of account types ("Kontoart") that denotes a credit card account, see
     * {@link \Fhp\Segment\HIUPD\HIUPDv6::$kontoart} for the complete list of ranges.
     *
     * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
     * Section: E.3 "Kontoinformation"
     */
    private const KONTOART_CREDIT_CARD_MIN = 50;
    private const KONTOART_CREDIT_CARD_MAX = 59;

    /** The business transaction that retrieves credit card transactions. */
    private const REQUEST_NAME = 'DKKKU';

    /** @var CreditCardAccount[] */
    private $accounts = [];

    public static function create(): GetCreditCardAccounts
    {
        return new GetCreditCardAccounts();
    }

    /**
     * @return CreditCardAccount[] The credit card accounts, possibly empty.
     */
    public function getAccounts(): array
    {
        $this->ensureDone();
        return $this->accounts;
    }

    protected function createRequest(BPD $bpd, ?UPD $upd)
    {
        if ($upd === null) {
            throw new \InvalidArgumentException('Cannot determine credit card accounts without UPD');
        }

        foreach ($upd->hiupd ?? [] as $hiupd) {
            if (!self::isCreditCardAccount($hiupd)) {
                continue;
            }
            $ktv = $hiupd->getKontoverbindung();
            if ($ktv === null || $ktv->kontonummer === null) {
                continue;
            }
            $this->accounts[] = (new CreditCardAccount())
                ->setAccountNumber($ktv->kontonummer)
                ->setSubAccount($ktv->unterkontomerkmal)
                ->setBlz($ktv->kik->kreditinstitutscode ?? $bpd->getBankCode())
                ->setName(UPD::accountHolderName($hiupd))
                ->setProductName($hiupd->getKontoproduktbezeichnung())
                ->setCurrency($hiupd->getKontowaehrung())
                ->setAccountType($hiupd->getKontoart());
        }

        // Everything was computed from the UPD, so no request to the server is necessary.
        $this->isDone = true;
        return [];
    }

    /**
     * Prefers the explicit list of permitted business transactions, because that directly states
     * whether credit card transactions can be retrieved for the account. Only if the bank does not
     * send such a list do we fall back to the account type.
     */
    private static function isCreditCardAccount(HIUPD $hiupd): bool
    {
        $erlaubteGeschaeftsvorfaelle = $hiupd->getErlaubteGeschaeftsvorfaelle();
        if (count($erlaubteGeschaeftsvorfaelle) > 0) {
            foreach ($erlaubteGeschaeftsvorfaelle as $erlaubterGeschaeftsvorfall) {
                if ($erlaubterGeschaeftsvorfall->getGeschaeftsvorfall() === self::REQUEST_NAME) {
                    return true;
                }
            }
            return false;
        }

        $kontoart = $hiupd->getKontoart();
        return $kontoart !== null
            && $kontoart >= self::KONTOART_CREDIT_CARD_MIN
            && $kontoart <= self::KONTOART_CREDIT_CARD_MAX;
    }

    public function processResponse(Message $response)
    {
        throw new \AssertionError('GetCreditCardAccounts never sends a request');
    }
}
