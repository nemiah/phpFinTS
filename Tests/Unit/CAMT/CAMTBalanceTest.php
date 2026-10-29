<?php

namespace Fhp\Tests\Unit\CAMT;

use Fhp\CAMT\CAMT;
use Fhp\Model\StatementOfAccount\Statement;
use Fhp\Model\StatementOfAccount\StatementOfAccount;
use Fhp\MT940\MT940;
use PHPUnit\Framework\TestCase;

/**
 * The balances of a camt.052 report are typed: OPBD is the balance at the start of the day, ITBD/CLBD the balance
 * after the reported bookings. The parser used to report OPBD as the end balance as well, which froze
 * {@link Statement::getEndBalance()} at the day's opening value (observed at an Atruvia/Volksbank account that showed
 * -18,59 EUR while 12.944,64 EUR of credits had already been booked that day).
 */
class CAMTBalanceTest extends TestCase
{
    private const NS = 'urn:iso:std:iso:20022:tech:xsd:camt.052.001.02';

    public function testInterimBookedBalanceIsTheEndBalance(): void
    {
        $result = (new CAMT())->parse([$this->buildReport([
            ['OPBD', '18.59', 'DBIT', '2026-07-17'],
            ['ITBD', '12944.64', 'CRDT', '2026-07-17'],
        ])]);

        $statement = $result['2026-07-17'];
        $this->assertSame(18.59, $statement['start_balance']['amount']);
        $this->assertSame(MT940::CD_DEBIT, $statement['start_balance']['credit_debit']);
        $this->assertSame(12944.64, $statement['end_balance']['amount']);
        $this->assertSame(MT940::CD_CREDIT, $statement['end_balance']['credit_debit']);

        $model = StatementOfAccount::fromCAMTArray($result)->getStatements()[0];
        $this->assertSame(18.59, $model->getStartBalance());
        $this->assertSame(Statement::CD_DEBIT, $model->getCreditDebit());
        $this->assertSame(12944.64, $model->getEndBalance());
    }

    public function testClosingBookedBalanceWinsOverInterimBalance(): void
    {
        $result = (new CAMT())->parse([$this->buildReport([
            ['ITBD', '100.00', 'CRDT', '2026-07-17'],
            ['CLBD', '150.00', 'CRDT', '2026-07-17'],
            ['OPBD', '10.00', 'CRDT', '2026-07-17'],
        ])]);

        $this->assertSame(10.0, $result['2026-07-17']['start_balance']['amount']);
        $this->assertSame(150.0, $result['2026-07-17']['end_balance']['amount']);
    }

    public function testDebitClosingBalanceIsReportedAsNegativeEndBalance(): void
    {
        $result = (new CAMT())->parse([$this->buildReport([
            ['OPBD', '10.00', 'CRDT', '2026-07-17'],
            ['CLBD', '40.00', 'DBIT', '2026-07-17'],
        ])]);

        $model = StatementOfAccount::fromCAMTArray($result)->getStatements()[0];
        $this->assertSame(-40.0, $model->getEndBalance());
    }

    public function testAvailableBalancesAndOpeningBalanceAloneYieldNoEndBalance(): void
    {
        $result = (new CAMT())->parse([$this->buildReport([
            ['OPBD', '18.59', 'DBIT', '2026-07-17'],
            ['ITAV', '12944.64', 'CRDT', '2026-07-17'],
        ])]);

        $this->assertSame(18.59, $result['2026-07-17']['start_balance']['amount']);
        $this->assertArrayNotHasKey('end_balance', $result['2026-07-17']);
        $this->assertNull(StatementOfAccount::fromCAMTArray($result)->getStatements()[0]->getEndBalance());
    }

    /**
     * @param array<int, array{0: string, 1: string, 2: string, 3: string}> $balances type, amount, CRDT/DBIT, date
     */
    private function buildReport(array $balances): string
    {
        $ns = self::NS;
        $balanceXml = '';
        foreach ($balances as [$type, $amount, $creditDebit, $date]) {
            $balanceXml .= "<Bal><Tp><CdOrPrtry><Cd>{$type}</Cd></CdOrPrtry></Tp><Amt Ccy=\"EUR\">{$amount}</Amt>"
                . "<CdtDbtInd>{$creditDebit}</CdtDbtInd><Dt><Dt>{$date}</Dt></Dt></Bal>";
        }

        return <<<XML
<Document xmlns="{$ns}">
  <BkToCstmrAcctRpt>
    <Rpt>
      <Id>1</Id>
      <Acct><Id><IBAN>DE44500105175407324931</IBAN></Id></Acct>
      {$balanceXml}
      <Ntry>
        <Amt Ccy="EUR">12963.23</Amt>
        <CdtDbtInd>CRDT</CdtDbtInd>
        <Sts>BOOK</Sts>
        <BookgDt><Dt>2026-07-17</Dt></BookgDt>
        <ValDt><Dt>2026-07-17</Dt></ValDt>
        <NtryDtls>
          <TxDtls>
            <RltdPties>
              <Dbtr><Nm>Max Mustermann</Nm></Dbtr>
              <DbtrAcct><Id><IBAN>DE89370400440532013000</IBAN></Id></DbtrAcct>
            </RltdPties>
            <RmtInf><Ustrd>Rechnung 4711</Ustrd></RmtInf>
          </TxDtls>
        </NtryDtls>
      </Ntry>
    </Rpt>
  </BkToCstmrAcctRpt>
</Document>
XML;
    }
}
