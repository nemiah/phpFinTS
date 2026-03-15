<?php

namespace Fhp\Segment;

/**
 * Marks a request segment as supporting pagination, this means that the bank can split the result into several
 * response segments that have to be queried one by one via the pagination token.
 */
interface Paginateable
{
    public function setPaginationToken(string $paginationToken);
}
