<?php

namespace Fhp\Segment;

trait PaginateableTrait
{
    public function setPaginationToken(?string $paginationToken)
    {
        $this->aufsetzpunkt = $paginationToken;
    }
}
