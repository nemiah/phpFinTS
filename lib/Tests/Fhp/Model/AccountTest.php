<?php

namespace Tests\Fhp\Model;

use Fhp\Model\Account;

class AccountTest extends \PHPUnit_Framework_TestCase
{
    public function test_getter_and_setter()
    {
        $m = new Account();
        $this->assertNull($m->getId());
        $this->assertNull($m->getAccountDescription());
        $this->assertNull($m->getAccountNumber());
        $this->assertNull($m->getAccountOwnerName());
        $this->assertNull($m->getBankCode());
        $this->assertNull($m->getCurrency());
        $this->assertNull($m->getCustomerId());
        $this->assertNull($m->getIban());

        // test description
        $m->setAccountDescription('Description');
        $this->assertSame('Description', $m->getAccountDescription());

        // test account number
        $m->setAccountNumber('123123123');
        $this->assertSame('123123123', $m->getAccountNumber());

        // test account owner name
        $m->setAccountOwnerName('The Owner');
        $this->assertSame('The Owner', $m->getAccountOwnerName());

        // test bank code
        $m->setBankCode('123123123');
        $this->assertSame('123123123', $m->getBankCode());

        // test currency
        $m->setCurrency('EUR');
        $this->assertSame('EUR', $m->getCurrency());

        // test customer ID
        $m->setCustomerId('123123123');
        $this->assertSame('123123123', $m->getCustomerId());

        // test iban
        $m->setIban('DE123123123123');
        $this->assertSame('DE123123123123', $m->getIban());
    }
}
