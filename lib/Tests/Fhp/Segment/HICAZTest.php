<?php

namespace Tests\Fhp\Segment;

use Fhp\Segment\CAZ\HICAZv1;

class HICAZTest extends \PHPUnit\Framework\TestCase
{
    // Example from FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf, Chapter E.7.1 (SIMPLIFIED)
    // First example: two segments seperated by +
    // Second example: two segments  seperated by +, first segment has a group of two XMLs seperated by :
    // According to specification first segmnet has "gebuchte Umsätze", second segment has "vorgemerkte Umsätze"
    // Inside segemnts several XMLs can be present, seperated by ":"

    private const HICAZ_TEST_START = 'HICAZ:5:1:3+DE06940594210000027227:TESTDETT421:::280:+urn?:iso?:std?:iso?:20022?:tech?:xsd?:camt.052.001.02+';
    private const SAMPLE_XML_DOC1 = (
        '<?xml version="1.0" encoding="UTF-8"?>' .
        '<Document xmlns="urn:iso:std:iso:20022:tech:xsd:camt.052.001.02" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' .
        'xsi:schemaLocation="urn:iso:std:iso:20022:tech:xsd:camt.052.001.02 camt.052.001.02.xsd">' .
        '<BkToCstmrAcctRpt><GrpHdr><MsgId>camt52_20131118101510__ONLINEBA</MsgId>' .
        '<CreDtTm>2013-11-18T10:15:10+01:00</CreDtTm><MsgPgntn><PgNb>1</PgNb><LastPgInd>true</LastPgInd></MsgPgntn></GrpHdr>' .
        '<Rpt><Id>camt052_ONLINEBA</Id>' .
        '<Ntry><Sts>BOOK</Sts></Ntry>' .
        '<Ntry><Sts>BOOK</Sts></Ntry>' .
        '</Rpt></BkToCstmrAcctRpt></Document>'
    );
    private const SAMPLE_XML_DOC2 = (
        '<?xml version="1.0" encoding="UTF-8"?>' .
        '<Document xmlns="urn:iso:std:iso:20022:tech:xsd:camt.052.001.02" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' .
        'xsi:schemaLocation="urn:iso:std:iso:20022:tech:xsd:camt.052.001.02 camt.052.001.02.xsd">' .
        '<BkToCstmrAcctRpt><GrpHdr><MsgId>camt52_20131118101510__ONLINEBA</MsgId>' .
        '<CreDtTm>2013-11-18T10:15:10+01:00</CreDtTm><MsgPgntn><PgNb>1</PgNb><LastPgInd>true</LastPgInd></MsgPgntn></GrpHdr>' .
        '<Rpt><Id>camt052_ONLINEBA</Id>' .
        '<Ntry><Sts>BOOK</Sts></Ntry>' .
        '<Ntry><Sts>BOOK</Sts></Ntry>' .
        '<Ntry><Sts>BOOK</Sts></Ntry>' .
        '<Ntry><Sts>BOOK</Sts></Ntry>' .
        '</Rpt></BkToCstmrAcctRpt></Document>'
    );
    private const SAMPLE_XML_DOC3 = (
        '<?xml version="1.0" encoding="UTF-8"?>' .
        '<Document xmlns="urn:iso:std:iso:20022:tech:xsd:camt.052.001.02" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' .
        'xsi:schemaLocation="urn:iso:std:iso:20022:tech:xsd:camt.052.001.02 camt.052.001.02.xsd">' .
        '<BkToCstmrAcctRpt><GrpHdr><MsgId>camt52_20131118101510__ONLINEBA</MsgId>' .
        '<CreDtTm>2013-11-18T10:15:10+01:00</CreDtTm><MsgPgntn><PgNb>1</PgNb><LastPgInd>true</LastPgInd></MsgPgntn></GrpHdr>' .
        '<Rpt><Id>camt052_ONLINEBA</Id>' .
        '<Ntry><Sts>PDNG</Sts></Ntry>' .
        '</Rpt></BkToCstmrAcctRpt></Document>'
    );

    public function testHICAZparse()
    {
        // First example: two  XMLs  seperated by ":" - both are gebuchteUmsaetze
        $hicaz1 = HICAZv1::parse(
            static::HICAZ_TEST_START .
            '@' . strlen(static::SAMPLE_XML_DOC1) . '@' .
            static::SAMPLE_XML_DOC1 .
            ':' .
            '@' . strlen(static::SAMPLE_XML_DOC2) . '@' .
            static::SAMPLE_XML_DOC2 .
            "'"
        );

        $this->assertEquals([static::SAMPLE_XML_DOC1, static::SAMPLE_XML_DOC2],
            $hicaz1->getGebuchteUmsaetze());

        // Second example: two areas  seperated by +, first area has a group of two XMLs seperated by :

        $hicaz2 = HICAZv1::parse(
            static::HICAZ_TEST_START .
            '@' . strlen(static::SAMPLE_XML_DOC1) . '@' .
            static::SAMPLE_XML_DOC1 .
            ':@' . strlen(static::SAMPLE_XML_DOC2) . '@' .
            static::SAMPLE_XML_DOC2 .
            '+@' . strlen(static::SAMPLE_XML_DOC3) . '@' .
            static::SAMPLE_XML_DOC3 .
            "'"
        );
        $this->assertEquals([static::SAMPLE_XML_DOC1, static::SAMPLE_XML_DOC2],
            $hicaz2->getGebuchteUmsaetze());
        $this->assertEquals(static::SAMPLE_XML_DOC3,
            $hicaz2->getNichtGebuchteUmsaetze());
    }
}
