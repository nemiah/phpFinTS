<?php /** @noinspection PhpDocSignatureInspection */

namespace Tests\Fhp\Syntax;

use Fhp\Syntax\Serializer;

class SerializerTest extends \PHPUnit\Framework\TestCase
{

    public function escapeProvider()
    {
        return [ // expected, input
            ['ABC?+DEF', 'ABC+DEF'],
            ['ABC???+DEF', 'ABC?+DEF'],
            ['ABC??DEF', 'ABC?DEF'],
            ['ABC?:DEF', 'ABC:DEF'],
            ['foo?@bar.de', 'foo@bar.de'],
            ['??pseudo?:pass?\'special?@', '?pseudo:pass\'special@'],
            ['nothingtodo', 'nothingtodo'],
            ['??', '?'],
            ['?:', ':'],
            ['?@', '@'],
            ['?\'', '\''],
            ['????', '??'],
            ['', ''],
            ['', null],
        ];
    }

    /** @dataProvider escapeProvider */
    public function test_escape($expected, $input)
    {
        $this->assertEquals($expected, Serializer::escape($input));
    }

    public function provideSerializeDataElement()
    {
        return [ // expected, value, type
            ['15', 15, 'int'],
            ['1000', 1000, 'integer'],
            ['15,', 15.0, 'float'],
            ['15,5', 15.5, 'float'],
            ['0,', 0.0, 'float'],
            ['J', true, 'bool'],
            ['N', false, 'boolean'],
            ['1000', '1000', 'string'],
            [utf8_decode('ä'), 'ä', 'string'],
            ['5?:5', "5:5", 'string'],
        ];
    }

    /** @dataProvider provideSerializeDataElement */
    public function test_serializeDataElement($expected, $value, $type)
    {
        $this->assertSame($expected, Serializer::serializeDataElement($value, $type));
    }

    public function test_fillMissingKeys()
    {
        $arr = array(0 => 'a', 2 => 'b', 4 => 'c');
        Serializer::fillMissingKeys($arr, 'X');
        $this->assertEquals(array(0 => 'a', 1 => 'X', 2 => 'b', 3 => 'X', 4 => 'c'), $arr);
    }
}
