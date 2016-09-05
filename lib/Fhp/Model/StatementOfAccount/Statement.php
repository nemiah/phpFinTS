<?php

namespace Fhp\Model\StatementOfAccount;

/**
 * Class Statement
 * @package Fhp\Model\StatementOfAccount
 */
class Statement
{
    const CD_CREDIT = 'credit';
    const CD_DEBIT = 'debit';

    /**
     * @var array of Transaction
     */
    protected $transactions = array();

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
    public function getTransactions()
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
     *
     * @return float
     */
    public function getStartBalance()
    {
        return $this->startBalance;
    }

    /**
     * Set startBalance
     *
     * @param float $startBalance
     *
     * @return $this
     */
    public function setStartBalance($startBalance)
    {
        $this->startBalance = (float) $startBalance;

        return $this;
    }

    /**
     * Get creditDebit
     *
     * @return string
     */
    public function getCreditDebit()
    {
        return $this->creditDebit;
    }

    /**
     * Set creditDebit
     *
     * @param string|null $creditDebit
     *
     * @return $this
     */
    public function setCreditDebit($creditDebit)
    {
        $this->creditDebit = $creditDebit;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return $this
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;

        return $this;
    }
}
