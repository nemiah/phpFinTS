<?php

namespace Fhp\MT535;

use Fhp\Model\StatementOfHoldings\Holding;
use Fhp\Model\StatementOfHoldings\StatementOfHoldings;

/**
 * Data format: MT 535 (Version SRG 1998)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Finanzdatenformate_2010-08-06_final_version.pdf
 * Section: B.4
 */
class MT535
{
    /** @var string */
    private $cleanedRawData;

    public function __construct(string $rawData)
    {
        // The divider can be either \r\n or @@
        $divider = substr_count($rawData, "\r\n-") > substr_count($rawData, '@@-') ? "\r\n" : '@@';
        $this->cleanedRawData = preg_replace('#' . $divider . '([^:])#ms', '$1', $rawData);
    }

    public function parseDepotWert(): float
    {
        preg_match('/:16R:ADDINFO(.*?):16S:ADDINFO/sm', $this->cleanedRawData, $block);
        preg_match('/EUR(.*)/sm', $block[1], $matches);
        return floatval(str_replace(',', '.', $matches[1]));
    }

    public function parseHoldings(): StatementOfHoldings
    {
        $result = new StatementOfHoldings();
        preg_match_all('/:16R:FIN(.*?):16S:FIN/sm', $this->cleanedRawData, $blocks);
        foreach ($blocks[1] as $block) {
            $holding = new Holding();
            // handle ISIN, WKN & Name
            // :35B:ISIN DE0005190003/DE/519000BAY.MOTOREN WERKE AG ST
            if (preg_match('/^:35B:(.*?):/sm', $block, $iwn)) {
                preg_match('/^.{5}(.{12})/sm', $iwn[1], $r);
                $holding->setISIN($r[1]);
                preg_match('/^.{21}(.{6})/sm', $iwn[1], $r);
                $holding->setWKN($r[1]);
                preg_match('/^.{27}(.*)/sm', $iwn[1], $r);
                $holding->setName($r[1]);
            }

            // handle acquisition price
            // e.g ':70E::HOLD//1STK23,968293+EUR'
            if (preg_match('/:70E::HOLD\/\/\d*STK2(\d*),(\d*)\+([A-Z]{3})/sm', $block, $iwn)) {
                $holding->setAcquisitionPrice((float) $iwn[1].'.'.$iwn[2]);
                if ($holding->getCurrency() === null) {
                    $holding->setCurrency($iwn[3]);
                }
            }

            // handle Price
            // :90B::MRKT//ACTU/EUR76,06
            // A1G1UF
            if (preg_match('/:90(.)::(.*?):/sm', $block, $iwn)) {
                if ($iwn[1] == 'B') {
                    // Currency
                    preg_match('/^.{11}(.{3})/sm', $iwn[2], $r);
                    $holding->setCurrency($r[1]);
                    // Price
                    preg_match('/^.{14}(.*)/sm', $iwn[2], $r);
                    $holding->setPrice(floatval(str_replace(',', '.', $r[1])));
                } elseif ($iwn[1] == 'A') {
                    $holding->setCurrency('%');
                    // Price
                    preg_match('/^.{11}(.*)/sm', $iwn[2], $r);
                    $holding->setPrice(floatval(str_replace(',', '.', $r[1])) / 100);
                }
            }

            // handle Amount
            // :93B::AGGR//UNIT/2666,000
            if (preg_match('/:93B::(.*?):/sm', $block, $iwn)) {
                // Amount
                preg_match('/^.{11}(.*)/sm', $iwn[1], $r);
                $holding->setAmount(floatval(str_replace(',', '.', $r[1])));
            }

            if ($holding->getAmount() !== null && $holding->getPrice() !== null) {
                if ($holding->getCurrency() === '%') {
                    $holding->setValue($holding->getPrice() / 100);
                } else {
                    $holding->setValue($holding->getPrice() * $holding->getAmount());
                }
            }

            // Bereitstellungsdatum
            // :98A::PRIC//20210304
            // :98C::STAT//20250104140541
            if (preg_match('/:98([AC])::(.*?):/sm', $block, $iwn)) {
                preg_match('/^.{6}(.{8})/sm', $iwn[2], $r);
                $holding->setDate($this->getDate($r[1]));
                $time = new \DateTime();
                if ($iwn[1] == 'C') {
                    // 98C has a time component
                    preg_match('/^.{14}(\d\d)(\d\d)(\d\d)/sm', $iwn[2], $r);
                    $time->setTime($r[1], $r[2], $r[3]);
                } else {
                    $time->setTime(0, 0);
                }
                $holding->setTime($time);
            }

            $result->addHolding($holding);
        }
        return $result;
    }

    protected function getDate(string $val): \DateTime
    {
        preg_match('/(\d{4})(\d{2})(\d{2})/', $val, $m);
        try {
            return new \DateTime($m[1] . '-' . $m[2] . '-' . $m[3]);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Invalid date: $val", 0, $e);
        }
    }
}
