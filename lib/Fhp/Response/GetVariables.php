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
		$variables->tanModes = $this->parseTanModes($segments);
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
