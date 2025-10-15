<?php

namespace Fhp;

use Fhp\Protocol\BPD;
use Fhp\Protocol\Message;
use Fhp\Protocol\UnexpectedResponseException;
use Fhp\Protocol\UPD;
use Fhp\Segment\BaseSegment;
use Fhp\Segment\HIRMS\Rueckmeldungscode;
use Fhp\Segment\Paginateable;

/**
 * Represents actions that need to support pagination, this means that the bank can split the result into several
 * responses that have to be queried one by one via the pagination token.
 * To do this, the request segments are stored and reused and potentially written to a database or sent over the
 * internet unencrypted, so the segments should not contain sensitive data.
 */
abstract class PaginateableAction extends BaseAction
{
    /**
     * Stores the request created by BaseAction::getNextRequest to be reused in case the bank wants
     * to split the result over multiple pages e.g. request/response pairs. This avoids the need for {@link BPD} to be
     * available for paginated requests.
     */
    protected ?array $requestSegments = null;

    /**
     * If set, the last response from the server regarding this action indicated that there are more results to be
     * fetched using this pagination token. This is called "Aufsetzpunkt" in the specification.
     */
    protected ?string $paginationToken = null;

    /**
     * @deprecated Beginning from PHP7.4 __unserialize is used for new generated strings, then this method is only used for previously generated strings - remove after May 2023
     */
    public function serialize(): string
    {
        return serialize($this->__serialize());
    }

    public function __serialize(): array
    {
        return [
            parent::__serialize(),
            $this->paginationToken,
            $this->requestSegments,
        ];
    }

    /**
     * @deprecated Beginning from PHP7.4 __unserialize is used for new generated strings, then this method is only used for previously generated strings - remove after May 2023
     */
    public function unserialize($serialized)
    {
        self::__unserialize(unserialize($serialized));
    }

    public function __unserialize(array $serialized): void
    {
        list(
            $parentSerialized,
            $this->paginationToken,
            $this->requestSegments) = $serialized;

        is_array($parentSerialized) ?
            parent::__unserialize($parentSerialized) :
            parent::unserialize($parentSerialized);
    }

    /**
     * @return bool True if the response has not been read completely yet, i.e. additional requests to the server are
     *     necessary to continue reading the requested data.
     */
    public function hasMorePages(): bool
    {
        return !$this->isDone() && $this->paginationToken !== null;
    }

    public function processResponse(Message $response)
    {
        if (($pagination = $response->findRueckmeldung(Rueckmeldungscode::PAGINATION)) !== null) {
            if (count($pagination->rueckmeldungsparameter) !== 1) {
                throw new UnexpectedResponseException("Unexpected pagination request: $pagination");
            }
            // There is at least one more page
            $this->paginationToken = $pagination->rueckmeldungsparameter[0];
        } else {
            // No pagination or last page
            parent::processResponse($response);
        }
    }

    public function getNextRequest(?BPD $bpd, ?UPD $upd)
    {
        if ($this->requestSegments === null) {
            $this->requestSegments = parent::getNextRequest($bpd, $upd);
        } elseif ($this->paginationToken !== null) {
            foreach ($this->requestSegments as $segment) {
                if ($segment instanceof Paginateable) {
                    $segment->setPaginationToken($this->paginationToken);
                }
            }
            $this->paginationToken = null;
        }
        return $this->requestSegments;
    }
}
