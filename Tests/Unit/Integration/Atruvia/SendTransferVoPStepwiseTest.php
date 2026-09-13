<?php

namespace Fhp\Tests\Unit\Integration\Atruvia;

use Fhp\UnsupportedException;

/**
 * Runs the same Verification of Payee scenarios as {@link SendTransferVoPTest}, but against a bank that announces
 * "schrittweise Lieferung" (S) of the payment status report in HIVPPS, as e.g. comdirect does.
 *
 * That parameter only describes how the bank splits the pain.002 message *if* it uses the Aufsetzpunkt mechanism, so
 * all of the inherited scenarios have to work just the same. Only when the bank actually delivers a partial report do
 * we have to give up, because stitching the deltas back together is not implemented.
 */
class SendTransferVoPStepwiseTest extends SendTransferVoPTest
{
    protected static function anonymousInitResponse(): string
    {
        $stepwise = str_replace(
            'HIVPPS:78:1:3+1+1+1+999:J:V:J:J:',
            'HIVPPS:78:1:3+1+1+1+999:J:S:J:J:',
            parent::anonymousInitResponse()
        );
        if ($stepwise === parent::anonymousInitResponse()) {
            throw new \AssertionError('Failed to patch HIVPPS in the test BPD');
        }
        return $stepwise;
    }

    /**
     * With "schrittweise Lieferung" (S) an intermediate delivery only contains the delta, so the client would have to
     * stitch the deliveries together. That is not implemented, and we expect a clear error instead of a wrong result.
     * @throws \Throwable
     */
    public function testIntermediateReportDelivery(): void
    {
        $this->initDialog();
        $action = $this->createAction();

        $response = static::buildVopReportResponse(
            static::SEND_TRANSFER_RESPONSE_POLLING_NEEDED,
            static::VOP_REPORT_PARTIAL_MATCH_XML_PAYLOAD
        );
        $this->expectMessage(static::SEND_TRANSFER_REQUEST, $response);

        $this->expectException(UnsupportedException::class);
        $this->expectExceptionMessage('The stepwise transfer of VOP reports is not yet supported');
        $this->fints->execute($action);
    }
}
