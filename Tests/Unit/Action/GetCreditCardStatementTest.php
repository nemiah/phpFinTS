<?php

namespace Fhp\Tests\Unit\Action;

use Fhp\Action\GetCreditCardStatement;
use Fhp\Model\CreditCardAccount;
use Fhp\Protocol\BPD;
use Fhp\Protocol\Message;
use Fhp\Segment\BaseSegment;
use Fhp\Segment\HIRMS\HIRMSv2;
use Fhp\Segment\HIRMS\Rueckmeldung;
use Fhp\Segment\HIRMS\Rueckmeldungscode;
use Fhp\Segment\KKU\DIKKUv2;
use Fhp\Segment\KKU\DKKKUv2;

/**
 * Tests {@link GetCreditCardStatement}, in particular the pagination handling.
 *
 * NOTE: The pagination is exercised here with a simulated response, because it could not be
 * triggered against a real bank: the tested account's transactions all fit into a single response,
 * and DKKKU offers no way to ask for a smaller page (unlike HKKAZ it has no "maximaleAnzahlEintraege"
 * field). What this test therefore cannot prove is that the pagination token sits at the wire
 * position assumed by {@link DKKKUv2::$aufsetzpunkt} — see the note there.
 */
class GetCreditCardStatementTest extends ActionTestCase
{
    private const ACCOUNT = '5555000011112222';

    private const RECORD_1 =
        '5555000011112222:20260717:20260718::12,34:EUR:D:1,:12,34:EUR:D:EXAMPLE SHOP:BERLIN::::::::J:1000000000000001:5411';
    private const RECORD_2 =
        '5555000011112222:20260716:20260717::56,78:EUR:D:1,:56,78:EUR:D:OTHER SHOP:HAMBURG::::::::J:1000000000000002:5412';
    private const RECORD_3 =
        '5555000011112222:20260715:20260716::9,99:EUR:D:1,:9,99:EUR:D:THIRD SHOP:MUNICH::::::::J:1000000000000003:5413';

    private static function dikku(string ...$records): DIKKUv2
    {
        return DIKKUv2::parse(
            'DIKKU:5:2:3+' . self::ACCOUNT . '++D:1234,56:EUR:20260719+++' . implode('+', $records) . "'"
        );
    }

    /** Builds a response message, optionally announcing that another page follows. */
    private static function response(DIKKUv2 $dikku, ?string $paginationToken = null): Message
    {
        $segments = [];
        if ($paginationToken !== null) {
            $rueckmeldung = new Rueckmeldung();
            $rueckmeldung->rueckmeldungscode = Rueckmeldungscode::AUFSETZPUNKT;
            $rueckmeldung->rueckmeldungstext = 'Es liegen weitere Informationen vor.';
            $rueckmeldung->rueckmeldungsparameter = [$paginationToken];

            $hirms = HIRMSv2::createEmpty();
            $hirms->rueckmeldung = [$rueckmeldung];
            $segments[] = $hirms;
        }
        $segments[] = $dikku;
        return Message::createPlainMessage($segments);
    }

    private static function action(): GetCreditCardStatement
    {
        $account = (new CreditCardAccount())->setAccountNumber(self::ACCOUNT)->setBlz('60050101');
        return GetCreditCardStatement::create($account, new \DateTime('2026-04-20'), new \DateTime('2026-07-19'));
    }

    private static function bpd(): BPD
    {
        return self::createBpd(BaseSegment::parse('DIKKUS:45:2:3+1+1+0+90:N:J\''));
    }

    public function testBuildsRequest()
    {
        $action = self::action();
        $requestSegments = self::nextRequest($action, self::bpd());

        $this->assertCount(1, $requestSegments);
        /** @var DKKKUv2 $request */
        $request = $requestSegments[0];
        $this->assertInstanceOf(DKKKUv2::class, $request);
        $this->assertEquals(self::ACCOUNT, $request->kontonummer);
        $this->assertEquals(self::ACCOUNT, $request->kontoverbindung->kontonummer);
        $this->assertEquals('60050101', $request->kontoverbindung->kik->kreditinstitutscode);
        $this->assertEquals('20260420', $request->vonDatum);
        $this->assertEquals('20260719', $request->bisDatum);
        $this->assertNull($request->aufsetzpunkt);
    }

    public function testSinglePageResponse()
    {
        $action = self::action();
        self::nextRequest($action, self::bpd());
        $action->processResponse(self::response(self::dikku(self::RECORD_1, self::RECORD_2)));

        $this->assertFalse($action->hasMorePages());
        $this->assertTrue($action->isDone());
        $this->assertCount(2, $action->getStatement()->getTransactions());
    }

    public function testPaginatedResponseAccumulatesAllPages()
    {
        $action = self::action();
        $bpd = self::bpd();

        // First page: two records, and the bank announces that more are available.
        self::nextRequest($action, $bpd);
        $action->processResponse(self::response(self::dikku(self::RECORD_1, self::RECORD_2), 'PAGE2'));

        $this->assertTrue($action->hasMorePages());
        $this->assertFalse($action->isDone());

        // The follow-up request must carry the pagination token, everything else unchanged.
        $requestSegments = self::nextRequest($action, $bpd);
        /** @var DKKKUv2 $request */
        $request = $requestSegments[0];
        $this->assertEquals('PAGE2', $request->aufsetzpunkt);
        $this->assertEquals(self::ACCOUNT, $request->kontonummer);
        $this->assertEquals('20260420', $request->vonDatum);

        // Second page: one more record, no further announcement.
        $action->processResponse(self::response(self::dikku(self::RECORD_3)));

        $this->assertFalse($action->hasMorePages());
        $this->assertTrue($action->isDone());

        $transactions = $action->getStatement()->getTransactions();
        $this->assertCount(3, $transactions);
        $this->assertEquals(-12.34, $transactions[0]->getAmount());
        $this->assertEquals(-56.78, $transactions[1]->getAmount());
        $this->assertEquals(-9.99, $transactions[2]->getAmount());
    }

    /** The result must not be built before the last page arrived, since records span pages. */
    public function testStatementIsUnavailableWhileMorePagesFollow()
    {
        $action = self::action();
        self::nextRequest($action, self::bpd());
        $action->processResponse(self::response(self::dikku(self::RECORD_1), 'PAGE2'));

        $this->expectException(\Fhp\Protocol\ActionIncompleteException::class);
        $action->getStatement();
    }

    public function testSerializationRoundTripPreservesTheRequest()
    {
        $action = self::action();
        self::nextRequest($action, self::bpd());
        $action->processResponse(self::response(self::dikku(self::RECORD_1), 'PAGE2'));

        /** @var GetCreditCardStatement $restored */
        $restored = unserialize(serialize($action));

        // The restored action must continue with the same page and produce the same request.
        $this->assertTrue($restored->hasMorePages());
        $requestSegments = self::nextRequest($restored, self::bpd());
        /** @var DKKKUv2 $request */
        $request = $requestSegments[0];
        $this->assertEquals('PAGE2', $request->aufsetzpunkt);
        $this->assertEquals(self::ACCOUNT, $request->kontonummer);
        $this->assertEquals('20260420', $request->vonDatum);
        $this->assertEquals('20260719', $request->bisDatum);
    }

    private static function accountOnly(): CreditCardAccount
    {
        return (new CreditCardAccount())->setAccountNumber(self::ACCOUNT)->setBlz('60050101');
    }

    /** The DIKKUS fixture announces a period of 90 days. */
    public function testRejectsRangeSpanningMoreThanTheAnnouncedPeriod()
    {
        $action = GetCreditCardStatement::create(
            self::accountOnly(),
            new \DateTime('-200 days'),
            new \DateTime()
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/spanning more than 90 days/');
        $action->getNextRequest(self::bpd(), null);
    }

    public function testAcceptsRangeWithinTheAnnouncedPeriod()
    {
        $action = GetCreditCardStatement::create(
            self::accountOnly(),
            new \DateTime('-89 days'),
            new \DateTime()
        );

        $this->assertCount(1, $action->getNextRequest(self::bpd(), null));
    }

    /**
     * Older transactions have to be fetched window by window, so a short range far in the past must
     * be accepted even though it starts beyond the announced period.
     */
    public function testAcceptsShortRangeFarInThePast()
    {
        $action = GetCreditCardStatement::create(
            self::accountOnly(),
            new \DateTime('-180 days'),
            new \DateTime('-90 days')
        );

        $requestSegments = $action->getNextRequest(self::bpd(), null);
        $this->assertCount(1, $requestSegments);
        /** @var DKKKUv2 $request */
        $request = $requestSegments[0];
        $this->assertEquals((new \DateTime('-180 days'))->format('Ymd'), $request->vonDatum);
        $this->assertEquals((new \DateTime('-90 days'))->format('Ymd'), $request->bisDatum);
    }

    /** Without a to-date the range extends to today, which still must not be too wide. */
    public function testRejectsOpenEndedRangeStartingTooFarBack()
    {
        $action = GetCreditCardStatement::create(self::accountOnly(), new \DateTime('-200 days'));

        $this->expectException(\InvalidArgumentException::class);
        $action->getNextRequest(self::bpd(), null);
    }

    /** Without a from-date the bank decides how much to return, so there is nothing to validate. */
    public function testAcceptsRangeWithoutFromDate()
    {
        $action = GetCreditCardStatement::create(self::accountOnly());

        $this->assertCount(1, $action->getNextRequest(self::bpd(), null));
    }

    public function testRejectsAnAccountWithoutNumber()
    {
        $this->expectException(\InvalidArgumentException::class);
        GetCreditCardStatement::create(new CreditCardAccount());
    }

    public function testRejectsReversedDateRange()
    {
        $this->expectException(\InvalidArgumentException::class);
        GetCreditCardStatement::create(
            (new CreditCardAccount())->setAccountNumber(self::ACCOUNT),
            new \DateTime('2026-07-19'),
            new \DateTime('2026-04-20')
        );
    }
}
