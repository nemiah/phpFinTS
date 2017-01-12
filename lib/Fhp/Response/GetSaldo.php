<?php

namespace Fhp\Response;

use Fhp\Model\Saldo;

/**
 * Class GetSaldo
 * @package Fhp\Response
 */
class GetSaldo extends Response
{
    const SEG_ACCOUNT_INFORMATION = 'HISAL';
    const SALDO_DEBIT = 'D';
    const SALDO_CREDIT = 'C';

    /**
     * Creates a Saldo object from response body.
     *
     * @return Saldo|null
     * @throws \Exception
     */
    public function getSaldoModel()
    {
        $model = null;
        $saldoSec = $this->findSegment(static::SEG_ACCOUNT_INFORMATION);

        if (is_string($saldoSec)) {
            $saldoSec = $this->splitSegment($saldoSec);
            array_shift($saldoSec); // get rid of header
            $model = $this->createModelFromArray($saldoSec);
        }

        return $model;
    }

    /**
     * Creates a Saldo model from array.
     *
     * @param array $array
     * @return Saldo
     * @throws \Exception
     */
    protected function createModelFromArray(array $array)
    {
        $model = new Saldo();
        $saldoDeg = $this->splitDeg($array[3]);

        $amount = str_replace(',', '.', $saldoDeg[1]);
        $creditDebit = trim($saldoDeg[0]);

        if (static::SALDO_DEBIT == $creditDebit) {
            $amount = - (float) $amount;
        } elseif (static::SALDO_CREDIT == $creditDebit) {
            $amount = (float) $amount;
        } else {
            throw new \Exception('Invalid Soll-Haben-Kennzeichen: ' . $creditDebit);
        }

        $model->setAmount($amount);
        $model->setCurrency($saldoDeg[2]);

        $valutaDate = $saldoDeg[3];
        preg_match('/(\d{4})(\d{2})(\d{2})/', $valutaDate, $m);
        $valutaDate = new \DateTime($m[1] . '-' . $m[2] . '-' . $m[3]);
        $model->setValuta($valutaDate);

        return $model;
    }
}
