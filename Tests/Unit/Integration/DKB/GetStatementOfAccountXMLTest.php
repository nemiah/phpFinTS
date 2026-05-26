<?php

namespace Fhp\Tests\Unit\Integration\DKB;

use Fhp\Action\GetStatementOfAccount;
use Fhp\Action\GetStatementOfAccountXML;
use Fhp\BaseAction;
use Fhp\Tests\FinTsPeer;

/**
 * Integration test for the CAMT (HKCAZ / HICAZ) statement path against the
 * DKB fixture.
 *
 * Background: This test was requested by `@ampaze` on
 * https://github.com/nemiah/phpFinTS/issues/564 to help diagnose a reported
 * truncation of `FinTs::persist()` output that manifested only against the
 * live DKB endpoint. The DKB anonymous-init fixture already advertises
 * `HICAZSv1` (and the UPD declares `HKCAZ:1`), so a fixture-driven CAMT
 * round-trip exercises the same code paths as the production-broken case.
 *
 * Mirrors `Tests/Unit/Integration/GLS/GetStatementOfAccountXMLTest.php`
 * but uses DKB credentials, account, TAN mode (921 / `pushTAN`) and the
 * DKB-flavoured TAN challenge dialog.
 */
class GetStatementOfAccountXMLTest extends DKBIntegrationTestBase
{
    // Initial request: HKCAZv1 (statement, CAMT). Note no HKTAN trailer — the DKB BPD does not advertise
    // HICAZS national-account fields, and TAN-mode 921 (HHD1.3.2OPT) in v1 doesn't need an HKTAN here.
    public const GET_STATEMENT_REQUEST = "HKCAZ:3:1+DExxABCDEFGH1234567890:BYLADEM1001+urn?:iso?:std?:iso?:20022?:tech?:xsd?:camt.052.001.02+N+20200205'";
    public const GET_STATEMENT_RESPONSE_BEFORE_TAN = "HIRMG:3:2+0010::Nachricht entgegengenommen.'HIRMS:4:2:4+0030::Auftrag empfangen - Bitte die empfangene TAN eingeben.(MBT62820200001)'HITAN:5:6:4+4++2472-12-07-21.27.57.123456+Bitte geben Sie die pushTAN ein.+++SomePhone1'";

    // After the user submits the TAN, the bank sends the response wrapped in an envelope (HNHBK/HNVSK/HNVSD).
    public const SEND_TAN_REQUEST = "HNHBK:1:3+000000000425+300+FAKEDIALOGIDabcdefghijklmnopqr+3'HNVSK:998:3+PIN:2+998+1+1::FAKEKUNDENSYSTEMIDabcdefghij+1:20190102:030405+2:2:13:@8@00000000:5:1+280:12030000:test?@user:V:0:0+0'HNVSD:999:1+@206@HNSHK:2:4+PIN:2+921+9999999+1+1+1::FAKEKUNDENSYSTEMIDabcdefghij+1+1:20190102:030405+1:999:1+6:10:19+280:12030000:test?@user:S:0:0'HKTAN:3:6+2++++2472-12-07-21.27.57.123456+N'HNSHA:4:2+9999999++12345:777666''HNHBS:5:1+3'";

    // The HICAZ payload after successful TAN: an empty (or single-page) CAMT response, then pagination tokens.
    public const GET_STATEMENT_EMPTY_HICAZ_RESPONSE = "HICAZ:6:1:3+DExxABCDEFGH1234567890:BYLADEM1001:1234567890::280:12030000+urn?:iso?:std?:iso?:20022?:tech?:xsd?:camt.052.001.02+@262@<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?><Document xmlns=\"urn:iso:std:iso:20022:tech:xsd:camt.052.001.02\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"urn:iso:std:iso:20022:tech:xsd:camt.052.001.02 camt.052.001.02.xsd\"></Document>'";
    public const SEND_TAN_RESPONSE = "HIRMG:3:2+3060::Bitte beachten Sie die enthaltenen Warnungen/Hinweise.'HIRMS:4:2:3+0900::*TAN entwertet.'HITAN:5:6:3+2++2472-12-07-21.27.57.123456'";

    private function runInitialRequest(): GetStatementOfAccountXML
    {
        $getStatement = GetStatementOfAccountXML::create($this->getTestAccount(), new \DateTime('2020-02-05'));
        $this->fints->execute($getStatement);
        return $getStatement;
    }

    /**
     * Sanity check: full flow with TAN, ensuring HKCAZ / HICAZ are exercised against the DKB fixture.
     * @throws \Throwable
     */
    public function testWithTan()
    {
        $this->initDialog();

        $this->expectMessage(static::GET_STATEMENT_REQUEST, static::GET_STATEMENT_RESPONSE_BEFORE_TAN);
        $getStatement = $this->runInitialRequest();
        $this->assertTrue($getStatement->needsTan());

        $this->completeWithTan($getStatement);
    }

    /**
     * The key scenario from issue #564: persist the FinTs state right after the bank requested a TAN,
     * then load it in a fresh peer and finish the dialog. If FinTs::persist() ever produces a string
     * that does not survive unserialize(), this test fails — which is the regression we want guarded.
     * @throws \Throwable
     */
    public function testWithTanPersist()
    {
        $this->initDialog();

        $this->expectMessage(static::GET_STATEMENT_REQUEST, static::GET_STATEMENT_RESPONSE_BEFORE_TAN);
        $getStatement = $this->runInitialRequest();
        $this->assertTrue($getStatement->needsTan());

        // Persist with full state ($minimal=false): this is the path that production reports to truncate.
        $persistedInstance = $this->fints->persist();
        $this->assertNotEmpty($persistedInstance);

        // Bare-minimum sanity: result must unserialize. This is the assertion that issue #564 violates.
        $unserialized = @unserialize($persistedInstance);
        $this->assertNotFalse(
            $unserialized,
            sprintf(
                "FinTs::persist() produced a non-unserializable string (length=%d). " .
                "Tail (hex, last 200 bytes): %s",
                strlen($persistedInstance),
                bin2hex(substr($persistedInstance, -200))
            )
        );
        $persistedGetStatement = serialize($getStatement);

        // Pretend that we close everything and reopen, as if from a new PHP process.
        $this->connection->expects($this->once())->method('disconnect');
        $this->fints = new FinTsPeer($this->options, $this->credentials);
        $this->fints->loadPersistedInstance($persistedInstance);
        /** @var GetStatementOfAccount $getStatement */
        $getStatement = unserialize($persistedGetStatement);

        $this->completeWithTan($getStatement);
    }

    /**
     * Same as above but with the $minimal=true persistence (BPD/UPD omitted), as a contrast point:
     * if testWithTanPersist were to fail on the BPD, this one should still succeed.
     * @throws \Throwable
     */
    public function testWithTanMinimalPersist()
    {
        $this->initDialog();

        $this->expectMessage(static::GET_STATEMENT_REQUEST, static::GET_STATEMENT_RESPONSE_BEFORE_TAN);
        $getStatement = $this->runInitialRequest();
        $this->assertTrue($getStatement->needsTan());

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
    private function completeWithTan(BaseAction $getStatement)
    {
        $this->expectMessage(static::SEND_TAN_REQUEST, static::SEND_TAN_RESPONSE . self::GET_STATEMENT_EMPTY_HICAZ_RESPONSE);
        $this->fints->submitTan($getStatement, '777666');
        $this->assertFalse($getStatement->needsTan());
    }
}
