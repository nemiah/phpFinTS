<?php

namespace Tests\Fhp\Options;

use Fhp\Options\Credentials;
use Fhp\Options\FinTsOptions;
use Fhp\Options\SanitizingLogger;

class SanitizingLoggerTest extends \PHPUnit\Framework\TestCase
{
    public function test_sanitize()
    {
        $credentials = Credentials::create('USER123', 'pw+?123');
        $options = new FinTsOptions();
        $options->productName = 'ABCDEFGHIJKLMNOPQRS';
        $needles = SanitizingLogger::computeNeedles([$credentials, $options, 'RAWNEEDLE']);
        $sanitize = function ($str) use ($needles) {
            $result = SanitizingLogger::sanitizeForLogging($str, $needles);
            $this->assertEquals(strlen($str), strlen($result));
            return $result;
        };
        $this->assertEquals(
            "HKVVB:4:3+3+0+0+PRIVATE____________+1.0'HKTAN:5:",
            $sanitize("HKVVB:4:3+3+0+0+ABCDEFGHIJKLMNOPQRS+1.0'HKTAN:5:"));
        $this->assertEquals(
            'Look here is the password: PRIVATE',
            $sanitize('Look here is the password: pw+?123'));
        $this->assertEquals(
            "HNSHA:4:2+9999999++PRIVATE__'",
            $sanitize("HNSHA:4:2+9999999++pw?+??123'")); // Note: The password is escaped to wire format here.
        $this->assertEquals(
            "20190102:030405+1:999:1+6:10:19+280:11223344:PRIVATE:S:0:0'HIRMG:3:2",
            $sanitize("20190102:030405+1:999:1+6:10:19+280:11223344:USER123:S:0:0'HIRMG:3:2"));
    }
}
