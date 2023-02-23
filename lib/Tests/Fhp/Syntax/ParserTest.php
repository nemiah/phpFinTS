<?php

namespace Tests\Fhp\Syntax;

use Fhp\Syntax\Parser;

class ParserTest extends \PHPUnit\Framework\TestCase
{
    public function testSplitEscapedStringEmpty()
    {
        $this->assertEquals([], Parser::splitEscapedString('+', ''));
        $this->assertEquals(['', ''], Parser::splitEscapedString('+', '+'));
    }

    public function testSplitEscapedStringWithoutEscaping()
    {
        $this->assertEquals(['ABC', 'DEF'], Parser::splitEscapedString('+', 'ABC+DEF'));
        $this->assertEquals(['ABC', '', 'DEF'], Parser::splitEscapedString('+', 'ABC++DEF'));
        $this->assertEquals(['ABC', ''], Parser::splitEscapedString('+', 'ABC+'));
        $this->assertEquals(['', '', 'ABC'], Parser::splitEscapedString('+', '++ABC'));
    }

    public function testSplitEscapedStringWithEscaping()
    {
        $this->assertEquals(['A?+', 'DEF'], Parser::splitEscapedString('+', 'A?++DEF'));
        $this->assertEquals(['?+C', '', 'D?+'], Parser::splitEscapedString('+', '?+C++D?+'));
        $this->assertEquals(['ABC', '?+'], Parser::splitEscapedString('+', 'ABC+?+'));
        $this->assertEquals(['', '', '?+C'], Parser::splitEscapedString('+', '++?+C'));
    }

    public function testSplitEscapedStringWithBinaryBlock()
    {
        $this->assertEquals(['A@4@xxxxD', 'EF'], Parser::splitEscapedString('+', 'A@4@xxxxD+EF'));
        $this->assertEquals(['A@4@++++D', 'EF'], Parser::splitEscapedString('+', 'A@4@++++D+EF'));
        $this->assertEquals(['A', '@1@x@0D', 'EF'], Parser::splitEscapedString('+', 'A+@1@x@0D+EF'));
        $this->assertEquals(['@4@xxxxD', 'EF'], Parser::splitEscapedString('+', '@4@xxxxD+EF'));
        $this->assertEquals(['A@4@xxxx', 'EF'], Parser::splitEscapedString('+', 'A@4@xxxx+EF'));
        $this->assertEquals(['@4@xxxx'], Parser::splitEscapedString('+', '@4@xxxx'));
        $this->assertEquals(['@4@++++'], Parser::splitEscapedString('+', '@4@++++'));
    }

    public function testSplitEscapedStringWithEscapingAndBinaryBlock()
    {
        $this->assertEquals(['A@4@xxxxD', '?+'], Parser::splitEscapedString('+', 'A@4@xxxxD+?+'));
        $this->assertEquals(['A@4@xxxx?+', 'EF'], Parser::splitEscapedString('+', 'A@4@xxxx?++EF'));
        $this->assertEquals(['?+@4@+xxxD', '?+'], Parser::splitEscapedString('+', '?+@4@+xxxD+?+'));
        $this->assertEquals(['?+@4@xxx+D', '?+'], Parser::splitEscapedString('+', '?+@4@xxx+D+?+'));
    }

    public function testUnescape()
    {
        $this->assertEquals('ABC+DEF', Parser::unescape('ABC+DEF'));
        $this->assertEquals('ABC+DEF', Parser::unescape('ABC?+DEF'));
        $this->assertEquals('ABC?+DEF', Parser::unescape('ABC??+DEF'));
        $this->assertEquals('ABC?DEF', Parser::unescape('ABC?DEF'));
        $this->assertEquals('ABC:DEF', Parser::unescape('ABC?:DEF'));
    }

    public function testParseDataElement()
    {
        $this->assertSame(15, Parser::parseDataElement('15', 'int'));
        $this->assertSame(1000, Parser::parseDataElement('1000', 'integer'));
        $this->assertSame(15.0, Parser::parseDataElement('15,', 'float'));
        $this->assertSame(15.5, Parser::parseDataElement('15,5', 'float'));
        $this->assertSame(0.0, Parser::parseDataElement('0,', 'float'));
        $this->assertSame(true, Parser::parseDataElement('J', 'bool'));
        $this->assertSame(false, Parser::parseDataElement('N', 'boolean'));
        $this->assertSame('1000', Parser::parseDataElement('1000', 'string'));
        $this->assertSame('ä', Parser::parseDataElement(mb_convert_encoding('ä', 'ISO-8859-1', 'UTF-8'), 'string'));

        $this->assertSame(null, Parser::parseDataElement('', 'int'));
        $this->assertSame(null, Parser::parseDataElement('', 'string'));
    }

    public function testParseDataElementInvalidInt()
    {
        $this->expectException(\InvalidArgumentException::class);
        Parser::parseDataElement('lala', 'int');
    }

    public function testParseDataElementInvalidFloatWrongDecimalSeparator()
    {
        $this->expectException(\InvalidArgumentException::class);
        Parser::parseDataElement('15.5', 'float');
    }

    public function testParseDataElementInvalidFloatMultipleDecimalSeparator()
    {
        $this->expectException(\InvalidArgumentException::class);
        Parser::parseDataElement('15,5,5', 'float');
    }

    public function testParseDataElementInvalidFloatNoDecimalSeparator()
    {
        $this->expectException(\InvalidArgumentException::class);
        Parser::parseDataElement('15', 'float');
    }

    // NOTE: Test coverage of DEGs and Segments is provided by tests in Test\Fhp\Segment.
}
