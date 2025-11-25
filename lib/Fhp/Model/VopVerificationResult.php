<?php

namespace Fhp\Model;

use Fhp\Protocol\UnexpectedResponseException;

/**
 * Possible outcomes of the Verification of Payee check that the bank did on a transfer we want to execute.
 * TODO Once we have PHP8.1, turn this into an enum. That's why we use UpperCamelCase below (Symfony style for enums).
 * @see FinTS_3.0_Messages_Geschaeftsvorfaelle_VOP_1.01_2025_06_27_FV.pdf (chapter D under "VOP-Prüfergebnis")
 * @see https://febelfin.be/media/pages/publicaties/2023/febelfin-standaarden-voor-online-bankieren/971728b297-1746523070/febelfin-standard-payment-status-report-xml-2025-v1.0-en_final.pdf
 */
class VopVerificationResult
{
    /** The verification completed and successfully matched the payee information. */
    public const CompletedFullMatch = 'CompletedFullMatch';
    /** The verification completed and only partially matched the payee information. */
    public const CompletedCloseMatch = 'CompletedCloseMatch';
    /** The verification completed but could not match the payee information. */
    public const CompletedNoMatch = 'CompletedNoMatch';
    /** The verification completed but not all included transfers were successfully matched. */
    public const CompletedPartialMatch = 'CompletedPartialMatch';
    /**
     * The verification was attempted but could not be completed. More information MAY be available from
     * {@link VopConfirmationRequest::getVerificationNotApplicableReason()}.
     */
    public const NotApplicable = 'NotApplicable';

    public function __construct()
    {
        // Disallow instantiation, because we'll turn this into an enum.
        throw new \AssertionError('There should be no instances of VopVerificationResult');
    }

    /**
     * @param ?string $codeFromBank The verification status code received from the bank.
     * @return ?string One of the constants defined above, or null if the code could not be recognized.
     */
    public static function parse(?string $codeFromBank): ?string
    {
        return match ($codeFromBank) {
            null => null,
            'RCVC' => self::CompletedFullMatch,
            'RVMC' => self::CompletedCloseMatch,
            'RVNM' => self::CompletedNoMatch,
            'RVCM' => self::CompletedPartialMatch,
            'RVNA' => self::NotApplicable,
            default => throw new UnexpectedResponseException("Unexpected VOP result code: $codeFromBank"),
        };
    }
}
