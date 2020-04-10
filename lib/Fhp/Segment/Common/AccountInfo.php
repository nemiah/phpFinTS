<?php

namespace Fhp\Segment\Common;

/**
 * Common interface for DEGs that represent accounts.
 */
interface AccountInfo
{
    /** @return string This is the IBAN, if available, or the plain account number otherwise. */
    public function getAccountNumber();

    /** @return string|null This is the BIC, if available, or the country-specific bank code otherwise. */
    public function getBankIdentifier();
}
