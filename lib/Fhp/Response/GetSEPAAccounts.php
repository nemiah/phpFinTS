<?php

namespace Fhp\Response;

use Fhp\Model\SEPAAccount;

/**
 * Class GetSEPAAccounts
 * @package Fhp\Response
 */
class GetSEPAAccounts extends Response
{
    const SEG_ACCOUNT_INFORMATION = 'HISPA';

    /** @var array */
    protected $accounts = array();

    /**
     * Creates SEPA Account array list with SEPAAccount models.
     *
     * @return SEPAAccount[]
     */
    public function getSEPAAccountsArray()
    {
        $accounts = $this->findSegment(static::SEG_ACCOUNT_INFORMATION);

        if (is_string($accounts)) {
            $accounts = $this->splitSegment($accounts);
            array_shift($accounts);
            foreach ($accounts as $account) {
                $array = $this->splitDeg($account);
                $this->accounts[] = $this->createModelFromArray($array);
            }
        }

        return $this->accounts;
    }

    /**
     * Creates a SEPAAccount model from array.
     *
     * @param array $array
     * @return SEPAAccount
     */
    protected function createModelFromArray(array $array)
    {
        $account = new SEPAAccount();
        $account->setIban($array[1]);
        $account->setBic($array[2]);
        $account->setAccountNumber($array[3]);
        $account->setSubAccount($array[4]);
        $account->setBlz($array[6]);

        return $account;
    }
}
