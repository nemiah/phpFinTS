<?php

namespace Fhp\ResponseTest;

use Fhp\Response\GetVariables;

class GetVariablesTest extends \PHPUnit_Framework_TestCase
{
	public function testParseTanModesSparkasse()
	{
		$segments = array(
			'HITANS:169:6:4+1+1+1+J:N:0:910:2:HHD1.3.0:::chipTAN manuell:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:0:N:1:911:2:HHD1.3.2OPT:HHDOPT1:1.3.2:chipTAN optisch:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:0:N:1:912:2:HHD1.3.2USB:HHDUSB1:1.3.2:chipTAN-USB:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:0:N:1:913:2:Q1S:Secoder_UC:1.2.0:chipTAN-QR:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:0:N:1:920:2:smsTAN:::smsTAN:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:2:N:5:921:2:pushTAN:::pushTAN:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:2:N:2:900:2:iTAN:::iTAN:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:0:N:0'
		);
		$gv = new GetVariables(null, null);
		$modes = $gv->parseTanModes($segments);
		$this->assertEquals($modes[910], 'chipTAN manuell');
		$this->assertEquals($modes[911], 'chipTAN optisch');
		$this->assertEquals($modes[912], 'chipTAN-USB');
		$this->assertEquals($modes[913], 'chipTAN-QR');
		$this->assertEquals($modes[920], 'smsTAN');
		$this->assertEquals($modes[921], 'pushTAN');
		$this->assertEquals($modes[900], 'iTAN');
	}
}
