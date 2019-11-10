<?php

namespace Fhp\Message;

use Fhp\DataElementGroups\SecurityProfile;
use Fhp\Segment\AbstractSegment;
use Fhp\Segment\HNHBS;
use Fhp\Segment\HNSHA;
use Fhp\Segment\HNSHK;
use Fhp\Segment\HNVSD;
use Fhp\Segment\HNVSK;
use Fhp\Segment\SegmentInterface;

/**
 * NOTE: There is also the (newer) Fhp\Protocol\Message class.
 */
class Message extends AbstractMessage
{
    /**
     * @var int
     */
    protected $securityReference;

    /**
     * @var string
     */
    protected $pin;

    /**
     * @var string
     */
    protected $bankCode;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $systemId;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var int
     */
    protected $profileVersion;

    /**
     * @var string
     */
    protected $securityFunction;

    /**
     * @var array
     */
    private $encryptedSegments = array();

    /**
     * @var HNVSD
     */
    protected $encryptionEnvelop;

    /**
     * Message constructor.
     * @param string $bankCode
     * @param string $username
     * @param string $pin
     * @param $systemId
     * @param int $dialogId
     * @param int $messageNumber
     * @param AbstractSegment[] $segments
     * @param array $options
     */
    public function __construct(
        $bankCode,
        $username,
        $pin,
        $systemId,
        $dialogId = 0,
        $messageNumber = 0,
        array $segments = array(),
        array $options = array(),
		$tan = null
    ) {
        $this->dialogId = $dialogId;
        $this->messageNumber = $messageNumber;
        $this->bankCode = $bankCode;
        $this->username = $username;
        $this->pin = $pin;
        $this->systemId = $systemId;
        $this->options = $options;
        $this->profileVersion = SecurityProfile::PROFILE_VERSION_1;
        $this->securityFunction = HNSHK::SECURITY_FUNC_999;

        $segmentNumberOffset = 2; // HNHBK + Start from 1
        $useEncryption = true; // Disable encryption for debugging only (no all banks will accept unencrypted data)

        $this->securityReference = !$useEncryption ? 1 : rand(1000000, 9999999);

        if(isset($options[static::OPT_PINTAN_MECH])) {
            if (!in_array('999', $this->options[static::OPT_PINTAN_MECH])) {
                $this->profileVersion = SecurityProfile::PROFILE_VERSION_2;
                $this->securityFunction = $this->options[static::OPT_PINTAN_MECH][0];
            }
        }

        $this->encryptionEnvelop = new HNVSD(999, '');

        if($useEncryption) {
            $this->addSegment($this->buildEncryptionHead()); // HNVSK
            $this->addSegment($this->encryptionEnvelop);
            $segmentNumberOffset -= 2; // HNVSK + HNVSD have different numbers
        }

        /** @var AbstractSegment[] $subSegments */
        $subSegments = [];
        $subSegments[] = $this->buildSignatureHead(); // HNSHK

        foreach ($segments as $segment) {
            $subSegments[] = $segment;
        }

        $subSegments[] = new HNSHA(null, $this->securityReference, $this->pin, $tan);

        foreach($subSegments as $subSegment) {

            $subSegment->setSegmentNumber(count($this->segments) + count($this->encryptedSegments) + $segmentNumberOffset);

            if($useEncryption) {
                $this->addEncryptedSegment($subSegment);
            } else {
                $this->addSegment($subSegment);
            }
        }

        $this->addSegment(new HNHBS(
            count($this->segments) + count($this->encryptedSegments) + $segmentNumberOffset,
            $this->messageNumber
        ));
    }

    /**
     * @return HNVSK
     * @codeCoverageIgnore
     */
    protected function buildEncryptionHead()
    {
        return new HNVSK(
            998,
            $this->bankCode,
            $this->username,
            $this->systemId,
            HNVSK::SECURITY_SUPPLIER_ROLE_ISS,
            HNVSK::DEFAULT_COUNTRY_CODE,
            HNVSK::COMPRESSION_NONE,
            $this->profileVersion
        );
    }

    /**
     * @return HNSHK
     * @codeCoverageIgnore
     */
    protected function buildSignatureHead()
    {
        return new HNSHK(
            2,
            $this->securityReference,
            280, // country code
            $this->bankCode,
            $this->username,
            $this->systemId,
            $this->securityFunction,
            HNSHK::SECURITY_BOUNDARY_SHM,
            HNSHK::SECURITY_SUPPLIER_ROLE_ISS,
            $this->profileVersion
        );
    }

    /**
     * Adds a encrypted segment to the message.
     *
     * @param SegmentInterface $segment
     */
    protected function addEncryptedSegment(SegmentInterface $segment)
    {
        $this->encryptedSegments[] = $segment;
        $encodedData = $this->encryptionEnvelop->getEncodedData()->getData();
        $encodedData .= (string) $segment;
        $this->encryptionEnvelop->setEncodedData($encodedData);
    }

    /**
     * Only for read-only access.
     * @return SegmentInterface[]
     */
    public function getEncryptedSegments()
    {
        return $this->encryptedSegments;
    }
}