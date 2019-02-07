<?php

namespace Fhp\Parser\Dialect;

use Fhp\Parser\MT940;

/**
 * Class MT940
 * @package Fhp\Parser
 */
class PostbankMT940 extends MT940
{
    const DIALECT_ID = 'https://hbci.postbank.de/banking/hbci.do';

    function extractStructuredDataFromRemittanceLines($descriptionLines, &$gvc, &$rawLines)
    {
        // z.B bei Zinsen o.ä. ist alles leer
        if (!isset($descriptionLines[0])) {
            return [];
        }
        $structuredStartFound = preg_match('/^[A-Z]{4}\+/', $descriptionLines[0]) === 1;

        if ($structuredStartFound) {
            return parent::extractStructuredDataFromRemittanceLines($descriptionLines, $gvc, $rawLines);
        }

        // Bie Auslandsüberweisungen (=210)
        // Der Empfänger name steht als erstes im Verwendungszweck und teile des Verwendungszwecks stehen im Namen
        if ($gvc == '210')
        {
            $name = array_shift($descriptionLines);

            array_unshift($descriptionLines, $rawLines[33]);
            array_unshift($descriptionLines, $rawLines[32]);
            $rawLines[32] = $name;
            $rawLines[33] = '';

        }

        return [
            'SVWZ' => implode("\n", $descriptionLines)
        ];
    }
}
