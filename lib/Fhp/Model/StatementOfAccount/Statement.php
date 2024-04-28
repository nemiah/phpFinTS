<?php

namespace Fhp\Model\StatementOfAccount;

class Statement
{
    public const CD_CREDIT = 'credit';
    public const CD_DEBIT = 'debit';

    /**
     * @var array of Transaction
     */
    protected $transactions = [];

    /**
     * @var float
     */
    protected $startBalance = 0.0;

    /**
     * @var float|null
     */
    protected $endBalance = null;

    /**
     * @var string|null
     */
    protected $creditDebit = null;

    /**
     * @var \DateTime|null
     */
    protected $date;

    /**
     * Get transactions
     *
     * @return Transaction[]
     */
    public function getTransactions(): array
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction)
    {
        $this->transactions[] = $transaction;
    }

    /**
     * Get startBalance
     */
    public function getStartBalance(): float
    {
        return $this->startBalance;
    }

    /**
     * Set startBalance
     *
     * @return $this
     */
    public function setStartBalance(float $startBalance)
    {
        $this->startBalance = (float) $startBalance;

        return $this;
    }

    /**
     * Get endBalance
     * @return ?float returns the value, if given by the bank or null if unknown
     */
    public function getEndBalance(): ?float
    {
        return $this->endBalance;
    }

    /**
     * Set endBalance
     *
     * @return $this
     */
    public function setEndBalance(float $endBalance)
    {
        $this->endBalance = (float) $endBalance;

        return $this;
    }

    /**
     * Get creditDebit
     */
    public function getCreditDebit(): ?string
    {
        return $this->creditDebit;
    }

    /**
     * Set creditDebit
     *
     * @return $this
     */
    public function setCreditDebit(?string $creditDebit)
    {
        $this->creditDebit = $creditDebit;

        return $this;
    }

    /**
     * Get date
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * Set date
     *
     * @return $this
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;

        return $this;
    }
}
