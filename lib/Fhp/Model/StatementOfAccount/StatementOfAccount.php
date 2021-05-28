<?php

namespace Fhp\Model\StatementOfAccount;

use Fhp\MT940\MT940;

class StatementOfAccount
{
    /**
     * @var Statement[]
     */
    protected $statements = [];

    /**
     * Get statements
     *
     * @return Statement[]
     */
    public function getStatements(): array
    {
        return $this->statements;
    }

    /**
     * Gets statement for given date.
     *
     * @param string|\DateTime $date
     */
    public function getStatementForDate($date): ?Statement
    {
        if (is_string($date)) {
            $date = static::parseDate($date);
        }

        foreach ($this->statements as $stmt) {
            if ($stmt->getDate() == $date) {
                return $stmt;
            }
        }

        return null;
    }

    /**
     * Checks if a statement with given date exists.
     *
     * @param string|\DateTime $date
     */
    public function hasStatementForDate($date): bool
    {
        return null !== $this->getStatementForDate($date);
    }

    private static function parseDate(string $date): \DateTime
    {
        try {
            return new \DateTime($date);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Invalid date: $date", 0, $e);
        }
    }

    /**
     * @param array $array A parsed MT940 dataset, as returned from {@link MT940::parse()}.
     * @return StatementOfAccount A new instance that contains the given data.
     */
    public static function fromMT940Array(array $array): StatementOfAccount
    {
        $result = new StatementOfAccount();
        foreach ($array as $date => $statement) {
            if ($result->hasStatementForDate($date)) {
                $statementModel = $result->getStatementForDate($date);
            } else {
                $statementModel = new Statement();
                $statementModel->setDate(static::parseDate($date));
                $statementModel->setStartBalance((float) $statement['start_balance']['amount']);
                $statementModel->setCreditDebit($statement['start_balance']['credit_debit']);
                $result->statements[] = $statementModel;
            }

            if (isset($statement['transactions'])) {
                foreach ($statement['transactions'] as $trx) {
                    $replaceIn = [
                        'booking_text',
                        'description_1',
                        'description_2',
                        'description',
                        'name',
                    ];
                    foreach ($replaceIn as $k) {
                        if (isset($trx['description'][$k])) {
                            $trx['description'][$k] = str_replace('@@', '', $trx['description'][$k]);
                        }
                    }

                    $transaction = new Transaction();
                    $transaction->setBookingDate(static::parseDate($trx['booking_date']));
                    $transaction->setValutaDate(static::parseDate($trx['valuta_date']));
                    $transaction->setCreditDebit($trx['credit_debit']);
                    $transaction->setIsStorno($trx['is_storno']);
                    $transaction->setAmount($trx['amount']);
                    $transaction->setBookingCode($trx['description']['booking_code']);
                    $transaction->setBookingText($trx['description']['booking_text']);
                    $transaction->setDescription1($trx['description']['description_1']);
                    $transaction->setDescription2($trx['description']['description_2']);
                    $transaction->setStructuredDescription($trx['description']['description']);
                    $transaction->setBankCode($trx['description']['bank_code']);
                    $transaction->setAccountNumber($trx['description']['account_number']);
                    $transaction->setName($trx['description']['name']);
                    $transaction->setBooked($trx['booked']);
                    $transaction->setPN($trx['description']['primanoten_nr']);
                    $transaction->setTextKeyAddition($trx['description']['text_key_addition']);
                    $statementModel->addTransaction($transaction);
                }
            }
        }
        return $result;
    }
}
