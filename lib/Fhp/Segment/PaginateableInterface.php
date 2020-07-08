<?php

namespace Fhp\Segment;

interface PaginateableInterface
{
    public function setPaginationToken(?string $paginationToken);
}
