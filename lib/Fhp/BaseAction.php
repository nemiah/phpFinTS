<?php

namespace Fhp;

use Fhp\Model\TanRequest;
use Fhp\Protocol\ActionIncompleteException;
use Fhp\Protocol\BPD;
use Fhp\Protocol\Message;
use Fhp\Protocol\ServerException;
use Fhp\Protocol\TanRequiredException;
use Fhp\Protocol\UnexpectedResponseException;
use Fhp\Protocol\UPD;
use Fhp\Segment\BaseSegment;

/**
 * Base class for actions that can be performed against a bank server. On a high level, there are two kinds of actions:
 *  - requests for information (e.g. an account statement), which the bank server will return
 *  - transactions (e.g. a wire transfer to another account), which the bank server will execute.
 *
 * In part, this class is designed like futures/promises in concurrent programming. The outcome of the action (i.e. the
 * requested information or the execution confirmation of the transaction) becomes available in the future, possibly
 * much later than when the request was sent, in case the user needs to enter a TAN.
 * All action instances are serializable, so that the execution can be interrupted to ask the user for a TAN. Once the
 * TAN is available, the execution can resume either a couple seconds later in the same PHP session using the same
 * physical connection to the bank, or on the order of minutes later in a new PHP session with a newly established
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
    /** @var int[] Stores segment numbers that were assigned to the segments returned from {@link #createRequest()}. */
    private $requestSegmentNumbers;

    /** @var TanRequest|null */
    private $tanRequest;

    /** @var bool */
    private $isAvailable;
    /** @var ServerException|UnexpectedResponseException|\RuntimeException|null */
    private $error;

    /**
     * NOTE: A common mistake is to call this function directly. Instead, you probably want `serialize($instance)`.
     *
     * An action can only be serialized *after* it has been executed in case it needs a TAN, i.e. when the result is not
     * present yet.
     * If a sub-class overrides this, it should call the parent function and include it in its result.
     * @return string The serialized action, e.g. for storage in a database. This will not contain sensitive user data.
     */
    public function serialize()
    {
        if (!$this->needsTan()) {
            throw new \RuntimeException('Cannot serialize this action, because it is not waiting for a TAN.');
        }
        return serialize([$this->requestSegmentNumbers, $this->tanRequest]);
    }

    /** {@inheritdoc} */
    public function unserialize($serialized)
    {
        list($this->requestSegmentNumbers, $this->tanRequest) = unserialize($serialized);
    }

    /**
     * @return bool Whether the underlying operation has completed (whether successfully or not) and the result or error
     *     message in this future is available. Note: If this returns false, check {@link #needsTan()}.
     */
    public function isAvailable()
    {
        return $this->isAvailable;
    }

    /**
     * @return bool Whether the underlying operation has completed successfully and the result in this future is
     *     available.
     */
    public function isSuccess()
    {
        return $this->isAvailable && $this->error === null;
    }

    /**
     * @return bool Whether the underlying operation has completed unsuccessfully and the {@link #getError()} is
     *     available.
     */
    public function isError()
    {
        return $this->isAvailable && $this->error !== null;
    }

    /**
     * @return bool If this returns true, the underlying operation has not completed because it is awaiting a TAN. You
     *     should ask the user for this TAN and pass it to {@link #submitTan()}.
     */
    public function needsTan()
    {
        return !$this->isAvailable && $this->tanRequest !== null;
    }

    /**
     * @return TanRequest|null
     */
    public function getTanRequest(): ?TanRequest
    {
        return $this->tanRequest;
    }

    /**
     * @return ServerException|UnexpectedResponseException|\RuntimeException|null
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @throws \Exception If the action failed.
     */
    public function maybeThrowError()
    {
        if ($this->error !== null) {
            throw $this->error;
        }
    }

    /**
     * @throws ActionIncompleteException If the action hasn't even been executed.
     * @throws TanRequiredException If the action needs a TAN.
     * @throws \Exception If the action failed.
     */
    public function ensureSuccess()
    {
        $this->maybeThrowError();
        if ($this->tanRequest !== null) {
            throw new TanRequiredException($this->tanRequest);
        } elseif (!$this->isAvailable) {
            throw new ActionIncompleteException();
        }
    }

    /**
     * Called when this action is about to be executed, in order to construct the request.
     * @param BPD $bpd See {@link BPD}.
     * @param UPD $upd See {@link UPD}.
     * @return BaseSegment[] A series of segments that should be sent to the bank server. Note that an action can return
     *     an empty array to indicate that it does not need to make a request to the server, but can instead compute the
     *     result just from the BPD/UPD.
     * @throws \InvalidArgumentException When the request cannot be built because the input data or BPD/UPD is invalid.
     */
    abstract public function createRequest($bpd, $upd);

    /**
     * Called when this action was executed on the server, to process the response. In case the response indicates that
     * this action failed, this function may throw an appropriate exception. Sub-classes should override this function
     * and call the parent/super function.
     * @param Message $response A fake message that contains the subset of segments received from the server that
     *     were in response to the request segments that were created by {@link #createRequest()}.
     * @param BPD $bpd See {@link BPD}.
     * @param UPD $upd See {@link UPD}.
     * @throws UnexpectedResponseException When the response indicates failure.
     */
    public function processResponse($response, $bpd, $upd)
    {
        unset($response, $bpd, $upd); // These parameters are used in sub-classes.
        $this->isAvailable = true;
    }

    /**
     * @param \Exception $error The error that occurred when executing this action.
     * @param BPD $bpd See {@link BPD}.
     * @param UPD $upd See {@link UPD}.
     */
    public function processError($error, $bpd, $upd)
    {
        unset($bpd, $upd); // These parameters are used in sub-classes.
        $this->isAvailable = true;
        $this->error = $error;
    }

    /** @return int[] */
    public function getRequestSegmentNumbers()
    {
        return $this->requestSegmentNumbers;
    }

    /**
     * To be called only by the FinTs instance that executes this action.
     * @param int[] $requestSegmentNumbers
     */
    final public function setRequestSegmentNumbers($requestSegmentNumbers)
    {
        if (isset($this->requestSegmentNumbers)) {
            throw new \AssertionError('Cannot setRequestSegmentNumbers again');
        }
        foreach ($requestSegmentNumbers as $segmentNumber) {
            if (!is_int($segmentNumber)) {
                throw new \InvalidArgumentException("Invalid segment number: $segmentNumber");
            }
        }
        $this->requestSegmentNumbers = $requestSegmentNumbers;
    }

    /**
     * To be called only by the FinTs instance that executes this action.
     * @param TanRequest|null $tanRequest
     */
    final public function setTanRequest($tanRequest)
    {
        $this->tanRequest = $tanRequest;
    }
}
