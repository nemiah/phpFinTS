<?php

namespace Fhp\Response;

use Fhp\Model\StatementOfAccount\Statement;
use Fhp\Model\StatementOfAccount\StatementOfAccount;
use Fhp\Model\StatementOfAccount\Transaction;
use Fhp\Parser\MT940;

/**
 * Class GetStatementOfAccount
 * @package Fhp\Response
 */
class GetStatementOfAccount extends Response
{
    const SEG_ACCOUNT_INFORMATION = 'HIKAZ';

    /**
     * @return StatementOfAccount|null
     * @throws \Fhp\Parser\Exception\MT940Exception
     */
    public function getStatementOfAccount()
    {
        return static::createModelFromArray(
            $this->getStatementOfAccountArray()
        );
    }

    /**
     * @return array
     * @throws \Fhp\Parser\Exception\MT940Exception
     */
    public function getStatementOfAccountArray()
    {
        $data = [];
        $seg = $this->findSegment(static::SEG_ACCOUNT_INFORMATION);
        if (is_string($seg)) {
            if (preg_match('/@(\d+)@(.+)/ms', $seg, $m)) {
                $parser = new MT940($m[2]);
                $data = $parser->parse(MT940::TARGET_ARRAY);
            }
        }

        return $data;
    }

    /**
     * Creates a StatementOfAccount model from array.
     *
     * @param array $array
     * @return StatementOfAccount|null
     */
    public static function createModelFromArray(array $array)
    {
        if (empty($array)) {
            return null;
        }

        $soa = new StatementOfAccount();
        foreach ($array as $date => $statement) {
            $statementModel = new Statement();
            $statementModel->setDate(new \DateTime($date));
            $statementModel->setStartBalance((float) $statement['start_balance']['amount']);
            $statementModel->setCreditDebit($statement['start_balance']['credit_debit']);

            if (isset($statement['transactions'])) {
                foreach ($statement['transactions'] as $trx) {
                    $transaction = new Transaction();
                    $transaction->setDate(new \DateTime($date));
                    $transaction->setCreditDebit($trx['credit_debit']);
                    $transaction->setAmount($trx['amount']);
                    $transaction->setBookingText($trx['description']['booking_text']);
                    $transaction->setDescription1($trx['description']['description_1']);
                    $transaction->setDescription2($trx['description']['description_2']);
                    $transaction->setBankCode($trx['description']['bank_code']);
                    $transaction->setAccountNumber($trx['description']['account_number']);
                    $transaction->setName($trx['description']['name']);
                    $statementModel->addTransaction($transaction);
                }
            }
            $soa->addStatement($statementModel);
        }

        return $soa;
    }


}
