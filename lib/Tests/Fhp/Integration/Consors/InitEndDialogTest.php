<?php

namespace Tests\Fhp\Integration\Consors;

use Fhp\CurlException;
use Fhp\Protocol\ServerException;

class InitEndDialogTest extends ConsorsIntegrationTestBase
{
    /**
     * @throws CurlException
     * @throws ServerException
     */
    public function test_getTanModes_and_getTanMedia()
    {
        // When we request the TAN modes, it should first request the BPD anonymously.
        $this->expectMessage(static::ANONYMOUS_INIT_REQUEST, static::ANONYMOUS_INIT_RESPONSE);
        $this->expectMessage(static::ANONYMOUS_END_REQUEST, static::ANONYMOUS_END_RESPONSE);
        // And then it should use a personal dialog with Sicherheitsfunktion=900 (auto-guessed because it's the only one) to retrieve the allowed modes (3920).
        $this->expectMessage(static::SYNC_REQUEST, static::SYNC_RESPONSE);
        $this->expectMessage(static::SYNC_END_REQUEST, static::SYNC_END_RESPONSE);

        $tanModes = $this->fints->getTanModes();
        $this->assertAllMessagesSeen();

        $this->assertArrayHasKey(900, $tanModes);
        $tanMode = $tanModes[900];
        $this->assertEquals('SecurePlus', $tanMode->getName());
        $this->assertFalse($tanMode->needsTanMedium());
        $this->fints->selectTanMode($tanMode);
    }

    /**
     * @throws \Throwable
     */
    public function test_init_and_end_dialog()
    {
        $this->initDialog();
        $this->assertNotNull($this->fints->getDialogId());
        $this->expectMessage(static::FINAL_END_REQUEST, static::FINAL_END_RESPONSE);
        $this->fints->endDialog();
    }
}
