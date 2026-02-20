<?php

namespace Fhp\Tests\Unit;

use Fhp\Action\SendSEPADirectDebit;
use Fhp\Model\SEPAAccount;

class SendSEPADirectDebitTest extends FinTsTestCase
{
    public function testCanSendLargeFiles()
    {
        $account = new SEPAAccount();

        $painString = file_get_contents(__DIR__ . '/../resources/pain.008.002.02.xml');

        $sepa = SendSEPADirectDebit::create($account, $painString);

        $this->assertInstanceOf(SendSEPADirectDebit::class, $sepa);
    }
}
