<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HIUPD;

use Fhp\Segment\BaseSegment;

/**
 * Segment: Kontoinformation (Version 6)
 * Bezugssegment: HKVVB
 * Sender: Kreditinstitut
 *
 * Note: This is a repeated segment, there is one instance per account.
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: E.3 "Kontoinformation"
 */
class HIUPDv6 extends BaseSegment implements HIUPD
{
    /** @var \Fhp\Segment\Common\KtvV3|null */ // Note: Specification wants version 2, but only specifies version 3.
    public $kontoverbindung;
    /** @var string|null Max length: 34 */
    public $iban;
    /** @var string */
    public $kundenId;
    /**
     * 1 – 9: Kontokorrent-/Girokonto
     * 10 – 19: Sparkonto
     * 20 – 29: Festgeldkonto (Termineinlagen)
     * 30 – 39: Wertpapierdepot
     * 40 – 49: Kredit-/Darlehenskonto
     * 50 – 59: Kreditkartenkonto
     * 60 – 69: Fonds-Depot bei einer Kapitalanlagegesellschaft
     * 70 – 79: Bausparvertrag
     * 80 – 89: Versicherungsvertrag
     * 90 – 99: Sonstige (nicht zuordenbar)
     * @var int|null
     */
    public $kontoart;
    /** @var string|null */
    public $kontowaehrung;
    /** @var string */
    public $name1;
    /** @var string|null */
    public $name2;
    /** @var string|null */
    public $kontoproduktbezeichnung;
    /** @var KontolimitV2|null */
    public $kontolimit;
    /** @var ErlaubteGeschaeftsvorfaelleV2[]|null @Max(999) */
    public $erlaubteGeschaeftsvorfaelle;
    /**
     * JSON-encoded extra information.
     * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
     * Section: E.3.1 "Aufbau der UPD-Erweiterung, kontobezogen"
     * @var string|null Max length: 2048
     */
    public $erweiterungKontobezogen;
}
