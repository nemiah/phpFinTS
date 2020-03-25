<?php

// NOTE: In FinTsTestCase, this namespace name is hard-coded in order to be able to mock the rand() function below.

namespace Fhp\Protocol;

use Fhp\Model\NoPsd2TanMode;
use Fhp\Model\TanMode;
use Fhp\Options\Credentials;
use Fhp\Options\FinTsOptions;
use Fhp\Segment\BaseSegment;
use Fhp\Segment\HIRMS\Rueckmeldung;
use Fhp\Segment\HIRMS\RueckmeldungContainer;
use Fhp\Segment\HNHBK\HNHBKv3;
use Fhp\Segment\HNHBS\HNHBSv1;
use Fhp\Segment\HNSHA\BenutzerdefinierteSignaturV1;
use Fhp\Segment\HNSHA\HNSHAv2;
use Fhp\Segment\HNSHK\HNSHKv4;
use Fhp\Segment\HNVSD\HNVSDv1;
use Fhp\Segment\HNVSK\HNVSKv3;
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
    public function findSegments(string $segmentType): array
    {
        return array_values(array_filter($this->plainSegments, function ($segment) use ($segmentType) {
            /* @var BaseSegment $segment */
            return $segment instanceof $segmentType;
        }));
    }

    /**
     * @param string $segmentType The PHP type (class name or interface) of the segment.
     * @return BaseSegment|null The segment, or null if it was found.
     */
    public function findSegment(string $segmentType): ?BaseSegment
    {
        $matchedSegments = $this->findSegments($segmentType);
        if (count($matchedSegments) > 1) {
            throw new UnexpectedResponseException("Multiple segments matched $segmentType");
        }
        return count($matchedSegments) === 0 ? null : $matchedSegments[0];
    }

    /**
     * @param string $segmentType The PHP type (class name or interface) of the segment.
     * @return bool Whether any such segment exists.
     */
    public function hasSegment(string $segmentType): bool
    {
        return $this->findSegment($segmentType) !== null;
    }

    /**
     * @param string $segmentType The PHP type (class name or interface) of the segment.
     * @return BaseSegment The segment, never null.
     * @throws UnexpectedResponseException If the segment was not found.
     */
    public function requireSegment(string $segmentType): BaseSegment
    {
        $matchedSegment = $this->findSegment($segmentType);
        if ($matchedSegment === null) {
            throw new UnexpectedResponseException("Segment not found: $segmentType");
        }
        return $matchedSegment;
    }

    /**
     * @param int $segmentNumber The segment number to search for.
     * @return BaseSegment|null The segment with that number, or null if there is none.
     */
    public function findSegmentByNumber(int $segmentNumber): ?BaseSegment
    {
        foreach ($this->getAllSegments() as $segment) {
            if ($segment->getSegmentNumber() === $segmentNumber) {
                return $segment;
            }
        }
        return null;
    }

    /**
     * @param int[] $referenceNumbers The numbers of the reference segments.
     * @return Message A new message that just contains the plain segment from $this message which refer to one
     *     of the given $referenceSegments.
     */
    public function filterByReferenceSegments(array $referenceNumbers): Message
    {
        $result = new Message();
        if (count($referenceNumbers) === 0) {
            return $result;
        }
        $result->plainSegments = array_filter($this->plainSegments, function ($segment) use ($referenceNumbers) {
            /** @var BaseSegment $segment */
            $referenceNumber = $segment->segmentkopf->bezugselement;
            return $referenceNumber !== null && in_array($referenceNumber, $referenceNumbers);
        });
        $result->header = $this->header;
        $result->footer = $this->footer;
        $result->signatureHeader = $this->signatureHeader;
        $result->signatureFooter = $this->signatureFooter;
        return $result;
    }

    /**
     * @param int $code The response code to search for.
     * @return Rueckmeldung|null The corresponding Rueckmeldung instance, or null if not found.
     */
    public function findRueckmeldung(int $code): ?Rueckmeldung
    {
        foreach ($this->plainSegments as $segment) {
            if ($segment instanceof RueckmeldungContainer) {
                $rueckmeldung = $segment->findRueckmeldung($code);
                if ($rueckmeldung !== null) {
                    return $rueckmeldung;
                }
            }
        }
        return null;
    }

    /** @return Rueckmeldung[] */
    public function findRueckmeldungen(int $code): array
    {
        $rueckmeldungen = [];
        foreach ($this->plainSegments as $segment) {
            if ($segment instanceof RueckmeldungContainer) {
                $rueckmeldungen = array_merge($rueckmeldungen, $segment->findRueckmeldungen($code));
            }
        }
        return $rueckmeldungen;
    }

    /**
     * @return string The HBCI/FinTS wire format for this message, ISO-8859-1 encoded.
     */
    public function serialize(): string
    {
        $result = '';
        foreach ($this->wrapperSegments as $segment) {
            $result .= Serializer::serializeSegment($segment);
        }
        return $result;
    }

    /**
     * Wraps the given segments in an "encryption" envelope (see class documentation). Inverse of {@link #parse()}.
     * @param BaseSegment[]|MessageBuilder $plainSegments The plain segments to be wrapped. Segment numbers do not need
     *     to be set yet (or they will be overwritten).
     * @param FinTsOptions $options See {@link FinTsOptions}.
     * @param string $kundensystemId See {@link #$kundensystemId}.
     * @param Credentials $credentials The credentials used to authenticate the message.
     * @param TanMode|null $tanMode Optionally specifies which two-step TAN mode to use, defaults to 999 (single step).
     * @param string|null The TAN to be sent to the server (in HNSHA). If this is present, $tanMode must be present.
     * @return Message The built message, ready to be sent to the server through {@link FinTsNew::sendMessage()}.
     */
    public static function createWrappedMessage($plainSegments, FinTsOptions $options, string $kundensystemId, Credentials $credentials, ?TanMode $tanMode, $tan): Message
    {
        $message = new Message();
        $message->plainSegments = $plainSegments instanceof MessageBuilder ? $plainSegments->segments : $plainSegments;

        $tanMode = $tanMode instanceof NoPsd2TanMode ? null : $tanMode;
        $randomReference = strval(rand(1000000, 9999999)); // Call unqualified rand() for unit test mocking to work.
        $signature = BenutzerdefinierteSignaturV1::create($credentials->pin, $tan);
        $numPlainSegments = count($message->plainSegments); // This is N, see $encryptedSegments.

        $message->wrapperSegments = [ // See $encryptedSegments documentation for the structure.
            $message->header = HNHBKv3::createEmpty()->setSegmentNumber(1),
            HNVSKv3::create($options, $credentials, $kundensystemId, $tanMode), // Segment number 998
            HNVSDv1::create(array_merge( // Segment number 999
                [$message->signatureHeader = HNSHKv4::create(
                    $randomReference, $options, $credentials, $tanMode, $kundensystemId
                )->setSegmentNumber(2)],
                static::setSegmentNumbers($message->plainSegments, 3),
                [$message->signatureFooter = HNSHAv2::create($randomReference, $signature)
                    ->setSegmentNumber($numPlainSegments + 3), ]
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
    public static function createPlainMessage($segments): Message
    {
        $message = new Message();
        $message->plainSegments = $segments instanceof MessageBuilder ? $segments->segments : $segments;
        $message->wrapperSegments = array_merge(
            [$message->header = HNHBKv3::createEmpty()->setSegmentNumber(1)],
            static::setSegmentNumbers($message->plainSegments, 2),
            [$message->footer = HNHBSv1::createEmpty()->setSegmentNumber(2 + count($message->plainSegments))]
        );
        return $message;
    }

    /**
     * Parses the given wire format and unwraps the "encryption" envelope (see class documentation) if it exists
     * (in which case this function acts as the inverse of {@link #createWrappedMessage()}), or leaves as is otherwise
     * (and acts as inverse of {@link #createPlainMessage()}).
     *
     * @param string $rawMessage The received message in HBCI/FinTS wire format. This should be ISO-8859-1-encoded.
     * @return Message The parsed message.
     * @throws \InvalidArgumentException When the parsing fails.
     */
    public static function parse(string $rawMessage): Message
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
            // Postbank is not following the Spec and does not send the Header and Footer

            $signatureFooterAsExpected = end($result->plainSegments) instanceof HNSHAv2;
            $signatureHeaderAsExpected = reset($result->plainSegments) instanceof HNSHKv4;

            if ($signatureHeaderAsExpected xor $signatureFooterAsExpected) {
                throw new \InvalidArgumentException("Expected first segment to be HNSHK and last segement to be HNSHA or both to be absent: $rawMessage");
            }

            if ($signatureHeaderAsExpected) {
                $result->signatureHeader = array_shift($result->plainSegments);
            }
            if ($signatureFooterAsExpected) {
                $result->signatureFooter = array_pop($result->plainSegments);
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

    /**
     * @param BaseSegment[] $segments The segments to be numbered. Will be modified.
     * @param int $segmentNumber The number for the *first* segment, subsequent segment get the subsequent integers.
     * @return BaseSegment[] The same array, for chaining.
     */
    private static function setSegmentNumbers(array $segments, int $segmentNumber): array
    {
        foreach ($segments as $segment) {
            $segment->segmentkopf->segmentnummer = $segmentNumber;
            if ($segment->segmentkopf->segmentnummer >= HNVSKv3::SEGMENT_NUMBER) {
                throw new \InvalidArgumentException('Too many segments');
            }
            ++$segmentNumber;
        }
        return $segments;
    }
}
