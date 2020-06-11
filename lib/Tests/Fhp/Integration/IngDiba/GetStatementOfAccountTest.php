<?php

namespace Tests\Fhp\Integration\IngDiba;

use Fhp\Action\GetStatementOfAccount;

class GetStatementOfAccountTest extends IngDibaIntegrationTestBase
{
    // Statement request (HKKAZ). Note: We don't actually have a real response here, so this integration test does not
    // cover ING-DiBa's MT940 format.
    const GET_STATEMENT_REQUEST = "HNHBK:1:3+000000000434+300+FAKEDIALOGIDabcdefghijklmnopqr+2'HNVSK:998:3+PIN:1+998+1+1::FAKEKUNDENSYSTEMIDabcdefghijkl+1:20190102:030405+2:2:13:@8@00000000:5:1+280:50010517:test?@user:V:0:0+0'HNVSD:999:1+@213@HNSHK:2:4+PIN:1+999+9999999+1+1+1::FAKEKUNDENSYSTEMIDabcdefghijkl+1+1:20190102:030405+1:999:1+6:10:19+280:50010517:test?@user:S:0:0'HKKAZ:3:5+1234567890::280:50010517+N+20200301+20200325'HNSHA:4:2+9999999++123456''HNHBS:5:1+2'";
    const GET_STATEMENT_RESPONSE = "HNHBK:1:3+000000000357+300+FAKEDIALOGIDabcdefghijklmnopqr+2+FAKEDIALOGIDabcdefghijklmnopqr:3'HNVSK:998:3+PIN:1+998+1+2::FAKEKUNDENSYSTEMIDabcdefghijkl+1+2:2:13:@8@00000000:6:1+280:50010517:test?@user:V:0:0+0'HNVSD:999:1+@119@HIRMG:2:2:+0010::Die Nachricht wurde entgegengenommen.'HIRMS:3:2:3+0020::Der Auftrag wurde ausgefuhrt.'HIKAZ:4:5:3+@0@''HNHBS:5:1+2'";

    /**
     * @throws \Throwable
     */
    public function test_getStatementOfAccountTest()
    {
        $this->initDialog();
        $this->expectMessage(static::GET_STATEMENT_REQUEST, static::GET_STATEMENT_RESPONSE);
        $getStatement = GetStatementOfAccount::create($this->getTestAccount(),
            new \DateTime('2020-03-01'), new \DateTime('2020-03-25'), false);
        $this->fints->execute($getStatement);
        $this->assertFalse($getStatement->needsTan());
        $this->assertEmpty($getStatement->getStatement()->getStatements());
    }
}
