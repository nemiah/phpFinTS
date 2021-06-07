<?php

namespace Fhp\MT535;

/**
 * Data format: MT 535 (Version SRG 1998)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Finanzdatenformate_2010-08-06_final_version.pdf
 * Section: B.4
 */
class MT535
{
    public function parse(string $rawData): object
    {
        // The divider can be either \r\n or @@
        $divider = substr_count($rawData, "\r\n-") > substr_count($rawData, '@@-') ? "\r\n" : '@@';

        $cleanedRawData = preg_replace('#' . $divider . '([^:])#ms', '$1', $rawData);

        preg_match('/:16R:GENL(.*?):16S:GENL/sm', $cleanedRawData, $blockA);
        preg_match('/:16R:ADDINFO(.*?):16S:ADDINFO/sm', $cleanedRawData, $blockC);
        preg_match_all('/:16R:FIN(.*?):16S:FIN/sm', $cleanedRawData, $blockB);

        $ret = new \StdClass();

        $result = [];
        foreach ($blockB[1] as $block) {
            $o = new \stdClass();
            // handle ISIN, WKN & Name
            // :35B:ISIN DE0005190003/DE/519000BAY.MOTOREN WERKE AG ST
            if (preg_match('/^:35B:(.*?):/sm', $block, $iwn)) {
                preg_match('/^.{5}(.{12})/sm', $iwn[1], $r);
                $o->isin = $r[1];
                preg_match('/^.{21}(.{6})/sm', $iwn[1], $r);
                $o->wkn = $r[1];
                preg_match('/^.{27}(.*)/sm', $iwn[1], $r);
                $o->name = $r[1];
            }

            // handle Price
            // :90B::MRKT//ACTU/EUR76,06
            //A1G1UF
            if (preg_match('/:90(.)::(.*?):/sm', $block, $iwn)) {
                if ($iwn[1] == 'B') {
                    //Currency
                    preg_match('/^.{11}(.{3})/sm', $iwn[2], $r);
                    $o->currency = $r[1];
                    //Price
                    preg_match('/^.{14}(.*)/sm', $iwn[2], $r);
                    $o->price = floatval(str_replace(',', '.', $r[1]));
                } elseif ($iwn[1] == 'A') {
                    $o->currency = '%';
                    //Price
                    preg_match('/^.{11}(.*)/sm', $iwn[2], $r);
                    $o->price = floatval(str_replace(',', '.', $r[1]));
                }
            }

            //handle Amount
            //:93B::AGGR//UNIT/2666,000
            if (preg_match('/:93B::(.*?):/sm', $block, $iwn)) {
                //Amount
                preg_match('/^.{11}(.*)/sm', $iwn[1], $r);
                $o->amount = floatval(str_replace(',', '.', $r[1]));
            }

            //Bereitstellungsdatum
            //:98A::PRIC//20210304
            if (preg_match('/:98(A|C)::(.*?):/sm', $block, $iwn)) {
                preg_match('/^.{6}(.{8})/sm', $iwn[2], $r);
                $o->date = $this->getDate($r[1]);
                if ($iwn[1] == 'C') {
                    preg_match('/^.{14}(.{6})/sm', $iwn[2], $r);
                    $o->time = $r[1];
                } else {
                    $o->time = new \DateTime();
                    $o->time->setTime(0, 0);
                }
            }

            $result[] = $o;
        }
        $ret->blockA = $blockA;
        $ret->blockB = $result;
        $ret->blockC = $blockC;
        return $ret;
    }

    protected function getDate(string $val): string
    {
        preg_match('/(\d{4})(\d{2})(\d{2})/', $val, $m);
        return $m[1] . '-' . $m[2] . '-' . $m[3];
    }
}
