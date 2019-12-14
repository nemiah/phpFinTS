<?php

namespace Fhp\Model\StatementOfAccount;

class Statement
{
    const CD_CREDIT = 'credit';
    const CD_DEBIT = 'debit';

    /**
     * @var array of Transaction
     */
    protected $transactions = [];

    /**
     * @var float
     */
    protected $startBalance = 0.0;

    /**
     * @var string
     */
    protected $creditDebit;

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

    /**
     * Set transactions
     *
     * @param array $transactions
     *
     * @return $this
     */
    public function setTransactions(array $transactions = null)
    {
        $this->transactions = $transactions;

        return $this;
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
     * Get creditDebit
     */
    public function getCreditDebit(): string
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
