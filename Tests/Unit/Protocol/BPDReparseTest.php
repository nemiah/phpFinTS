<?php

namespace Fhp\Tests\Unit\Protocol;

use Fhp\FinTs;
use Fhp\Options\Credentials;
use Fhp\Options\FinTsOptions;
use Fhp\Protocol\BPD;
use Fhp\Segment\AnonymousSegment;
use Fhp\Segment\HIBPA\HIBPAv3;
use Fhp\Segment\KKU\DIKKUSv2;
use Fhp\Syntax\Parser;
use PHPUnit\Framework\TestCase;

/**
 * A persisted BPD keeps the segments in the shape the library version that received them could parse. Segments that
 * were unknown back then are stored as AnonymousSegment and stay that way after a library upgrade, because the bank
 * only resends the BPD when it bumps the BPD version. Here DIKKUS (credit card statements) plays the role of the
 * newly implemented segment.
 */
class BPDReparseTest extends TestCase
{
    private const RAW_DIKKUS = "DIKKUS:45:2:3+1+1+0+90:N:J'";
    private const RAW_UNKNOWN = "HIXYZS:46:1:3+1+1+0+N'";

    public function testReparsesSegmentsTheLibraryNowImplements()
    {
        $bpd = self::bpdWithAnonymousSegments();
        $this->assertNull($bpd->getLatestSupportedParameters('DIKKUS'));

        $bpd->reparseAnonymousSegments();

        /** @var DIKKUSv2 $dikkus */
        $dikkus = $bpd->getLatestSupportedParameters('DIKKUS');
        $this->assertInstanceOf(DIKKUSv2::class, $dikkus);
        $this->assertSame(90, $dikkus->getParameter()->speicherzeitraum);
        // Still unknown segments are left alone.
        $this->assertInstanceOf(AnonymousSegment::class, $bpd->parameters['HIXYZS'][1]);
    }

    public function testPersistedInstanceIsReparsedOnLoad()
    {
        $options = new FinTsOptions();
        $options->url = 'https://bank.example/fints';
        $options->bankCode = '12345678';
        $options->productName = 'TEST';
        $options->productVersion = '1.0';
        $credentials = Credentials::create('user', 'pin');

        $persisted = serialize([2, self::bpdWithAnonymousSegments(), null, null, null, null, null, null, 1]);
        $fints = FinTs::new($options, $credentials, $persisted);

        $this->assertInstanceOf(DIKKUSv2::class, $fints->getBpd()->getLatestSupportedParameters('DIKKUS'));
    }

    private static function bpdWithAnonymousSegments(): BPD
    {
        $bpd = new BPD();
        $bpd->hibpa = HIBPAv3::parse("HIBPA:4:3:3+10+280:12345678+Testbank+1+1+300+500'");
        $dikkus = Parser::parseAnonymousSegment(self::RAW_DIKKUS);
        $unknown = Parser::parseAnonymousSegment(self::RAW_UNKNOWN);
        $bpd->parameters['DIKKUS'][$dikkus->getVersion()] = $dikkus;
        $bpd->parameters['HIXYZS'][$unknown->getVersion()] = $unknown;
        return $bpd;
    }
}
