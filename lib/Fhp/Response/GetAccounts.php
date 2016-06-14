<?php

namespace Fhp\Response;

use Fhp\Model\Account;

class GetAccounts extends Response
{
    const SEG_ACCOUNT_INFORMATION = 'HIUPD';

    protected $accounts = [];

    public function getAccounts()
    {
        $accounts = $this->findSegments(static::SEG_ACCOUNT_INFORMATION);

        foreach ($accounts as $account) {
            $accountParts = $this->splitSegment($account);
            $this->accounts[] = $this->createModelFromArray($accountParts);
        }

        return $this->accounts;
    }

    protected function createModelFromArray(array $array)
    {
        $account = new Account();
        list($accountNumber, $x, $countryCode, $bankCode) = explode(':', $array[1]);
        $account->setId($array[1]);
        $account->setAccountNumber($accountNumber);
        $account->setBankCode($bankCode);
        $account->setIban($array[2]);
        $account->setCustomerId($array[3]);
        $account->setCurrency($array[5]);
        $account->setAccountOwnerName($array[6]);
        $account->setAccountDescription($array[8]);

        return $account;
    }
}
