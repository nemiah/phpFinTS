<?php

namespace Tests\Fhp\Integration\Consors;

use Tests\Fhp\FinTsPeer;

class GetBPDTest extends ConsorsIntegrationTestBase
{
    /**
     * @throws \Throwable
     */
    public function test_getBpd()
    {
        // For this test, only the BPD should be requested in an anonymous dialog.
        $this->expectMessage(static::ANONYMOUS_INIT_REQUEST, static::ANONYMOUS_INIT_RESPONSE);
        $this->expectMessage(static::ANONYMOUS_END_REQUEST, static::ANONYMOUS_END_RESPONSE);

        $bpd = FinTsPeer::fetchBpd($this->options);

        $this->assertEquals('Consors', $bpd->hibpa->kreditinstitutsbezeichnung);
        $this->assertTrue($bpd->supportsPsd2());
        $this->assertTrue($bpd->supportsParameters('HIKAZS', 6));
    }
}
