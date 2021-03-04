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
        ];
    }

    /** @dataProvider escapeProvider */
    public function testEscape($expected, $input)
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
            ['5?:5', '5:5', 'string'],
            ['', null, 'int'],
            ['', null, 'string'],
        ];
    }

    /** @dataProvider provideSerializeDataElement */
    public function testSerializeDataElement($expected, $value, $type)
    {
        $this->assertSame($expected, Serializer::serializeDataElement($value, $type));
    }
}
