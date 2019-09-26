<?php

namespace Fhp\Response;

use Fhp\Segment\BaseSegment;
use Fhp\Segment\HITANS\HITANS;

/**
 * Class GetVariables
 * @package Fhp\Response
 */
class GetVariables extends Response
{
	public function get()
	{
		$variables = new \stdClass();
		$segments = $this->findSegments('HITANS');
		
        $allTanModes = $this->parseTanModes($segments);
        
        $variables->tanModes = array();
		foreach ($this->findSegments('HIRMS') as $segment) {
            $segment = $this->splitSegment($segment);
            foreach ($segment as $de)
                if (substr($de, 0, 6) === "3920::") {
                    $de = $this->splitDeg($de);
                    $de = array_slice($de, 3);

                    foreach ($de as $methodNr)
                        if (array_key_exists($methodNr, $allTanModes))
                            $variables->tanModes[$methodNr] = $allTanModes[$methodNr];
                    break;
                }
        }

		return $variables;
	}

	public function getSupportedTanMechanisms() {
		return $this->get()->tanModes;
	}

	private function parseTanModes($segments)
	{
		$result = array();
		foreach ($segments as $segmentRaw) {
            $hitans = BaseSegment::parse($segmentRaw);
            if (!($hitans instanceof HITANS)) {
                throw new \InvalidArgumentException("All HITANS segments must implement the HITANS interface");
            }
            foreach ($hitans->getParameterZweiSchrittTanEinreichung()->getVerfahrensparameterZweiSchrittVerfahren() as $verfahren) {
                $result[$verfahren->getSicherheitsfunktion()] = $verfahren->getNameDesZweiSchrittVerfahrens();
            }
        }
		return $result;
	}
}
