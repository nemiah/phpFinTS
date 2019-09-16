<?php

namespace Fhp\Response;

use Fhp\Parser\Exception\MT940Exception;

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

	public function parseTanModes($segments)
	{
		// extracted from https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2018-02-23_final_version.pdf
		// Zwei-Schritt-TAN-Einreichung, Parameter
		//
		// 1. Segmentkopf:
		// Beispiel: HITANS:169:6:4
		// 1.1. Segmentkennung (HITANS)
		// 1.2. Segmentnummer
		// 1.3. Segmentversion
		// 1.4. Bezugssegment
		// 2. Maximale Anzahl Aufträge (int(3))
		// 3. Anzahl Signaturen mindestens (0, 1, 2, 3)
		// 4. Sicherheitsklasse (0, 1, 2, 3, 4)
		// 5. Parameter Zwei-Schritt-TAN Einreichung
		// 5.1. Einschrittverfahren erlaubt [jn] (V1-V6)
		// 5.2. Mehr als ein TANpflichtiger Auftrag pro Nachricht erlaubt [jn] (V1-V6)
		// 5.3. AuftragsHashwertverfahren [code] (V1-V6)
		// 5.4. Sicherheitsprofil Banken-Signatur bei HITAN [code] (V1)
		// 5.4. Verfahrensparameter ZweiSchritt-Verfahren (V2-V6)
		// 5.5. Verfahrensparameter ZweiSchritt-Verfahren (V1)
		// Sparkasse has 5 elements but declares version 6
		$result = array();
		foreach ($segments as $segmentRaw) {
			$segment = $this->splitSegment($segmentRaw);

			$segmentHeader = $this->splitDeg($segment[0]);
			$version = $segmentHeader[2];  // 1.3

			$paramsIndex = count($segment) - 1;
			$params = $this->splitDeg($segment[$paramsIndex]);

			// 5. Parameter Zwei-Schritt-TAN-Einreichung:
			// 5.4/5.5 s. Verfahrensparameter Zwei-Schritt-Verfahren
			// Beispiel: J:N:0:910:2:HHD1.3.0:::chipTAN manuell:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:0:N:1:911:2:HHD1.3.2OPT:HHDOPT1:1.3.2:chipTAN optisch:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:0:N:1:912:2:HHD1.3.2USB:HHDUSB1:1.3.2:chipTAN-USB:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:0:N:1:913:2:Q1S:Secoder_UC:1.2.0:chipTAN-QR:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:0:N:1:920:2:smsTAN:::smsTAN:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:2:N:5:921:2:pushTAN:::pushTAN:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:2:N:2:900:2:iTAN:::iTAN:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:0:N:0"
			// -> Version 6
			// J:N:0: (-> $processParamsOffset = 4)
			// 910:2:HHD1.3.0:::chipTAN manuell:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:0:N:1:
			// 911:2:HHD1.3.2OPT:HHDOPT1:1.3.2:chipTAN optisch:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:0:N:1:
			// 912:2:HHD1.3.2USB:HHDUSB1:1.3.2:chipTAN-USB:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:0:N:1:
			// 913:2:Q1S:Secoder_UC:1.2.0:chipTAN-QR:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:0:N:1:
			// 920:2:smsTAN:::smsTAN:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:2:N:5:
			// 921:2:pushTAN:::pushTAN:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:2:N:2:
			// 900:2:iTAN:::iTAN:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:0:N:0"

			$processParamsOffset = $this->getTanProcessParamsOffsetForVersion($version);
			$processNameIndex = $this->getTanProcessNameIndexForVersion($version);
			$processParamElementCount = $this->getTanProcessParamElementCountForVersion($version);
			array_splice($params, 0, $processParamsOffset);
			if (count($params) % $processParamElementCount !== 0) {
				throw new MT940Exception('Invalid number of params for HITANS version ' . $version . ': ' . count($params));
			}
			$paramBlockIterations = count($params) / $processParamElementCount;
			for ($i = 0; $i < $paramBlockIterations; $i++) {
				$blockOffset = $i * $processParamElementCount;
				$num = $params[$blockOffset]; // first element in block
				$name = $params[$processNameIndex + $blockOffset];
				$result[$num] = $name;
			}
		}
		return $result;
	}

	private function getTanProcessParamsOffsetForVersion($version)
	{
		switch ($version) {
			case '1':
				return 4;
			case '2':
			case '3':
			case '4':
			case '5':
			case '6':
				return 3;
			default:
				throw new MT940Exception('Unknown HITANS version ' . $version);
		}
	}
	private function getTanProcessParamElementCountForVersion($version)
	{
		switch ($version) {
			case '1':
				return 11;
				break;
			case '2':
				return 15;
			case '3':
				return 18;
			case '4':
			case '5':
				return 22;
			case '6':
				return 21;
			default:
				throw new MT940Exception('Unknown HITANS version ' . $version);
		}
	}
	private function getTanProcessNameIndexForVersion($version)
	{
		switch ($version) {
			case '1':
			case '2':
			case '3':
				return 3;
			case '4':
			case '5':
			case '6':
				return 5;
			default:
				throw new MT940Exception('Unknown HITANS version ' . $version);
		}
	}
}
