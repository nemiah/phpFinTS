<?php
/** @noinspection PhpUnused */

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
     */
    public function __construct(array $errors, array $warnings, array $requestSegments, Message $request, Message $response)
    {
        $this->errors = $errors;
        $this->warnings = $warnings;
        $this->requestSegments = $requestSegments;
        $this->request = $request;
        $this->response = $response;
        $errorsStr = count($errors) === 0 ? '' : "FinTS errors:\n" . implode("\n", $errors);
        $warningsStr = count($warnings) === 0 ? '' : "FinTS warnings:\n" . implode("\n", $warnings);
        $segmentsStr = count($requestSegments) === 0 ? '' : "Request segments:\n" . implode("\n", $requestSegments);
        parent::__construct(implode("\n", array_filter([$errorsStr, $warningsStr, $segmentsStr])));
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
     * @param int $code A Rueckmeldungscode to look for (should be an error code, i.e. 9xxx).
     * @return bool Whether an error with this code is present.
     */
    public function hasError(int $code): bool
    {
        foreach ($this->errors as $error) {
            if ($error->rueckmeldungscode === $code) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param int $code A Rueckmeldungscode to look for (should be a warning code, i.e. 3xxx).
     * @return bool Whether a warning with this code is present.
     */
    public function hasWarning(int $code): bool
    {
        foreach ($this->warnings as $warning) {
            if ($warning->rueckmeldungscode === $code) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool True if the {@link Credentials} used to make this request are wrong. If this returns true, the
     *     application should ask the user to re-enter the credentials before making any further requests to the bank.
     */
    public function indicatesBadLoginData(): bool
    {
        return $this->hasError(Rueckmeldungscode::PIN_UNGUELTIG);
    }

    /**
     * @return bool If the error indicates that the account (bank account and/or online banking access) has been locked,
     *     usually due to suspicious activity or failed login attempts. If this returns true, the application should
     *     refrain from logging in / using that account in any automated way before getting confirmation from the user
     *     that it has been unlocked.
     */
    public function indicatesLocked(): bool
    {
        return $this->hasError(Rueckmeldungscode::PIN_GESPERRT)
            || $this->hasError(Rueckmeldungscode::TEILNEHMER_GESPERRT)
            || $this->hasWarning(Rueckmeldungscode::PIN_VORLAEUFIG_GESPERRT)
            || $this->hasWarning(Rueckmeldungscode::ZUGANG_VORLAEUFIG_GESPERRT)
            || $this->hasError(Rueckmeldungscode::ZUGANG_GESPERRT);
    }

    /**
     * @return bool True if the provided TAN is invalid (including entirely wrong, used for another transaction already,
     *     or exceeded its expiration time).
     */
    public function indicatesBadTan(): bool
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
    public static function detectAndThrowErrors(Message $response, Message $request)
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
                    if ($referenceSegment !== null) {
                        $requestSegment = $request->findSegmentByNumber($referenceSegment);
                        if ($requestSegment !== null) {
                            $requestSegments[] = $requestSegment;
                        }
                    }
                } elseif (Rueckmeldungscode::isWarning($rueckmeldung->rueckmeldungscode)) {
                    $warnings[] = $rueckmeldung;
                }
            }
        }
        if (count($errors) > 0) {
            throw new ServerException($errors, $warnings, $requestSegments, $request, $response);
        }
    }

    /**
     * The response that the bank sent, that contained the errors
     */
    public function getResponse(): Message
    {
        return $this->response;
    }
}
