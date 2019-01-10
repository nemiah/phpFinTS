<?php

namespace Fhp\Response;

use Fhp\Segment\HKCAZ;

/**
 * Class BankToCustomerAccountReportHICAZ.php
 * @package Fhp\Response
 */
class BankToCustomerAccountReportHICAZ extends Response
{
    const SEG_ACCOUNT_INFORMATION = 'HICAZ';

    /**
     * Gets the raw MT940 string from response.
     *
     * @return string
     */
    public function getBookedXML()
    {
        $seg = $this->findSegment(static::SEG_ACCOUNT_INFORMATION);

        $parts = $this->splitSegment($seg);

        if (count($parts) > 3) {

            if ($parts[2] == HKCAZ::CAMT_FORMAT . '.xsd') {
                list($empty, $length, $xml) = explode('@', $parts[3], 3);
                if ($empty == '' && intval($length) == strlen($xml)) {
                    return $xml;
                }

                throw new \Exception('Fehler im XML Payload');

            } else {
                throw new \Exception('Unerwartetes CAMT XML Format (' . $parts[2] . ')');
            }
        }

        return '';
    }
    
    protected function conformToUtf8($string)
    {
        return $string;
    }
}
