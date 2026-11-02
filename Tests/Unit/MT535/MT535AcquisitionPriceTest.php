<?php

namespace Fhp\Tests\Unit\MT535;

use Fhp\MT535\MT535;
use PHPUnit\Framework\TestCase;

class MT535AcquisitionPriceTest extends TestCase
{
    private const HOLDING_TEMPLATE = ":16R:FIN\r\n"
        . ":35B:ISIN DE0005190003\r\n"
        . "/DE/519000\r\n"
        . "BAY.MOTOREN WERKE AG ST\r\n"
        . ":90B::MRKT//ACTU/EUR76,06\r\n"
        . ":98A::PRIC//20251030\r\n"
        . ":93B::AGGR//UNIT/10,\r\n"
        . '%s'
        . ":16S:FIN\r\n";

    private static function statement(string ...$holdings): string
    {
        return ":16R:GENL\r\n:28E:1/ONLY\r\n:98C::STAT//20251030120000\r\n:16S:GENL\r\n"
            . implode('', $holdings)
            . ":16R:ADDINFO\r\nEUR760,6\r\n:16S:ADDINFO\r\n-";
    }

    private static function holding(string $field70E): string
    {
        return sprintf(self::HOLDING_TEMPLATE, $field70E);
    }

    /**
     * Line 1 of field 70E carries further subfields (here the date of the last movement), as e.g. DKB sends
     * them. The acquisition price in line 2 used to be dropped because the parser expected line 2 right after "STK".
     */
    public function testAcquisitionPriceAfterFurtherSubfieldsInLineOne(): void
    {
        $parser = new MT535(self::statement(
            self::holding(":70E::HOLD//1STK++++20250825\r\n2224,5983333+EUR\r\n"),
            self::holding(":70E::HOLD//1STK++++20250911\r\n2224,+EUR\r\n"),
        ));
        $holdings = $parser->parseHoldings()->getHoldings();

        $this->assertCount(2, $holdings);
        $this->assertEqualsWithDelta(224.5983333, $holdings[0]->getAcquisitionPrice(), 1e-9);
        $this->assertEqualsWithDelta(224.0, $holdings[1]->getAcquisitionPrice(), 1e-9);
        $this->assertSame('EUR', $holdings[0]->getCurrency());
        $this->assertEqualsWithDelta(76.06, $holdings[0]->getPrice(), 1e-9);
        $this->assertSame('DE0005190003', $holdings[0]->getISIN());
    }

    /**
     * Example from the specification (FinTS 3.0 Finanzdatenformate, B.4), cf. #464.
     */
    public function testAcquisitionPriceAsInSpecificationExample(): void
    {
        $parser = new MT535(self::statement(
            self::holding(":70E::HOLD//1STK+805+00028+LU+20240822+\r\n22,5624+EUR+\r\n3\r\n4\r\n")
        ));
        $holdings = $parser->parseHoldings()->getHoldings();

        $this->assertEqualsWithDelta(2.5624, $holdings[0]->getAcquisitionPrice(), 1e-9);
        $this->assertSame('EUR', $holdings[0]->getCurrency());
    }

    public function testAcquisitionPriceDirectlyAfterStk(): void
    {
        $parser = new MT535(self::statement(self::holding(":70E::HOLD//1STK\r\n223,968293+EUR\r\n")));
        $holdings = $parser->parseHoldings()->getHoldings();

        $this->assertEqualsWithDelta(23.968293, $holdings[0]->getAcquisitionPrice(), 1e-9);
    }

    public function testAcquisitionPriceWithAtAtDivider(): void
    {
        $raw = str_replace("\r\n", '@@', self::statement(
            self::holding(":70E::HOLD//1STK++++20250825\r\n2224,5983333+EUR\r\n")
        ));
        $holdings = (new MT535($raw))->parseHoldings()->getHoldings();

        $this->assertEqualsWithDelta(224.5983333, $holdings[0]->getAcquisitionPrice(), 1e-9);
    }

    public function testNoAcquisitionPrice(): void
    {
        $holdings = (new MT535(self::statement(self::holding(''))))->parseHoldings()->getHoldings();

        $this->assertNull($holdings[0]->getAcquisitionPrice());
    }
}
