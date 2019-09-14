<?php

namespace Tests\Fhp\Parser;

use Fhp\Parser\FinTsParser;

class FinTsParserTest extends \PHPUnit_Framework_TestCase
{
    public function test_splitEscapedString_empty()
    {
        $this->assertEquals(array(), FinTsParser::splitEscapedString('+', ''));
        $this->assertEquals(array('', ''), FinTsParser::splitEscapedString('+', '+'));
    }

    public function test_splitEscapedString_without_escaping()
    {
        $this->assertEquals(array('ABC', 'DEF'), FinTsParser::splitEscapedString('+', 'ABC+DEF'));
        $this->assertEquals(array('ABC', '', 'DEF'), FinTsParser::splitEscapedString('+', 'ABC++DEF'));
        $this->assertEquals(array('ABC', ''), FinTsParser::splitEscapedString('+', 'ABC+'));
        $this->assertEquals(array('', '', 'ABC'), FinTsParser::splitEscapedString('+', '++ABC'));
    }

    public function test_splitEscapedString_with_escaping()
    {

        $this->assertEquals(array('A?+', 'DEF'), FinTsParser::splitEscapedString('+', 'A?++DEF'));
        $this->assertEquals(array('?+C', '', 'D?+'), FinTsParser::splitEscapedString('+', '?+C++D?+'));
        $this->assertEquals(array('ABC', '?+'), FinTsParser::splitEscapedString('+', 'ABC+?+'));
        $this->assertEquals(array('', '', '?+C'), FinTsParser::splitEscapedString('+', '++?+C'));
    }
}
