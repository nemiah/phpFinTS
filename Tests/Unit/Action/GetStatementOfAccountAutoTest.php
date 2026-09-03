<?php

namespace Fhp\Tests\Unit\Action;

use Fhp\Action\GetStatementOfAccount;
use Fhp\Action\GetStatementOfAccountXML;
use Fhp\Model\StatementOfAccount\Statement;
use Fhp\Tests\FinTsPeer;
use Fhp\Tests\Unit\Integration\GLS\GetStatementOfAccountXMLTest;
use Fhp\Tests\Unit\Integration\GLS\GLSIntegrationTestBase;

/**
 * Tests {@link GetStatementOfAccount}, which picks the statement format based on the BPD. The bank in
 * {@link GLSIntegrationTestBase} supports both HIKAZS (MT 940) and HICAZS (CAMT XML), so the CAMT format has to win and
 * the messages exchanged with the bank are exactly the ones from {@link GetStatementOfAccountXMLTest}.
 *
 * NOTE: This test deliberately does not live in the directory of that bank, because only the dialog around the
 * statement (login, TAN, pagination) is a recording of a real conversation with it. The CAMT documents below are
 * hand-written, no bank ever sent them.
 *
 * This is the regression test for https://github.com/nemiah/phpFinTS/issues/553, where the action forgot that it had
 * decided for CAMT while it was serialized during the TAN request, and then failed with
 * "Only got 0 HIKAZ response segments!" once the statement arrived.
 */
class GetStatementOfAccountAutoTest extends GLSIntegrationTestBase
{
    /**
     * A minimal, synthetic camt.052 document, modelled after the (private) one from issue #553: two booked entries, a
     * pending (not yet booked) one and an opening balance. Kept ASCII-only so that its length is the same before and after the ISO-8859-1 conversion
     * that the responses go through.
     */
    public const CAMT_DOCUMENT = '<?xml version="1.0" encoding="ISO-8859-1" ?><Document xmlns="urn:iso:std:iso:20022:tech:xsd:camt.052.001.02" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><BkToCstmrAcctRpt><Rpt><Id>1234567890-2020-02-05</Id><Acct><Id><IBAN>DExxABCDEFGH1234567890</IBAN></Id></Acct>'
        . '<Bal><Tp><CdOrPrtry><Cd>OPBD</Cd></CdOrPrtry></Tp><Amt Ccy="EUR">1234.56</Amt><CdtDbtInd>CRDT</CdtDbtInd><Dt><Dt>2020-02-05</Dt></Dt></Bal>'
        . '<Ntry><Amt Ccy="EUR">123.45</Amt><CdtDbtInd>CRDT</CdtDbtInd><Sts>BOOK</Sts><BookgDt><Dt>2020-02-05</Dt></BookgDt><ValDt><Dt>2020-02-05</Dt></ValDt>'
        . '<NtryDtls><TxDtls><RmtInf><Ustrd>GUTSCHRIFT TESTZAHLUNG</Ustrd></RmtInf><RltdPties><Dbtr><Nm>SENDER NAME</Nm></Dbtr></RltdPties></TxDtls></NtryDtls></Ntry>'
        . '<Ntry><Amt Ccy="EUR">42.00</Amt><CdtDbtInd>DBIT</CdtDbtInd><Sts>BOOK</Sts><BookgDt><Dt>2020-02-05</Dt></BookgDt><ValDt><Dt>2020-02-06</Dt></ValDt>'
        . '<NtryDtls><TxDtls><RmtInf><Ustrd>MIETE FEBRUAR</Ustrd></RmtInf><RltdPties><Cdtr><Nm>EMPFAENGER NAME</Nm></Cdtr></RltdPties></TxDtls></NtryDtls></Ntry>'
        . '<Ntry><Amt Ccy="EUR">9.99</Amt><CdtDbtInd>DBIT</CdtDbtInd><Sts>PDNG</Sts><BookgDt><Dt>2020-02-05</Dt></BookgDt><ValDt><Dt>2020-02-05</Dt></ValDt>'
        . '<NtryDtls><TxDtls><RmtInf><Ustrd>KARTENZAHLUNG VORGEMERKT</Ustrd></RmtInf><RltdPties><Cdtr><Nm>SUPERMARKT</Nm></Cdtr></RltdPties></TxDtls></NtryDtls></Ntry>'
        . '</Rpt></BkToCstmrAcctRpt></Document>';

    /** The CAMT version that {@link CAMT_DOCUMENT} uses, as announced by the bank in the HICAZ segment. */
    public const CAMT_VERSION = 'camt.052.001.02';

    /** How this CAMT version spells the status of a transaction that the bank has not booked yet. */
    public const STATUS_PENDING = '<Sts>PDNG</Sts>';

    /** The separate document that the bank sends for transactions it has received but not booked yet. */
    private static function unbookedCamtDocument(): string
    {
        return '<?xml version="1.0" encoding="ISO-8859-1" ?><Document xmlns="urn:iso:std:iso:20022:tech:xsd:' . static::CAMT_VERSION . '">'
            . '<BkToCstmrAcctRpt><Rpt><Id>1234567890-vorgemerkt</Id><Acct><Id><IBAN>DExxABCDEFGH1234567890</IBAN></Id></Acct>'
            . '<Ntry><Amt Ccy="EUR">17.50</Amt><CdtDbtInd>DBIT</CdtDbtInd>' . static::STATUS_PENDING
            . '<BookgDt><Dt>2020-02-05</Dt></BookgDt><ValDt><Dt>2020-02-05</Dt></ValDt>'
            . '<NtryDtls><TxDtls><RmtInf><Ustrd>TANKSTELLE</Ustrd></RmtInf></TxDtls></NtryDtls></Ntry>'
            . '</Rpt></BkToCstmrAcctRpt></Document>';
    }

    /** Like {@link hicazWithTransactions()}, but the bank also sends the not-yet-booked transactions. */
    private static function hicazWithUnbookedTransactions(): string
    {
        $unbooked = self::unbookedCamtDocument();
        return rtrim(self::hicazWithTransactions(), "'") . '+@' . strlen($unbooked) . '@' . $unbooked . "'";
    }

    /** Like {@link GetStatementOfAccountXMLTest::GET_STATEMENT_EMPTY_HICAZ_RESPONSE}, but with actual transactions. */
    private static function hicazWithTransactions(): string
    {
        return 'HICAZ:6:1:3+DExxABCDEFGH1234567890:GENODEM1GLS:1234567890::280:43060967+urn?:iso?:std?:iso?:20022?:tech?:xsd?:'
            . static::CAMT_VERSION . '+@' . strlen(static::CAMT_DOCUMENT) . '@' . static::CAMT_DOCUMENT . "'";
    }

    /**
     * @throws \Throwable
     */
    private function runInitialRequest(): GetStatementOfAccount
    {
        $getStatement = GetStatementOfAccount::create($this->getTestAccount(), new \DateTime('2020-02-05'));
        $this->fints->execute($getStatement);
        return $getStatement;
    }

    /**
     * The bank asks for a TAN, the action is persisted while the user enters it, and the statement itself only arrives
     * afterwards - the exact sequence from issue #553.
     *
     * @throws \Throwable
     */
    public function testCamtIsChosenAndSurvivesPersist()
    {
        $this->initDialog();

        $this->expectMessage(GetStatementOfAccountXMLTest::GET_STATEMENT_REQUEST,
            mb_convert_encoding(GetStatementOfAccountXMLTest::GET_STATEMENT_RESPONSE_BEFORE_TAN, 'ISO-8859-1', 'UTF-8'));
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

        $this->expectMessage(GetStatementOfAccountXMLTest::SEND_TAN_REQUEST,
            mb_convert_encoding(GetStatementOfAccountXMLTest::SEND_TAN_RESPONSE . self::hicazWithTransactions(), 'ISO-8859-1', 'UTF-8'));
        $this->expectMessage(GetStatementOfAccountXMLTest::GET_STATEMENT_PAGE_2_REQUEST,
            mb_convert_encoding(GetStatementOfAccountXMLTest::GET_STATEMENT_PAGE_2_RESPONSE, 'ISO-8859-1', 'UTF-8'));
        $this->expectMessage(GetStatementOfAccountXMLTest::GET_STATEMENT_PAGE_3_REQUEST,
            mb_convert_encoding(GetStatementOfAccountXMLTest::GET_STATEMENT_PAGE_3_RESPONSE, 'ISO-8859-1', 'UTF-8'));
        $this->fints->submitTan($getStatement, '123456');
        $this->assertFalse($getStatement->needsTan());

        $this->assertInstanceOf(GetStatementOfAccountXML::class, $getStatement->getDelegate());
        $this->assertSame([static::CAMT_DOCUMENT, '', ''], array_map(function (string $xml) {
            // The two paginated responses contain an empty camt document, which is not interesting here.
            return str_contains($xml, '<Ntry>') ? $xml : '';
        }, $getStatement->getRawResponse()));

        $statement = $getStatement->getStatement();
        $this->assertCount(1, $statement->getStatements());
        $statement1 = $statement->getStatements()[0];
        $this->assertEquals(new \DateTime('2020-02-05'), $statement1->getDate());
        $this->assertEqualsWithDelta(1234.56, $statement1->getStartBalance(), 0.01);
        $this->assertCount(3, $statement1->getTransactions());

        $transaction1 = $statement1->getTransactions()[0];
        $this->assertEquals(Statement::CD_CREDIT, $transaction1->getCreditDebit());
        $this->assertEqualsWithDelta(123.45, $transaction1->getAmount(), 0.01);
        $this->assertEquals(new \DateTime('2020-02-05'), $transaction1->getBookingDate());
        $this->assertEquals('SENDER NAME', $transaction1->getName());

        $transaction2 = $statement1->getTransactions()[1];
        $this->assertEquals(Statement::CD_DEBIT, $transaction2->getCreditDebit());
        $this->assertEqualsWithDelta(42.00, $transaction2->getAmount(), 0.01);
        $this->assertEquals(new \DateTime('2020-02-06'), $transaction2->getValutaDate());
        $this->assertEquals('EMPFAENGER NAME', $transaction2->getName());

        // The booking status has to be recognized in all CAMT versions, which spell it differently.
        $this->assertTrue($transaction1->getBooked());
        $this->assertTrue($transaction2->getBooked());
        $this->assertFalse($statement1->getTransactions()[2]->getBooked());
        $this->assertEquals('SUPERMARKT', $statement1->getTransactions()[2]->getName());
    }

    /**
     * Accessing MT 940 specific results has to fail with a helpful message when the bank answered in CAMT XML.
     *
     * @throws \Throwable
     */
    public function testMT940ResultsAreUnavailable()
    {
        $this->initDialog();

        $this->expectMessage(GetStatementOfAccountXMLTest::GET_STATEMENT_REQUEST,
            mb_convert_encoding(GetStatementOfAccountXMLTest::GET_STATEMENT_RESPONSE_BEFORE_TAN, 'ISO-8859-1', 'UTF-8'));
        $getStatement = $this->runInitialRequest();

        $this->expectMessage(GetStatementOfAccountXMLTest::SEND_TAN_REQUEST,
            mb_convert_encoding(GetStatementOfAccountXMLTest::SEND_TAN_RESPONSE . self::hicazWithTransactions(), 'ISO-8859-1', 'UTF-8'));
        $this->expectMessage(GetStatementOfAccountXMLTest::GET_STATEMENT_PAGE_2_REQUEST,
            mb_convert_encoding(GetStatementOfAccountXMLTest::GET_STATEMENT_PAGE_2_RESPONSE, 'ISO-8859-1', 'UTF-8'));
        $this->expectMessage(GetStatementOfAccountXMLTest::GET_STATEMENT_PAGE_3_REQUEST,
            mb_convert_encoding(GetStatementOfAccountXMLTest::GET_STATEMENT_PAGE_3_RESPONSE, 'ISO-8859-1', 'UTF-8'));
        $this->fints->submitTan($getStatement, '123456');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/retrieved with .*GetStatementOfAccountXML.*GetStatementOfAccountMT940/s');
        $getStatement->getRawMT940();
    }

    /**
     * Banks report transactions that they have received but not booked yet in a separate CAMT document. They are only
     * part of the statement if the caller asked for them.
     *
     * @throws \Throwable
     */
    public function testUnbookedTransactionsAreIncludedWhenRequested()
    {
        $this->initDialog();

        $this->expectMessage(GetStatementOfAccountXMLTest::GET_STATEMENT_REQUEST,
            mb_convert_encoding(GetStatementOfAccountXMLTest::GET_STATEMENT_RESPONSE_BEFORE_TAN, 'ISO-8859-1', 'UTF-8'));
        // The request is the same either way, the bank decides whether to send unbooked transactions at all.
        $getStatement = GetStatementOfAccountXML::create(
            $this->getTestAccount(), new \DateTime('2020-02-05'), null, null, false, true);
        $this->fints->execute($getStatement);

        $this->expectMessage(GetStatementOfAccountXMLTest::SEND_TAN_REQUEST,
            mb_convert_encoding(GetStatementOfAccountXMLTest::SEND_TAN_RESPONSE . self::hicazWithUnbookedTransactions(), 'ISO-8859-1', 'UTF-8'));
        $this->expectMessage(GetStatementOfAccountXMLTest::GET_STATEMENT_PAGE_2_REQUEST,
            mb_convert_encoding(GetStatementOfAccountXMLTest::GET_STATEMENT_PAGE_2_RESPONSE, 'ISO-8859-1', 'UTF-8'));
        $this->expectMessage(GetStatementOfAccountXMLTest::GET_STATEMENT_PAGE_3_REQUEST,
            mb_convert_encoding(GetStatementOfAccountXMLTest::GET_STATEMENT_PAGE_3_RESPONSE, 'ISO-8859-1', 'UTF-8'));
        $this->fints->submitTan($getStatement, '123456');

        // The pages without unbooked transactions must not add anything, and the booked ones stay separate.
        $this->assertSame([self::unbookedCamtDocument()], $getStatement->getUnbookedXML());
        $this->assertCount(3, $getStatement->getBookedXML());
        $this->assertCount(4, $getStatement->getRawResponse());

        $transactions = $getStatement->getStatement()->getStatements()[0]->getTransactions();
        $this->assertCount(4, $transactions);
        $unbooked = $transactions[3];
        $this->assertFalse($unbooked->getBooked());
        $this->assertEqualsWithDelta(17.50, $unbooked->getAmount(), 0.01);
        $this->assertEquals('TANKSTELLE', $unbooked->getMainDescription());
    }

    /**
     * Unbooked transactions that the caller did not ask for stay out of the statement, but remain accessible.
     *
     * @throws \Throwable
     */
    public function testUnbookedTransactionsAreExcludedByDefault()
    {
        $this->initDialog();

        $this->expectMessage(GetStatementOfAccountXMLTest::GET_STATEMENT_REQUEST,
            mb_convert_encoding(GetStatementOfAccountXMLTest::GET_STATEMENT_RESPONSE_BEFORE_TAN, 'ISO-8859-1', 'UTF-8'));
        $getStatement = $this->runInitialRequest(); // Does not ask for unbooked transactions.

        $this->expectMessage(GetStatementOfAccountXMLTest::SEND_TAN_REQUEST,
            mb_convert_encoding(GetStatementOfAccountXMLTest::SEND_TAN_RESPONSE . self::hicazWithUnbookedTransactions(), 'ISO-8859-1', 'UTF-8'));
        $this->expectMessage(GetStatementOfAccountXMLTest::GET_STATEMENT_PAGE_2_REQUEST,
            mb_convert_encoding(GetStatementOfAccountXMLTest::GET_STATEMENT_PAGE_2_RESPONSE, 'ISO-8859-1', 'UTF-8'));
        $this->expectMessage(GetStatementOfAccountXMLTest::GET_STATEMENT_PAGE_3_REQUEST,
            mb_convert_encoding(GetStatementOfAccountXMLTest::GET_STATEMENT_PAGE_3_RESPONSE, 'ISO-8859-1', 'UTF-8'));
        $this->fints->submitTan($getStatement, '123456');

        // The bank sent them anyway, so they are still available separately ...
        $delegate = $getStatement->getDelegate();
        $this->assertInstanceOf(GetStatementOfAccountXML::class, $delegate);
        $this->assertSame([self::unbookedCamtDocument()], $delegate->getUnbookedXML());

        // ... but they are not part of the statement, which only has the three transactions of the booked document.
        $this->assertCount(3, $getStatement->getRawResponse());
        $this->assertCount(3, $getStatement->getStatement()->getStatements()[0]->getTransactions());
    }
}
