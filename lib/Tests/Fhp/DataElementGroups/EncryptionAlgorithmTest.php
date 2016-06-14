<?php

namespace Tests\Fhp\DataElementGroups;

use Fhp\DataElementGroups\EncryptionAlgorithm;

class EncryptionAlgorithmTest extends \PHPUnit_Framework_TestCase
{
    public function test_to_string()
    {
        $e = new EncryptionAlgorithm();
        $this->assertEquals('2:2:13:@8@00000000:5:1', (string) $e);
        $this->assertEquals('2:2:13:@8@00000000:5:1', $e->toString());
    }

    public function test_custom_to_string()
    {
        $e = new EncryptionAlgorithm(
            EncryptionAlgorithm::TYPE_OSY,
            EncryptionAlgorithm::OPERATION_MODE_ISO_9796_1,
            EncryptionAlgorithm::ALGORITHM_KEY_TYPE_SYM_PUB,
            EncryptionAlgorithm::ALGORITHM_IV_DESCRIPTION_IVC
        );

        $this->assertEquals('2:16:6:1:5:1', (string) $e);
        $this->assertEquals('2:16:6:1:5:1', $e->toString());
    }
}
