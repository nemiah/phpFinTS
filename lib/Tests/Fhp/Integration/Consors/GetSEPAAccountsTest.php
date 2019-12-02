<?php

namespace Tests\Fhp\Integration\Consors;

class GetSEPAAccountsTest extends ConsorsIntegrationTestBase
{
    const GET_ACCOUNTS_REQUEST = "HKSPA:3:1'";
    const GET_ACCOUNTS_RESPONSE = "HIRMG:2:2:+0010::Die Nachricht wurde entgegengenommen.'HIRMS:3:2:3+0020::Der Auftrag wurde ausgefuhrt.'HISPA:4:1:3+J:DExxABCDEFGH0123456789:CSDBDE71XXX:123456789::280:ABCDEFGH+J:DE21<PRIVAT>0987654321:CSDBDE71XXX:987654321::280:<PRIVAT>+J:DE03<PRIVAT>0<PRIVATE>:CSDBDE71XXX:<PRIVATE>::280:<PRIVAT>+N:::987456123::280:<PRIVAT>'";

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
