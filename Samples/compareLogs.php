<?php
require '../vendor/autoload.php';
require "diff.php";

$hibiscusLog = trim(file_get_contents("hbcitrace_2019-09-29_11-15-59.log"));
$phpFinTSLog = trim(file_get_contents("state.log"));
$hibiscusLog = str_replace("\00", "0", $hibiscusLog);

$hibiscusLog = explode("\n", $hibiscusLog);
unset($hibiscusLog[0]);

$phpFinTSLog = explode("\n", $phpFinTSLog);
foreach($phpFinTSLog AS $i => $line){
	if(strpos($line, ">") !== 0 AND strpos($line, "<") !== 0){
		unset($phpFinTSLog[$i]);
		continue;
	}
	
	$phpFinTSLog[$i] = str_replace("> ", "", $phpFinTSLog[$i]);
	$phpFinTSLog[$i] = str_replace("< ", "", $phpFinTSLog[$i]);
}

$phpFinTSLogParsed = array();
foreach($phpFinTSLog AS $message){
	$data = mask(preg_split("#'(?=[A-Z]{4,}:\d|')#", $message));
	
	$phpFinTSLogParsed[] = implode("\n", $data);
}

$hibiscusLogParsed = array();
foreach($hibiscusLog AS $message){
	$data = mask(preg_split("#'(?=[A-Z]{4,}:\d|')#", $message));
	
	$hibiscusLogParsed[] = implode("\n", $data);
}

$diff = Diff::toTable(Diff::compare(implode("\n", $hibiscusLogParsed), implode("\n", $phpFinTSLogParsed), false));


$html = '<!doctype html>
<html class="no-js" lang="de">
    <head>
        <meta charset="utf-8">
        <title>compareLogs</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		
		<style type="text/css">
			.diff td{
				vertical-align : top;
				white-space    : pre;
				white-space    : pre-wrap;
				font-family    : monospace;
			}
			
			.diffDeleted {
				background-color:#f3d2d2;
			}
			.diffInserted {
				background-color:#d7f3d2;
			}
		  </style>
    </head>
    <body>'.$diff.'
    </body>
</html>
';


file_put_contents("compareLogs.html", $html);

function mask($data){
	#echo $data[0]."\n";
	
	foreach($data AS $k => $segment){
		$type = substr($segment, 0, 5);
		echo $type."\n";
		switch($type){
			case "HNHBK":
				$data[$k] = preg_replace("/HNHBK:([0-9]):([0-9])\+([0-9]+)\+300\+[0-9a-zA-Z#%]+\+([0-9]+)/", "HNHBK:\\1:\\2+*+300+*+\\3", $data[$k]);
				$data[$k] = preg_replace("/HNHBK:([0-9]):([0-9])\+([0-9]+)\+300\+[0-9a-zA-Z#%]+\+1\+[0-9a-zA-Z#%]+:1/", "HNHBK:\\1:\\2+*+300+*+1+*", $data[$k]);
			break;

			case "HNSHA":
				$data[$k] = preg_replace("/HNSHA:([0-9]):([0-9])\+([0-9]+)\+\+[0-9a-zA-Z]+/", "HNSHA:\\1:\\2+*++*", $data[$k]);
			break;
		
			case "HNVSK":
				$data[$k] = preg_replace("/HNVSK:([0-9]+):([0-9]+)\+PIN:([0-9]+)\+([0-9]+)\+1\+1::[0-9a-zA-Z]+\+1:[0-9]+:[0-9]+\+2:2:13:/", "HNVSK:\\1:\\2+PIN:\\3+\\4+1+1::*+1:*:*+2:2:13:", $data[$k]);
			break;
		
			case "HKIDN":
				$data[$k] = preg_replace("/HKIDN:([0-9]+):([0-9]+)\+280:([0-9]+)\+([0-9]+)\+[a-zA-ZT0-9]+\+1/", "HKIDN:\\1:\\2+280:\\3+\\4+*+1", $data[$k]);
			break;
		
			case "HKVVB":
				$data[$k] = preg_replace("/HKVVB:([0-9]+):([0-9]+)[\+A-Za-z0-9\.]+/", "HKVVB:\\1:\\2+…", $data[$k]);
			break;
		
			#HKVVB:([0-9]+):([0-9]+)\+([0-9]+)+589+1+A44C2953982351617D475443E+2.8
			#HKVVB:\\1:\\2+\\3+0+0+A53E768DD59EBCF18943ECB28+3.0
		}
	}
	return $data;
}