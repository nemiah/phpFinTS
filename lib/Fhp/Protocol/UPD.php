<?php

namespace Fhp\Protocol;

use Fhp\Model\SEPAAccount;
use Fhp\Segment\HIUPA\HIUPAv4;
use Fhp\Segment\HIUPD\HIUPD;
use Fhp\Segment\HIUPD\HIUPDv4;
use Fhp\Segment\HIUPD\HIUPDv6;

/**
 * Contains the "Userparameterdaten" (UPD), i.e. configuration information that was retrieved from the bank server
 * during dialog initialization and that is user-specific. This library currently does not store persisted UPD, so it
 * just retrieves them freshly every time.
 */
class UPD
{
    /** @var HIUPAv4 The HIBPA segment received from the server, which contains most of the UPD data. */
    public $hiupa;
    /** @var HIUPDv4[] All HIUPD segments from the server, which contain per-account information. */
    public $hiupd;

    public function getVersion()
    {
        return $this->hiupa->updVersion;
    }

    /**
     * @param Message $response A dialog initialization response from the server.
     * @return boolean True if the UPD data is contained in the response and {@link #extractFromResponse()} would
     *     succeed.
     */
    public static function containedInResponse($response)
    {
        return $response->hasSegment(HIUPAv4::class);
    }

    /**
     * @param Message $response The dialog initialization response from the server, which should contain the UPD
     *     data.
     * @return UPD A new UPD instance with the extracted configuration data.
     */
    public static function extractFromResponse($response)
    {
        $upd = new UPD();
        $upd->hiupa = $response->requireSegment(HIUPAv4::class);
        $upd->hiupd = $response->findSegments(HIUPD::class);
        return $upd;
    }

    /**
     * @param SEPAAccount $account The account to test the support for
     * @param string $requestName The request that shall be sent to the bank.
     * @return boolean True if the given request can be used by the current user for the given account.
     */
    public function isRequestSupportedForAccount(SEPAAccount $account, $requestName)
    {
        // Every Account the User has access to, has seperate permissions
        foreach ($this->hiupd as $hiupd) {
            $accountFound = false;
            if ($hiupd instanceof HIUPDv6 && !is_null($hiupd->iban)) {
                $accountFound = $hiupd->iban == $account->getIban();
            } elseif (!is_null($hiupd->kontoverbindung) && !is_null($hiupd->kontoverbindung->kontonummer)) {
                $accountFound = $hiupd->kontoverbindung->kontonummer == $account->getAccountNumber();
            }
            if ($accountFound) {
                foreach ($hiupd->erlaubteGeschaeftsvorfaelle as $erlaubterGeschaeftsvorfall) {
                    if ($erlaubterGeschaeftsvorfall->geschaeftsvorfall == $requestName) {
                        return true;
                    }
                }
                return false;
            }
        }

        return false;
    }
}
