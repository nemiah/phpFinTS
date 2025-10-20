<?php

namespace Fhp;

use Tests\Fhp\Integration\DKB\SendSEPATransferTest as DKBSendSEPATransferTest;

class BaseActionTanSerializationTest extends DKBSendSEPATransferTest
{
    /**
     * @throws \Throwable
     */
    public function testSerializesTanRequest()
    {
        // We piggy-back on the DKB integration test to provide an action that has some reasonable data inside and that
        // has already been executed so that a TAN request is present.
        $this->initDialog();
        $this->expectMessage($this->getSendTransferRequest(), static::SEND_TRANSFER_RESPONSE);
        $originalAction = $this->runInitialRequest();

        // Sanity-check that the TAN request is present.
        $this->assertNotNull($originalAction->getTanRequest());
        $this->assertNotNull($originalAction->getNeedTanForSegment());

        // Do a serialization roundtrip.
        $serializedAction = serialize($originalAction);
        $unserializedAction = unserialize($serializedAction);

        // Verify that the TAN request hasn't changed.
        $this->assertEquals($originalAction->getTanRequest(), $unserializedAction->getTanRequest());
        $this->assertEquals($originalAction->getNeedTanForSegment(), $unserializedAction->getNeedTanForSegment());
    }
}
