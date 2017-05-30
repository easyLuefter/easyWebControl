<?php

echo date("d.m.Y H:i:s", time()) . " var_dump argv:\r\n";
var_dump($argv); echo "\r\n";

$path = pathinfo($argv['0'])['dirname'];
$PHPname = pathinfo($argv['0'])['basename'];

//run only once
if (!file_exists("/tmp/$PHPname.lock")) file_put_contents("/tmp/$PHPname.lock", "dummy", FILE_APPEND);
$fp = fopen("/tmp/$PHPname.lock", 'r+');
if (!flock($fp, LOCK_EX | LOCK_NB)) {
	echo "$PHPname is already running (locked)\n...exiting\n";
    exit;
} else echo "$PHPname not locked\n";

if (isset($argv['1'])) {
	if ($argv['1'] == "d") {
		$delay = 5*60;		// 5 min
		echo date("d.m.Y H:i:s") . " $delay sec start-up delay\r\n";
		sleep($delay); 		// wait $delay sec after system start
	}
}

echo date("d.m.Y H:i:s") . " $PHPname started\n";

include "locals/locals.php";
include "const.php";
include "locals/mySqlConnect.php";

			
$RHSpeed = 0;
$FTRmode = "    ";
mysql_query("UPDATE $tableName_config SET FTRmode= '$FTRmode' WHERE config = 1");
mysql_query("UPDATE $tableName_vars SET RHSpeed= '$RHSpeed' WHERE vars = 1");

$time = time();
$doWork = TRUE;

while ($doWork == TRUE) {


	echo "\n" . date("H:i:s ") . "humTemp\n";

	$res = mysql_query("SELECT * FROM $tableName_hum");
	$hum = mysql_fetch_assoc($res);

	$res = mysql_query("SELECT * FROM $tableName_config");
	$config = mysql_fetch_assoc($res);
	
	$res = mysql_query("SELECT * FROM $tableName_av WHERE actualValue=1");
	$mesures = mysql_fetch_assoc($res);

	$res = mysql_query("SELECT * FROM $tableName_vars");
	$vars = mysql_fetch_assoc($res);
	
	
	//wird alle 60 sec aufgerufen


	// Berechnung RH Feuchte-Mittelwert	1
	$res = mysql_query("SELECT AVG(AbluftRH) as FSavRH01 from $tableName WHERE timeStamp > " . (time()/60 - $hum['FSavCount1']));
	$mes1 = mysql_fetch_assoc($res);
	echo "FSavRH01: $mes1[FSavRH01]\n";
	// Berechnung RH Feuchte-Mittelwert 2
	$res = mysql_query("SELECT AVG(AbluftRH) as FSavRH02 from $tableName WHERE timeStamp > " . (time()/60 - 60*24 * $hum['FSavCount2']));
	$mes2 = mysql_fetch_assoc($res);
	echo "FSavRH02: $mes2[FSavRH02]\n";
	mysql_query("UPDATE $tableName_vars SET FSavRH01=$mes1[FSavRH01], FSavRH02= $mes2[FSavRH02] WHERE vars = 1");
	
	$res = mysql_query("SELECT * FROM $tableName_vars");
	$vars = mysql_fetch_assoc($res);
	$RHSpeed = $vars['RHSpeed'];


	$FTRmode = "    ";

	if (($config['Mode'] == "MIN") || ($config['Mode'] == "MAX")) {	 // if mode MIN or MAX skip HumTempFuncts
		$RHSpeed = 0;

	// Feuchte-Sprung kurzfristig - keine Überptüfung der Feuchte- oder Hitze-Abfuhr  --> Erhöhen der Lüfterleistung
	} else if (($mesures['AbluftRH'] > ($vars['FSavRH01'] + $hum['FSgap1'])) && ($hum['FSmaxPosOffset1'] != 0) && (abs($vars['Dcomponent']) < 3)) {   
		echo "FS1+ \r\n";
		$FTRmode ="FS1+";
		if (($tmpRHSpeed = $RHSpeed + $hum['FSincL1']) > $hum['FSmaxPosOffset1']) $tmpRHSpeed = $hum['FSmaxPosOffset1'];
		if ($tmpRHSpeed > --$RHSpeed) $RHSpeed = $tmpRHSpeed;
		mysql_query("UPDATE $tableName_vars SET FS1nCount = $hum[FS1n] WHERE vars = 1");
	} else if ($vars['FS1nCount'] > 0) { 
		echo "FS1nCount > 0\r\n";
		$FTRmode = "FS1n";
		mysql_query("UPDATE $tableName_vars SET FS1nCount = FS1nCount -1 WHERE vars = 1"); // ..and do nothing
		if ($vars['enfnCount'] > 0) mysql_query("UPDATE $tableName_vars SET enfnCount = enfnCount -1 WHERE vars = 1"); // ..and do nothing

	// Feuchte-Sprung mittelfristig - Überprüfung der Feuchte-Abfuhr  --> Erhöhen der Lüfterleistung
	} else if (($mesures['AbluftRH'] > ($vars['FSavRH02'] + $hum['FSgap2'])) && ($hum['FSmaxPosOffset2'] != 0) && (abs($vars['Dcomponent']) < 3)) {   
		echo "FS2+ \r\n";
		$FTRmode ="FS2+";
		if (($tmpRHSpeed = $RHSpeed + $hum['FSincL2']) > $hum['FSmaxPosOffset2']) $tmpRHSpeed = $hum['FSmaxPosOffset2'];
		if ($tmpRHSpeed > --$RHSpeed) $RHSpeed = $tmpRHSpeed;
		//$FS1nCount = $hum['FS2n'];

	// anti-Schimmel - Feuchteabfuhr möglich --> Erhöhen der Lüfterleistung
	} else if (($mesures['AbluftAH'] > $vars['AS_AH']) && ($hum['ASposOffset'] != 0) && ($mesures['AbluftAH'] > $mesures['AussenluftAH'])) {   
		$FTRmode = "AS+";
		if (($tmpRHSpeed = $RHSpeed + 2) > $hum['ASposOffset']) $tmpRHSpeed = $hum['ASposOffset'];
		if ($tmpRHSpeed > --$RHSpeed) $RHSpeed = $tmpRHSpeed;

	// anti-Schimmel - Feuchteabfuhr nicht möglich --> Reduktion der Lüfterleistung
	} else if (($mesures['AbluftAH'] > $vars['AS_AH']) && ($hum['ASnegOffset'] != 0) && ($mesures['AbluftAH'] <= $mesures['AussenluftAH'])) {   
		$FTRmode = "AS-";
		if (($tmpRHSpeed = $RHSpeed - 2) < $hum['ASnegOffset']) $tmpRHSpeed = $hum['ASnegOffset'];
		if ($tmpRHSpeed < ++$RHSpeed) $RHSpeed = $tmpRHSpeed;

	// maximale Feuchte	- Feuchteabfuhr möglich	--> Erhöhen der Lüfterleistung
	} else if (($mesures['AbluftRH'] > $hum['RHmax']) && ($hum['RHmaxPosOffset'] != 0) && ($mesures['AbluftAH'] > $mesures['AussenluftAH'])) {   
		$FTRmode = "MxF+";
		if (($tmpRHSpeed = $RHSpeed + 2) > $hum['RHmaxPosOffset']) $tmpRHSpeed = $hum['RHmaxPosOffset'];
		if ($tmpRHSpeed > --$RHSpeed) $RHSpeed = $tmpRHSpeed;

	// maximale Feuchte	- Feuchteabfuhr nicht möglich --> Reduktion der Lüfterleistung
	} else if (($mesures['AbluftRH'] > $hum['RHmax']) && ($hum['RHmaxNegOffset'] != 0) && ($mesures['AbluftAH'] <= $mesures['AussenluftAH'])) {   
		$FTRmode = "MxF-";
		if (($tmpRHSpeed = $RHSpeed - 2) < $hum['RHmaxNegOffset']) $tmpRHSpeed = $hum['RHmaxNegOffset'];
		if ($tmpRHSpeed < ++$RHSpeed) $RHSpeed = $tmpRHSpeed;

	// minimale Feuchte	- Feuchtezufuhr möglich --> Erhöhen der Lüfterleistung
	} else if (($mesures['AbluftRH'] < $hum['RHmin']) && ($hum['RHminPosOffset'] != 0) && ($mesures['AbluftAH'] > $mesures['AussenluftAH'])) {   
		$FTRmode = "MnF+";
		if (($tmpRHSpeed = $RHSpeed + 2) > $hum['RHminPosOffset']) $tmpRHSpeed = $hum['RHminPosOffset'];
		if ($tmpRHSpeed > --$RHSpeed) $RHSpeed = $tmpRHSpeed;

	// minimale Feuchte	- Feuchtezufuhr nicht möglich --> Reduktion der Lüfterleistung
	} else if (($mesures['AbluftRH'] < $hum['RHmin']) && ($hum['RHminNegOffset'] != 0) && ($mesures['AbluftAH'] <= $mesures['AussenluftAH'])) {   
		$FTRmode = "MnF-";
		if (($tmpRHSpeed = $RHSpeed - 2) < $hum['RHminNegOffset']) $tmpRHSpeed = $hum['RHminNegOffset'];
		if ($tmpRHSpeed < ++$RHSpeed) $RHSpeed = $tmpRHSpeed;

	// maximale Temperatur - Hitze-Abfuhr möglich --> Erhöhen der Lüfterleistung
	} else if (($mesures['Abluft'] > $hum['tempMax']) && ($hum['tempMaxPosOffset'] != 0) && ($mesures['Abluft'] > $mesures['Aussenluft'])) {   
		$FTRmode = "MaT+";
		if (($tmpRHSpeed = $RHSpeed + 2) > $hum['tempMaxPosOffset']) $tmpRHSpeed = $hum['tempMaxPosOffset'];
		if ($tmpRHSpeed > --$RHSpeed) $RHSpeed = $tmpRHSpeed;

	// maximale Temperatur - Hitze-Abfuhr nicht möglich --> Reduktion der Lüfterleistung
	} else if (($mesures['Abluft'] > $hum['tempMax']) && ($hum['tempMaxNegOffset'] != 0) && ($mesures['Abluft'] < $mesures['Aussenluft'])) {   
		$FTRmode = "MaT-";
		if (($tmpRHSpeed = $RHSpeed - 2) < $hum['tempMaxNegOffset']) $tmpRHSpeed = $hum['tempMaxNegOffset'];
		if ($tmpRHSpeed < ++$RHSpeed) $RHSpeed = $tmpRHSpeed;

	// minimale Temperatur - Hitze-Zufuhr möglich --> Erhöhen der Lüfterleistung
	} else if (($mesures['Abluft'] < $hum['tempMin']) && ($hum['tempMinPosOffset'] != 0) && ($mesures['Abluft'] < $mesures['Aussenluft'])) {   
		$FTRmode = "MnT+";
		if (($tmpRHSpeed = $RHSpeed + 2) > $hum['tempMinPosOffset']) $tmpRHSpeed = $hum['tempMinPosOffset'];
		if ($tmpRHSpeed > --$RHSpeed) $RHSpeed = $tmpRHSpeed;

	// minimale Temperatur - Hitze-Zufuhr nicht möglich --> Reduktion der Lüfterleistung
	} else if (($mesures['Abluft'] < $hum['tempMin']) && ($hum['tempMinNegOffset'] != 0) && ($mesures['Abluft'] > $mesures['Aussenluft'])) {   
		$FTRmode = "MnT-";
		if (($tmpRHSpeed = $RHSpeed - 2) < $hum['tempMinNegOffset']) $tmpRHSpeed = $hum['tempMinNegOffset'];
		if ($tmpRHSpeed < ++$RHSpeed) $RHSpeed = $tmpRHSpeed;

	// Entfeuchtefunktion
	} else if (($hum['enfMaxPosOffset'] != 0) && ($mesures['Abluft'] - $mesures['Aussenluft'] > 5) && ($mesures['Aussenluft'] > $mesures['Fortluft'])) {   
		$FTRmode = "Entf";
		if (($tmpRHSpeed = $RHSpeed + 2) > $hum['enfMaxPosOffset']) $tmpRHSpeed = $hum['enfMaxPosOffset'];
		if ($tmpRHSpeed > --$RHSpeed) $RHSpeed = $tmpRHSpeed;
		mysql_query("UPDATE $tableName_vars SET enfnCount = $hum[enfN] WHERE vars = 1");
	} else if ($vars['enfnCount'] > 0) { 
		$FTRmode = "Enfn";
		if ($vars['enfnCount'] > 0) mysql_query("UPDATE $tableName_vars SET enfnCount = enfnCount -1 WHERE vars = 1"); // ..and do nothing		
	} else {
		if ($RHSpeed > 0) $RHSpeed--;
		if ($RHSpeed < 0) $RHSpeed++;
	}
	echo "FTRmode: '$FTRmode'\n";
	echo "RHSpeed: $RHSpeed\n";
	echo "FS1nCount: $vars[FS1nCount]\n";
	echo "enfnCount: $vars[enfnCount]\n";

	mysql_query("UPDATE $tableName_config SET FTRmode= '$FTRmode' WHERE config = 1");
	mysql_query("UPDATE $tableName_vars SET RHSpeed= '$RHSpeed' WHERE vars = 1");


	$time += 60;
	if ($time - time() > 0) sleep($time -time());
}

fclose($fp); // close lock file
?>
