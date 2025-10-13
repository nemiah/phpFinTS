<?php

namespace Fhp\Segment;

use Fhp\Segment\VPP\HIVPPSv1;
use Fhp\Segment\VPP\ParameterNamensabgleichPruefauftragV1;
use Fhp\Syntax\Parser;
use PHPUnit\Framework\TestCase;

/**
 * Among other things, this test covers the serialization of arrays with @Max annotation.
 */
class HIVPPSTest extends TestCase
{
    private HIVPPSv1 $hivpps;

    public function setUp(): void
    {
        $this->hivpps = HIVPPSv1::createEmpty();
        $this->hivpps->setSegmentNumber(42);
        $this->hivpps->maximaleAnzahlAuftraege = 43;
        $this->hivpps->anzahlSignaturenMindestens = 44;
        $this->hivpps->sicherheitsklasse = 45;
        $this->hivpps->parameter = new ParameterNamensabgleichPruefauftragV1();
        $this->hivpps->parameter->maximaleAnzahlCreditTransferTransactionInformationOptIn = 1;
        $this->hivpps->parameter->aufklaerungstextStrukturiert = true;
        $this->hivpps->parameter->artDerLieferungPaymentStatusReport = 'Art';
        $this->hivpps->parameter->sammelzahlungenMitEinemAuftragErlaubt = false;
        $this->hivpps->parameter->eingabeAnzahlEintraegeErlaubt = false;
        $this->hivpps->parameter->unterstuetztePaymentStatusReportDatenformate = 'Test';
    }

    public function testPopulatedArray()
    {
        $this->hivpps->parameter->vopPflichtigerZahlungsverkehrsauftrag = ['HKFOO', 'HKBAR'];

        $serialized = $this->hivpps->serialize();
        $this->assertEquals("HIVPPS:42:1+43+44+45+1:J:Art:N:N:Test:HKFOO:HKBAR'", $serialized);

        /** @var HIVPPSv1 $parsed */
        $parsed = Parser::parseSegment($serialized, HIVPPSv1::class);
        $this->assertEquals(['HKFOO', 'HKBAR'], $parsed->parameter->vopPflichtigerZahlungsverkehrsauftrag);
    }
}
