<?php

namespace Tests\Fhp\Segment;

use Fhp\Segment\HNVSD\HNVSDv1;
use PHPUnit\Framework\TestCase;

class HNVSDTest extends TestCase
{
    /**
     * Note: On the immediate syntactic level, this string only contains a single segment, namely HNVSD. All the other
     * segments are just nested inside of its `datenVerschluesselt` field, though they might as well be some other 198
     * arbitrary characters.
     */
    const REAL_DKB_RESPONSE = 'HNVSD:999:1+@198@HNSHK:2:4+PIN:1+999+7000000+1+1+2::tgxxxxxxxxxxxxxxxxxxxxxxxxxA+1+1+1:999:1+6:10:16+280:12030000:xxx?@xxxxx:S:0:0\'HIRMG:3:2+0010::Nachricht entgegengenommen.+0100::Dialog beendet.\'HNSHA:4:2+7000000\'\'';

    public function test_parse_real_consors_response()
    {
        // The point of this unit test is to ensure that the parser does not get confused by all the syntax characters
        // nested in the binary field.
        $hnvsd = HNVSDv1::parse(static::REAL_DKB_RESPONSE);
        $this->assertEquals(198, strlen($hnvsd->datenVerschluesselt->getData()));
    }

    public function test_length_too_long()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Incomplete binary block');
        HNVSDv1::parse(str_replace('@198@', '@199@', static::REAL_DKB_RESPONSE));
    }

    public function test_length_too_short()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('got 198');
        HNVSDv1::parse(str_replace('@198@', '@197@', static::REAL_DKB_RESPONSE));
    }

    public function test_iso_8859_encoded_length()
    {
        // In UTF-8 (used by PHP), the character ä uses two bytes:
        $this->assertEquals(2, strlen('ä'));
        // In ISO-8859-1 (FinTS wire format, and thus used for Bin lengths), it's just one byte:
        $this->assertEquals(1, strlen(utf8_decode('ä')));
        // So when we replace "Nachricht" with "Nächricht", the above message should still be valid.
        $this->assertEquals(strlen(utf8_decode('Nachricht')), strlen(utf8_decode('Nächricht')));

        $encodedResponse = str_replace('Nachricht', utf8_decode('Nächricht'), static::REAL_DKB_RESPONSE);
        $this->assertFalse(strpos($encodedResponse, 'Nachricht')); // Make sure the replacement was effective.
        $hnvsd = HNVSDv1::parse($encodedResponse);
        $this->assertEquals(198, strlen($hnvsd->datenVerschluesselt->getData()));
        $this->assertNotFalse(strpos($hnvsd->datenVerschluesselt->getData(), utf8_decode('Nächricht')));
    }
}
