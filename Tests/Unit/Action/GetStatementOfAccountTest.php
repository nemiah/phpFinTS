<?php

namespace Fhp\Tests\Unit\Action;

use Fhp\Action\GetStatementOfAccount;
use Fhp\Model\SEPAAccount;
use Fhp\Protocol\BPD;
use Fhp\Protocol\Message;
use Fhp\Protocol\UPD;
use Fhp\Segment\BaseSegment;
use Fhp\Segment\CAZ\HKCAZv1;
use Fhp\Segment\KAZ\HIKAZSv5;
use Fhp\Segment\KAZ\HIKAZSv6;
use Fhp\Segment\KAZ\HIKAZSv7;
use Fhp\Segment\KAZ\HKKAZv5;
use Fhp\Segment\KAZ\HKKAZv6;
use Fhp\Segment\KAZ\HKKAZv7;
use Fhp\Segment\KAZ\ParameterKontoumsaetzeV2;
use Fhp\Segment\SPA\HISPASv1;
use Fhp\Segment\SPA\ParameterSepaKontoverbindungAnfordernV1;

class GetStatementOfAccountTest extends \PHPUnit\Framework\TestCase
{
    public function testRejectsReversedDateRange()
    {
        $this->expectException(\InvalidArgumentException::class);
        GetStatementOfAccount::create(
            self::account(),
            new \DateTimeImmutable('2026-07-19'),
            new \DateTimeImmutable('2026-04-20')
        );
    }

    public function testCreateRequestBuildsHkkazV5FromDateTimeInterface()
    {
        $action = GetStatementOfAccount::create(
            self::account(),
            new \DateTimeImmutable('2026-04-20'),
            new \DateTimeImmutable('2026-07-19'),
            true
        );

        $requestSegments = $action->getNextRequest(self::createBpd(self::createHikazsSegment(5, true)), null);

        $this->assertCount(1, $requestSegments);
        /** @var HKKAZv5 $request */
        $request = $requestSegments[0];
        self::assertInstanceOf(HKKAZv5::class, $request);
        self::assertTrue($request->alleKonten);
        self::assertSame('20260420', $request->vonDatum);
        self::assertSame('20260719', $request->bisDatum);
        self::assertSame('5407324931', $request->kontoverbindungAuftraggeber->kontonummer);
        self::assertSame('50010517', $request->kontoverbindungAuftraggeber->kik->kreditinstitutscode);
    }

    public function testCreateRequestBuildsHkkazV7FromDateTimeInterface()
    {
        $action = GetStatementOfAccount::create(
            self::account(),
            new \DateTimeImmutable('2026-04-20'),
            new \DateTimeImmutable('2026-07-19'),
            true
        );

        $bpd = self::createBpd(
            self::createHikazsSegment(7, true),
            self::createHispasSegment(true)
        );
        $requestSegments = $action->getNextRequest($bpd, null);

        self::assertCount(1, $requestSegments);
        /** @var HKKAZv7 $request */
        $request = $requestSegments[0];
        self::assertInstanceOf(HKKAZv7::class, $request);
        self::assertTrue($request->alleKonten);
        self::assertSame('20260420', $request->vonDatum);
        self::assertSame('20260719', $request->bisDatum);
        self::assertSame('DE44500105175407324931', $request->kontoverbindungInternational->iban);
        self::assertSame('INGDDEFFXXX', $request->kontoverbindungInternational->bic);
        self::assertSame('5407324931', $request->kontoverbindungInternational->kontonummer);
    }

    public function testCreateRequestRejectsAllAccountsWhenNotSupportedByBank()
    {
        $action = GetStatementOfAccount::create(self::account(), null, null, true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('allAccounts=true');
        $action->getNextRequest(self::createBpd(self::createHikazsSegment(6, false)), null);
    }

    /**
     * Banks without HIKAZS (e.g. Atruvia/Volksbank) make the action fall back to HKCAZ. Those banks also tend to
     * require a TAN for HKCAZ, so applications serialize the action while waiting for it and then feed the response
     * to the unserialized copy. The fallback must survive that roundtrip, otherwise the copy expects HIKAZ segments.
     */
    public function testXmlFallbackSurvivesSerialization()
    {
        $action = GetStatementOfAccount::create(self::account(), new \DateTimeImmutable('2026-04-20'), new \DateTimeImmutable('2026-07-19'));

        $requestSegments = $action->getNextRequest(self::createCamtOnlyBpd(), self::createUpdWithHkcaz());
        self::assertCount(1, $requestSegments);
        self::assertInstanceOf(HKCAZv1::class, $requestSegments[0]);
        // FinTs::execute() numbers the segments before sending, which serialization of the request relies on.
        $requestSegments[0]->setSegmentNumber(3);
        $action->setRequestSegmentNumbers([3]);

        /** @var GetStatementOfAccount $action */
        $action = unserialize(serialize($action));

        $action->processResponse(Message::parse(self::camtResponse()));

        $statements = $action->getStatement()->getStatements();
        self::assertCount(1, $statements);
        $transactions = $statements[0]->getTransactions();
        self::assertCount(1, $transactions);
        self::assertSame(12.5, $transactions[0]->getAmount());
        self::assertSame('Max Mustermann', $transactions[0]->getName());
    }

    /** Actions serialized by earlier versions of this library carry no XML fallback and must still unserialize. */
    public function testUnserializesActionsWithoutXmlFallback()
    {
        $action = GetStatementOfAccount::create(self::account(), new \DateTimeImmutable('2026-04-20'), new \DateTimeImmutable('2026-07-19'));
        $serialized = $action->__serialize();
        self::assertNull(array_pop($serialized)); // Drop the trailing XML fallback entry to emulate the old format.

        $unserialized = new GetStatementOfAccount();
        $unserialized->__unserialize($serialized);

        $requestSegments = $unserialized->getNextRequest(self::createBpd(self::createHikazsSegment(6, true)), null);
        self::assertCount(1, $requestSegments);
        self::assertInstanceOf(HKKAZv6::class, $requestSegments[0]);
        self::assertSame('20260420', $requestSegments[0]->vonDatum);
    }

    /** A BPD that offers HKCAZ but no HKKAZ at all, like Atruvia banks do. */
    private static function createCamtOnlyBpd(): BPD
    {
        $hicazs = BaseSegment::parse("HICAZS:58:1:3+1+1+1+90:N:N:urn?:iso?:std?:iso?:20022?:tech?:xsd?:camt.052.001.02'");
        $bpd = self::createBpd($hicazs, self::createHispasSegment(true));
        unset($bpd->parameters['HIKAZS']);
        $bpd->parameters['HICAZS'][$hicazs->getVersion()] = $hicazs;
        return $bpd;
    }

    private static function createUpdWithHkcaz(): UPD
    {
        $upd = new UPD();
        $upd->hiupd = [BaseSegment::parse(
            "HIUPD:7:6:4+5407324931::280:50010517+DE44500105175407324931+9999999999+1+EUR+Mustermann+Max+Girokonto++HKSAK:1+HKCAZ:1+HKSPA:1'"
        )];
        return $upd;
    }

    private static function camtResponse(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<Document xmlns="urn:iso:std:iso:20022:tech:xsd:camt.052.001.02">'
            . '<BkToCstmrAcctRpt><GrpHdr><MsgId>1</MsgId><CreDtTm>2026-07-19T10:00:00+02:00</CreDtTm></GrpHdr>'
            . '<Rpt><Id>1</Id><Acct><Id><IBAN>DE44500105175407324931</IBAN></Id></Acct>'
            . '<Ntry><Amt Ccy="EUR">12.50</Amt><CdtDbtInd>CRDT</CdtDbtInd><Sts>BOOK</Sts>'
            . '<BookgDt><Dt>2026-07-17</Dt></BookgDt><ValDt><Dt>2026-07-17</Dt></ValDt>'
            . '<NtryDtls><TxDtls><RltdPties><Dbtr><Nm>Max Mustermann</Nm></Dbtr>'
            . '<DbtrAcct><Id><IBAN>DE89370400440532013000</IBAN></Id></DbtrAcct></RltdPties>'
            . '<RmtInf><Ustrd>Rechnung 4711</Ustrd></RmtInf></TxDtls></NtryDtls></Ntry>'
            . '</Rpt></BkToCstmrAcctRpt></Document>';

        return "HNHBK:1:3+000000000000+300+0+1+0:1'"
            . "HIRMG:2:2+0020::Auftrag ausgeführt.'"
            . 'HICAZ:3:1:3+DE44500105175407324931:INGDDEFFXXX:5407324931::280:50010517+urn?:iso?:std?:iso?:20022?:tech?:xsd?:camt.052.001.02+@' . strlen($xml) . '@' . $xml . "'"
            . "HNHBS:4:1+1'";
    }

    private static function account(): SEPAAccount
    {
        return (new SEPAAccount())
            ->setIban('DE44500105175407324931')
            ->setBic('INGDDEFFXXX')
            ->setAccountNumber('5407324931')
            ->setBlz('50010517');
    }

    private static function parameterKontoumsaetze(bool $allAccountsAllowed): ParameterKontoumsaetzeV2
    {
        $parameter = new ParameterKontoumsaetzeV2();
        $parameter->speicherzeitraum = 90;
        $parameter->eingabeAnzahlEintraegeErlaubt = false;
        $parameter->alleKontenErlaubt = $allAccountsAllowed;
        return $parameter;
    }

    private static function createHikazsSegment(int $version, bool $allAccountsAllowed): HIKAZSv5|HIKAZSv6|HIKAZSv7
    {
        switch ($version) {
            case 5:
                $segment = HIKAZSv5::createEmpty();
                break;
            case 6:
                $segment = HIKAZSv6::createEmpty();
                break;
            case 7:
                $segment = HIKAZSv7::createEmpty();
                break;
            default:
                throw new \InvalidArgumentException("Unsupported test HIKAZS version $version");
        }

        $segment->maximaleAnzahlAuftraege = 1;
        $segment->anzahlSignaturenMindestens = 1;
        if (!$segment instanceof HIKAZSv5) {
            $segment->sicherheitsklasse = 0;
        }
        $segment->parameter = self::parameterKontoumsaetze($allAccountsAllowed);
        return $segment;
    }

    private static function createHispasSegment(bool $nationalAccountAllowed): HISPASv1
    {
        $parameter = new ParameterSepaKontoverbindungAnfordernV1();
        $parameter->einzelkontenabrufErlaubt = true;
        $parameter->nationaleKontoverbindungErlaubt = $nationalAccountAllowed;
        $parameter->strukturierterVerwendungszweckErlaubt = false;
        $parameter->unterstuetzteSepaDatenformate = ['urn:iso:std:iso:20022:tech:xsd:pain.001.001.03'];

        $segment = HISPASv1::createEmpty();
        $segment->maximaleAnzahlAuftraege = 1;
        $segment->anzahlSignaturenMindestens = 1;
        $segment->sicherheitsklasse = 0;
        $segment->parameter = $parameter;
        return $segment;
    }

    private static function createBpd(BaseSegment $hikazs, ?BaseSegment $hispas = null): BPD
    {
        $bpd = new class extends BPD {
            public function getBankName()
            {
                return 'Testbank';
            }
        };

        $bpd->parameters['HIKAZS'][$hikazs->getVersion()] = $hikazs;
        if ($hispas !== null) {
            $bpd->parameters['HISPAS'][$hispas->getVersion()] = $hispas;
        }

        return $bpd;
    }
}
