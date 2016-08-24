<?php

namespace Tests\Fhp\Model\StatementOfAccount;

use Fhp\Model\StatementOfAccount\Statement;
use Fhp\Model\StatementOfAccount\Transaction;

class StatementTest extends \PHPUnit_Framework_TestCase
{
    public function test_getter_and_setter()
    {
        $obj = new Statement();

        $this->assertInternalType('array', $obj->getTransactions());
        $this->assertEmpty($obj->getTransactions());
        $this->assertSame(0.0, $obj->getStartBalance());
        $this->assertNull($obj->getCreditDebit());
        $this->assertNull($obj->getDate());

        $trx1 = new Transaction();
        $trx2 = new Transaction();

        $obj->addTransaction($trx1);
        $this->assertCount(1, $obj->getTransactions());

        $obj->addTransaction($trx2);
        $this->assertCount(2, $obj->getTransactions());

        $obj->setTransactions(null);
        $this->assertNull($obj->getTransactions());

        $obj->setTransactions(array());
        $this->assertInternalType('array', $obj->getTransactions());
        $this->assertCount(0, $obj->getTransactions());

        $trxArray = array($trx1, $trx2);
        $obj->setTransactions($trxArray);
        $this->assertInternalType('array', $obj->getTransactions());
        $this->assertCount(2, $obj->getTransactions());

        $obj->setStartBalance(20.00);
        $this->assertInternalType('float', $obj->getStartBalance());
        $this->assertSame(20.00, $obj->getStartBalance());

        $obj->setStartBalance('string');
        $this->assertSame(0.0, $obj->getStartBalance());

        $obj->setCreditDebit(Statement::CD_CREDIT);
        $this->assertSame(Statement::CD_CREDIT, $obj->getCreditDebit());

        $obj->setCreditDebit(Statement::CD_DEBIT);
        $this->assertSame(Statement::CD_DEBIT, $obj->getCreditDebit());

        $date = new \DateTime();
        $obj->setDate($date);
        $this->assertSame($date, $obj->getDate());
    }
}
