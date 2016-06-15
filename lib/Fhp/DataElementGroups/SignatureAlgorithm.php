<?php

namespace Fhp\DataElementGroups;

use Fhp\Deg;

/**
 * Class SignatureAlgorithm.
 * @package Fhp\DataElementGroups
 */
class SignatureAlgorithm extends Deg
{
    const SIG_ALGO_USAGE_OSG = 6; // Owner Signing (OSG)

    const SIG_ALGO_DES = 1;
    const SIG_ALGO_RSA = 10;

    const OPERATION_MODE_CBC = 2;
    const OPERATION_MODE_ISO_9796_1 = 16;
    const OPERATION_MODE_ISO_9796_2 = 17;
    const OPERATION_MODE_RSASSA_PKCS_RSAES_PKCS = 18;
    const OPERATION_MODE_RSASSA_PSS = 19;
    const OPERATION_MODE_999 = 999;

    /**
     * SignatureAlgorithm constructor.
     *
     * @param int $sigAlgoUsage
     * @param int $sigAlgo
     * @param int $operationMode
     */
    public function __construct(
        $sigAlgoUsage = self::SIG_ALGO_USAGE_OSG,
        $sigAlgo = self::SIG_ALGO_RSA,
        $operationMode = self::OPERATION_MODE_ISO_9796_1
    ) {
        $this->addDataElement($sigAlgoUsage);
        $this->addDataElement($sigAlgo);
        $this->addDataElement($operationMode);
    }
}
