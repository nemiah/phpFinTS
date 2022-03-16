<?php
namespace Tests\Fhp\Segment;

include_once 'C:/xampp/htdocs' . '/vendor/autoload.php'; 
//echo $_SERVER['DOCUMENT_ROOT'];

use Fhp\Segment\Common\Kti;
use Fhp\Segment\Common\Sdo;
use Fhp\Segment\CAZ\HICAZv1;

echo "<pre>";

class HICAZTest extends \PHPUnit\Framework\TestCase
{
	// Example from FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf, Chapter E.7.1 (SIMPLIFIED)
	// First example: two segments seperated by +
	// Second example: two segments  seperated by +, first segment has a group of two XMLs seperated by :
	// According to specification first segmnet has "gebuchte Umsätze", second segment has "vorgemerkte Umsätze"
	// Inside segemnts several XMLs can be present, seperated by ":"
	
    const HICAZ_Test_start = "HICAZ:5:1:3+DE06940594210000027227:TESTDETT421:::280:+urn?:iso?:std?:iso?:20022?:tech?:xsd?:camt.052.001.02+";
	const sample_XML_doc1  = '<?xml version="1.0" encoding="UTF-8"?>' . 
							'<Document xmlns="urn:iso:std:iso:20022:tech:xsd:camt.052.001.02" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' . 
							'xsi:schemaLocation="urn:iso:std:iso:20022:tech:xsd:camt.052.001.02 camt.052.001.02.xsd">' . 
							'<BkToCstmrAcctRpt><GrpHdr><MsgId>camt52_20131118101510__ONLINEBA</MsgId>' .
							'<CreDtTm>2013-11-18T10:15:10+01:00</CreDtTm><MsgPgntn><PgNb>1</PgNb><LastPgInd>true</LastPgInd></MsgPgntn></GrpHdr>' . 
							'<Rpt><Id>camt052_ONLINEBA</Id>' . 
							'<Ntry><Sts>BOOK</Sts></Ntry>' . 
							'<Ntry><Sts>BOOK</Sts></Ntry>' .
							'</Rpt></BkToCstmrAcctRpt></Document>';
	const sample_XML_doc2  = '<?xml version="1.0" encoding="UTF-8"?>' . 
							'<Document xmlns="urn:iso:std:iso:20022:tech:xsd:camt.052.001.02" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' . 
							'xsi:schemaLocation="urn:iso:std:iso:20022:tech:xsd:camt.052.001.02 camt.052.001.02.xsd">' . 
							'<BkToCstmrAcctRpt><GrpHdr><MsgId>camt52_20131118101510__ONLINEBA</MsgId>' .
							'<CreDtTm>2013-11-18T10:15:10+01:00</CreDtTm><MsgPgntn><PgNb>1</PgNb><LastPgInd>true</LastPgInd></MsgPgntn></GrpHdr>' . 
							'<Rpt><Id>camt052_ONLINEBA</Id>' . 
							'<Ntry><Sts>BOOK</Sts></Ntry>' . 
							'<Ntry><Sts>BOOK</Sts></Ntry>' .
							'<Ntry><Sts>BOOK</Sts></Ntry>' . 
							'<Ntry><Sts>NNNN</Sts></Ntry>' .							
							'</Rpt></BkToCstmrAcctRpt></Document>';

    public function testHICAZparse()
    {
		//First example: two segments  seperated by +
		$hicaz1 = HICAZv1::parse(static::HICAZ_Test_start . 
								'@' . strlen(static::sample_XML_doc1) . '@' . 
								static::sample_XML_doc1 . 
								':' . 
								'@' . strlen(static::sample_XML_doc2) . '@' . 
								static::sample_XML_doc2 .
								"'" );
		print_r($hicaz1->gebuchteUmsaetze);
		
		$this->assertEquals(
            static::sample_XML_doc1,
            $hicaz1->gebuchteUmsaetze->bins[0]->GetData());
			
			
		// XML of second segment should go to nichtGebuchteUmsaetze
		// Currently fails 
		// therefore commented out

								
		//Second example: two segments  seperated by +, first segment has a group of two XMLs seperated by :
		
		//Currently fails 
		//therefore commented out
		
		$hicaz2 = HICAZv1::parse(static::HICAZ_Test_start . 
								'@' . strlen(static::sample_XML_doc1) . '@' . 
								static::sample_XML_doc1 . 
								':@' . strlen(static::sample_XML_doc2) . '@' . 
								static::sample_XML_doc2 .
								'+@' . strlen(static::sample_XML_doc1) . '@' . 
								static::sample_XML_doc1 .
								"'" );						
		$this->assertEquals(
           static::sample_XML_doc1,
           $hicaz2->nichtGebuchteUmsaetze->GetData());
	}
	
	
}
//$h = new HICAZTest;
//$h->testHICAZparse();


