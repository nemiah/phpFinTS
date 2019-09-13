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
 * Class Message.
 *
 * @package Fhp\Message
 */
class Message extends AbstractMessage
{
    /**
     * @var int
     */
    protected $encryptedSegmentsCount = 0;

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
     * @param array $encryptedSegments
     * @param array $options
     */
    public function __construct(
        $bankCode,
        $username,
        $pin,
        $systemId,
        $dialogId = 0,
        $messageNumber = 0,
        array $encryptedSegments = array(),
        array $options = array(),
		$tan = null
    ) {
        $this->securityReference = rand(1000000, 9999999);
        $this->dialogId = $dialogId;
        $this->messageNumber = $messageNumber;
        $this->bankCode = $bankCode;
        $this->username = $username;
        $this->pin = $pin;
        $this->systemId = $systemId;
        $this->options = $options;
        $this->profileVersion = SecurityProfile::PROFILE_VERSION_1;
        $this->securityFunction = HNSHK::SECURITY_FUNC_999;

        if(isset($options[static::OPT_PINTAN_MECH])) {
            if (!in_array('999', $this->options[static::OPT_PINTAN_MECH])) {
                $this->profileVersion = SecurityProfile::PROFILE_VERSION_2;
                $this->securityFunction = $this->options[static::OPT_PINTAN_MECH][0];
            }
        }

        $signatureHead = $this->buildSignatureHead();
        $hnvsk = $this->buildEncryptionHead();

        $this->addSegment($hnvsk);

        $this->encryptionEnvelop = new HNVSD(999, '');
        $this->addSegment($this->encryptionEnvelop);

        $this->addEncryptedSegment($signatureHead);

        foreach ($encryptedSegments as $es) {
            $this->addEncryptedSegment($es);
        }

        $curCount = count($encryptedSegments) + 3;

        $signatureEnd = new HNSHA($curCount, $this->securityReference, $this->pin, $tan);
        $this->addEncryptedSegment($signatureEnd);
        $this->addSegment(new HNHBS($curCount + 1, $this->messageNumber));
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
        $this->encryptedSegmentsCount++;
        $this->encryptedSegments[] = $segment;
        $encodedData = $this->encryptionEnvelop->getEncodedData()->getData();
        $encodedData .= (string) $segment;
        $this->encryptionEnvelop->setEncodedData($encodedData);
    }

    /**
     * Only for read-only access.
     * @return AbstractSegment[]
     */
    public function getEncryptedSegments()
    {
        return $this->encryptedSegments;
    }
}