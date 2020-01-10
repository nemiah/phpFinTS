<?php

namespace Fhp\Segment\HIRMS;

/**
 * Enum for the response codes that the server can send.
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/FinTS_Rueckmeldungscodes_2019-07-22_final_version.pdf
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2018-02-23_final_version.pdf
 */
abstract class Rueckmeldungscode
{
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
        return 9000 < $code && $code < 9999;
    }

    /**
     * Umfang der Prüfung ist kreditinstitutsspezifisch. Mindestanforderung: physisch korrekt empfangen; Status ist
     * nicht rechtsverbindlich.
     */
    const ENTGEGENGENOMMEN = 10;

    /**
     * Der Auftrag wurde ausgeführt.
     */
    const AUSGEFUEHRT = 20;

    /**
     * Bestätigung der Dialogbeendigung des Benutzers oder des Kreditinstituts.
     */
    const BEENDET = 100;

    /**
     * Nicht verfügbar.
     * zurzeit keine Börsenkurse abrufbar
     * Keine neuen Einträge im Statusprotokoll
     * Information wird zur Zeit nicht angeboten
     * Wertpapierdatei ist bereits aktuell
     */
    const NICHT_VERFUEGBAR = 3010;

    /**
     * Es liegen weitere Informationen vor.
     * Tells the client that the response is incomplete and the request needs to be re-sent with the pagination token
     * ("Aufsetzpunkt") that is contained in the Rueckmeldung parameters.
     */
    const PAGINATION = 3040;

    /**
     * Zugelassene Ein- und Zwei-Schritt-Verfahren für den Benutzer (+ Rückmeldungsparameter).
     * The parameters reference the VerfahrensparameterZweiSchrittVerfahren.sicherheitsfunktion values (900..997) from
     * HITANS, or 999 to indicate Ein-Schritt-Verfahren.
     */
    const ZUGELASSENE_VERFAHREN = 3920;

    /**
     * In einer Nachricht ist mindestens ein fehlerhafter Auftrag enthalten.
     */
    const TEILWEISE_FEHLERHAFT = 9050;

    /**
     * Kreditinstitutsseitige Beendigung des Dialoges
     */
    const ABGEBROCHEN = 9800;

    /**
     * Ihre PIN ist gesperrt.
     */
    const PIN_GESPERRT = 9930;

    /**
     * Sperrung des Kontos nach %1 Fehlversuchen
     * Teilnehmersperre durchgeführt
     * Teilnehmersperre durchgeführt, Entsperren nur durch Kreditinstitut
     */
    const TEILNEHMER_GESPERRT = 9931;

    /**
     * Ihr Zugang ist gesperrt - Bitte informieren Sie Ihren Berater.
     * @link https://wiki.windata.de/index.php?title=HBCI-Fehlermeldungen
     */
    const ZUGANG_GESPERRT = 9933;

    /**
     * TAN ungültig.
     * Signatur ungültig.
     */
    const TAN_UNGUELTIG = 9941;

    /**
     * PIN ungültig.
     * Neue PIN ungültig.
     * Neue PIN zu kurz.
     * Neue PIN zu lang.
     */
    const PIN_UNGUELTIG = 9942;

    /**
     *  TAN bereits verbraucht.
     */
    const TAN_BEREITS_VERBRAUCHT = 9943;

    /**
     * Zeitüberschreitung im Zwei-Schritt-Verfahren
     * TAN/Signatur ungültig
     */
    const ZEITUEBERSCHREITUNG_IM_ZWEI_SCHRITT_VERFAHREN = 9951;
}
