<?php

namespace Fhp\Parser;

use Fhp\Parser\Exception\MT940Exception;

/**
 * Class MT940
 * @package Fhp\Parser
 */
class MT940
{
    const TARGET_ARRAY = 0;

    const CD_CREDIT = 'credit';
    const CD_DEBIT = 'debit';

    /** @var string */
    protected $rawData;
    /** @var string */
    protected $soaDate;

    /**
     * MT940 constructor.
     *
     * @param $rawData
     */
    public function __construct($rawData)
    {
        $this->rawData = (string) $rawData;
    }

    /**
     * @param string $target
     * @return array
     * @throws MT940Exception
     */
    public function parse($target)
    {
        switch ($target) {
            case static::TARGET_ARRAY:
                return $this->parseToArray();
                break;
            default:
                throw new MT940Exception('Invalid parse type provided');
        }
    }

    /**
     * @return array
     */
    protected function parseToArray()
    {
        $result = array();
        $days = explode("\r\n-", $this->rawData);
        foreach ($days as &$day) {
            $day = explode("\r\n:", $day);
            // remove not so important data
            array_shift($day);
            array_shift($day);
            array_shift($day);
            array_shift($day);
            for ($i = 0; $i < count($day); $i++) {
                // handle start balance
                // 60F:C160401EUR1234,56
                if (0 === strpos($day[$i], '60F:')) {
                    // remove 60F: for better parsing
                    $day[$i] = substr($day[$i], 4);
                    $this->soaDate = $this->getDate(substr($day[$i], 1, 6));

                    if (!isset($result[$this->soaDate])) {
                        $result[$this->soaDate] = array('start_balance' => array());
                    }

                    $cdMark = substr($day[$i], 0, 1);
                    if ($cdMark == 'C') {
                        $result[$this->soaDate]['start_balance']['credit_debit'] = static::CD_CREDIT;
                    } elseif ($cdMark == 'D') {
                        $result[$this->soaDate]['start_balance']['credit_debit'] = static::CD_DEBIT;
                    }

                    $amount = str_replace(',', '.', substr($day[$i], 10));
                    $result[$this->soaDate]['start_balance']['amount'] = $amount;
                } elseif (
                    // found transaction
                    // trx:61:1603310331DR637,39N033NONREF
                    0 === strpos($day[$i], '61:')
                    && isset($day[$i + 1])
                    && 0 === strpos($day[$i + 1], '86:')
                ) {
                    $transaction = substr($day[$i], 3);
                    $description = substr($day[$i + 1], 3);

                    //$transactionDate = $this->getDate(substr($transaction, 0, 6));

                    if (!isset($result[$this->soaDate]['transactions'])) {
                        $result[$this->soaDate]['transactions'] = array();
                    }

                    // short form for better handling
                    $trx = &$result[$this->soaDate]['transactions'];

                    preg_match('/^\d{6}(\d{4})?(C|D|RC|RD)([A-Z]{1})?([^N]+)N/', $transaction, $trxMatch);
                    if ($trxMatch[2] == 'C') {
                        $trx[count($trx)]['credit_debit'] = static::CD_CREDIT;
                    } elseif ($trxMatch[2] == 'D') {
                        $trx[count($trx)]['credit_debit'] = static::CD_DEBIT;
                    } else {
                        die('cd mark not found in: ' . $transaction);
                    }

                    $amount = $trxMatch[4];
                    $amount = str_replace(',', '.', $amount);
                    $trx[count($trx) - 1]['amount'] = floatval($amount);

                    $description = $this->parseDescription($description);
                    $trx[count($trx) - 1]['description'] = $description;
                }
            }
        }

        return $result;
    }

    /**
     * @param string $descr
     * @return array
     */
    protected function parseDescription($descr)
    {
        $prepared = array();
        $result = array();

        // prefill with empty values
        for ($i = 0; $i <= 63; $i++) {
            $prepared[$i] = null;
        }

        $descr = str_replace('? ', '?', $descr);
        preg_match_all('/\?(\d{2})([^\?]+)/', $descr, $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            $prepared[(int) $m[1]] = $m[2];
        }

        // verwendungszweck
        $description = '';
        for ($i = 20; $i <= 29; $i++) {
            $description .= $prepared[$i];
        }

        $description2 = '';
        for ($i = 60; $i <= 63; $i++) {
            $description2 .= $prepared[$i];
        }

        $result['booking_text']      = trim(str_replace("\r\n", '', $prepared[0]));
        $result['primanoten_nr']     = trim(str_replace("\r\n", '', $prepared[10]));
        $result['description_1']     = trim(str_replace("\r\n", '', $description));
        $result['bank_code']         = trim(str_replace("\r\n", '', $prepared[30]));
        $result['account_number']    = trim(str_replace("\r\n", '', $prepared[31]));
        $result['name']              = trim(str_replace("\r\n", '', $prepared[32] . $prepared[33]));
        $result['text_key_addition'] = trim(str_replace("\r\n", '', $prepared[34]));
        $result['description_2']     = trim(str_replace("\r\n", '', $description2));

        return $result;
    }

    /**
     * @param string $val
     * @return string
     */
    protected function getDate($val)
    {
        $val = '20' . $val;
        preg_match('/(\d{4})(\d{2})(\d{2})/', $val, $m);
        return $m[1] . '-' . $m[2] . '-' . $m[3];
    }
}
