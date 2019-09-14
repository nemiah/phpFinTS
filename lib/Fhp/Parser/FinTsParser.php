<?php


namespace Fhp\Parser;

/**
 * Class FinTsParser
 *
 * Helper functions for parsing the FinTs wire format.
 *
 * @package Fhp\Parser
 */
abstract class FinTsParser
{

    /**
     * The FinTs wire format specifies escaping with a question mark `?` for the syntax characters `+:'?@`. This
     * function splits strings delimited by one of these while honoring escaping within.
     *
     * @link: https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
     * Section H.1.3 "Entwertung"
     *
     * @param string $delimiter The delimiter around which to split.
     * @param string $str The raw string, usually a response from the server.
     * @return string[] The splitted substrings. Note that escaped characters inside will still be escaped.
     */
    public static function splitEscapedString($delimiter, $str)
    {
        if (empty($str)) return array();
        // Since most of the $delimiters used in FinTs are also special characters in regexes, we need to escape.
        $delimiter = preg_quote($delimiter, '/');
        // This regex uses a negated look-behind. Generally, the regex `(?<!foo)x` matches an `x` that is NOT preceded
        // by `foo`. In this case, we want to match on the split $delimiter when it is not preceded by the escape
        // character `?`, which we need to escape because it's a special character in regexes.
        return preg_split("/(?<!\\?)$delimiter/", $str);
    }
}
