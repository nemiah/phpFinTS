<?php

namespace Tests\Fhp\Integration\DKB;

class GetSEPAAccountsTest extends DKBIntegrationTestBase
{
    public const GET_ACCOUNTS_REQUEST = "HKSPA:3:2'";
    public const GET_ACCOUNTS_RESPONSE = "HIRMG:3:2+0010::Nachricht entgegengenommen.'HIRMS:4:2:3+0020::Der Auftrag wurde ausgefuhrt.'HISPA:5:2:3+J:DExxABCDEFGH1234567890:BYLADEM1001:1234567890::280:ABCDEFGH'";

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

        $this->assertCount(1, $accounts);
        $account = $accounts[0];
        $this->assertEquals('DExxABCDEFGH1234567890', $account->getIban());
        $this->assertEquals('BYLADEM1001', $account->getBic());
        $this->assertEquals('1234567890', $account->getAccountNumber());
        $this->assertEmpty($account->getSubAccount());
        $this->assertEquals('ABCDEFGH', $account->getBlz());
    }
}
