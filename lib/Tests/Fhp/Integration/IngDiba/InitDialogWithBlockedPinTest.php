<?php

namespace Tests\Fhp\Integration\IngDiba;

use Fhp\Protocol\ServerException;

class InitDialogWithBlockedPinTest extends IngDibaIntegrationTestBase
{
    // Note that this overrides the SYNC_RESPONSE in the super class. It's what the bank sends when the PIN was wrong.
    public const SYNC_RESPONSE = "HNHBK:1:3+000000000239+300+FAKEDIALOGIDabcdefghijklmnopqr+1+FAKEDIALOGIDabcdefghijklmnopqr:1'HIRMG:2:2:+9800::Der Dialog wurde abgebrochen.+9942::Log-in fehlgeschlagen. 3 Fehlversuche fuhren zur Sperrung. Entsperren auf ING.de'HNHBS:3:1+1'";

    /**
     * @throws \Throwable
     */
    public function testInitDialogWithBlockedPin()
    {
        $this->expectException(ServerException::class);
        $this->expectExceptionMessageMatches('/.*Log-in fehlgeschlagen.*/');
        $this->initDialog();
    }

    protected function tearDown(): void
    {
        // After the dialog was aborted due to invalid PIN, the usual dialog initialization messages won't happen anymore.
        $this->expectedMessages = [];
        parent::tearDown();
    }
}
