<?php

namespace Tests\Fhp\Syntax;

use Fhp\Syntax\Parser;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    public function test_splitEscapedString_empty()
    {
        $this->assertEquals(array(), Parser::splitEscapedString('+', ''));
        $this->assertEquals(array('', ''), Parser::splitEscapedString('+', '+'));
    }

    public function test_splitEscapedString_without_escaping()
    {
        $this->assertEquals(array('ABC', 'DEF'), Parser::splitEscapedString('+', 'ABC+DEF'));
        $this->assertEquals(array('ABC', '', 'DEF'), Parser::splitEscapedString('+', 'ABC++DEF'));
        $this->assertEquals(array('ABC', ''), Parser::splitEscapedString('+', 'ABC+'));
        $this->assertEquals(array('', '', 'ABC'), Parser::splitEscapedString('+', '++ABC'));
    }

    public function test_splitEscapedString_with_escaping()
    {

        $this->assertEquals(array('A?+', 'DEF'), Parser::splitEscapedString('+', 'A?++DEF'));
        $this->assertEquals(array('?+C', '', 'D?+'), Parser::splitEscapedString('+', '?+C++D?+'));
        $this->assertEquals(array('ABC', '?+'), Parser::splitEscapedString('+', 'ABC+?+'));
        $this->assertEquals(array('', '', '?+C'), Parser::splitEscapedString('+', '++?+C'));
    }

    public function test_unescape()
    {
        $this->assertEquals('ABC+DEF', Parser::unescape('ABC+DEF'));
        $this->assertEquals('ABC+DEF', Parser::unescape('ABC?+DEF'));
        $this->assertEquals('ABC?+DEF', Parser::unescape('ABC??+DEF'));
        $this->assertEquals('ABC?DEF', Parser::unescape('ABC?DEF'));
        $this->assertEquals('ABC:DEF', Parser::unescape('ABC?:DEF'));
    }

    public function test_parseDataElement()
    {
        $this->assertSame(15, Parser::parseDataElement('15', 'int'));
        $this->assertSame(1000, Parser::parseDataElement('1000', 'integer'));
        $this->assertSame(15.0, Parser::parseDataElement('15,', 'float'));
        $this->assertSame(15.5, Parser::parseDataElement('15,5', 'float'));
        $this->assertSame(0.0, Parser::parseDataElement('0,', 'float'));
        $this->assertSame(true, Parser::parseDataElement('J', 'bool'));
        $this->assertSame(false, Parser::parseDataElement('N', 'boolean'));
        $this->assertSame("1000", Parser::parseDataElement('1000', 'string'));
    }

    public function test_parseDataElement_invalid_int()
    {
        $this->expectException(\InvalidArgumentException::class);
        Parser::parseDataElement('lala', 'int');
    }

    public function test_parseDataElement_invalid_float_wrong_decimal_separator()
    {
        $this->expectException(\InvalidArgumentException::class);
        Parser::parseDataElement('15.5', 'float');
    }

    public function test_parseDataElement_invalid_float_multiple_decimal_separator()
    {
        $this->expectException(\InvalidArgumentException::class);
        Parser::parseDataElement('15,5,5', 'float');
    }

    public function test_parseDataElement_invalid_float_no_decimal_separator()
    {
        $this->expectException(\InvalidArgumentException::class);
        Parser::parseDataElement('15', 'float');
    }

    // NOTE: Test coverage of DEGs and Segments is provided by tests in Test\Fhp\Segment.
}
