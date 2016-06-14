<?php

namespace Tests\Fhp\Model;

use Fhp\Model\Saldo;

class SaldoTest extends \PHPUnit_Framework_TestCase
{
    public function test_getter_and_setter()
    {
        $s = new Saldo();
        $this->assertNull($s->getCurrency());
        $this->assertNull($s->getAmount());
        $this->assertNull($s->getValuta());

        // test currency
        $s->setCurrency('EUR');
        $this->assertSame('EUR', $s->getCurrency());

        // test amount
        $s->setAmount(12.00);
        $this->assertSame(12.00, $s->getAmount());

        $d = new \DateTime();
        $s->setValuta($d);
        $this->assertEquals($d, $s->getValuta());
    }
}
