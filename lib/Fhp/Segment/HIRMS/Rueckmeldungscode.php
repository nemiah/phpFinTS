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
    public static function isSuccess($code)
    {
        return 0 < $code && $code < 1000;
    }

    // NOTE: FinTS v4 additionally knows "Hinweise" (similar to INFO level in logging) that are 1000..1999.

    /**
     * @param int $code A code received from the server.
     * @return bool Whether it is a warning code (indicating that the action was executed, but there may have been a
     *     problem in doing so).
     */
    public static function isWarning($code)
    {
        return 3000 < $code && $code < 4000;
    }

    /**
     * @param int $code A code received from the server.
     * @return bool Whether it is a warning code (indicating that the action was rejected).
     */
    public static function isError($code)
    {
        return 9000 < $code && $code < 9999;
    }

    /**
     * Bestätigung der Dialogbeendigung des Benutzers oder des Kreditinstituts.
     */
    const BEENDET = 100;


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

}
