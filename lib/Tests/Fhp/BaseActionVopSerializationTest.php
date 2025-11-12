<?php

namespace Fhp;

use Tests\Fhp\Integration\Atruvia\SendTransferVoPTest;

class BaseActionVopSerializationTest extends SendTransferVoPTest
{
    /**
     * @throws \Throwable
     */
    public function testSerializesPollingInfo()
    {
        // We piggy-back on the Atruvia integration test to provide an action that has some reasonable data inside and
        // has already been executed so that polling is now required.
        $this->initDialog();
        $originalAction = $this->createAction();
        $this->expectMessage(static::SEND_TRANSFER_REQUEST, mb_convert_encoding(static::SEND_TRANSFER_RESPONSE_POLLING_NEEDED, 'ISO-8859-1', 'UTF-8'));
        $this->fints->execute($originalAction);

        // Sanity-check that the polling is now expected.
        $this->assertNotNull($originalAction->getPollingInfo());

        // Do a serialization roundtrip.
        $serializedAction = serialize($originalAction);
        $unserializedAction = unserialize($serializedAction);

        // Verify that the polling info is still the same.
        $this->assertEquals($originalAction->getPollingInfo(), $unserializedAction->getPollingInfo());
    }

    /**
     * @throws \Throwable
     */
    public function testSerializesVopConfirmationRequest()
    {
        // We piggy-back on the Atruvia integration test to provide an action that has some reasonable data inside and
        // has already been executed so that polling is now required.
        $this->initDialog();
        $originalAction = $this->createAction();
        $this->expectMessage(static::SEND_TRANSFER_REQUEST, mb_convert_encoding(static::SEND_TRANSFER_RESPONSE_POLLING_NEEDED, 'ISO-8859-1', 'UTF-8'));
        $response = static::buildVopReportResponse(static::VOP_REPORT_PARTIAL_MATCH_RESPONSE, static::VOP_REPORT_PARTIAL_MATCH_XML_PAYLOAD);
        $this->expectMessage(static::POLL_VOP_REQUEST, $response);
        $this->fints->execute($originalAction);
        $this->assertTrue($originalAction->needsPollingWait());
        $this->fints->pollAction($originalAction);

        // Sanity-check that the VOP confirmation is now expected.
        $this->assertNotNull($originalAction->getVopConfirmationRequest());

        // Do a serialization roundtrip.
        $serializedAction = serialize($originalAction);
        $unserializedAction = unserialize($serializedAction);

        // Verify that the polling info is still the same.
        $this->assertEquals($originalAction->getVopConfirmationRequest(), $unserializedAction->getVopConfirmationRequest());
    }
}
