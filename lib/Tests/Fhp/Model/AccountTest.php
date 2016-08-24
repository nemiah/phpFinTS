<?php

namespace Tests\Fhp\Model;

use Fhp\Model\Account;

class AccountTest extends \PHPUnit_Framework_TestCase
{
    public function test_getter_and_setter()
    {
        $obj = new Account();
        $this->assertNull($obj->getId());
        $this->assertNull($obj->getAccountDescription());
        $this->assertNull($obj->getAccountNumber());
        $this->assertNull($obj->getAccountOwnerName());
        $this->assertNull($obj->getBankCode());
        $this->assertNull($obj->getCurrency());
        $this->assertNull($obj->getCustomerId());
        $this->assertNull($obj->getIban());

        // test id
        $obj->setId(10);
        $this->assertSame(10, $obj->getId());

        // test description
        $obj->setAccountDescription('Description');
        $this->assertSame('Description', $obj->getAccountDescription());

        // test account number
        $obj->setAccountNumber('123123123');
        $this->assertSame('123123123', $obj->getAccountNumber());

        // test account owner name
        $obj->setAccountOwnerName('The Owner');
        $this->assertSame('The Owner', $obj->getAccountOwnerName());

        // test bank code
        $obj->setBankCode('123123123');
        $this->assertSame('123123123', $obj->getBankCode());

        // test currency
        $obj->setCurrency('EUR');
        $this->assertSame('EUR', $obj->getCurrency());

        // test customer ID
        $obj->setCustomerId('123123123');
        $this->assertSame('123123123', $obj->getCustomerId());

        // test iban
        $obj->setIban('DE123123123123');
        $this->assertSame('DE123123123123', $obj->getIban());
    }
}
