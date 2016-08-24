<?php

namespace Tests\Fhp\Model;

use Fhp\Model\SEPAAccount;

class SEPAAccountTest extends \PHPUnit_Framework_TestCase
{
    public function test_getter_and_setter()
    {
        $obj = new SEPAAccount();

        $this->assertNull($obj->getAccountNumber());
        $this->assertNull($obj->getBic());
        $this->assertNull($obj->getBlz());
        $this->assertNull($obj->getIban());
        $this->assertNull($obj->getSubAccount());

        $this->assertSame('123456789', $obj->setAccountNumber('123456789')->getAccountNumber());
        $this->assertSame('123456789', $obj->setBic('123456789')->getBic());
        $this->assertSame('123456789', $obj->setIban('123456789')->getIban());
        $this->assertSame('123456789', $obj->setBlz('123456789')->getBlz());
        $this->assertSame('123456789', $obj->setSubAccount('123456789')->getSubAccount());
    }
}
