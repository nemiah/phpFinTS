<?php

namespace Fhp\Response;

use Fhp\Segment\CAZ\HICAZv1;
use Fhp\Segment\HKCAZ;

class BankToCustomerAccountReportHICAZ extends Response
{
    const SEG_ACCOUNT_INFORMATION = 'HICAZ';

    /**
     * CAMT XML
     *
     * @return string
     */
    public function getBookedXML()
    {
        /** @var HICAZv1 $seg */
        $seg = $this->getSegment(static::SEG_ACCOUNT_INFORMATION);

        if ($seg->getCamtDescriptor() != HKCAZ::CAMT_FORMAT_FQ) {
            throw new \Exception('Unerwartetes CAMT XML Format ' . $seg->getCamtDescriptor() . ', erwartet war ' .  HKCAZ::CAMT_FORMAT_FQ);
        }

        $xml = $seg->getGebuchteUmsaetze()->getData();

        return $xml;
    }

    /** @deprecated only used with the deprecated Response::findSegments */
    protected function conformToUtf8($string)
    {
        return $string;
    }
}
