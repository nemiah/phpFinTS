<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\CAZ;

use Fhp\Segment\BaseSegment;
use Fhp\Syntax\Bin;

/**
 * Segment: Kontoumsätze rückmelden/Zeitraum camt
 * Bezugssegment: HKCAZ
 * Sender: Kreditinstitut
 *
 * @link: https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX01lc3NhZ2VzX0dlc2NoYWVmdHN2b3JmYWVsbGVfMjAyMi0wNC0xNV9maW5hbF92ZXJzaW9uLnBkZiIsInBhZ2UiOjEyN30.nQ1tJDZlRp30Fh2ZXZK147v2xOOrEHIrmTu-gjeHHMQ/FinTS_3.0_Messages_Geschaeftsvorfaelle_2022-04-15_final_version.pdf
 * Section: C.2.3.1.1.1 b)
 */
class HICAZv1 extends BaseSegment
{
    public \Fhp\Segment\Common\Kti $kontoverbindungInternational;

    /** Der camt-Descriptor beschreibt Ort, Name und Version einer camt Schema-Definition als URN. */
    public string $camtDescriptor;

    /**
     * Umsätze, die auf dem Kundenkonto erfolgt sind und zum Zeitpunkt des Kundenauftrags vom Kreditinstitut bereits
     * gebucht wurden.
     * Gebuchte camt-Umsätze werden als camt.052 message für Umsatzabfragen bzw. camt.053 message für den elektronischen
     * Kontoauszug (s. [Datenformate]) bereitgestellt und werden als transparentes Datenformat im Sinne von FinTS transportiert
     */
    public GebuchteCamtUmsaetze $gebuchteUmsaetze;

    /**
     * Noch nicht gebuchte Umsätze, die dem Kunden im camt.052-Format zusätzlich rückgemeldet werden und zum Zeitpunkt
     * des Kundenauftrags vom Kreditinstitut noch nicht gebucht wurden. Nicht gebuchte Umsätze können nicht auftreten,
     * wenn der vom Kunden angegebene Zeitraum in der Vergangenheit liegt.
     */
    public ?Bin $nichtGebuchteUmsaetze = null;

    public function getKontoverbindungInternational(): \Fhp\Segment\Common\Kti
    {
        return $this->kontoverbindungInternational;
    }

    public function getCamtDescriptor(): string
    {
        return $this->camtDescriptor;
    }

    /**
     * @return string[]
     */
    public function getGebuchteUmsaetze(): array
    {
        return $this->gebuchteUmsaetze->getData();
    }

    public function getNichtGebuchteUmsaetze(): string
    {
        return $this->nichtGebuchteUmsaetze->getData();
    }
}
