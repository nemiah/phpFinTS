<?php

namespace Fhp\Segment\VPP;

use Fhp\Segment\BaseDeg;

class UnterstuetztePaymentStatusReports extends BaseDeg
{
    /** @var string[] @Max(99) Max length each: 256 */
    public array $paymentStatusReportDescriptor;
}