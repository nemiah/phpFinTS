<?php /** @noinspection PhpUnused */

namespace Fhp\Protocol;

use Fhp\FinTsOptions;
use Fhp\Model\TanMode;
use Fhp\Segment\AnonymousSegment;
use Fhp\Segment\BaseSegment;
use Fhp\Segment\HIBPA\HIBPAv3;
use Fhp\Segment\HIPINS\HIPINSv1;
use Fhp\Segment\HITANS\HITANS;

/**
 * Segmentfolge: Bankparameterdaten (Version 3)
 *
 * Contains the "Bankparameterdaten" (BPD), i.e. configuration information that was retrieved from the bank server
 * during a synchronization. This library currently does not store persisted BPD, so it just retrieves them freshly
 * every time.
 *
 * Note: The following segments are part of BPD but not explicity implemented in this library:
 * - HIKOM (lists physical communication channels to the bank, but this library only supports HTTPS and the library user
 *   needs to specify the URL explicitly, so there is no need to know the HIKOM contents).
 * - HISHV (lists security protocols that the bank supports, but this library only supports PIN/TAN).
 * - HIKPV (lists compression protocols, but this library supports none).
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section D.1
 */
class BPD
{
    /** @var HIBPAv3 The HIBPA segment received from the server, which contains most of the BPD data. */
    public $hibpa;

    /**
     * The parameters for each business transaction type, indexed in a nested array structure:
     * - Outer keys are segment identifiers (e.g. 'HIKAZS')
     * - Inner keys are segment versions (these keys are numerically sorted in DESCENDING order, so that the newest and
     *   thus most interesting segment is first)
     * - Inner values are the (possibly anonymous) parameter segments.
     * @var BaseSegment[][]
     */
    public $parameters = [];

    /**
     * @var bool[] An array mapping business transaction request types ('HKxyz' strings) to a bool indicating whether
     *     the respective business transaction needs a TAN, according to the HIPINS information.
     */
    public $tanRequired = [];

    /**
     * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2018-02-23_final_version.pdf
     * Section: B.8.2
     * @var TanMode[] All TAN modes supported by this bank. Note that the UPD contains the modes that the user can use.
     */
    public $allTanModes = [];

    public function getVersion()
    {
        return $this->hibpa->bpdVersion;
    }

    public function getBankCode()
    {
        return $this->hibpa->kreditinstitutskennung->kreditinstitutscode;
    }

    public function getBankName()
    {
        return $this->hibpa->kreditinstitutsbezeichnung;
    }

    /**
     * @param string $type A business transaction type, represented by the segment name of the respective parameter
     *     segment (Geschäftsvorfallparameter segment, aka. Segmentparametersegment). Example: 'HIKAZS'.
     * @return BaseSegment|null The latest parameter segment that is explicitly implemented in this library (never an
     *     AnonymousSegment), or null if none exists.
     */
    public function getLatestSupportedParameters($type)
    {
        if (!array_key_exists($type, $this->parameters)) {
            return null;
        }
        foreach ($this->parameters[$type] as $segment) {
            if (!($segment instanceof AnonymousSegment)) {
                return $segment;
            }
        }
        return null;
    }

    /**
     * @param string $type A business transaction type, see above.
     * @return BaseSegment The latest parameter segment, never null.
     * @throws UnexpectedResponseException If no version exists.
     */
    public function requireLatestSupportedParameters($type)
    {
        $result = $this->getLatestSupportedParameters($type);
        if ($result === null) {
            throw new UnexpectedResponseException(
                "The server does not support any $type versions implemented in this library");
        }
        return $result;
    }

    /**
     * @param BaseSegment[] $requestSegments The segments that shall be sent to the bank.
     * @return bool True if any of the given segments requires a TAN according to HIPINS.
     */
    public function tanRequiredForRequest($requestSegments)
    {
        foreach ($requestSegments as $segment) {
            if ($this->tanRequired[$segment->getName()] ?? false) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Message $response The dialog initialization response from the server.
     * @param FinTsOptions $options See {@link FinTsOptions}.
     * @return BPD A new BPD instance with the extracted configuration data.
     */
    public static function extractFromResponse($response, $options)
    {
        $bpd = new BPD();
        $bpd->hibpa = $response->requireSegment(HIBPAv3::class);

        // Extract the HIxyzS segments, which contain parameters that describe how (future) requests for the particular
        // type of business transaction have to look.
        foreach ($response->plainSegments as $segment) {
            $segmentName = $segment->getName();
            if (strlen($segmentName) === 6 && $segmentName[5] === 'S') {
                $bpd->parameters[$segmentName][$segment->getVersion()] = $segment;
                krsort($bpd->parameters[$segmentName]); // Newest first.
            }
        }
        ksort($bpd->parameters); // Sort alphabetically, for easier debugging.

        // Extract from HIPINS which HKxyz requests will need a TAN.
        /** @var HIPINSv1 $hipins */
        $hipins = $response->requireSegment(HIPINSv1::class);
        foreach ($hipins->parameter->geschaeftsvorfallspezifischePinTanInformationen as $typeInfo) {
            $bpd->tanRequired[$typeInfo->segmentkennung] = $typeInfo->tanErforderlich;
        }

        // Extract all TanModes from HIPINS.
        /** @var HITANS $hitans */
        $hitans = $bpd->requireLatestSupportedParameters('HITANS');
        if ($hitans->getVersion() < 6) {
            $options['logger']->warning('HITANSv' . $hitans->getSegmentNumber()
                . ' is deprecated. Please let the phpFinTS maintainers know that your bank still uses this.');
        }
        foreach ($hitans->getParameterZweiSchrittTanEinreichung()->getVerfahrensparameterZweiSchrittVerfahren() as $verfahren) {
            $bpd->allTanModes[$verfahren->getId()] = $verfahren;
        }
        return $bpd;
    }
}
