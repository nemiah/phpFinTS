<?php

namespace Fhp\Tests\Unit\CAMT;

use Fhp\CAMT\CAMT;
use PHPUnit\Framework\TestCase;

class CAMTStructuredFieldsTest extends TestCase
{
    private \ReflectionMethod $extractReflectionMethod;

    protected function setUp(): void
    {
        $this->extractReflectionMethod = new \ReflectionMethod(CAMT::class, 'extractStructuredFieldsFromText');
        $this->extractReflectionMethod->setAccessible(true);
    }

    /**
     * @dataProvider UstrdProvider
     */
    public function testExtractStructuredFieldsFromText(string $text, array $expectedStructuredTags): void
    {
        $parser = new CAMT();

        $fields = $this->extractReflectionMethod->invoke($parser, $text);

        self::assertSame($expectedStructuredTags, $fields);
    }

    public static function UstrdProvider(): array
    {
        return [
            ['This is a plain textEREF+EEID-20260804-123MREF+123CRED+DE456', ['EREF' => 'EEID-20260804-123', 'MREF' => '123', 'CRED' => 'DE456']],
            ['SVWZ+Payment for invoice 123EREF+ABC123 MREF+XYZ789', ['SVWZ' => 'Payment for invoice 123', 'EREF' => 'ABC123', 'MREF' => 'XYZ789']],
            ['EREF+ABC123SVWZ+Payment for invoice 123IBAN+DE44500105175407324931', ['EREF' => 'ABC123', 'SVWZ' => 'Payment for invoice 123', 'IBAN' => 'DE44500105175407324931']],
        ];
    }
}
