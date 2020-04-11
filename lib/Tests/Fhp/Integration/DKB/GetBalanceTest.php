<?php

namespace Tests\Fhp\Integration\DKB;

use Fhp\Action\GetBalance;

class GetBalanceTest extends DKBIntegrationTestBase
{
    const GET_BALANCE_REQUEST = "HKSAL:3:5+1234567890::280:12030000+J'";
    const GET_BALANCE_RESPONSE = "HIRMG:3:2+3060::Bitte beachten Sie die enthaltenen Warnungen/Hinweise.'HIRMS:4:2:3+0020::Der Auftrag wurde ausgefuhrt.'HIRMS:5:2:4+3076::Starke Kundenauthentifizierung nicht notwendig.'HITAN:6:6:4+4++noref+nochallenge+++SomePhone1'HISAL:7:5:3+1234567890::280:12030000+Sichteinlagen+EUR+C:123,45:EUR:20200409+C:0,:EUR:20200409+0,:EUR+123,45:EUR'";

    /**
     * @throws \Throwable
     */
    public function test_getBalance()
    {
        $this->initDialog();

        $this->expectMessage(static::GET_BALANCE_REQUEST, static::GET_BALANCE_RESPONSE);
        $getBalance = GetBalance::create($this->getTestAccount(), true);
        $this->fints->execute($getBalance);
        $this->assertFalse($getBalance->needsTan());
        $balances = $getBalance->getBalances();

        $this->assertCount(1, $balances);
        $balance = $balances[0];
        $this->assertEquals('1234567890', $balance->getAccountInfo()->getAccountNumber());
        $this->assertEquals('12030000', $balance->getAccountInfo()->getBankIdentifier());
        $this->assertEquals('Sichteinlagen', $balance->getKontoproduktbezeichnung());
        $this->assertEquals(+123.45, $balance->getGebuchterSaldo()->getAmount());
        $this->assertEquals(new \DateTime('2020-04-09T00:00:00'), $balance->getGebuchterSaldo()->getTimestamp());
        $this->assertEquals(0, $balance->getSaldoDerVorgemerktenUmsaetze()->getAmount());
    }
}
