<?php

namespace Tests\Fhp\Protocol;

use Fhp\Protocol\DialogInitialization;

class DialogInitializationTest extends \PHPUnit\Framework\TestCase
{
    public function testSerializableInterfaceMigration()
    {
        $kundensystemId = 'kunden-system-id';
        $needTanForSegment = 'test-segment';

        $object = new DialogInitializationTestModel(
            $kundensystemId,
            $needTanForSegment,
        );

        $string = serialize($object);

        /** @var DialogInitialization $object2 */
        $object2 = unserialize($string);
        self::assertTrue(is_object($object2));
        self::assertTrue($object !== $object2);
        unset($object);

        // Test child class: DialogInitialization
        self::assertTrue($object2->getKundensystemId() == $kundensystemId);

        // test parent class: BaseClass
        self::assertTrue($object2->getNeedTanForSegment() == $needTanForSegment);
    }

    public function testSerializableInterfaceMigrationFromString()
    {
        $kundensystemId = 'kunden-system-id2';
        $needTanForSegment = 'test-segment2';

        $string = 'C:48:"Tests\Fhp\Protocol\DialogInitializationTestModel":108:{a:5:{i:0;s:43:"a:3:{i:0;N;i:1;N;i:2;s:13:"test-segment2";}";i:1;N;i:2;s:17:"kunden-system-id2";i:3;N;i:4;N;}}';

        /** @var DialogInitialization $object2 */
        $object2 = unserialize($string);
        self::assertTrue(is_object($object2));

        // Test child class: DialogInitialization
        self::assertTrue($object2->getKundensystemId() == $kundensystemId);

        // test parent class: BaseClass
        self::assertTrue($object2->getNeedTanForSegment() == $needTanForSegment);
    }
}
