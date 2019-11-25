<?php

namespace Tests\Fhp\Segment;

use Fhp\Segment\AnonymousSegment;
use Fhp\Syntax\Parser;

class AnonymousSegmentTest extends \PHPUnit\Framework\TestCase
{
    const RAW_SEGMENT = "HNXXX:4:3+A++C:D:E+F+'";

    private static function getElements($segment)
    {
        try {
            $class = new \ReflectionClass(AnonymousSegment::class);
            $property = $class->getProperty('elements');
            $property->setAccessible(true);
            return $property->getValue($segment);
        } catch (\ReflectionException $e) {
            throw new \RuntimeException($e);
        }
    }

    public function test_parse()
    {
        $segment = Parser::parseAnonymousSegment(static::RAW_SEGMENT);
        $this->assertEquals('HNXXX', $segment->getName());
        $this->assertEquals('HNXXXv3', $segment->type);
        $this->assertEquals(3, $segment->getVersion());
        $this->assertEquals(['A', null, ['C', 'D', 'E'], 'F', null], static::getElements($segment));

        $segment2 = Parser::detectAndParseSegment(static::RAW_SEGMENT);
        $this->assertEquals($segment, $segment2);
    }

    public function test_parse_empty()
    {
        $this->expectException(\InvalidArgumentException::class);
        Parser::parseAnonymousSegment('');
    }

    public function test_serialize()
    {
        $segment = Parser::parseAnonymousSegment(static::RAW_SEGMENT);
        $this->assertEquals(static::RAW_SEGMENT, $segment->serialize());
    }
}
