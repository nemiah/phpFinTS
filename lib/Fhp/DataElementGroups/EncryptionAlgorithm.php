<?php

namespace Fhp\DataElementGroups;

use Fhp\Deg;

/**
 * Class EncryptionAlgorithm.
 *
 * @package Fhp\DataElementGroups
 */
class EncryptionAlgorithm extends Deg
{
    const TYPE_OSY = 2;

    const OPERATION_MODE_CBC = 2;
    const OPERATION_MODE_ISO_9796_1 = 16;
    const OPERATION_MODE_ISO_9796_2 = 17;
    const OPERATION_MODE_RSASSA_PKCS_RSAES_PKCS = 18;
    const OPERATION_MODE_RSASSA_PSS = 19;
    const OPERATION_MODE_999 = 999;

    const ALGORITHM_2_KEY_TRIPLE_DES = 13;
    const ALGORITHM_AES_256 = 14;
    const DEFAULT_ALGORITHM_IV = '@8@00000000';

    const ALGORITHM_KEY_TYPE_SYM_SYM = 5;
    const ALGORITHM_KEY_TYPE_SYM_PUB = 6;
    const ALGORITHM_IV_DESCRIPTION_IVC = 1;

    /**
     * EncryptionAlgorithm constructor.
     *
     * @param int $type
     * @param int $operationMode
     * @param int $algorithm
     * @param string $algorithmIv
     * @param int $algorithmKeyType
     * @param int $algorithmIvDescription
     */
    public function __construct(
        $type = self::TYPE_OSY,
        $operationMode = self::OPERATION_MODE_CBC,
        $algorithm = self::ALGORITHM_2_KEY_TRIPLE_DES,
        $algorithmIv = self::DEFAULT_ALGORITHM_IV,
        $algorithmKeyType = self::ALGORITHM_KEY_TYPE_SYM_SYM,
        $algorithmIvDescription = self::ALGORITHM_IV_DESCRIPTION_IVC
    ) {
        $this->addDataElement($type);
        $this->addDataElement($operationMode);
        $this->addDataElement($algorithm);
        $this->addDataElement($algorithmIv);
        $this->addDataElement($algorithmKeyType);
        $this->addDataElement($algorithmIvDescription);
    }
}
