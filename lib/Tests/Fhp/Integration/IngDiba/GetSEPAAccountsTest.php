<?php

namespace Tests\Fhp\Integration\IngDiba;

class GetSEPAAccountsTest extends IngDibaIntegrationTestBase
{
    public const GET_ACCOUNTS_REQUEST = "HNHBK:1:3+000000000389+300+FAKEDIALOGIDabcdefghijklmnopqr+2'HNVSK:998:3+PIN:1+998+1+1::FAKEKUNDENSYSTEMIDabcdefghijkl+1:20190102:030405+2:2:13:@8@00000000:5:1+280:50010517:test?@user:V:0:0+0'HNVSD:999:1+@168@HNSHK:2:4+PIN:1+999+9999999+1+1+1::FAKEKUNDENSYSTEMIDabcdefghijkl+1+1:20190102:030405+1:999:1+6:10:19+280:50010517:test?@user:S:0:0'HKSPA:3:1'HNSHA:4:2+9999999++123456''HNHBS:5:1+2'";
    public const GET_ACCOUNTS_RESPONSE = "HNHBK:1:3+000000000477+300+FAKEDIALOGIDabcdefghijklmnopqr+2+FAKEDIALOGIDabcdefghijklmnopqr:2'HNVSK:998:3+PIN:1+998+1+2::FAKEKUNDENSYSTEMIDabcdefghijkl+1+2:2:13:@8@00000000:6:1+280:50010517:test?@user:V:0:0+0'HNVSD:999:1+@239@HIRMG:2:2:+0010::Die Nachricht wurde entgegengenommen.'HIRMS:3:2:3+0020::Der Auftrag wurde ausgefuhrt.'HISPA:4:1:3+J:DExxABCDEFGH1234567890:INGDDEFFXXX:1234567890::280:50010517+J:DExxABCDEFGH1234567842:INGDDEFFXXX:1234567842::280:50010517''HNHBS:5:1+2'";

    /**
     * @throws \Throwable
     */
    public function testGetSEPAAccounts()
    {
        $this->initDialog();

        $this->expectMessage(static::GET_ACCOUNTS_REQUEST, static::GET_ACCOUNTS_RESPONSE);
        $getAccounts = new \Fhp\Action\GetSEPAAccounts();
        $this->fints->execute($getAccounts);
        $accounts = $getAccounts->getAccounts();

        $this->assertCount(2, $accounts);
        $account1 = $accounts[0];
        $this->assertEquals('DExxABCDEFGH1234567890', $account1->getIban());
        $this->assertEquals('INGDDEFFXXX', $account1->getBic());
        $this->assertEquals('1234567890', $account1->getAccountNumber());
        $this->assertEmpty($account1->getSubAccount());
        $this->assertEquals(static::TEST_BANK_CODE, $account1->getBlz());
        $account2 = $accounts[1];
        $this->assertEquals('DExxABCDEFGH1234567842', $account2->getIban());
        $this->assertEquals('INGDDEFFXXX', $account2->getBic());
        $this->assertEquals('1234567842', $account2->getAccountNumber());
        $this->assertEmpty($account2->getSubAccount());
        $this->assertEquals(static::TEST_BANK_CODE, $account2->getBlz());
    }
}
