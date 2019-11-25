<?php

namespace Fhp\Response;

use Fhp\Model\StatementOfAccount\Statement;
use Fhp\Model\StatementOfAccount\StatementOfAccount;
use Fhp\Model\StatementOfAccount\Transaction;

class GetStatementOfAccount extends Response
{
    const SEG_ACCOUNT_INFORMATION = 'HIKAZ';

    /**
     * Gets the raw MT940 string from response.
     *
     * @return string
     */
    public function getRawMt940()
    {
        $seg = $this->findSegment(static::SEG_ACCOUNT_INFORMATION);
        if (is_string($seg)) {
            if (preg_match('/@(\d+)@(.+)/ms', $seg, $m)) {
                return $m[2];
            }
        }

        return '';
    }

    /**
     * Adds statements to an existing StatementOfAccount object.
     *
     * @param array $array
     * @param StatementOfAccount $statementOfAccount
     * @return StatementOfAccount
     */
    protected static function addFromArray(array $array, StatementOfAccount $statementOfAccount)
    {
        foreach ($array as $date => $statement) {
            if ($statementOfAccount->hasStatementForDate($date)) {
                $statementModel = $statementOfAccount->getStatementForDate($date);
            } else {
                $statementModel = new Statement();
                $statementModel->setDate(new \DateTime($date));
                $statementModel->setStartBalance((float) $statement['start_balance']['amount']);
                $statementModel->setCreditDebit($statement['start_balance']['credit_debit']);
                $statementOfAccount->addStatement($statementModel);
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
                    $transaction->setBookingDate(new \DateTime($trx['booking_date']));
                    $transaction->setValutaDate(new \DateTime($trx['valuta_date']));
                    $transaction->setCreditDebit($trx['credit_debit']);
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
                    $statementModel->addTransaction($transaction);
                }
            }
        }

        return $statementOfAccount;
    }

    /**
     * Creates a StatementOfAccount model from array.
     *
     * @param array $array
     * @return StatementOfAccount
     */
    public static function createModelFromArray(array $array)
    {
        $soa = static::addFromArray($array, new StatementOfAccount());

        return $soa;
    }
}
