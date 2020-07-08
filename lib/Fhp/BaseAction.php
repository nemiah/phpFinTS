<?php

/** @noinspection PhpUnused */

namespace Fhp;

use Fhp\Model\TanRequest;
use Fhp\Protocol\ActionIncompleteException;
use Fhp\Protocol\BPD;
use Fhp\Protocol\Message;
use Fhp\Protocol\MessageBuilder;
use Fhp\Protocol\TanRequiredException;
use Fhp\Protocol\UnexpectedResponseException;
use Fhp\Protocol\UPD;
use Fhp\Segment\BaseSegment;
use Fhp\Segment\HIRMS\Rueckmeldung;
use Fhp\Segment\HIRMS\Rueckmeldungscode;
use Fhp\Segment\PaginateableInterface;

/**
 * Base class for actions that can be performed against a bank server. On a high level, there are two kinds of actions:
 *  - requests for information (e.g. an account statement), which the bank server will return
 *  - transactions (e.g. a wire transfer to another account), which the bank server will execute.
 *
 * In part, this class is designed like futures/promises in concurrent programming. The outcome of the action (i.e. the
 * requested information or the execution confirmation of the transaction) becomes available in the future, possibly
 * much later than when the request was sent, in case the user needs to enter a TAN.
 * All action instances are serializable, so that the execution can be interrupted to ask the user for a TAN. Once the
 * TAN is available, the execution can resume either a couple seconds later in the same PHP process using the same
 * physical connection to the bank, or on the order of minutes later in a new PHP process with a newly established
 * connection to the bank. Note that the serialization only applies to selected relevant request parameters, and not to
 * the response. Thus it is only possible to serialize an action when its execution has been attempted but resulted in a
 * TAN request.
 * Actions that do not require a TAN will complete immediately.
 *
 * The implementation of an action consists of two parts: assembling the request to the bank, and processing the
 * response.
 */
abstract class BaseAction implements \Serializable
{
    /** @var int[] Stores segment numbers that were assigned to the segments returned from {@link createRequest()}. */
    private $requestSegmentNumbers;

    /** @var ?MessageBuilder Stores the message that is the result of processing the segment created by @createRequest() */
    private $message = null;

    /**
     * If set, the last response from the server regarding this action asked for a TAN from the user.
     * @var TanRequest|null
     */
    private $tanRequest;

    /**
     * If set, the last response from the server regarding this action indicated that there are more results to be
     * fetched using this pagination token. This is called "Aufsetzpunkt" in the specification.
     * @var string|null
     */
    private $paginationToken;

    /** @var bool */
    private $isDone;

    /**
     * Will be populated with the message the bank sent along with the success indication, can be used to show to
     * the user.
     * @var string
     */
    public $successMessage;

    /**
     * NOTE: A common mistake is to call this function directly. Instead, you probably want `serialize($instance)`.
     *
     * An action can only be serialized *after* it has been executed in case it needs a TAN, i.e. when the result is not
     * present yet.
     * If a sub-class overrides this, it should call the parent function and include it in its result.
     * @return string The serialized action, e.g. for storage in a database. This will not contain sensitive user data.
     */
    public function serialize(): string
    {
        if (!$this->needsTan()) {
            throw new \RuntimeException('Cannot serialize this action, because it is not waiting for a TAN.');
        }
        return serialize([$this->requestSegmentNumbers, $this->tanRequest, $this->paginationToken, $this->message]);
    }

    /** {@inheritdoc} */
    public function unserialize($serialized)
    {
        list($this->requestSegmentNumbers, $this->tanRequest, $this->paginationToken, $this->message) = unserialize($serialized);
    }

    /**
     * @return bool Whether the underlying operation has completed successfully and the result in this "future" is
     *     available. Note: If this returns false, check {@link needsTan()}.
     */
    public function isDone(): bool
    {
        return $this->isDone;
    }

    /**
     * @return bool If this returns true, the underlying operation has not completed because it is awaiting a TAN. You
     *     should ask the user for this TAN and pass it to {@link submitTan()}.
     */
    public function needsTan(): bool
    {
        return !$this->isDone && $this->tanRequest !== null;
    }

    public function getTanRequest(): ?TanRequest
    {
        return $this->tanRequest;
    }

    /**
     * @return bool True if the response has not been read completely yet, i.e. additional requests to the server are
     *     necessary to continue reading the requested data.
     */
    public function hasMorePages(): bool
    {
        return !$this->isDone && $this->paginationToken !== null;
    }

    /**
     * @return string|null Possibly a pagination token to be sent to the server. For actions that support pagination,
     *     this should be read in {@link createRequest()}.
     */
    public function getPaginationToken(): ?string
    {
        return $this->paginationToken;
    }

    /**
     * Throws an exception unless this action has been successfully executed, i.e. in the following cases:
     *  - the action has not been {@link FinTs::execute()}-d at all or the {@link FinTs::execute()} call for it threw an
     *    exception,
     *  - the action is awaiting a TAN that first needs to be supplied with {@link FinTs::submitTan()}.
     *
     * After executing an action, you can use this function to make sure that it succeeded. This is especially useful
     * for actions that don't have any results (as each result getter would call {@link ensureDone()} internally).
     * On the other hand, you do not need to call this function if you make sure that (1) you called
     * {@link FinTs::execute()} and (2) you checked {@link needsTan()} and, if it returned true, supplied a TAN by
     * calling {@ink FinTs::submitTan()}. Note that both exception types thrown from this method are sub-classes of
     * {@link \RuntimeException}, so you shouldn't need a try-catch block at the call site for this.
     * @throws ActionIncompleteException If the action hasn't even been executed.
     * @throws TanRequiredException If the action needs a TAN.
     */
    public function ensureDone()
    {
        if ($this->tanRequest !== null) {
            throw new TanRequiredException($this->tanRequest);
        } elseif (!$this->isDone) {
            throw new ActionIncompleteException();
        }
    }

    /**
     * Called when this action is about to be executed, in order to construct the request. This function can be called
     * multiple times in case the response is paginated. On all but the first call, {@link getPaginationToken()} will
     * return a non-null token that should be included in the returned request.
     * @param BPD $bpd See {@link BPD}.
     * @param UPD|null $upd See {@link UPD}. This is usually present (non-null), except for a few special login and TAN
     *     management actions.
     * @return BaseSegment|BaseSegment[] A segment or a series of segments that should be sent to the bank server.
     *     Note that an action can return an empty array to indicate that it does not need to make a request to the
     *     server, but can instead compute the result just from the BPD/UPD, in which case it should set
     *     `$this->isDone = true;` already in {@link createRequest()} and {@link processResponse()} will never
     *     be executed.
     * @throws \InvalidArgumentException When the request cannot be built because the input data or BPD/UPD is invalid.
     */
    abstract public function createRequest(BPD $bpd, ?UPD $upd);

    /**
     * Called when this action was executed on the server (never if {@link createRequest()} returned an empty request),
     * to process the response. This function can be called multiple times in case the response is paginated.
     * In case the response indicates that this action failed, this function may throw an appropriate exception. Sub-classes should override this function
     * and call the parent/super function.
     * @param Message $response A fake message that contains the subset of segments received from the server that
     *     were in response to the request segments that were created by {@link createRequest()}.
     * @throws UnexpectedResponseException When the response indicates failure.
     */
    public function processResponse(Message $response)
    {
        $pagination = $response->findRueckmeldung(Rueckmeldungscode::PAGINATION);
        if ($pagination === null) {
            $this->paginationToken = null;
            $this->isDone = true;

            $info = $response->findRueckmeldungen(Rueckmeldungscode::AUSGEFUEHRT);
            if (count($info) === 0) {
                $info = $response->findRueckmeldungen(Rueckmeldungscode::ENTGEGENGENOMMEN);
            }
            if (count($info) > 0) {
                $this->successMessage = implode("\n", array_map(function (Rueckmeldung $rueckmeldung) {
                    return $rueckmeldung->rueckmeldungstext;
                }, $info));
            }
        } else {
            if (count($pagination->rueckmeldungsparameter) !== 1) {
                throw new UnexpectedResponseException("Unexpected pagination request: $pagination");
            }
            $this->paginationToken = $pagination->rueckmeldungsparameter[0];
        }
    }

    /** @return int[] */
    public function getRequestSegmentNumbers(): array
    {
        return $this->requestSegmentNumbers;
    }

    /**
     * To be called only by the FinTs instance that executes this action.
     * @param int[] $requestSegmentNumbers
     */
    final public function setRequestSegmentNumbers(array $requestSegmentNumbers)
    {
        foreach ($requestSegmentNumbers as $segmentNumber) {
            if (!is_int($segmentNumber)) {
                throw new \InvalidArgumentException("Invalid segment number: $segmentNumber");
            }
        }
        $this->requestSegmentNumbers = $requestSegmentNumbers;
    }

    /**
     * To be called only by the FinTs instance that executes this action.
     */
    final public function setTanRequest(?TanRequest $tanRequest)
    {
        $this->tanRequest = $tanRequest;
    }

    final public function setMessage(MessageBuilder $message)
    {
        $this->message = $message;
    }

    final public function getMessage()
    {
        if ($this->getPaginationToken() !== null && $this->message instanceof MessageBuilder) {
            $requestSegment = $this->message->segments[0];
            if ($requestSegment instanceof PaginateableInterface) {
                $requestSegment->setPaginationToken($this->getPaginationToken());
            }
        }
        return $this->message;
    }
}
