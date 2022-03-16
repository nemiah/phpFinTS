<?php

namespace Tests\Fhp\Integration\GLS;

use Fhp\Action\GetStatementOfAccount;
use Fhp\Action\GetStatementOfAccountXML;
use Tests\Fhp\FinTsPeer;

class GetStatementOfAccountXMLTest extends GLSIntegrationTestBase
{
    public const GET_STATEMENT_REQUEST = "HKCAZ:3:1+DExxABCDEFGH1234567890:GENODEM1GLS:1234567890::280:43060967+urn?:iso?:std?:iso?:20022?:tech?:xsd?:camt.052.001.02+N+20200205'HKTAN:4:6+4+HKCAZ'";
    public const GET_STATEMENT_RESPONSE_BEFORE_TAN = "HIRMG:3:2+0010::Nachricht entgegengenommen.'HIRMS:4:2:4+0030::Auftrag empfangen - Sicherheitsfreigabe erforderlich'HITAN:5:6:4+4++PRIVATE______________________+Ihre TAN wurde als SMS an Ihr Mobiltelefon ?'012345678910?' gesendet.'";

    public const SEND_TAN_REQUEST = "HNHBK:1:3+000000000441+300+FAKEDIALOGIDabcdefghijklmnopqr+3'HNVSK:998:3+PIN:2+998+1+1::FAKEKUNDENSYSTEMIDabcdefghij+1:20190102:030405+2:2:13:@8@00000000:5:1+280:43060967:PRIVATE__:V:0:0+0'HNVSD:999:1+@223@HNSHK:2:4+PIN:2+942+9999999+1+1+1::FAKEKUNDENSYSTEMIDabcdefghij+1+1:20190102:030405+1:999:1+6:10:19+280:43060967:PRIVATE__:S:0:0'HKTAN:3:6+2++++PRIVATE______________________+N'HNSHA:4:2+9999999++PRIVATE_____________:123456''HNHBS:5:1+3'";
    public const SEND_TAN_RESPONSE = "HIRMG:3:2+3060::Bitte beachten Sie die enthaltenen Warnungen/Hinweise.'HIRMS:4:2:3+3040::*Es liegen noch weitere CAMT Umsätze vor:0_=2#=20200515#=9382064#=0#=0#=0+0900::*TAN entwertet.'HITAN:5:6:3+2++PRIVATE______________________'";

    public const GET_STATEMENT_EMPTY_HICAZ_RESPONSE = "HICAZ:6:1:3+DExxABCDEFGH1234567890:GENODEM1GLS:1234567890::280:43060967+urn?:iso?:std?:iso?:20022?:tech?:xsd?:camt.052.001.02+@262@<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?><Document xmlns=\"urn:iso:std:iso:20022:tech:xsd:camt.052.001.02\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"urn:iso:std:iso:20022:tech:xsd:camt.052.001.02 camt.052.001.02.xsd\"></Document>'";

    public const GET_STATEMENT_PAGE_2_REQUEST = "HKCAZ:3:1+DExxABCDEFGH1234567890:GENODEM1GLS:1234567890::280:43060967+urn?:iso?:std?:iso?:20022?:tech?:xsd?:camt.052.001.02+N+20200205+++0_=2#=20200515#=9382064#=0#=0#=0'HKTAN:4:6+4+HKCAZ'";
    public const GET_STATEMENT_PAGE_2_RESPONSE = "HIRMG:3:2+3060::Bitte beachten Sie die enthaltenen Warnungen/Hinweise.'HIRMS:4:2:3+3040::*Es liegen noch weitere CAMT Umsätze vor:0_=2#=20200703#=15530363#=0#=0#=0'HIRMS:5:2:4+3076::Starke Kundenauthentifizierung nicht notwendig.'HITAN:6:6:4+4++noref+nochallenge'" . self::GET_STATEMENT_EMPTY_HICAZ_RESPONSE;

    public const GET_STATEMENT_PAGE_3_REQUEST = "HKCAZ:3:1+DExxABCDEFGH1234567890:GENODEM1GLS:1234567890::280:43060967+urn?:iso?:std?:iso?:20022?:tech?:xsd?:camt.052.001.02+N+20200205+++0_=2#=20200703#=15530363#=0#=0#=0'HKTAN:4:6+4+HKCAZ'";
    public const GET_STATEMENT_PAGE_3_RESPONSE = "HIRMG:3:2+3060::Bitte beachten Sie die enthaltenen Warnungen/Hinweise.'HIRMS:4:2:3+0020::*Abfrage CAMT Umsätze erfolgreich durchgeführt'HIRMS:5:2:4+3076::Starke Kundenauthentifizierung nicht notwendig.'HITAN:6:6:4+4++noref+nochallenge'" . self::GET_STATEMENT_EMPTY_HICAZ_RESPONSE;

    private function runInitialRequest(): GetStatementOfAccountXML
    {
        $getStatement = GetStatementOfAccountXML::create($this->getTestAccount(), new \DateTime('2020-02-05'));
        $this->fints->execute($getStatement);
        return $getStatement;
    }

    /**
     * @throws \Throwable
     */
    public function testWithTanPaginated()
    {
        //$this->initDialog();

        //$this->expectMessage(static::GET_STATEMENT_REQUEST, utf8_decode(static::GET_STATEMENT_RESPONSE_BEFORE_TAN));
        //$getStatement = $this->runInitialRequest();
        //$this->assertTrue($getStatement->needsTan());

        //$this->completeWithTan($getStatement);
    }

    /**
     * @throws \Throwable
     */
    public function testWithTanMinimalPersistPaginated()
    {
        $this->initDialog();

        $this->expectMessage(static::GET_STATEMENT_REQUEST, utf8_decode(static::GET_STATEMENT_RESPONSE_BEFORE_TAN));
        $getStatement = $this->runInitialRequest();
        $this->assertTrue($getStatement->needsTan());

        // Pretend that we close everything and open everything from scratch, as if it were a new PHP process.
        $persistedInstance = $this->fints->persist(true);
        $persistedGetStatement = serialize($getStatement);
        $this->connection->expects($this->once())->method('disconnect');
        $this->fints = new FinTsPeer($this->options, $this->credentials);
        $this->fints->loadPersistedInstance($persistedInstance);
        /** @var GetStatementOfAccount $getStatement */
        $getStatement = unserialize($persistedGetStatement);

        $this->completeWithTan($getStatement);
    }

    /**
     * @throws \Throwable
     */
    private function completeWithTan(GetStatementOfAccountXML $getStatement)
    {
        $this->expectMessage(static::SEND_TAN_REQUEST, utf8_decode(static::SEND_TAN_RESPONSE . self::GET_STATEMENT_EMPTY_HICAZ_RESPONSE));
        $this->expectMessage(self::GET_STATEMENT_PAGE_2_REQUEST, utf8_decode(self::GET_STATEMENT_PAGE_2_RESPONSE));
        $this->expectMessage(self::GET_STATEMENT_PAGE_3_REQUEST, utf8_decode(self::GET_STATEMENT_PAGE_3_RESPONSE));
        $this->fints->submitTan($getStatement, '123456');
        $this->assertFalse($getStatement->needsTan());
    }
}
