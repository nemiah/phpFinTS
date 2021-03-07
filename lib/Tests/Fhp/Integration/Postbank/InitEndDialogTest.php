<?php

namespace Tests\Fhp\Integration\Postbank;

class InitEndDialogTest extends PostbankIntegrationTestBase
{
    /**
     * @throws \Fhp\Protocol\ServerException
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
