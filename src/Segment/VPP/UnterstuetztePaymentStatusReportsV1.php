<?php

namespace Fhp\Segment\VPP;

use Fhp\Segment\BaseDeg;

/**
 * DEG: Unterstützte Payment Status Reports
 *
 * @see FinTS_3.0_Messages_Geschaeftsvorfaelle_VOP_1.01_2025_06_27_FV.pdf
 *
 * The specification doesn't actually specify the contents of this DEG. In section C.10.7.1.1 a), it's used, but not
 * specified anywhere. We have to infer its contents indirectly, and we align with what other FinTS libraries do:
 *  - https://github.com/hbci4j/hbci4java/blob/f5dd47fca7b4cf1163ac1b955495dec1b195340e/src/main/resources/hbci-300.xml#L2207-L2209
 */
class UnterstuetztePaymentStatusReportsV1 extends BaseDeg
{
    /** @var string[] @Max(99) Max length each: 256 */
    public array $paymentStatusReportDescriptor;
}
