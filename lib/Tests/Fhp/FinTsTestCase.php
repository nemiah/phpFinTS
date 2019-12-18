<?php /** @noinspection PhpUndefinedClassInspection */

namespace Tests\Fhp;

use Fhp\Connection;
use Fhp\Credentials;
use Fhp\FinTsOptions;
use Fhp\Segment\HNHBK\HNHBKv3;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class FinTsTestCase extends TestCase
{
    use \phpmock\phpunit\PHPMock;

    const TEST_URL = 'https://testbank.de/hbci';
    const TEST_BANK_CODE = '11223344'; // Can be overridden in sub-classes!
    const TEST_PRODUCT_NAME = '123456789ABCDEF0123456789';
    const TEST_PRODUCT_VERSION = '1.0';
    const TEST_USERNAME = 'test@user';
    const TEST_PIN = '12345';
    const TEST_TAN_MODE = '942'; // Can be overridden in sub-classes!

    /** @var FinTsOptions */
    protected $options;

    /** @var Credentials */
    protected $credentials;

    /** @var Connection&MockObject */
    protected $connection;

    /** @var FinTsPeer */
    protected $fints;

    /** @var \DateTime Hard-coded time for unit tests. */
    protected $now;

    /** @var string[][] Series of tuples of expected request and mock response */
    protected $expectedMessages;

    protected function setUp(): void
    {
        // We mock rand() for the $randomReference generation in Fhp\Protocol\Message.
        $randMock = $this->getFunctionMock('Fhp\Protocol', 'rand');
        $randMock->expects($this->any())->with(1000000, 9999999)->willReturn(9999999);
        // We mock time() for the timestamps in the encryption/signature headers in SicherheitsdatumUndUhrzeitV2.php.
        $this->now = new \DateTime('2019-01-02 03:04:05');
        $timeMock = $this->getFunctionMock('Fhp\Segment\HNVSK', 'time');
        $timeMock->expects($this->any())->with()->willReturn($this->now->getTimestamp());

        $this->options = new FinTsOptions();
        $this->options->url = static::TEST_URL;
        $this->options->productName = static::TEST_PRODUCT_NAME;
        $this->options->productVersion = static::TEST_PRODUCT_VERSION;
        $this->options->bankCode = static::TEST_BANK_CODE;
        $this->credentials = Credentials::create(static::TEST_USERNAME, static::TEST_PIN);
        $this->fints = new FinTsPeer($this->options, $this->credentials);
        $this->fints->mockConnection = $this->setUpConnection();
    }

    protected function setUpConnection()
    {
        $this->connection = $this->createMock(Connection::class);
        $this->connection->expects($this->any())->method('send')->willReturnCallback(function ($request) {
            // Check that the request itself is valid wrt. to the length declared in its header.
            if (preg_match('/^HNHBK:\\d+:\\d+\\+(\\d+)/', $request, $lengthMatch) === 1) {
                $expectedLength = intval($lengthMatch[1]);
                $this->assertSame($expectedLength, strlen($request), $request);
            }

            // Grab the next expected request and its mock response.
            $this->assertNotEmpty($this->expectedMessages, "Expected no more requests, but got: $request");
            list($expectedRequest, $mockResponse) = array_shift($this->expectedMessages);

            // Check that the request matches the expectation.
            if (strlen($expectedRequest) > 0 && strpos($expectedRequest, 'HNHBK') !== 0) {
                // The expected request is just the inner part, so we need to unwrap the actual request. This is done in
                // in a quick and hacky way, which slices everything from HNSHK's terminating delimiter to the start of
                // HNSHA.
                $this->assertEquals(1, preg_match("/HNSHK.*?'(.*?')HNSHA:/s", $request, $match), "For request: $request");
                $request = $match[1];
            }
            $this->assertEquals($expectedRequest, $request);

            // Send the mock response.
            if (strpos($mockResponse, 'HNHBK') !== 0) {
                // The mock response is just the inner part, so we need to wrap it in a fake envelope.
                $mockPrefix = 'HNHBK:1:3+';
                // Note: The 4242 is the message number. It's garbage and a constant, but the SUT does not verify it.
                $mockMiddle = "+300+FAKEDIALOGIDabcdefghijklmnopqr+4242+FAKEDIALOGIDabcdefghijklmnopqr:2'HNVSK:998:3+PIN:2+998+1+2::FAKEKUNDENSYSTEMIDabcdefghij+1:20190102:030405+2:2:13:@8@00000000:5:1+280:11223344:test?@user:V:0:0+0'";
                $hnvsdContent = 'HNSHK:2:4+PIN:2+' . static::TEST_TAN_MODE . "+9999999+1+1+2::FAKEKUNDENSYSTEMIDabcdefghij+1+1:20190102:030405+1:999:1+6:10:19+280:11223344:test?@user:S:0:0'"
                    . $mockResponse . "HNSHA:10:2+9999999'";
                $hnvsd = 'HNVSD:999:1+@' . strlen($hnvsdContent) . '@' . $hnvsdContent . "'";
                $mockSuffix = "HNHBS:5:1+2'";
                $newLength = strlen($mockPrefix) + HNHBKv3::NACHRICHTENGROESSE_LENGTH
                    + strlen($mockMiddle) + strlen($hnvsd) + strlen($mockSuffix);
                $newLength = str_pad($newLength, HNHBKv3::NACHRICHTENGROESSE_LENGTH, '0', STR_PAD_LEFT);
                return $mockPrefix . $newLength . $mockMiddle . $hnvsd . $mockSuffix;
            } else {
                return $mockResponse;
            }
        });
        return $this->connection;
    }

    protected function tearDown(): void
    {
        $this->assertAllMessagesSeen();
    }

    /**
     * @param string $request Can be a full request (starting with HNHBK) or just the inner part of HNVSD, more
     *     precisely the slice *between* the HNSHK and HNSHA segments. Note that the latter forms a weaker expectation,
     *     as the SUT could be sending a wrong wrapper and we wouldn't notice.
     * @param string $response Can be a full response (starting with HNHBK) or just the inner part of HNVSD, more
     *     precisely the slice *between* the HNSHK and HNSHA segments.
     */
    protected function expectMessage($request, $response)
    {
        $this->expectedMessages[] = [$request, $response];
    }

    protected function assertAllMessagesSeen()
    {
        $this->assertEmpty($this->expectedMessages, 'Expected requests were not received');
    }
}
