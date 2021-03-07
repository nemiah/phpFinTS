<?php

namespace Tests\Fhp\Integration\IngDiba;

class InitEndDialogTest extends IngDibaIntegrationTestBase
{
    /**
     * @throws \Throwable
     */
    public function testInitAndEndDialog()
    {
        $this->initDialog();
        $this->assertNotNull($this->fints->getDialogId());
        $this->expectMessage(static::FINAL_END_REQUEST, static::FINAL_END_RESPONSE);
        $this->fints->endDialog();
    }
}
