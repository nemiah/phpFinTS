<?php

namespace Fhp\Protocol;

use Fhp\Credentials;
use Fhp\FinTsOptions;
use Fhp\Model\TanMode;
use Fhp\Segment\BaseSegment;
use Fhp\Segment\HNHBK\HNHBKv3;
use Fhp\Segment\HNHBS\HNHBSv1;
use Fhp\Segment\HNSHA\BenutzerdefinierteSignaturV1;
use Fhp\Segment\HNSHA\HNSHAv2;
use Fhp\Segment\HNSHK\HNSHKv4;
use Fhp\Segment\HNVSD\HNVSDv1;
use Fhp\Segment\HNVSK\HNVSKv3;
use Fhp\Segment\SegmentInterface;
use Fhp\Syntax\Parser;
use Fhp\Syntax\Serializer;

/**
 * NOTE: There is also the (newer) Fhp\Message\Message class.
 *
 * This class builds a message that has the structure of an encrypted message as defined in the original HBCI
 * specification (first link below). However, it implements only the structure and no actual encryption or cryptographic
 * signature, because the PIN/TAN specification says not to use the HBCI cryptosystem -- instead there is just
 * encryption on the transport level (TLS), which this library implements through Curl provided that the user connects
 * to an HTTPS address.
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_HBCI_Rel_20181129_final_version.pdf
 * Section B.5
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2018-02-23_final_version.pdf
 * Section A
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section B.8
 */
class Message
{
    /**
     * The segments in the original message structure, i.e. before wrapping/"encryption" or after
     * unwrapping/"decryption". This excludes all the headers/footers.
     * @var BaseSegment[]
     */
    public $plainSegments = [];

    /**
     * The wrapper segments that form the "encrypted" message structure, which includes the plain segments in HNVSD.
     *  - No. 1: HNHBK Message header
     *  - No. 998: HNVSK Encryption header
     *  - No. 999: HNVSD "Encrypted" contents, actually just wrapped plaintext.
     *    * No. 2 HNSHK Signature head (starts at 2 because there would implicitly be a HNHBK at 1)
     *    * No. 3 through N+2: All the $plainSegments (with N := count($plainSegments))
     *    * No. N+3: HNSHA Signature footer
     *  - No. N+4: HNHBS Message footer
     * @var BaseSegment[]
     */
    public $wrapperSegments = [];

    /**
     * The same HNHBK segment that is also stored inside $wrappedSegments above.
     * @var HNHBKv3
     */
    public $header;

    /**
     * The same HNHBS segment that is also stored inside $wrappedSegments above.
     * @var HNHBSv1
     */
    public $footer;

    /** @var HNSHKv4|null */
    public $signatureHeader;
    /** @var HNSHAv2|null */
    public $signatureFooter;

    /**
     * @return \Generator|BaseSegment[] All plain and wrapper segments in this message.
     */
    public function getAllSegments()
    {
        yield from $this->plainSegments;
        yield from $this->wrapperSegments;
    }

    /**
     * @throws \InvalidArgumentException If any segment in this message is invalid.
     */
    public function validate()
    {
        foreach ($this->getAllSegments() as $segment) {
            try {
                $segment->validate();
            } catch (\InvalidArgumentException $e) {
                throw new \InvalidArgumentException("Invalid segment {$segment->segmentkopf->segmentkennung}", 0, $e);
            }
        }
    }

    // TODO Add unit test coverage for the functions below.

    /**
     * @param string $segmentType The PHP type (class name or interface) of the segment(s).
     * @return BaseSegment[] All segments of this type (possibly an empty array).
     */
    public function findSegments($segmentType)
    {
        return array_values(array_filter($this->plainSegments, function ($segment) use ($segmentType) {
            /** @var BaseSegment $segment */
            return $segment instanceof $segmentType;
        }));
    }

    /**
     * @param string $segmentType The PHP type (class name or interface) of the segment.
     * @return BaseSegment|null The segment, or null if it was found.
     */
    public function findSegment($segmentType)
    {
        $matchedSegments = $this->findSegments($segmentType);
        if (count($matchedSegments) > 1) {
            throw new UnexpectedResponseException("Multiple segments matched $segmentType");
        }
        return empty($matchedSegments) ? null : $matchedSegments[0];
    }

    /**
     * @param string $segmentType The PHP type (class name or interface) of the segment.
     * @return boolean Whether any such segment exists.
     */
    public function hasSegment($segmentType)
    {
        return $this->findSegment($segmentType) !== null;
    }

    /**
     * @param string $segmentType The PHP type (class name or interface) of the segment.
     * @return BaseSegment The segment, never null.
     * @throws UnexpectedResponseException If the segment was not found.
     */
    public function requireSegment($segmentType)
    {
        $matchedSegment = $this->findSegment($segmentType);
        if ($matchedSegment === null) {
            throw new UnexpectedResponseException("Segment not found: $segmentType");
        }
        return $matchedSegment;
    }

    /**
     * @param integer $segmentNumber The segment number to search for.
     * @return BaseSegment|null The segment with that number, or null if there is none.
     */
    public function findSegmentByNumber($segmentNumber)
    {
        foreach ($this->getAllSegments() as $segment) {
            if ($segment->getSegmentNumber() === $segmentNumber) {
                return $segment;
            }
        }
        return null;
    }

    /**
     * @param SegmentInterface[]|int[] $referenceSegments The reference segments (or their numbers).
     * @return Message A new message that just contains the plain segment from $this message which refer to one
     *     of the given $referenceSegments.
     */
    public function filterByReferenceSegments($referenceSegments)
    {
        $result = new Message();
        if (empty($referenceSegments)) return $result;
        $referenceNumbers = array_map(function ($referenceSegment) {
            /** @var SegmentInterface|int $referenceSegment */
            return is_int($referenceSegment) ? $referenceSegment : $referenceSegment->getSegmentNumber();
        }, $referenceSegments);
        $result->plainSegments = array_filter($this->plainSegments, function ($segment) use ($referenceNumbers) {
            /** @var BaseSegment $segment */
            $referenceNumber = $segment->segmentkopf->bezugselement;
            return $referenceNumber !== null && in_array($referenceNumber, $referenceNumbers);
        });
        return $result;
    }

    /**
     * @return string The HBCI/FinTS wire format for this message, ISO-8859-1 encoded.
     */
    public function serialize()
    {
        $result = '';
        foreach ($this->wrapperSegments as $segment) {
            $result .= Serializer::serializeSegment($segment);
        }
        return $result;
    }

    /**
     * Wraps the given segmetns in an "encryption" envelope (see class documentation). Inverse of {@link #parse()}.
     * @param BaseSegment[]|MessageBuilder $plainSegments The plain segments to be wrapped.
     * @param FinTsOptions $options See {@link FinTsOptions}.
     * @param string $kundensystemId See {@link #$kundensystemId}.
     * @param Credentials $credentials The credentials used to authenticate the message.
     * @param TanMode|null $tanMode Optionally specifies which two-step TAN mode to use, defaults to 999 (single step).
     * @return Message The built message, ready to be sent to the server through {@link FinTsNew::sendMessage()}.
     */
    public static function createWrappedMessage($plainSegments, $options, $kundensystemId, $credentials, $tanMode)
    {
        $message = new Message();
        $message->plainSegments = $plainSegments instanceof MessageBuilder ? $plainSegments->segments : $plainSegments;

        $randomReference = strval(rand(1000000, 9999999));
        $signature = BenutzerdefinierteSignaturV1::create($credentials->pin, null); // TODO Provide a TAN!
        $numPlainSegments = count($message->plainSegments); // This is N, see $encryptedSegments.

        $message->wrapperSegments = [ // See $encryptedSegments documentation for the structure.
            $message->header = HNHBKv3::createEmpty()->setSegmentNumber(1),
            HNVSKv3::create($options, $credentials, $kundensystemId, $tanMode), // Segment number 998
            HNVSDv1::create(array_merge( // Segment number 999
                [$message->signatureHeader = HNSHKv4::create(
                    $randomReference, $options, $credentials, $tanMode, $kundensystemId
                )->setSegmentNumber(2)],
                $message->plainSegments,
                [$message->signatureFooter = HNSHAv2::create($randomReference, $signature)
                    ->setSegmentNumber($numPlainSegments + 3)]
            )),
            $message->footer = HNHBSv1::createEmpty()->setSegmentNumber($numPlainSegments + 4),
        ];

        return $message;
    }

    /**
     * Builds a plain message by adding header and footer to the given segments, but no "encryption" envelope.
     * Inverse of {@link #parse()}.
     * @param BaseSegment[]|MessageBuilder $segments
     * @return Message The built message, ready to be sent to the server through {@link FinTsNew::sendMessage()}.
     */
    public static function createPlainMessage($segments)
    {
        $message = new Message();
        $message->plainSegments = $segments instanceof MessageBuilder ? $segments->segments : $segments;
        $message->wrapperSegments = array_merge(
            [$message->header = HNHBKv3::createEmpty()->setSegmentNumber(1)],
            $message->plainSegments, // NOTE: Segment numbers are technically wrong, here we have 2..(N+2)
            [$message->footer = HNHBSv1::createEmpty()->setSegmentNumber(count($message->plainSegments) + 3)]
        );
        return $message;
    }

    /**
     * Parses the given wire format and unwraps the "encryption" envelope (see class documentation) if it exists
     * (in which case this function acts as the inverse of {@link #createWrappedMessage()}), or leaves as is otherwise
     * (and acts as inverse of {@link #createPlainMessage()}).
     *
     * @param string $rawMessage The received message in HBCI/FinTS wire format.
     * @return Message The parsed message.
     * @throws \InvalidArgumentException When the parsing fails.
     */
    public static function parse($rawMessage)
    {
        $result = new Message();
        $segments = Parser::parseSegments($rawMessage);

        // Message header and footer must always be there, or something went badly wrong.
        if (!($segments[0] instanceof HNHBKv3)) {
            throw new \InvalidArgumentException("Expected first segment to be HNHBK: $rawMessage");
        }
        if (!($segments[count($segments) - 1] instanceof HNHBSv1)) {
            throw new \InvalidArgumentException("Expected last segment to be HNHBS: $rawMessage");
        }
        $result->header = $segments[0];
        $result->footer = $segments[count($segments) - 1];

        // Check if there's an encryption header and "encrypted" data.
        // Section B.8 specifies that there are exactly 4 segments: HNHBK, HNVSK, HNVSD, HNHBS.
        if (count($segments) === 4 && $segments[1] instanceof HNVSKv3) {
            if (!($segments[2] instanceof HNVSDv1)) {
                throw new \InvalidArgumentException("Expected third segment to be HNVSD: $rawMessage");
            }
            $result->wrapperSegments = $segments;
            $result->plainSegments = Parser::parseSegments($segments[2]->datenVerschluesselt->getData());

            // Signature header and footer must always be there when the "encrypted" structure was used.
            $result->signatureHeader = array_shift($result->plainSegments);
            $result->signatureFooter = array_pop($result->plainSegments);
            if (!($result->signatureHeader instanceof HNSHKv4)) {
                throw new \InvalidArgumentException("Expected first segment to be HNSHK: $rawMessage");
            }
            if (!($result->signatureFooter instanceof HNSHAv2)) {
                throw new \InvalidArgumentException("Expected last segment to be HNSHA: $rawMessage");
            }
        } else {
            // Ensure that there's no encryption header anywhere, and we haven't just misunderstood the format.
            foreach ($segments as $segment) {
                if ($segment->getName() === 'HNVSK' || $segment->getName() === 'HNVSD') {
                    throw new \InvalidArgumentException("Unexpected encrypted format: $rawMessage");
                }
            }
            $result->plainSegments = $segments; // The message wasn't "encrypted".
        }
        return $result;
    }
}
