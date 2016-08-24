<?php

namespace Tests\Fhp\Model\StatementOfAccount;

use Fhp\Model\StatementOfAccount\Transaction;

class TransactionTest extends \PHPUnit_Framework_TestCase
{
    public function test_getter_and_setter()
    {
        $obj = new Transaction();

        $this->assertNull($obj->getAccountNumber());
        $this->assertNull($obj->getAmount());
        $this->assertNull($obj->getBankCode());
        $this->assertNull($obj->getBookingDate());
        $this->assertNull($obj->getBookingText());
        $this->assertNull($obj->getCreditDebit());
        $this->assertNull($obj->getDescription1());
        $this->assertNull($obj->getDescription2());
        $this->assertNull($obj->getName());
        $this->assertNull($obj->getValutaDate());

        $date = new \DateTime();
        $this->assertSame('123456789', $obj->setAccountNumber('123456789')->getAccountNumber());
        $this->assertSame(20.00, $obj->setAmount(20.00)->getAmount());
        $this->assertSame('123456789', $obj->setBankCode('123456789')->getBankCode());
        $this->assertSame($date, $obj->setBookingDate($date)->getBookingDate());
        $this->assertSame($date, $obj->setValutaDate($date)->getValutaDate());
        $this->assertSame('text', $obj->setBookingText('text')->getBookingText());
        $this->assertSame(Transaction::CD_DEBIT, $obj->setCreditDebit(Transaction::CD_DEBIT)->getCreditDebit());
        $this->assertSame(Transaction::CD_CREDIT, $obj->setCreditDebit(Transaction::CD_CREDIT)->getCreditDebit());
        $this->assertSame('desc1', $obj->setDescription1('desc1')->getDescription1());
        $this->assertSame('desc2', $obj->setDescription2('desc2')->getDescription2());
        $this->assertSame('name', $obj->setName('name')->getName());
    }
}
