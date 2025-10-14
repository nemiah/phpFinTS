<?php

namespace Fhp\MT940\Dialect;

use Fhp\MT940\MT940;

class SpardaMT940 extends MT940
{
    public const DIALECT_ID = 'https://fints.bankingonline.de/fints/FinTs30PinTanHttpGate';

    public function extractStructuredDataFromRemittanceLines($descriptionLines, string &$gvc, array &$rawLines, array $transaction): array
    {
        $otherInfo = [];
        $structuredStartFound = false;
        $lines = [];
        foreach ($descriptionLines as $line) {
            if ($structuredStartFound || preg_match('/^[A-Z]{4}\+ /', $line) === 1) {
                $structuredStartFound = true;
                $lines[] = $line;
            } else {
                $otherInfo[] = $line;
            }
        }
        if (!$structuredStartFound) {
            return ['SVWZ' => implode("\n", $otherInfo)];
        }

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
            $combined .= preg_replace('/ ([A-Z]{4}\+)$/', ' $1 ', $line);
        }
        $combined = implode('', $lines);

        // SEPA Bezeichner müssen in einer neuen Zeile Anfangen und kein Leerzeichen hinter dem + haben
        $fixed = preg_replace('/([A-Z]{4}\+) /', "\n$1", $combined);

        $correctedLines = explode("\n", trim($fixed, "\n"));

        // Buchungstext z.B. SEPA-ÜBERWEISUNG
        if (count($otherInfo) > 0) {
            $rawLines[0] = $bookingText = array_pop($otherInfo);

            switch ($bookingText) {
                case 'SEPA-ÜBERWEISUNG':
                    if ($transaction['credit_debit'] === static::CD_CREDIT) {
                        $gvc = '166';
                    }
                    if ($transaction['credit_debit'] === static::CD_DEBIT) {
                        $gvc = '177';
                    }
                    break;
                case 'SEPA-BASISLASTSCHRIFT':
                    $gvc = '105';
                    break;
                    // case 'SEPA-RÜCKLASTSCHRIFT':
                    // Hängt vom Betrag ab ?
            }
        }

        // Rest vom Namen, wenn der > 27 Zeichen ist, ja ernsthaft
        if (count($otherInfo) > 0) {
            $rawLines[33] .= array_pop($otherInfo);
        }

        $desc = parent::extractStructuredDataFromRemittanceLines($correctedLines, $gvc, $rawLines, $transaction);

        if (isset($desc['SVWZ']) && str_starts_with($desc['SVWZ'], 'Dauerauftrag')) {
            $gvc = '152';
            $rawLines[0] = 'Dauerauftrag-Gutschrift';
        }

        return $desc;
    }
}
