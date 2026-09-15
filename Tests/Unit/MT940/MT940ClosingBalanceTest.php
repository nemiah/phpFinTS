<?php

namespace Fhp\Tests\Unit\MT940;

use Fhp\Model\StatementOfAccount\StatementOfAccount;
use Fhp\MT940\MT940;
use PHPUnit\Framework\TestCase;

class MT940ClosingBalanceTest extends TestCase
{
    /**
     * The closing balance of the first statement carries the next day's date, as e.g. the FinTS Connect proxy that
     * fronts PayPal sends it. It used to be dropped because the parser keyed it by its own date.
     */
    public function testClosingBalanceWithDifferentDateStaysWithItsStatement(): void
    {
        $parsed = (new MT940())->parse(self::statement(':60F:D260717EUR2,27', ':62F:C260718EUR1234,56'));

        $this->assertSame('1234.56', $parsed['2026-07-17']['end_balance']['amount']);
        $this->assertSame(MT940::CD_CREDIT, $parsed['2026-07-17']['end_balance']['credit_debit']);
        $this->assertSame(1234.56, StatementOfAccount::fromMT940Array($parsed)->getStatements()[0]->getEndBalance());
    }

    public function testIntermediateClosingBalanceIsUsed(): void
    {
        $parsed = (new MT940())->parse(self::statement(':60F:C260717EUR1234,56', ':62M:C260717EUR1554,64'));

        $this->assertSame('1554.64', $parsed['2026-07-17']['end_balance']['amount']);
    }

    /**
     * The parser negates a debit closing balance and the model used to negate it again, so an overdrawn account
     * reported a positive end balance.
     */
    public function testDebitClosingBalanceIsNegative(): void
    {
        $parsed = (new MT940())->parse(self::statement(':60F:C260717EUR10,00', ':62F:D260717EUR5000,00'));

        $this->assertSame(MT940::CD_DEBIT, $parsed['2026-07-17']['end_balance']['credit_debit']);
        $this->assertSame(-5000.0, StatementOfAccount::fromMT940Array($parsed)->getStatements()[0]->getEndBalance());
    }

    public function testStatementWithoutClosingBalanceHasNoEndBalance(): void
    {
        $parsed = (new MT940())->parse(self::statement(':60F:D260717EUR2,27', ''));

        $this->assertArrayNotHasKey('end_balance', $parsed['2026-07-17']);
        $this->assertNull(StatementOfAccount::fromMT940Array($parsed)->getStatements()[0]->getEndBalance());
    }

    private static function statement(string $openingBalance, string $closingBalance): string
    {
        $crlf = "\r\n";

        return ':20:STARTUMS' . $crlf
            . ':25:92020000/7210891793' . $crlf
            . ':28C:0' . $crlf
            . $openingBalance . $crlf
            . ':61:2607170717CR320,08N062NONREF' . $crlf
            . ':86:654801?00654801?20PAYMENT?32Max Mustermann' . $crlf
            . ($closingBalance !== '' ? $closingBalance . $crlf : '')
            . '-' . $crlf;
    }
}
