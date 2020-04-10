<?php

namespace Fhp\Response;

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
}
