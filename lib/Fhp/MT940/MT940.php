<?php

namespace Fhp\MT940;

/**
 * Data format: MT 940 (Version SRG 2001)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Finanzdatenformate_2010-08-06_final_version.pdf
 * Section: B.8
 */
class MT940
{
    public const CD_CREDIT = 'credit';
    public const CD_DEBIT = 'debit';

    /**
     * @throws MT940Exception
     */
    public function parse(string $rawData): array
    {
        // The divider can be either \r\n or @@
        $divider = substr_count($rawData, "\r\n-") > substr_count($rawData, '@@-') ? "\r\n" : '@@';

        $cleanedRawData = preg_replace('#' . $divider . '([^:])#ms', '$1', $rawData);

        $booked = true;
        $result = [];
        $days = explode($divider . '-', $cleanedRawData);
        $soaDate = null;
        foreach ($days as &$day) {
            $day = explode($divider . ':', $day);

            for ($i = 0, $cnt = count($day); $i < $cnt; ++$i) {
                if (preg_match("/\+\@[0-9]+\@$/", trim($day[$i]))) {
                    $booked = false;
                }

                // handle start balance
                // 60F:C160401EUR1234,56
                if (preg_match('/^60(F|M):/', $day[$i])) {
                    // remove 60(F|M): for better parsing
                    $day[$i] = substr($day[$i], 4);
                    $soaDate = $this->getDate(substr($day[$i], 1, 6));

                    // if this statement date ist first seen set start_balance
                    // Note: all further transactions in different statements with the same soaDate will be appended
                    // there will be no new statement done for them. With bigger code changes this could be changed.
                    // For now this is shortcutted like this for fixing https://github.com/nemiah/phpFinTS/issues/367
                    if (!isset($result[$soaDate])) {
                        $result[$soaDate] = ['start_balance' => []];
                        $cdMark = substr($day[$i], 0, 1);
                        if ($cdMark === 'C') {
                            $result[$soaDate]['start_balance']['credit_debit'] = static::CD_CREDIT;
                        } elseif ($cdMark === 'D') {
                            $result[$soaDate]['start_balance']['credit_debit'] = static::CD_DEBIT;
                        }

                        $amount = str_replace(',', '.', substr($day[$i], 10));
                        $result[$soaDate]['start_balance']['amount'] = $amount;
                    }
                } elseif (
                    // found transaction
                    // trx:61:1603310331DR637,39N033NONREF
                    str_starts_with($day[$i], '61:')
                    && isset($day[$i + 1])
                    && str_starts_with($day[$i + 1], '86:')
                ) {
                    $transaction = substr($day[$i], 3);
                    $description = substr($day[$i + 1], 3);

                    if (!isset($result[$soaDate]['transactions'])) {
                        $result[$soaDate]['transactions'] = [];
                    }

                    // short form for better handling
                    $trx = &$result[$soaDate]['transactions'];

                    preg_match('/^\d{6}(\d{4})?(C|D|RC|RD)([A-Z]{1})?([^N]+)N/', $transaction, $trxMatch);
                    if ($trxMatch[2] === 'C' || $trxMatch[2] === 'RC') {
                        $trx[count($trx)]['credit_debit'] = static::CD_CREDIT;
                    } elseif ($trxMatch[2] === 'D' || $trxMatch[2] === 'RD') {
                        $trx[count($trx)]['credit_debit'] = static::CD_DEBIT;
                    } else {
                        throw new MT940Exception('cd mark not found in: ' . $transaction);
                    }

                    $trx[count($trx) - 1]['is_storno'] = ($trxMatch[2] === 'RC' or $trxMatch[2] === 'RD');

                    $amount = $trxMatch[4];
                    $amount = str_replace(',', '.', $amount);
                    $trx[count($trx) - 1]['amount'] = $amount;

                    // :61:1605110509D198,02NMSCNONREF
                    // 16 = year
                    // 0511 = valuta date
                    // 0509 = booking date

                    $year = substr($transaction, 0, 2);
                    $valutaDate = $this->getDate($year . substr($transaction, 2, 4));
                    $bookingDatePart = substr($transaction, 6, 4);

                    if (preg_match('/^\d{4}$/', $bookingDatePart) === 1) {
                        // try to guess the correct year of the booking date

                        $valutaDateTime = new \DateTime($valutaDate);
                        $bookingDateTime = new \DateTime($this->getDate($year . $bookingDatePart));

                        // the booking date can be before or after the valuata date
                        // and one of them can be in another year for example 12-31 and 01-01

                        $diff = $valutaDateTime->diff($bookingDateTime);

                        // if diff is more than half a year
                        if ($diff->days > 182) {
                            // and positive
                            if ($diff->invert === 0) {
                                // its in the last year
                                --$year;
                            }
                            // and negative
                            else {
                                // its in the next year
                                ++$year;
                            }
                        }
                        $bookingDate = $this->getDate($year . $bookingDatePart);
                    } else {
                        // if booking date not set in :61, then we have to take it from :60F
                        $bookingDate = $soaDate;
                    }

                    $trx[count($trx) - 1]['booking_date'] = $bookingDate;
                    $trx[count($trx) - 1]['valuta_date'] = $valutaDate;
                    $trx[count($trx) - 1]['booked'] = $booked;

                    $trx[count($trx) - 1]['description'] = $this->parseDescription($description, $trx[count($trx) - 1]);
                } elseif (
                    preg_match('/^62F:/', $day[$i]) // handle end balance
                ) {
                    // remove 62F: for better parsing
                    $day[$i] = substr($day[$i], 4);
                    $soaDate = $this->getDate(substr($day[$i], 1, 6));

                    if (isset($result[$soaDate])) {
                        #$result[$soaDate] = ['end_balance' => []];
                    
                        $amount = str_replace(',', '.', substr($day[$i], 10, -1));
                        $cdMark = substr($day[$i], 0, 1);
                        if ($cdMark == 'C') {
                            $result[$soaDate]['end_balance']['credit_debit'] = static::CD_CREDIT;
                        } elseif ($cdMark == 'D') {
                            $result[$soaDate]['end_balance']['credit_debit'] = static::CD_DEBIT;
                            $amount *= -1;
                        }

                        $result[$soaDate]['end_balance']['amount'] = $amount;
                    }
                }
            }
        }

        return $result;
    }

    protected function parseDescription($descr, $transaction): array
    {
        // Geschäftsvorfall-Code
        $gvc = substr($descr, 0, 3);

        $prepared = [];
        $result = [];

        // prefill with empty values
        for ($i = 0; $i <= 63; ++$i) {
            $prepared[$i] = null;
        }

        $descr = str_replace('? ', '?', $descr);

        preg_match_all('/\?(\d{2})([^\?]+)/', $descr, $matches, PREG_SET_ORDER);

        $descriptionLines = [];
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
                $descriptionLines[] = $m[2];
            }
            $prepared[$index] = $m[2];
        }

        $description = $this->extractStructuredDataFromRemittanceLines($descriptionLines, $gvc, $prepared, $transaction);

        $result['booking_code'] = $gvc;
        $result['booking_text'] = trim($prepared[0] ?? '');
        $result['description'] = $description;
        $result['primanoten_nr'] = trim($prepared[10] ?? '');
        $result['description_1'] = trim($description1);
        $result['bank_code'] = trim($prepared[30] ?? '');
        $result['account_number'] = trim($prepared[31] ?? '');
        $result['name'] = trim(($prepared[32] ?? '') . ($prepared[33] ?? ''));
        $result['text_key_addition'] = trim($prepared[34] ?? '');
        $result['description_2'] = $description2;
        $result['desc_lines'] = $descriptionLines;

        return $result;
    }

    /**
     * @param string[] $descriptionLines that contain the remittance information
     * @param string $gvc Geschätsvorfallcode; Out-Parameter, might be changed from information in remittance info
     * @param string[] $rawLines All the lines in the Multi-Purpose-Field 86; Out-Parameter, might be changed from information in remittance info
     */
    protected function extractStructuredDataFromRemittanceLines($descriptionLines, string &$gvc, array &$rawLines, array $transaction): array
    {
        $description = [];
        if (empty($descriptionLines) || strlen($descriptionLines[0]) < 5 || $descriptionLines[0][4] !== '+') {
            $description['SVWZ'] = implode('', $descriptionLines);
        } else {
            $lastType = null;
            foreach ($descriptionLines as $line) {
                if (strlen($line) >= 5 && $line[4] === '+') {
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

    protected function getDate(string $val): string
    {
        $val = '20' . $val;
        preg_match('/(\d{4})(\d{2})(\d{2})/', $val, $m);
        return $m[1] . '-' . $m[2] . '-' . $m[3];
    }
}
