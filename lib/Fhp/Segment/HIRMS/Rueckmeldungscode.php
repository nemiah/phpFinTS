<?php

namespace Fhp\Segment\HIRMS;

/**
 * Enum for the response codes that the server can send.
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL0ZpblRTX1J1ZWNrbWVsZHVuZ3Njb2Rlc18yMDI1LTA4LTE5X0ZWLnBkZiIsInBhZ2UiOjEyN30.V9_N0iZSIveDYPTedp13vkC3u8xQ918Kh-ZDmE13e4E/FinTS_Rueckmeldungscodes_2025-08-19_FV.pdf
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL0ZpblRTX1J1ZWNrbWVsZHVuZ3Njb2Rlc18yMDI1LTA4LTE5X0ZWLnBkZiIsInBhZ2UiOjEyN30.V9_N0iZSIveDYPTedp13vkC3u8xQ918Kh-ZDmE13e4E/FinTS_Rueckmeldungscodes_2025-08-19_FV.pdf
 */
abstract class Rueckmeldungscode
{
    /**
     * These response codes are soft warnings because the server allows unlocking the account through FinTS. But since
     * this library does not support the unlocking actions, we consider these warnings a hard failure, which aborts the
     * login. The user needs to unlock the account through the bank's web interface or customer support instead.
     */
    public const TREAT_WARNINGS_AS_ERRORS = [self::PIN_VORLAEUFIG_GESPERRT, self::ZUGANG_VORLAEUFIG_GESPERRT];

    /**
     * @param int $code A code received from the server.
     * @return bool Whether it is a success code (indicating that the action was executed normally).
     */
    public static function isSuccess(int $code): bool
    {
        return 0 < $code && $code < 1000;
    }

    // NOTE: FinTS v4 additionally knows "Hinweise" (similar to INFO level in logging) that are 1000..1999.

    /**
     * @param int $code A code received from the server.
     * @return bool Whether it is a warning code (indicating that the action was executed, but there may have been a
     *     problem in doing so).
     */
    public static function isWarning(int $code): bool
    {
        return 3000 < $code && $code < 4000;
    }

    /**
     * @param int $code A code received from the server.
     * @return bool Whether it is a warning code (indicating that the action was rejected).
     */
    public static function isError(int $code): bool
    {
        return (9000 < $code && $code < 9999) || in_array($code, self::TREAT_WARNINGS_AS_ERRORS);
    }

    /**
     * Umfang der Prüfung ist kreditinstitutsspezifisch. Mindestanforderung: physisch korrekt empfangen; Status ist
     * nicht rechtsverbindlich.
     */
    public const ENTGEGENGENOMMEN = 10;

    /**
     * Der Auftrag wurde ausgeführt.
     */
    public const AUSGEFUEHRT = 20;

    /**
     * Bestätigung der Dialogbeendigung des Benutzers oder des Kreditinstituts.
     */
    public const BEENDET = 100;

    /**
     * Nicht verfügbar.
     * zurzeit keine Börsenkurse abrufbar
     * Keine neuen Einträge im Statusprotokoll
     * Information wird zur Zeit nicht angeboten
     * Wertpapierdatei ist bereits aktuell
     */
    public const NICHT_VERFUEGBAR = 3010;

    /**
     * Es liegen weitere Informationen vor.
     * Tells the client that the response is incomplete and the request needs to be re-sent with the pagination token
     * ("Aufsetzpunkt") that is contained in the Rueckmeldung parameters.
     */
    public const PAGINATION = 3040;

    /**
     * Zugelassene Ein- und Zwei-Schritt-Verfahren für den Benutzer (+ Rückmeldungsparameter).
     * The parameters reference the VerfahrensparameterZweiSchrittVerfahren.sicherheitsfunktion values (900..997) from
     * HITANS, or 999 to indicate Ein-Schritt-Verfahren.
     */
    public const ZUGELASSENE_VERFAHREN = 3920;

    /**
     * PIN gesperrt. Entsperren mit GV "PIN-Sperre aufheben" möglich.
     * Note that this library does not support unlocking accounts through FinTS.
     */
    public const PIN_VORLAEUFIG_GESPERRT = 3931;

    /**
     * Ihr Zugang ist vorläufig gesperrt - bitte PIN-Sperre aufheben.
     * Es ist die Durchführung eines HKPSA erforderlich.
     * Note that this library does not support HKPSA.
     */
    public const ZUGANG_VORLAEUFIG_GESPERRT = 3938;

    /**
     * Starke Kundenauthentifizierung noch ausstehend.
     * Indicates that the decoupled authentication is still outstanding.
     */
    public const STARKE_KUNDENAUTHENTIFIZIERUNG_NOCH_AUSSTEHEND = 3956;

    /**
     * In einer Nachricht ist mindestens ein fehlerhafter Auftrag enthalten.
     */
    public const TEILWEISE_FEHLERHAFT = 9050;

    /**
     * Kreditinstitutsseitige Beendigung des Dialoges
     */
    public const ABGEBROCHEN = 9800;

    /**
     * Ihre PIN ist gesperrt.
     */
    public const PIN_GESPERRT = 9930;

    /**
     * Sperrung des Kontos nach %1 Fehlversuchen
     * Teilnehmersperre durchgeführt
     * Teilnehmersperre durchgeführt, Entsperren nur durch Kreditinstitut
     */
    public const TEILNEHMER_GESPERRT = 9931;

    /**
     * Ihr Zugang ist gesperrt - Bitte informieren Sie Ihren Berater.
     * @link https://wiki.windata.de/index.php?title=HBCI-Fehlermeldungen
     */
    public const ZUGANG_GESPERRT = 9933;

    /**
     * TAN ungültig.
     * Signatur ungültig.
     */
    public const TAN_UNGUELTIG = 9941;

    /**
     * PIN ungültig.
     * Neue PIN ungültig.
     * Neue PIN zu kurz.
     * Neue PIN zu lang.
     */
    public const PIN_UNGUELTIG = 9942;

    /**
     *  TAN bereits verbraucht.
     */
    public const TAN_BEREITS_VERBRAUCHT = 9943;

    /**
     * Zeitüberschreitung im Zwei-Schritt-Verfahren
     * TAN/Signatur ungültig
     */
    public const ZEITUEBERSCHREITUNG_IM_ZWEI_SCHRITT_VERFAHREN = 9951;
}
