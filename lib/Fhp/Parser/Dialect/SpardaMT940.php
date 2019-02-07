<?php

namespace Fhp\Parser\Dialect;

use Fhp\Parser\MT940;

/**
 * Class MT940
 * @package Fhp\Parser
 */
class SpardaMT940 extends MT940
{
    const DIALECT_ID = 'https://fints.bankingonline.de/fints/FinTs30PinTanHttpGate';

    function extractStructuredDataFromRemittanceLines($descriptionLines, &$gvc, &$rawLines)
    {
        $otherInfo = [];
        $structuredStartFound = false;
        $lines = [];
        $correctedLines = [];
        foreach ($descriptionLines as $line) {
            if ($structuredStartFound || preg_match('/^[A-Z]{4}\+ /', $line) === 1) {
                $structuredStartFound = true;
                $lines[] = $line;
            } else {
                $otherInfo[] = $line;
            }
        }
        if (!$structuredStartFound) {
            $correctedLines = $otherInfo;
            $otherInfo = [];

            return [
                'SVWZ' => implode("\n", $correctedLines)
            ];
        } else {

            // Beispiel
            /*
              0 => "SEPA-BASISLASTSCHRIFT"
              1 => "EREF+ xxxxxxxxxxxxxxxxxx MR"
              2 => "EF+ xxxxxxxxxxxxxxxx CRED+"
              3 => "XXxxxxxxxxxxxxxxxxx SVWZ+ A"
              4 => "bcdef ghijklmn opqr stuvwxy"
              5 => "z1 1234 678912345"
              6 => "ABWA+ Abcd Efghij"
            */

            $combined = '';
            foreach ($lines as $line) {
                // Sonderfall, für Zeile 2 aus dem Beispiel
                $combined .= preg_replace('/ ([A-Z]{4}\+)$/', " $1 ", $line);
            }
            $combined = implode("", $lines);

            // SEPA Bezeichner müssen in einer neuen Zeile Anfangen und kein Leerzeichen hinter dem + haben
            $fixed = preg_replace('/([A-Z]{4}\+) /', "\n$1", $combined);

            $correctedLines = explode("\n", trim($fixed, "\n"));
        }

        // Buchungstext z.B. SEPA-ÜBERWEISUNG
        if (count($otherInfo) > 0) {
            $rawLines[0] = $bookingText = array_pop($otherInfo);

            switch ($bookingText) {
                case 'SEPA-ÜBERWEISUNG':
                    $gvc = '166';
                case 'SEPA-BASISLASTSCHRIFT':
                    $gvc = '105';
                //case 'SEPA-RÜCKLASTSCHRIFT':
                // Hängt vom Betrag ab ?
                //}
                break;
            }
        }

        // Rest vom Namen, wenn der > 27 Zeichen ist, ja ernsthaft
        if (count($otherInfo) > 0) {
            $rawLines[33] .= array_pop($otherInfo);
        }

        $desc = parent::extractStructuredDataFromRemittanceLines($correctedLines, $gvc, $rawLines);

        if (isset($desc['SVWZ']) && strpos($desc['SVWZ'], 'Dauerauftrag') === 0) {
            $gvc = '152';
            $rawLines[0] = 'Dauerauftrag-Gutschrift';
        }

        return $desc;
    }
}
