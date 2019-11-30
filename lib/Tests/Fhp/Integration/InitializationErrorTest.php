<?php

namespace Tests\Fhp\Integration;

use Fhp\Protocol\ServerException;
use Tests\Fhp\FinTsTestCase;

class InitializationErrorTest extends FinTsTestCase
{
    /**
     * @throws \Throwable
     */
    public function testInitializationErrorMissingField()
    {
        $this->expectMessage(
            "HNHBK:1:3+000000000145+300+0+1'HKIDN:2:2+280:11223344+9999999999+0+0'HKVVB:3:3+0+0+0+123456789ABCDEF0123456789+1.0'HKTAN:4:6+4+HKIDN'HNHBS:5:1+1'",
            // Here the bank complains that HKIDN (segment 3) $kundensystemId (element 4) is mandatory but missing.
            // The field is actually present in the request (because the SUT is implemented correctly), but for testing
            // purposes we pretend that the bank reported this error anyway.
            "HNHBK:1:3+000000000200+300+0+1+0:1'HIRMG:2:2+9050::Die Nachricht enthält Fehler.+9800::Dialog abgebrochen'HIRMS:3:2:1+3110::Segment unbekannt'HIRMS:4:2:3+9160:4:Pflichtfeld nicht gefunden'HNHBS:5:1+1'");
        $this->connection->expects($this->once())->method('disconnect');
        $this->expectException(ServerException::class);
        $this->expectExceptionMessageMatches('/Pflichtfeld nicht gefunden/');
        $this->fints->selectTanMode(921, 'SomePhone1');
        $this->fints->initDialog();
    }

    /**
     * @throws \Throwable
     */
    public function testInitializationErrorInvalidSegment()
    {
        $this->expectMessage(
            "HNHBK:1:3+000000000145+300+0+1'HKIDN:2:2+280:11223344+9999999999+0+0'HKVVB:3:3+0+0+0+123456789ABCDEF0123456789+1.0'HKTAN:4:6+4+HKIDN'HNHBS:5:1+1'",
            // Here the bank responds as if one of the segments were invalid (e.g. missing its segment number). Note
            // again that the request produced by the SUT is valid, but for the sake of this test we pretend that the
            // bank responds with an error anyway, to see how it is handled.
            "HNHBK:1:3+000000000200+300+0+1+0:1'HIRMG:2:2+9050::Die Nachricht enthält Fehler.+9800::Dialog abgebrochen+9110::Falsche Segmentzusammenstellung:HNSHK/A'HIRMS:3:2:1+3110::Segment unbekannt'HNHBS:4:1+1'");
        $this->connection->expects($this->once())->method('disconnect');
        $this->expectException(ServerException::class);
        $this->expectExceptionMessageMatches('/Falsche Segmentzusammenstellung/');
        $this->fints->selectTanMode(921, 'SomePhone1');
        $this->fints->initDialog();
    }
}
