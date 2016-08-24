<?php

namespace Tests\Fhp\Model\StatementOfAccount;

use Fhp\Model\StatementOfAccount\Statement;
use Fhp\Model\StatementOfAccount\StatementOfAccount;

class StatementOfAccountTest extends \PHPUnit_Framework_TestCase
{
    public function test_getter_and_setter()
    {
        $obj = new StatementOfAccount();
        $this->assertInternalType('array', $obj->getStatements());

        $s1 = new Statement();
        $s2 = new Statement();

        $obj->addStatement($s1);
        $this->assertInternalType('array', $obj->getStatements());
        $this->assertCount(1, $obj->getStatements());
        $result = $obj->getStatements();
        $this->assertSame($s1, $result[0]);

        $obj->setStatements(null);
        $this->assertInternalType('array', $obj->getStatements());
        $this->assertEmpty($obj->getStatements());

        $obj->setStatements(array($s1, $s2));
        $this->assertInternalType('array', $obj->getStatements());
        $this->assertCount(2, $obj->getStatements());
        $this->assertSame(array($s1, $s2), $obj->getStatements());
    }
}
