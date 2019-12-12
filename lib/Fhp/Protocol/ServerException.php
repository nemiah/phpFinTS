<?php /** @noinspection PhpUnused */

namespace Fhp\Protocol;

use Fhp\Segment\HIRMG\HIRMGv2;
use Fhp\Segment\HIRMS\HIRMSv2;
use Fhp\Segment\HIRMS\Rueckmeldung;
use Fhp\Segment\HIRMS\Rueckmeldungscode;

/**
 * Thrown when the server response with a response code that indicates an error when executing the request.
 */
class ServerException extends \Exception
{
    /** @var Rueckmeldung[] */
    private $errors;

    /** @var Rueckmeldung[] */
    private $warnings;

    /** @var string[] Selected segments from the request that can be useful in debugging. */
    private $requestSegments;

    /** @var Message */
    private $request;

    /** @var Message */
    private $response;

    /**
     * @param Rueckmeldung[] $errors
     * @param Rueckmeldung[] $warnings
     * @param string[] $requestSegments (already serialized and sanitized)
     * @param Message $request
     * @param Message $response
     */
    public function __construct($errors, $warnings, $requestSegments, $request, $response)
    {
        $this->errors = $errors;
        $this->warnings = $warnings;
        $this->requestSegments = $requestSegments;
        $this->request = $request;
        $this->response = $response;
        $errorsStr = empty($errors) ? '' : "FinTS errors:\n" . implode("\n", $errors);
        $warningsStr = empty($warnings) ? '' : "FinTS warnings:\n" . implode("\n", $warnings);
        $segmentsStr = empty($requestSegments) ? '' : "Request segments:\n" . implode("\n", $requestSegments);
        parent::__construct(implode("\n", array_filter([$errorsStr, $warningsStr, $segmentsStr])));
    }

    /**
     * Takes all errors and warnings that pertain to any of the given $referenceSegments, puts them in a new
     * ServerException instance (if any) and removes them from this instance.
     * @param int[] $referenceNumbers The numbers of thte reference segments.
     * @return ServerException|null The part of the exception that pertains to the given reference segments, or null if
     *     none of the errors refer to them.
     */
    public function extractErrorsForReference($referenceNumbers)
    {
        if (empty($referenceNumbers)) {
            return null;
        }
        $errors = array_filter($this->errors, function ($error) use ($referenceNumbers) {
            /* @var Rueckmeldung $error */
            return in_array($error->referenceSegment, $referenceNumbers);
        });
        if (empty($errors)) {
            return null;
        }
        $warnings = array_filter($this->warnings, function ($error) use ($referenceNumbers) {
            /* @var Rueckmeldung $error */
            return in_array($error->referenceSegment, $referenceNumbers);
        });
        $this->errors = array_diff($this->errors, $errors);
        $this->warnings = array_diff($this->warnings, $warnings);
        return new ServerException($errors, $warnings, $this->requestSegments, $this->request, $this->response);
    }

    /**
     * @return Rueckmeldung[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return Rueckmeldung[]
     */
    public function getWarnings()
    {
        return $this->warnings;
    }

    /**
     * @return string[]
     */
    public function getRequestSegments()
    {
        return $this->requestSegments;
    }

    /**
     * @param int $code A Rueckmeldungscode to look for.
     * @return bool Whether an error with this code is present.
     */
    public function hasError($code)
    {
        foreach ($this->errors as $error) {
            if ($error->rueckmeldungscode === $code) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param int $code A Rueckmeldungscode to look for.
     * @return Rueckmeldung|null The first matching Rueckmeldung, which will have been removed from this instance, or
     *     null if no match was found.
     */
    public function extractError($code)
    {
        foreach ($this->errors as $index => $error) {
            if ($error->rueckmeldungscode === $code) {
                return array_splice($this->errors, $index, 1)[0];
            }
        }
        return null;
    }

    /**
     * @return bool True if the {@link Credentials} used to make this request are wrong. If this returns true, the
     *     application should ask the user to re-enter the credentials before making any further requests to the bank.
     */
    public function indicatesBadLoginData()
    {
        return $this->hasError(Rueckmeldungscode::PIN_UNGUELTIG);
    }

    /**
     * @return bool If the error indicates that the account (bank account and/or online banking access) has been locked,
     *     usually due to suspicious activity or failed login attempts. If this returns true, the application should
     *     refrain from logging in / using that account in any automated way before getting confirmation from the user
     *     that it has been unlocked.
     */
    public function indicatesLocked()
    {
        return $this->hasError(Rueckmeldungscode::PIN_GESPERRT)
            || $this->hasError(Rueckmeldungscode::TEILNEHMER_GESPERRT)
            || $this->hasError(Rueckmeldungscode::ZUGANG_GESPERRT);
    }

    /**
     * @return bool True if the provided TAN is invalid (including entirely wrong, used for another transaction already,
     *     or exceeded its expiration time).
     */
    public function indicatesBadTan()
    {
        return $this->hasError(Rueckmeldungscode::TAN_UNGUELTIG)
            || $this->hasError(Rueckmeldungscode::TAN_BEREITS_VERBRAUCHT)
            || $this->hasError(Rueckmeldungscode::ZEITUEBERSCHREITUNG_IM_ZWEI_SCHRITT_VERFAHREN);
    }

    /**
     * @param Message $response A response received from the server.
     * @param Message $request The original requests, from which this function pulls the segments that errors
     *     refer to, for ease of debugging.
     * @throws ServerException In case the response indicates an error.
     */
    public static function detectAndThrowErrors($response, $request)
    {
        /** @var HIRMGv2[]|HIRMSv2[] $segments */
        $segments = array_merge(
            [$response->requireSegment(HIRMGv2::class)],
            $response->findSegments(HIRMSv2::class)
        );
        $errors = [];
        $warnings = [];
        $requestSegments = [];
        foreach ($segments as $segment) {
            $referenceSegment = $segment->segmentkopf->bezugselement;
            foreach ($segment->rueckmeldung as $rueckmeldung) {
                $rueckmeldung->referenceSegment = $referenceSegment;
                if (Rueckmeldungscode::isError($rueckmeldung->rueckmeldungscode)) {
                    $errors[] = $rueckmeldung;
                    if (isset($referenceSegment)) {
                        $requestSegment = $request->findSegmentByNumber($referenceSegment);
                        if (isset($requestSegment)) {
                            $requestSegments[] = $requestSegment;
                        }
                    }
                } elseif (Rueckmeldungscode::isWarning($rueckmeldung->rueckmeldungscode)) {
                    $warnings[] = $rueckmeldung;
                }
            }
        }
        if (!empty($errors)) {
            throw new ServerException($errors, $warnings, $requestSegments, $request, $response);
        }
    }
}
