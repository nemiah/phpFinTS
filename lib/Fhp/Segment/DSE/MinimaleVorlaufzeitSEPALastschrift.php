<?php

namespace Fhp\Segment\DSE;

class MinimaleVorlaufzeitSEPALastschrift
{
    /**
     * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
     * Section: D ("Unterstützte SEPA-Lastschriftarten, codiert")
     */
    public const UNTERSTUETZTE_SEPA_LASTSCHRIFTARTEN_CODIERT = [
        ['CORE'],
        ['COR1'],
        ['CORE', 'COR1'],
    ];

    /**
     * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
     * Section: D ("SequenceType, codiert")
     */
    public const SEQUENCE_TYPE_CODIERT = [
        ['FNAL', 'RCUR', 'FRST', 'OOFF'],
        ['FNAL', 'RCUR'],
        ['FRST', 'OOFF'],
    ];

    /** Must be 0,1,2 */
    public int $unterstuetzteSEPALastschriftartenCodiert;

    /** Must be 0,1,2 */
    public int $sequenceTypeCodiert;

    /** In Days */
    public int $minimaleSEPAVorlaufzeit;

    /** After this time the request will fail when the value of is used, for example 130000 meaning 1pm */
    public string $cutOffZeit;

    public static function create(int $minimaleSEPAVorlaufzeit, string $cutOffZeit, ?int $unterstuetzteSEPALastschriftartenCodiert = null,
        ?int $sequenceTypeCodiert = null): MinimaleVorlaufzeitSEPALastschrift
    {
        $result = new MinimaleVorlaufzeitSEPALastschrift();
        $result->unterstuetzteSEPALastschriftartenCodiert = $unterstuetzteSEPALastschriftartenCodiert;
        $result->sequenceTypeCodiert = $sequenceTypeCodiert;
        $result->minimaleSEPAVorlaufzeit = $minimaleSEPAVorlaufzeit;
        $result->cutOffZeit = $cutOffZeit;

        return $result;
    }

    /** @return MinimaleVorlaufzeitSEPALastschrift[][]|array */
    public static function parseCoded(string $coded): array
    {
        $result = [];
        foreach (array_chunk(explode(';', $coded), 4) as list($unterstuetzteSEPALastschriftartenCodiert, $sequenceTypeCodiert, $minimaleSEPAVorlaufzeit, $cutOffZeit)) {
            $coreTypes = self::UNTERSTUETZTE_SEPA_LASTSCHRIFTARTEN_CODIERT[$unterstuetzteSEPALastschriftartenCodiert] ?? [];
            $seqTypes = self::SEQUENCE_TYPE_CODIERT[$sequenceTypeCodiert] ?? [];
            foreach ($coreTypes as $coreType) {
                foreach ($seqTypes as $seqType) {
                    $result[$coreType][$seqType] = MinimaleVorlaufzeitSEPALastschrift::create($minimaleSEPAVorlaufzeit, $cutOffZeit, $unterstuetzteSEPALastschriftartenCodiert, $sequenceTypeCodiert);
                }
            }
        }
        return $result;
    }

    /** @return MinimaleVorlaufzeitSEPALastschrift[][]|array */
    public static function parseCodedB2B(string $coded): array
    {
        $result = [];
        foreach (array_chunk(explode(';', $coded), 3) as list($sequenceTypeCodiert, $minimaleSEPAVorlaufzeit, $cutOffZeit)) {
            $seqTypes = self::SEQUENCE_TYPE_CODIERT[$sequenceTypeCodiert] ?? [];
            foreach ($seqTypes as $seqType) {
                $result['B2B'][$seqType] = MinimaleVorlaufzeitSEPALastschrift::create($minimaleSEPAVorlaufzeit, $cutOffZeit, null, $sequenceTypeCodiert);
            }
        }
        return $result;
    }
}
