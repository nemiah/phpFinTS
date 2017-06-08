<?php

namespace Fhp\Response;

/**
 * Class GetVariables
 * @package Fhp\Response
 */
class GetVariables extends Response
{
	public function get(){
		$variables = new \stdClass();
		$tanNames = array();
		$s = $this->findSegments("HITANS");
		foreach($s AS $sub){
			$ex = $this->splitSegment($sub);
			$cex = $this->splitDeg($ex[4]);
			for($i = 0; $i < 20; $i++){
				if(!isset($cex[3  + $i * 22]))
					break;
				
				$tanNames[$cex[3 + $i * 22]] = $cex[8 + $i * 22];
			}
		}
		
		$variables->tanModes = $tanNames;
		
		return $variables;
	}
}
