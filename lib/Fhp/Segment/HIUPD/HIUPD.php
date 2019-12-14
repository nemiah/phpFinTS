<?php

namespace Fhp\Segment\HIUPD;

use Fhp\Model\SEPAAccount;

/**
 * Segment: Kontoinformation
 * Bezugssegment: HKVVB
 * Sender: Kreditinstitut
 */
interface HIUPD
{
    /**
     * @param SEPAAccount $account An account.
     * @return bool True if this HIUPD segment pertains to the given account.
     */
    public function matchesAccount(SEPAAccount $account): bool;

    /**
     * @return ErlaubteGeschaeftsvorfaelle[]
     */
    public function getErlaubteGeschaeftsvorfaelle(): array;
}
