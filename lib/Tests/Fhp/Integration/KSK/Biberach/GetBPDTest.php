<?php

namespace Tests\Fhp\Integration\KSK\Biberach;

use Tests\Fhp\FinTsPeer;

class GetBPDTest extends KskBiberachIntegrationTestBase
{
    /**
     * @throws \Throwable
     */
    public function testGetBpd()
    {
        // For this test, only the BPD should be requested in an anonymous dialog.
        $this->expectMessage(static::ANONYMOUS_INIT_REQUEST, static::ANONYMOUS_INIT_RESPONSE);
        $this->expectMessage(static::ANONYMOUS_END_REQUEST, static::ANONYMOUS_END_RESPONSE);

        $bpd = FinTsPeer::fetchBpd($this->options);

        $this->assertTrue($bpd->supportsPsd2());
        $this->assertArrayHasKey(922, $bpd->allTanModes); // From HITANSv7
        $this->assertArrayHasKey(910, $bpd->allTanModes); // From HITANSv6
    }
}
