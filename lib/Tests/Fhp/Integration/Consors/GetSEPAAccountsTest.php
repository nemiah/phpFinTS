<?php

namespace Tests\Fhp\Integration\Consors;

class GetSEPAAccountsTest extends ConsorsIntegrationTestBase
{
    const GET_ACCOUNTS_REQUEST = "HKSPA:3:1'";
    const GET_ACCOUNTS_RESPONSE = "HIRMG:2:2:+0010::Die Nachricht wurde entgegengenommen.'HIRMS:3:2:3+0020::Der Auftrag wurde ausgefuhrt.'HISPA:4:1:3+J:DExxABCDEFGH0123456789:CSDBDE71XXX:123456789::280:ABCDEFGH+J:DE21PRIVATE_0987654321:CSDBDE71XXX:987654321::280:PRIVATE_+J:DE03PRIVATE_0PRIVATE__:CSDBDE71XXX:PRIVATE__::280:PRIVATE_+N:::987456123::280:PRIVATE_'";

    /**
     * @throws \Throwable
     */
    public function test_getSEPAAccounts()
    {
        $this->initDialog();

        $this->expectMessage(static::GET_ACCOUNTS_REQUEST, static::GET_ACCOUNTS_RESPONSE);
        $getAccounts = new \Fhp\Action\GetSEPAAccounts();
        $this->fints->execute($getAccounts);
        $accounts = $getAccounts->getAccounts();

        $this->assertCount(4, $accounts);
        $account = $accounts[0];
        $this->assertEquals('DExxABCDEFGH0123456789', $account->getIban());
        $this->assertEquals('CSDBDE71XXX', $account->getBic());
        $this->assertEquals('123456789', $account->getAccountNumber());
        $this->assertEmpty($account->getSubAccount());
        $this->assertEquals('ABCDEFGH', $account->getBlz());
    }
}
