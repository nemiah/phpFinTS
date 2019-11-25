<?php

namespace Fhp\Protocol;

class ServerExceptionTest extends \PHPUnit\Framework\TestCase
{
    const RESPONSE_WITH_WARNINGS = "HNHBK:1:3+000000000489+300+0+1+0:1'HIRMG:2:2:+3060::Teilweise liegen Warnungen/Hinweise vor.'HIRMS:3:2:3+0020::Angemeldet.+3076::Keine starke Authentifizierung erforderlich.+0901::PIN gultig.'HIRMS:4:2:4+0020::Informationen fehlerfrei entgegengenommen.+3920::Zugelassene Ein- und Zwei-Schritt-Verfahren fur den Benutzer:900'HIRMS:5:2:6+0020::Die Synchronisierung der Kundensystem-ID war erfolgreich.'HISYN:6:4:6+111111111111111111111111111111'HITAN:7:6:5+4++noref+nochallenge'HNHBS:8:1+1'";
    const RESPONSE_WITH_ERRORS = "HNHBK:1:3+000000000164+300+0+1+0:1'HIRMG:2:2:+9800::Der Dialog wurde abgebrochen.+9010::Unerwartete Nachrichtennummer. Bitte melden Sie sich erneut an.'HNHBS:3:1+1'";

    const REQUEST_WITH_SEGMENT_NUMBERS = "HNHBK:1:3+000000000126+300+0+4'HKKAZ:2:7+DE03123456789123456789:CSDBDE71XXX+N+20190901+20190922'HKTAN:3:6+4+HKKAZ'HNHBS:4:1+4'";
    const RESPONSE_WITH_HIRMS_NUMBERS = "HNHBK:1:3+000000000241+300+0+4+0:4'HIRMG:2:2:+9050::Nachricht teilweise fehlerhaft.'HIRMS:3:2:2+9010::Verarbeitung nicht moglich.+3076::Keine starke Authentifizierung erforderlich.'HIRMS:4:2:3+3070::Wir brauchen heute keine TAN.'HNHBS:5:1+4'";

    /**
     * @throws ServerException This should not actually throw because there are only warnings in the response.
     */
    public function test_detectAndThrowErrors_onlyWarnings()
    {
        $request = Message::createPlainMessage([]);
        $response = Message::parse(static::RESPONSE_WITH_WARNINGS);
        ServerException::detectAndThrowErrors($response, $request);
        $this->assertTrue(true);
    }

    /**
     * @throws ServerException This should not actually throw because there are only warnings in the response.
     */
    public function test_detectAndThrowErrors_withErrors()
    {
        $request = Message::createPlainMessage([]);
        $response = Message::parse(static::RESPONSE_WITH_ERRORS);
        $this->expectException(ServerException::class);
        ServerException::detectAndThrowErrors($response, $request);
    }

    private function getTestServerException()
    {
        $request = Message::parse(static::REQUEST_WITH_SEGMENT_NUMBERS);
        $response = Message::parse(static::RESPONSE_WITH_HIRMS_NUMBERS);
        try {
            ServerException::detectAndThrowErrors($response, $request);
            $this->assertTrue(false);
            return null;
        } catch (ServerException $exception) {
            $this->assertCount(2, $exception->getErrors()); // 9050 and 9010
            $this->assertCount(2, $exception->getWarnings()); // 3076 and 3070
            return $exception;
        }
    }

    public function test_extractErrorsForReference_takeNothing()
    {
        $exception = $this->getTestServerException();
        $extracted = $exception->extractErrorsForReference([]);
        $this->assertNull($extracted);
        $this->assertCount(2, $exception->getErrors()); // Unchanged
        $this->assertCount(2, $exception->getWarnings()); // Unchanged
    }

    public function test_extractErrorsForReference_takeSomeIncludingError()
    {
        $exception = $this->getTestServerException();
        $extracted = $exception->extractErrorsForReference([2]); // The 9010 references segment 2.
        $this->assertCount(1, $exception->getErrors()); // HIRMG
        $this->assertCount(1, $exception->getWarnings()); // 3070
        $this->assertCount(1, $extracted->getErrors()); // HIRMS 9010
        $this->assertCount(1, $extracted->getWarnings()); // 3076
    }

    public function test_extractErrorsForReference_takeSomeWithoutError()
    {
        $exception = $this->getTestServerException();
        $extracted = $exception->extractErrorsForReference([3]); // No error references segment 3.
        $this->assertNull($extracted);
        $this->assertCount(2, $exception->getErrors()); // Unchanged
        $this->assertCount(2, $exception->getWarnings()); // Unchanged
    }

    public function test_extractErrorsForReference_takeAll()
    {
        $request = Message::parse(static::REQUEST_WITH_SEGMENT_NUMBERS);
        $exception = $this->getTestServerException();
        $extracted = $exception->extractErrorsForReference($request->plainSegments);
        $this->assertCount(1, $exception->getErrors()); // HIRMG
        $this->assertCount(0, $exception->getWarnings()); // No warnings remain
        $this->assertCount(1, $extracted->getErrors()); // HIRMS 9010
        $this->assertCount(2, $extracted->getWarnings()); // 3076 and 3070
    }
}
