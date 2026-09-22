<?php

namespace Fhp\Segment\HIUPD;

/**
 * Implements {@link HIUPD::getAccountHolderName()}, which is the same for all HIUPD versions: banks split the account
 * holder name across the two name fields, e.g. "Mustermann" and "Max".
 */
trait AccountHolderNameTrait
{
    public function getAccountHolderName(): ?string
    {
        $parts = array_filter([$this->getName1(), $this->getName2()], function (?string $part) {
            return $part !== null && $part !== '';
        });
        return count($parts) === 0 ? null : implode(' ', $parts);
    }
}
