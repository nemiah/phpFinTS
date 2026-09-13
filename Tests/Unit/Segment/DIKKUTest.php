<?php

namespace Fhp\Tests\Unit\Segment;

use Fhp\Model\CreditCardStatement\CreditCardStatement;
use Fhp\Model\CreditCardStatement\CreditCardTransaction;
use Fhp\Segment\KKU\DIKKUv2;

/**
 * Tests parsing of the DIKKU segment (credit card transactions, DK business transaction DKKKU).
 *
 * The wire format below mirrors the structure of a real response from BW-Bank/LBBW, but all account
 * numbers, merchants, amounts and references have been replaced. The structure is what matters:
 * the number of data elements, which of them are empty, and the special cases documented in
 * {@link \Fhp\Segment\KKU\Kreditkartenumsatz}.
 */
class DIKKUTest extends \PHPUnit\Framework\TestCase
{
    private const ACCOUNT = '5555000011112222';

    /** Ordinary domestic transaction: amounts equal, exchange rate 1, merchant category code present. */
    private const RECORD_DOMESTIC =
        '5555000011112222:20260717:20260718::12,34:EUR:D:1,:12,34:EUR:D:EXAMPLE SHOP:BERLIN 555500******2233::::::::J:1000000000000001:5411';

    /** Foreign currency: 50.00 USD * 0.9 = 45.00 EUR. */
    private const RECORD_FOREIGN_CURRENCY =
        '5555000011112222:20260716:20260717::50,:USD:D:0,9:45,:EUR:D:EXAMPLE ONLINE:EXAMPLE.COM 555500******2233::::::::J:1000000000000002:5734';

    /** The monthly settlement is a credit and has no merchant, hence no merchant category code. */
    private const RECORD_SETTLEMENT =
        '5555000011112222:20260701:20260701::1000,:EUR:C:1,:1000,:EUR:C:Ausgleich Kreditkartenabrechnung:555500******2200::::::::J:1000000000000003';

    /**
     * A second card on the same account (different scheme, hence a different masked number) and an
     * umlaut, which arrives ISO-8859-1 encoded on the wire.
     */
    private const RECORD_SECOND_CARD =
        "5555000011112222:20260620:20260621::99,95:EUR:D:1,:99,95:EUR:D:Beispiel Baumarkt:T\xFCbingen 544444******2244::::::::J:1000000000000004:5200";

    /**
     * Older bookings identify the individual card rather than the account, use a date based reference
     * and carry no merchant category code.
     */
    private const RECORD_LEGACY_FORMAT =
        '5555000011112233:20260429:20260430::6,13:EUR:D:1,:6,13:EUR:D:BEISPIEL BAECKEREI:MUSTERSTADT 5555********2233::::::::J:2026043055550000111122331';

    private static function buildSegment(string ...$records): string
    {
        // Segmentkopf + Kontonummer + (empty) + Saldo + (empty) + (empty) + records
        return 'DIKKU:5:2:3+' . self::ACCOUNT . '++D:1234,56:EUR:20260719+++' . implode('+', $records) . "'";
    }

    public function testParsesHeaderAndBalance()
    {
        $dikku = DIKKUv2::parse(self::buildSegment(self::RECORD_DOMESTIC));

        $this->assertEquals('DIKKU', $dikku->getName());
        $this->assertEquals(2, $dikku->getVersion());
        $this->assertEquals(self::ACCOUNT, $dikku->getKontonummer());

        $saldo = $dikku->getSaldo();
        $this->assertNotNull($saldo);
        // 'D' means the customer owes the money, so the amount is negative.
        $this->assertEquals(-1234.56, $saldo->getAmount());
        $this->assertEquals('EUR', $saldo->getCurrency());
        $this->assertEquals('2026-07-19', $saldo->getTimestamp()->format('Y-m-d'));
    }

    public function testParsesDomesticRecord()
    {
        $dikku = DIKKUv2::parse(self::buildSegment(self::RECORD_DOMESTIC));
        $this->assertCount(1, $dikku->getUmsaetze());
        $umsatz = $dikku->getUmsaetze()[0];

        $this->assertEquals('5555000011112222', $umsatz->kontonummer);
        $this->assertEquals('20260717', $umsatz->belegdatum);
        $this->assertEquals('20260718', $umsatz->buchungsdatum);
        $this->assertEquals(12.34, $umsatz->betrag->wert);
        $this->assertEquals('EUR', $umsatz->betrag->waehrung);
        $this->assertEquals('D', $umsatz->sollHabenKennzeichen);
        $this->assertEquals(1.0, $umsatz->umrechnungskurs);
        $this->assertEquals(12.34, $umsatz->ursprungsbetrag->wert);
        $this->assertEquals('EUR', $umsatz->ursprungsbetrag->waehrung);
        $this->assertEquals('1000000000000001', $umsatz->referenz);
        $this->assertEquals('5411', $umsatz->branchenschluessel);

        // Only the first two of the nine Verwendungszweck fields are populated.
        $this->assertEquals(['EXAMPLE SHOP', 'BERLIN 555500******2233'], $umsatz->getVerwendungszweckLines());
        $this->assertNull($umsatz->verwendungszweck3);
        $this->assertNull($umsatz->verwendungszweck9);
    }

    /**
     * AqBanking documents the original amount as a duplicate of the billed amount and the exchange
     * rate as always "1,". Both are wrong, which only shows on foreign currency transactions.
     */
    public function testParsesForeignCurrencyRecord()
    {
        $dikku = DIKKUv2::parse(self::buildSegment(self::RECORD_FOREIGN_CURRENCY));
        $umsatz = $dikku->getUmsaetze()[0];

        $this->assertEquals(50.0, $umsatz->ursprungsbetrag->wert);
        $this->assertEquals('USD', $umsatz->ursprungsbetrag->waehrung);
        $this->assertEquals(0.9, $umsatz->umrechnungskurs);
        $this->assertEquals(45.0, $umsatz->betrag->wert);
        $this->assertEquals('EUR', $umsatz->betrag->waehrung);
        $this->assertEqualsWithDelta(
            $umsatz->betrag->wert,
            $umsatz->ursprungsbetrag->wert * $umsatz->umrechnungskurs,
            0.005
        );
    }

    public function testParsesRecordWithoutMerchantCategoryCode()
    {
        $dikku = DIKKUv2::parse(self::buildSegment(self::RECORD_SETTLEMENT));
        $umsatz = $dikku->getUmsaetze()[0];

        $this->assertEquals('C', $umsatz->sollHabenKennzeichen);
        $this->assertEquals(1000.0, $umsatz->betrag->wert);
        $this->assertEquals('1000000000000003', $umsatz->referenz);
        $this->assertNull($umsatz->branchenschluessel);
    }

    public function testDecodesUmlautsFromIso8859()
    {
        $dikku = DIKKUv2::parse(self::buildSegment(self::RECORD_SECOND_CARD));
        $umsatz = $dikku->getUmsaetze()[0];

        // The wire format is ISO-8859-1, the parser is expected to hand out UTF-8.
        $this->assertEquals('Tübingen 544444******2244', $umsatz->verwendungszweck2);
    }

    public function testParsesLegacyFormatRecord()
    {
        $dikku = DIKKUv2::parse(self::buildSegment(self::RECORD_LEGACY_FORMAT));
        $umsatz = $dikku->getUmsaetze()[0];

        $this->assertEquals('5555000011112233', $umsatz->kontonummer);
        $this->assertEquals('2026043055550000111122331', $umsatz->referenz);
        $this->assertNull($umsatz->branchenschluessel);
    }

    public function testParsesAllRecordsTogether()
    {
        $dikku = DIKKUv2::parse(self::buildSegment(
            self::RECORD_DOMESTIC,
            self::RECORD_FOREIGN_CURRENCY,
            self::RECORD_SETTLEMENT,
            self::RECORD_SECOND_CARD,
            self::RECORD_LEGACY_FORMAT
        ));
        $this->assertCount(5, $dikku->getUmsaetze());
    }

    public function testMapsToModel()
    {
        $dikku = DIKKUv2::parse(self::buildSegment(
            self::RECORD_DOMESTIC,
            self::RECORD_FOREIGN_CURRENCY,
            self::RECORD_SETTLEMENT
        ));
        $statement = CreditCardStatement::fromSegments([$dikku]);

        $this->assertEquals(self::ACCOUNT, $statement->getAccountNumber());
        $this->assertEquals(-1234.56, $statement->getBalance());
        $this->assertEquals('EUR', $statement->getBalanceCurrency());
        $this->assertCount(3, $statement->getTransactions());

        [$domestic, $foreign, $settlement] = $statement->getTransactions();

        $this->assertEquals(-12.34, $domestic->getAmount());
        $this->assertEquals(CreditCardTransaction::CD_DEBIT, $domestic->getCreditDebit());
        $this->assertEquals('2026-07-18', $domestic->getBookingDate()->format('Y-m-d'));
        $this->assertEquals('2026-07-17', $domestic->getValutaDate()->format('Y-m-d'));
        $this->assertEquals('EXAMPLE SHOP BERLIN 555500******2233', $domestic->getPurpose());
        // getMerchant() returns just the first line, without the location and masked card number that
        // getPurpose() carries, so the same merchant yields a stable name across bookings.
        $this->assertEquals('EXAMPLE SHOP', $domestic->getMerchant());
        $this->assertEquals(['EXAMPLE SHOP', 'BERLIN 555500******2233'], $domestic->getPurposeLines());
        $this->assertEquals('5411', $domestic->getMerchantCategoryCode());
        // No conversion took place, so no original amount is reported.
        $this->assertNull($domestic->getOriginalAmount());
        $this->assertNull($domestic->getExchangeRate());

        $this->assertEquals(-45.0, $foreign->getAmount());
        $this->assertEquals('EUR', $foreign->getCurrency());
        $this->assertEquals(-50.0, $foreign->getOriginalAmount());
        $this->assertEquals('USD', $foreign->getOriginalCurrency());
        $this->assertEquals(0.9, $foreign->getExchangeRate());

        // A credit is positive and has no merchant category code.
        $this->assertEquals(1000.0, $settlement->getAmount());
        $this->assertEquals(CreditCardTransaction::CD_CREDIT, $settlement->getCreditDebit());
        $this->assertNull($settlement->getMerchantCategoryCode());
        // A booking without a real merchant still exposes its first line via getMerchant().
        $this->assertEquals('Ausgleich Kreditkartenabrechnung', $settlement->getMerchant());
    }

    public function testEmptyStatement()
    {
        $statement = CreditCardStatement::fromSegments([]);
        $this->assertCount(0, $statement->getTransactions());
        $this->assertNull($statement->getBalance());
        $this->assertNull($statement->getAccountNumber());
    }
}
