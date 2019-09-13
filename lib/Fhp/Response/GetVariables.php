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
				
				$name = $cex[8 + $i * 22];
				if(strlen($name) < 3)
					continue;
				
				$num = $cex[3 + $i * 22];
				if(!is_numeric($num) OR trim($num) == "")
					continue;
				
				$tanNames[$num] = $name;
			}
		}
		
		$variables->tanModes = $tanNames;
		
		return $variables;
	}
}
