<?php

namespace Tests\Fhp;

use Fhp\Credentials;
use Fhp\FinTsOptions;

class SanitizingCLILoggerTest extends \PHPUnit\Framework\TestCase
{
    public function test_sanitize()
    {
        $credentials = Credentials::create('USER123', 'pw+?123');
        $options = new FinTsOptions();
        $options->productName = 'ABCDEFGHIJKLMNOPQRS';
        $needles = SanitizingCLILogger::computeNeedles([$credentials, $options, 'RAWNEEDLE']);
        $sanitize = function ($str) use ($needles) {
            $result = SanitizingCLILogger::sanitizeForLogging($str, $needles);
            $this->assertEquals(strlen($str), strlen($result));
            return $result;
        };
        $this->assertEquals(
            "HKVVB:4:3+3+0+0+<PRIVATE__________>+1.0'HKTAN:5:",
            $sanitize("HKVVB:4:3+3+0+0+ABCDEFGHIJKLMNOPQRS+1.0'HKTAN:5:"));
        $this->assertEquals(
            "Look here is the password: <PRIVA>",
            $sanitize("Look here is the password: pw+?123"));
        $this->assertEquals(
            "HNSHA:4:2+9999999++<PRIVATE>'",
            $sanitize("HNSHA:4:2+9999999++pw?+??123'")); // Note: The password is escaped to wire format here.
        $this->assertEquals(
            "20190102:030405+1:999:1+6:10:19+280:11223344:<PRIVA>:S:0:0'HIRMG:3:2",
            $sanitize("20190102:030405+1:999:1+6:10:19+280:11223344:USER123:S:0:0'HIRMG:3:2"));
    }
}
