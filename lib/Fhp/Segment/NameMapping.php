<?php

namespace Fhp\Segment;

/**
 * Class NameMapping
 * @package Fhp\Segment
 */
class NameMapping
{
    protected static $mapping = array(
        // Formals
        // http://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2011-06-14_final_version.pdf
        // Section: H.1.3
        'HKEND'  => 'Dialogende',
        'HKIDN'  => 'Identifikation',
        'HKSYN'  => 'Synchronisation',
        'HKVVB'  => 'Verarbeitungsvorbereitung',
        'HNHBK'  => 'Nachrichtenkopf',
        'HNHBS'  => 'Nachrichtenabschluss',
        'HNSHA'  => 'Signaturabschluss',
        'HNSHK'  => 'Signaturkopf',
        'HNVSD'  => 'Verschlüsselte Daten',
        'HNVSK'  => 'Verschlüsselungskopf',
        'HKISA'  => 'Anforderung eines öffentlichen Schlüssels',
        'HIBPA'  => 'Bankparameter allgemein',
        'HISSP'  => 'Bestätigung der Schlüsselsperrung',
        'HIKPV'  => 'Komprimierungsverfahren',
        'HIUPD'  => 'Kontoinformation',
        'HIKIM'  => 'Kreditinstitutsmeldung',
        'HKLIF'  => 'Life-Indikator',
        'HIRMS'  => 'Rückmeldung zu Segmenten',
        'HIRMG'  => 'Rückmeldungen zur Gesamtnachricht',
        'HKSAK'  => 'Schlüsseländerung',
        'HKSSP'  => 'Schlüsselsperrung',
        'HISHV'  => 'Sicherheitsverfahren',
        'HISYN'  => 'Synchronisierungsantwort',
        'HIISA'  => 'Übermittlung eines öffentlichen Schlüssels',
        'HIUPA'  => 'Userparameter allgemein',
        'HIKOM'  => 'Kommunikationszugang rückmelden',
        // Geschäftsvorfälle
        // http://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
        // Section: E.1
        'HKADR'  => 'Adressänderung',
        'HIADRS' => 'Adressänderung Parameter',
        'HITEA'  => 'Änderung terminierter Einzellastschrift bestätigen',
        'HIDSA'  => 'Änderung terminierter SEPA-Einzellastschriften bestätigen',
        'HIBSA'  => 'Änderung terminierter SEPA-Firmeneinzellastschrift bestätigen',
        'HICSA'  => 'Änderung terminierter SEPA-Überweisung bestätigen',
        'HITUA'  => 'Änderung terminierter Überweisung bestätigen',
        'HICVE'  => 'Anlage vorbereiteter SEPA-Überweisung bestätigen',
        'HIVUE'  => 'Anlage vorbereiteter Überweisung bestätigen',
        'HKCTD'  => 'Auftragsdetails für C-Transaktionen',
        'HICTDS' => 'Auftragsdetails für C-Transaktionen Parameter',
        'HICTD'  => 'Auftragsdetails für C-Transaktionen rückmelden',
        'HKAUE'  => 'Ausgeführte Überweisungen anfordern',
        'HIAUE'  => 'Ausgeführte Überweisungen rückmelden',
        'HIAUES' => 'Ausgeführte Überweisungen Parameter',
        'HKAUB'  => 'Auslandsüberweisung',
        'HKAOM'  => 'Auslandsüberweisung ohne Meldeteil',
        'HIAOMS' => 'Auslandsüberweisung ohne Meldeteil Parameter',
        'HIAUBS' => 'Auslandsüberweisung Parameter',
        'HKCTA'  => 'Auslösen von C-Transaktionen',
        'HICTAS' => 'Auslösen von C-Transaktionen Parameter',
        'HIAPN'  => 'Auswahl Postfach-Nachrichtentypen rückmelden',
        'HKFDB'  => 'Bearbeitungsstatus Dokument anfordern ',
        'HIFDBS' => 'Bearbeitungsstatus Dokument Parameter',
        'HIFDB'  => 'Bearbeitungsstatus Dokument rückmelden',
        'HKPPB'  => 'Bestand Daueraufträge Prepaidkarte laden anfordern',
        'HIPPBS' => 'Bestand Daueraufträge Prepaidkarte laden Parameter',
        'HIPPB'  => 'Bestand Daueraufträge Prepaidkarte laden rückmelden',
        'HKCUB'  => 'Bestand Empfängerkonten anfordern',
        'HKLWB'  => 'Bestand Lastschriftwiderspruch',
        'HKSAL'  => 'Saldenabfrage',
        'HISALS' => 'Saldenabfrage Parameter',
        'HISAL'  => 'Saldenrückmeldung',
        'HIEKAS' => 'Kontoauszug Parameter',
        'HIKAZS' => 'Kontoumsätze/Zeitraum Parameter',
        'HIQTGS' => 'Empfangsquittung Parameter',
        'HICSBS' => 'Bestand terminierter SEPA-Überweisungen Parameter',
        'HICSLS' => 'Terminierte SEPA-Überweisung löschen Parameter',
        'HKSPA'  => 'SEPA-Kontoverbindung anfordern',
        // tbc
        // PIN/TAN
        // http://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_Rel_20101027_final_version.pdf
        //
        'HIPAES' => 'PIN ändern Parameter',
        'HIPSPS' => 'PIN sperren Parameter',

    );

    /**
     * @param string $code
     * @return string
     */
    public static function codeToName($code)
    {
        return isset(static::$mapping[$code]) ? static::$mapping[$code] : $code;
    }

    /**
     * @param string $name
     * @return string
     */
    public static function nameToCode($name)
    {
        $flipped = array_flip(static::$mapping);
        return isset($flipped[$name]) ? $flipped[$name] : $name;
    }

    /**
     * @param string $text
     * @return string
     */
    public static function translateResponse($text)
    {
        return str_replace(array_flip(static::$mapping), static::$mapping, $text);
    }
}
