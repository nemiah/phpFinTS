<?php

namespace Fhp\DataElementGroups;

use Fhp\Deg;

/**
 * Class HashAlgorithm.
 *
 * @package Fhp\DataElementGroups
 */
class HashAlgorithm extends Deg
{
    const HASH_ALGORITHM_USAGE_OHA = 1; // Owner Hashing (OHA)

    const HASH_ALGORITHM_SHA_1 = 1;
    const HASH_ALGORITHM_X = 2;
    const HASH_ALGORITHM_SHA_256 = 3;
    const HASH_ALGORITHM_SHA_384 = 4;
    const HASH_ALGORITHM_SHA_512 = 5;
    const HASH_ALGORITHM_SHA_256_256 = 6;
    const HASH_ALGORITHM_NEGOTIATE = 999;

    const HASH_ALGORITHM_PARAM_DESCRIPTION_IVC = 1;

    /**
     * HashAlgorithm constructor.
     *
     * @param int $hashAlgorithmUsage
     * @param int $hashAlgorithm
     * @param int $hashAlgorithmParamDescription
     */
    public function __construct(
        $hashAlgorithmUsage = self::HASH_ALGORITHM_USAGE_OHA,
        $hashAlgorithm = self::HASH_ALGORITHM_NEGOTIATE,
        $hashAlgorithmParamDescription = self::HASH_ALGORITHM_PARAM_DESCRIPTION_IVC
    ) {
        $this->addDataElement($hashAlgorithmUsage);
        $this->addDataElement($hashAlgorithm);
        $this->addDataElement($hashAlgorithmParamDescription);
    }
}
