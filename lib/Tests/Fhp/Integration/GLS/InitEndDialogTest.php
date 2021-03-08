<?php

namespace Tests\Fhp\Integration\GLS;

class InitEndDialogTest extends GLSIntegrationTestBase
{
    /**
     * @throws \Fhp\Protocol\ServerException
     * @throws \Throwable
     */
    public function testInitAnonymous()
    {
        $this->InitAnonymous();
    }

    public function testInitAndEndDialog()
    {
        $this->initDialog();
        $this->assertNotNull($this->fints->getDialogId());
        $this->expectMessage(static::FINAL_END_REQUEST, static::FINAL_END_RESPONSE);
        $this->fints->endDialog();
    }
}
