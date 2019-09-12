<?php

namespace Fhp\Segment;

/**
 * Class HKTAN (Zwei-Schritt-TAN-Einreichung)
 * Segment type: Geschäftsvorfall
 *
 * @link: https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_Rel_20101027_final_version.pdf
 * Section: B.4.5.1.1.1
 *
 * @author Nena Furtmeier <support@furtmeier.it>
 * @package Fhp\Segment
 */
class HKTAN extends AbstractSegment
{
    const NAME = 'HKTAN';
    const VERSION = 6;

    /**
     * HKCDL constructor.
     * @param int $version
     * @param int $segmentNumber
     */
    public function __construct($version, $segmentNumber, $processID = null)
    {
		$data = array();
		$data[] = 4;
		if($processID){
			$data = array();
			if ($version == 6) {
				$data[] = 2; 
				$data[] = ""; 
				$data[] = ""; 
				$data[] = "";
				$data[] = $processID;
				$data[] = "N";
			} else { 
				$data[] = 2; 
				$data[] = ""; 
				$data[] = ""; 
				$data[] = "";
				$data[] = $processID;
				$data[] = "";
				$data[] = "N";
			}
		
			#$data[] = 2;
			#$data[] = "";
			#$data[] = "";
			#$data[] = "";
			#$data[] = $processID;
		} else {
			$data[] = "HKIDN";
			$data[] = "";
			$data[] = "";
			$data[] = "";
			$data[] = "N";
		}
		
		
        parent::__construct(
            static::NAME,
            $segmentNumber,
            $version,
            $data
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return static::NAME;
    }
}
