<?php

namespace Fhp\Segment;

use Fhp\Protocol\Message;
use Fhp\Protocol\MessageBuilder;
use Fhp\Segment\HIRMG\HIRMGv2;
use Fhp\Segment\HIRMS\HIRMSv2;
use Fhp\Syntax\Parser;
use PHPUnit\Framework\TestCase;

class FindRueckmeldungTraitTest extends TestCase
{
    private const TEST_RESPONSE = "HIRMG:2:2:+3060::Teilweise liegen Warnungen/Hinweise vor.'HIRMS:3:2:3+0020::Angemeldet.+3076::Keine starke Authentifizierung erforderlich.+0901::PIN gultig.'HIRMS:4:2:4+0020::Informationen fehlerfrei entgegengenommen.+3920::Zugelassene Ein- und Zwei-Schritt-Verfahren fur den Benutzer:900'HIRMS:5:2:6+0020::Die Synchronisierung der Kundensystem-ID war erfolgreich.'";

    private Message $testMessage;

    protected function setUp(): void
    {
        // We can't use Message::parse() because that also tries to unwrap the message.
        $this->testMessage = Message::createPlainMessage(
            MessageBuilder::create()
                ->add(Parser::parseSegments(self::TEST_RESPONSE))
        );
    }

    public function testFindRueckmeldungFound()
    {
        $this->assertEquals('Angemeldet.', $this->testMessage->findRueckmeldung(20)->rueckmeldungstext);
        $this->assertEquals([900], $this->testMessage->findRueckmeldung(3920)->rueckmeldungsparameter);
    }

    public function testFindRueckmeldungNotFound()
    {
        $this->assertNull($this->testMessage->findRueckmeldung(42));
    }

    public function testFindRueckmeldungen()
    {
        $this->assertCount(3, $this->testMessage->findRueckmeldungen(20));
        $this->assertCount(1, $this->testMessage->findRueckmeldungen(3920));
        $this->assertCount(0, $this->testMessage->findRueckmeldungen(42));
    }

    public function testGetAllRueckmeldungen()
    {
        /** @var HIRMGv2 $hirmg */
        $hirmg = $this->testMessage->findSegmentByNumber(2);
        $this->assertCount(1, $hirmg->getAllRueckmeldungen());

        /** @var HIRMSv2 $hirms */
        $hirms = $this->testMessage->findSegmentByNumber(3);
        $this->assertCount(3, $hirms->getAllRueckmeldungen());
    }

    public function testFindRueckmeldungscodesForReferenceSegment()
    {
        $this->assertEquals([20, 3076, 901], $this->testMessage->findRueckmeldungscodesForReferenceSegment(3));
        $this->assertEquals([20, 3920], $this->testMessage->findRueckmeldungscodesForReferenceSegment(4));
        $this->assertEquals([20], $this->testMessage->findRueckmeldungscodesForReferenceSegment(6));
    }
}
