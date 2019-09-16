<?php

namespace Tests\Fhp\Syntax;

use Fhp\Syntax\Serializer;

class SerializerTest extends \PHPUnit_Framework_TestCase
{
    public function test_serializeDataElement() 
    {
        $this->assertSame('15', Serializer::serializeDataElement(15, 'int'));
        $this->assertSame('1000', Serializer::serializeDataElement(1000, 'integer'));
        $this->assertSame('15,', Serializer::serializeDataElement(15.0, 'float'));
        $this->assertSame('15,5', Serializer::serializeDataElement(15.5, 'float'));
        $this->assertSame('0,', Serializer::serializeDataElement(0.0, 'float'));
        $this->assertSame('J', Serializer::serializeDataElement(true, 'bool'));
        $this->assertSame('N', Serializer::serializeDataElement(false, 'boolean'));
        $this->assertSame('1000', Serializer::serializeDataElement("1000", 'string'));
    }
    
    public function test_fillMissingKeys()
    {
        $arr = array(0 => 'a', 2 => 'b', 4 => 'c');
        Serializer::fillMissingKeys($arr, 'X');
        $this->assertEquals(array(0 => 'a', 1 => 'X', 2 => 'b', 3 => 'X', 4 => 'c'), $arr);
    }
}
