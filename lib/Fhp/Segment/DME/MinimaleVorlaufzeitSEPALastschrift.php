<?php

namespace Fhp\Segment\DME;

class MinimaleVorlaufzeitSEPALastschrift
{
    const UNTERSTUETZTE_SEPA_LASTSCHRIFTARTEN_CODIERT = [
        ['CORE'],
        ['COR1'],
        ['CORE', 'COR1'],
    ];

    const SEQUENCE_TYPE_CODIERT = [
        ['FNAL', 'RCUR', 'FRST', 'OOFF'],
        ['FNAL', 'RCUR'],
        ['FRST', 'OOFF'],
    ];

    /** @var int Must be 0,1,2 */
    public $unterstuetzteSEPALastschriftartenCodiert;

    /** @var int Must be 0,1,2 */
    public $sequenceTypeCodiert;

    /** @var int In Days */
    public $minimaleSEPAVorlaufzeit;

    /** @var string After this time the request will fail when the value of $minimaleSEPAVorlaufzeit is used, for example 130000 meaning 1pm */
    public $cutOffZeit;

    public static function create(int $minimaleSEPAVorlaufzeit, string $cutOffZeit, int $unterstuetzteSEPALastschriftartenCodiert = null, int $sequenceTypeCodiert = null)
    {
        $result = new MinimaleVorlaufzeitSEPALastschrift();
        $result->unterstuetzteSEPALastschriftartenCodiert = $unterstuetzteSEPALastschriftartenCodiert;
        $result->sequenceTypeCodiert = $sequenceTypeCodiert;
        $result->minimaleSEPAVorlaufzeit = $minimaleSEPAVorlaufzeit;
        $result->cutOffZeit = $cutOffZeit;

        return $result;
    }

    /** array[] */
    public static function parseCoded(string $coded)
    {
        $result = [];
        foreach (array_chunk(explode(';', $coded), 4) as $data) {
            $types = self::UNTERSTUETZTE_SEPA_LASTSCHRIFTARTEN_CODIERT[$data[0]] ?? [];
            $seqType = self::SEQUENCE_TYPE_CODIERT[$data[1]] ?? [];
            foreach ($types as $type) {
                foreach ($seqType as $seqType) {
                    $result[$type][$seqType] = MinimaleVorlaufzeitSEPALastschrift::create($data[2], $data[3], $data[0], $data[1]);
                }
            }
        }
        return $result;
    }
}
