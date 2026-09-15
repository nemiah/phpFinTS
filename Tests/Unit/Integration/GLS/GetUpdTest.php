<?php

namespace Fhp\Tests\Unit\Integration\GLS;

class GetUpdTest extends GLSIntegrationTestBase
{
    /**
     * @throws \Throwable
     */
    public function testUpdIsAvailableAfterLogin()
    {
        $this->assertNull($this->fints->getUpd());

        $this->initDialog();

        $upd = $this->fints->getUpd();
        $this->assertNotNull($upd);
        $hiupd = $upd->findHiupd($this->getTestAccount());
        $this->assertNotNull($hiupd);
        $this->assertSame('Kontokorrent', $hiupd->getKontoproduktbezeichnung());
        $this->assertSame('EUR', $hiupd->getKontowaehrung());
        $this->assertTrue($upd->isRequestSupportedForAccount($this->getTestAccount(), 'HKCAZ'));
    }
}
