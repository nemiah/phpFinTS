<?php

namespace Fhp\Response;

use Fhp\Model\SEPAAccount;

class GetSEPAAccounts extends Response
{
    const SEG_ACCOUNT_INFORMATION = 'HISPA';

    protected $accounts = [];

    public function getSEPAAccounts()
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

    protected function createModelFromArray(array $array)
    {
        $account = new SEPAAccount();
        $account->setIsSepaCapable($array[0] == 'J' ? true : false);
        $account->setIban($array[1]);
        $account->setBic($array[2]);
        $account->setAccountNumber($array[3]);
        $account->setSubAccount($array[4]);
        $account->setBlz($array[6]);

        return $account;
    }
}
