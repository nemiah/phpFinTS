<?php

namespace Fhp\Segment\VPP;

class ParameterNamensabgleichPruefauftrag
{
    public int $maximaleAnzahlCreditTransferTransactionInformationOptIn;

    public bool $aufklaerungstextStrukturiert;

    /** Allowed values: V, S */
    public string $artDerLieferungPaymentStatusReport;

    public bool $sammelzahlungenMitEinemAuftragErlaubt;

    public bool $eingabeAnzahlEintraegeErlaubt;

    public string $unterstuetztePaymentStatusReportDatenformate;

    /** @var string[] @Max(N) Max length each: 6 */
    public array $vopPflichtigerZahlungsverkehrsauftrag;
}