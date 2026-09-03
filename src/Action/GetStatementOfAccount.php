<?php

namespace Fhp\Action;

use Fhp\Model\SEPAAccount;
use Fhp\Model\StatementOfAccount\StatementOfAccount;
use Fhp\Protocol\BPD;
use Fhp\Protocol\Message;
use Fhp\Protocol\UPD;
use Fhp\UnsupportedException;

/**
 * Retrieves statements for one specific account or for all accounts that the user has access to. A statement is a
 * series of financial transactions that pertain to the account, grouped by day.
 *
 * Banks return statements either as CAMT XML (HKCAZ) or as MT 940 data (HKKAZ), and not all of them support both. This
 * action inspects the BPD and delegates to {@link GetStatementOfAccountXML} or {@link GetStatementOfAccountMT940}
 * accordingly, preferring CAMT XML because MT 940 is being phased out. Use {@link getStatement()} to obtain the result
 * regardless of the format that was used, or {@link getDelegate()} to find out which action it ended up using.
 *
 * If your application requires a particular format (e.g. because it parses the raw data itself), use the respective
 * action directly instead of this one.
 */
class GetStatementOfAccount extends AbstractGetStatementOfAccount
{
    // Request (if you add a field here, update __serialize() and __unserialize() as well).
    /** @var SEPAAccount */
    private $account;
    /** @var \DateTime */
    private $from;
    /** @var \DateTime */
    private $to;
    /** @var bool */
    private $allAccounts;
    /** @var bool */
    private $includeUnbooked;

    // Information from the BPD, only kept for backwards compatibility of the serialized format.
    /** @var string|null */
    private $bankName;

    /**
     * The action that this one delegates to, determined from the BPD in {@link createRequest()}. It is part of the
     * serialized form, so that the decision survives an interruption for a TAN.
     * @var AbstractGetStatementOfAccount|null
     */
    private $delegate;

    /**
     * @param SEPAAccount $account The account to get the statement for. This can be constructed based on information
     *     that the user entered, or it can be {@link SEPAAccount} instance retrieved from {@link getAccounts()}.
     * @param \DateTime|null $from If set, only transactions after this date (inclusive) are returned.
     * @param \DateTime|null $to If set, only transactions before this date (inclusive) are returned.
     * @param bool $allAccounts If set to true, will return statements for all accounts of the user. You still need to
     *     pass one of the accounts into $account, though.
     * @param bool $includeUnbooked If set to true, transactions that the bank has received but not booked yet are
     *     included, in both formats. Note that the bank only sends them if the requested time range reaches into the
     *     present.
     * @return GetStatementOfAccount A new action instance.
     */
    public static function create(SEPAAccount $account, ?\DateTime $from = null, ?\DateTime $to = null, bool $allAccounts = false, bool $includeUnbooked = false): GetStatementOfAccount
    {
        if ($from !== null && $to !== null && $from > $to) {
            throw new \InvalidArgumentException('From-date must be before to-date');
        }

        $result = new GetStatementOfAccount();
        $result->account = $account;
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
            $this->account, $this->from, $this->to, $this->allAccounts, $this->includeUnbooked,
            $this->bankName,
            $this->delegate,
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
            $this->account, $this->from, $this->to, $this->allAccounts, $this->includeUnbooked,
            $this->bankName,
            $this->delegate,
        ) = $serialized;

        is_array($parentSerialized) ?
            parent::__unserialize($parentSerialized) :
            parent::unserialize($parentSerialized);
    }

    /**
     * @return AbstractGetStatementOfAccount|null The action that this one delegates to, or null if it has not been
     *     executed yet. Useful to access format-specific results.
     * @noinspection PhpUnused
     */
    public function getDelegate(): ?AbstractGetStatementOfAccount
    {
        return $this->delegate;
    }

    public function getStatement(): StatementOfAccount
    {
        return $this->requireDelegate()->getStatement();
    }

    public function getRawResponse(): array
    {
        return $this->requireDelegate()->getRawResponse();
    }

    /**
     * @deprecated This action only returns MT 940 data if the bank does not support CAMT XML. Use
     *     {@link getRawResponse()} to obtain the raw data in whichever format the bank used, or use
     *     {@link GetStatementOfAccountMT940} directly if your application requires the MT 940 format.
     *
     * @return string The raw MT940 data received from the server.
     * @throws \RuntimeException If the bank returned CAMT XML instead.
     * @noinspection PhpUnused
     */
    public function getRawMT940(): string
    {
        return $this->requireDelegateOfType(GetStatementOfAccountMT940::class)->getRawMT940();
    }

    /**
     * @deprecated This action only returns MT 940 data if the bank does not support CAMT XML. Use
     *     {@link getStatement()} for the parsed statement independent of the format, or use
     *     {@link GetStatementOfAccountMT940} directly if your application requires the MT 940 format.
     *
     * @return array The parsed MT940 data.
     * @throws \RuntimeException If the bank returned CAMT XML instead.
     */
    public function getParsedMT940(): array
    {
        return $this->requireDelegateOfType(GetStatementOfAccountMT940::class)->getParsedMT940();
    }

    /**
     * @deprecated This action only returns CAMT XML if the bank supports it. Use {@link getRawResponse()} to obtain the
     *     raw data in whichever format the bank used, or use {@link GetStatementOfAccountXML} directly if your
     *     application requires the CAMT XML format.
     *
     * @return string[] The XML-Document(s) received from the bank, or empty array if the statement is unavailable/empty.
     * @throws \RuntimeException If the bank returned MT 940 data instead.
     * @noinspection PhpUnused
     */
    public function getBookedXML(): array
    {
        return $this->requireDelegateOfType(GetStatementOfAccountXML::class)->getBookedXML();
    }

    protected function createRequest(BPD $bpd, ?UPD $upd)
    {
        $this->bankName = $bpd->getBankName();
        $this->delegate ??= $this->createDelegate($bpd, $upd);
        return $this->delegate->createRequest($bpd, $upd);
    }

    public function processResponse(Message $response)
    {
        parent::processResponse($response);

        $delegate = $this->requireDelegate(false);
        // The delegate needs to know the segment numbers to validate the response against, and only this action (the one
        // that FinTs executes) is told about them.
        $delegate->setRequestSegmentNumbers($this->getRequestSegmentNumbers());
        $delegate->processResponse($response);
    }

    /**
     * Decides which format to request: CAMT XML if the bank (and the account) supports it, MT 940 otherwise.
     */
    private function createDelegate(BPD $bpd, ?UPD $upd): AbstractGetStatementOfAccount
    {
        $camtSupported = $bpd->getLatestSupportedParameters('HICAZS') !== null
            && ($upd === null || $upd->isRequestSupportedForAccount($this->account, 'HKCAZ'));
        if ($camtSupported) {
            return GetStatementOfAccountXML::create(
                $this->account, $this->from, $this->to, null, $this->allAccounts, $this->includeUnbooked);
        }
        if ($bpd->getLatestSupportedParameters('HIKAZS') !== null) {
            return GetStatementOfAccountMT940::create($this->account, $this->from, $this->to, $this->allAccounts, $this->includeUnbooked);
        }
        throw new UnsupportedException(
            'The bank does not support retrieving statements in any format implemented in this library (neither HKCAZ '
            . 'nor HKKAZ).');
    }

    /**
     * @param bool $ensureDone Whether to also verify that the action has completed, i.e. that results are available.
     * @return AbstractGetStatementOfAccount The delegate, guaranteed to be present.
     */
    private function requireDelegate(bool $ensureDone = true): AbstractGetStatementOfAccount
    {
        if ($ensureDone) {
            $this->ensureDone();
        }
        if ($this->delegate === null) {
            throw new \RuntimeException(
                'This action does not know which statement format it requested. It was probably restored from a '
                . 'serialized form that a different version of this library had created, in which case the request '
                . 'has to be started over.');
        }
        return $this->delegate;
    }

    /**
     * @param class-string<AbstractGetStatementOfAccount> $type
     * @return AbstractGetStatementOfAccount The delegate, guaranteed to be an instance of $type.
     */
    private function requireDelegateOfType(string $type): AbstractGetStatementOfAccount
    {
        $delegate = $this->requireDelegate();
        if (!$delegate instanceof $type) {
            throw new \RuntimeException(
                'This statement was retrieved with ' . get_class($delegate) . ', so the requested data is not '
                . 'available. Use ' . $type . ' directly if your application needs a particular format.');
        }
        return $delegate;
    }
}
