<?php

namespace Fhp\Segment\VPP;

use Fhp\Segment\BaseDeg;

/**
 * DEG: Parameter Namensabgleich Prüfauftrag
 *
 * @see FinTS_3.0_Messages_Geschaeftsvorfaelle_VOP_1.01_2025_06_27_FV.pdf
 *   Section: D
 */
class ParameterNamensabgleichPruefauftragV1 extends BaseDeg
{
    public int $maximaleAnzahlCreditTransferTransactionInformationOptIn;

    public bool $aufklaerungstextStrukturiert;

    /** Allowed values: V, S */
    public string $artDerLieferungPaymentStatusReport;

    public bool $sammelzahlungenMitEinemAuftragErlaubt;

    public bool $eingabeAnzahlEintraegeErlaubt;

    public string $unterstuetztePaymentStatusReportDatenformate;

    /** @var string[] @Max(999999) Max length each: 6 */
    public array $vopPflichtigerZahlungsverkehrsauftrag;
}
