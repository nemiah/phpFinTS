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
     * @param string $rawData
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
     * @throws MT940Exception
     */
    protected function parseToArray()
    {
        // The divider can be either \r\n or @@
        $divider = substr_count($this->rawData, "\r\n-") > substr_count($this->rawData, '@@-') ? "\r\n" : '@@';

        $cleanedRawData = preg_replace('#' . $divider . '([^:])#ms', '$1', $this->rawData);

        $booked = true;
        $result = array();
        $days = explode($divider . '-', $cleanedRawData);
        foreach ($days as &$day) {

            $day = explode($divider . ':', $day);

            for ($i = 0, $cnt = count($day); $i < $cnt; $i++) {
                if (preg_match("/^\+\@[0-9]+\@$/", trim($day[$i]))) {
                    $booked = false;
                }

                // handle start balance
                // 60F:C160401EUR1234,56
                if (preg_match('/^60(F|M):/', $day[$i])) {
                    // remove 60(F|M): for better parsing
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

                    if (!isset($result[$this->soaDate]['transactions'])) {
                        $result[$this->soaDate]['transactions'] = array();
                    }

                    // short form for better handling
                    $trx = &$result[$this->soaDate]['transactions'];

                    preg_match('/^\d{6}(\d{4})?(C|D|RC|RD)([A-Z]{1})?([^N]+)N/', $transaction, $trxMatch);
                    if ($trxMatch[2] == 'C' OR $trxMatch[2] == 'RC') {
                        $trx[count($trx)]['credit_debit'] = static::CD_CREDIT;
                    } elseif ($trxMatch[2] == 'D' OR $trxMatch[2] == 'RD') {
                        $trx[count($trx)]['credit_debit'] = static::CD_DEBIT;
                    } else {
                        throw new MT940Exception('cd mark not found in: ' . $transaction);
                    }

                    $amount = $trxMatch[4];
                    $amount = str_replace(',', '.', $amount);
                    $trx[count($trx) - 1]['amount'] = $amount;

                    $description = $this->parseDescription($description);
                    $trx[count($trx) - 1]['description'] = $description;

                    // :61:1605110509D198,02NMSCNONREF
                    // 16 = year
                    // 0511 = valuta date
                    // 0509 = booking date
                    $year = substr($transaction, 0, 2);
                    $valutaDate = $this->getDate($year . substr($transaction, 2, 4));

                    $bookingDate = substr($transaction, 6, 4);
                    if (preg_match('/^\d{4}$/', $bookingDate)) {
                        // if valuta date is earlier than booking date, then it must be in the new year.
                        $year = substr($transaction, 2, 2) < substr($transaction, 6, 2) ? --$year : $year;
                        if (substr($transaction, 2, 2) == '12' && substr($transaction, 6, 2) == '01') {
                            $year++;
                        } elseif (substr($transaction, 2, 2) == '01' && substr($transaction, 6, 2) == '12') {
                            $year--;
                        }
                        $bookingDate = $this->getDate($year . $bookingDate);
                    } else {
                        // if booking date not set in :61, then we have to take it from :60F
                        $bookingDate = $this->soaDate;
                    }

                    $trx[count($trx) - 1]['booking_date'] = $bookingDate;
                    $trx[count($trx) - 1]['valuta_date'] = $valutaDate;
                    $trx[count($trx) - 1]['booked'] = $booked;
                }
            }
        }

        return $result;
    }

    protected function parseDescription($descr)
    {
        // Geschäftsvorfall-Code
        $gvc = substr($descr, 0, 3);

        $prepared = array();
        $result = array();

        // prefill with empty values
        for ($i = 0; $i <= 63; $i++) {
            $prepared[$i] = null;
        }

        $descr = str_replace('? ', '?', $descr);

        preg_match_all('/\?(\d{2})([^\?]+)/', $descr, $matches, PREG_SET_ORDER);

        $descriptionLines = array();
        $description1 = ''; // Legacy, could be removed.
        $description2 = ''; // Legacy, could be removed.
        foreach ($matches as $m) {
            $index = (int) $m[1];

            if ((20 <= $index && $index <= 29) || (60 <= $index && $index <= 63)) {
                if (20 <= $index && $index <= 29) {
                    $description1 .= $m[2];
                } else {
                    $description2 .= $m[2];
                }
                if (!empty($m[2])) {
                    $descriptionLines[] = $m[2];
                }
            }
            $prepared[$index] = $m[2];
        }

        $description = $this->extractStructuredDataFromRemittanceLines($descriptionLines, $gvc, $prepared);

        $result['booking_code']      = $gvc;
        $result['booking_text']      = trim($prepared[0]);
        $result['description']       = $description;
        $result['primanoten_nr']     = trim($prepared[10]);
        $result['description_1']     = trim($description1);
        $result['bank_code']         = trim($prepared[30]);
        $result['account_number']    = trim($prepared[31]);
        $result['name']              = trim($prepared[32] . $prepared[33]);
        $result['text_key_addition'] = trim($prepared[34]);
        $result['description_2']     = $description2;
        $result['desc_lines']        = $descriptionLines;

        return $result;
    }

    /**
     * @param string[] $lines that contain the remittance information
     * @param string $gvc Geschätsvorfallcode; Out-Parameter, might be changed from information in remittance info
     * @param string $rawLines All the lines in the Multi-Purpose-Field 86; Out-Parameter, might be changed from information in remittance info
     * @return array
     */
    protected function extractStructuredDataFromRemittanceLines($descriptionLines, &$gvc, &$rawLines)
    {
        $description = array();
        if (empty($descriptionLines) || strlen($descriptionLines[0]) < 5 || $descriptionLines[0][4] !== '+') {
            $description['SVWZ'] = implode('', $descriptionLines);
        } else {
            $lastType = null;
            foreach ($descriptionLines as $line) {
                if (strlen($line) > 5 && $line[4] === '+') {
                    if ($lastType != null) {
                        $description[$lastType] = trim($description[$lastType]);
                    }
                    $lastType = substr($line, 0, 4);
                    $description[$lastType] = substr($line, 5);
                } else {
                    $description[$lastType] .= $line;
                }
                if (strlen($line) < 27) {
                    // Usually, lines are 27 characters long. In case characters are missing, then it's either the end
                    // of the current type or spaces have been trimmed from the end. We want to collapse multiple spaces
                    // into one and we don't want to leave trailing spaces behind. So add a single space here to make up
                    // for possibly missing spaces, and if it's the end of the type, it will be trimmed off later.
                    $description[$lastType] .= ' ';
                }
            }
            $description[$lastType] = trim($description[$lastType]);
        }

        return $description;
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
