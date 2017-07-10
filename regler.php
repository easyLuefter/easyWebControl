<?php

echo date("d.m.Y H:i:s", time()) . " var_dump argv:\r\n";
var_dump($argv); echo "\r\n";

$supRegTime = time();
if (isset($argv['1'])) {
	if ($argv['1'] == "d") {
		echo date("d.m.Y H:i:s") . " 60 sec start-up delay\r\n";
		sleep(60);  // start-up delay 60 sec
		$supRegTime = time() + 5*60;	// suppress regler for 5*60 secs
	}
}

$path = pathinfo($argv['0'])['dirname'];
$PHPname = pathinfo($argv['0'])['basename'];

//run only once
if (!file_exists("/tmp/$PHPname.lock")) {
	$old_umask = umask(0);
	file_put_contents("/tmp/$PHPname.lock", "dummy", FILE_APPEND);
	chmod("/tmp/$PHPname.lock",0666);
	umask($old_umask);
}
$fp = fopen("/tmp/$PHPname.lock", 'r+');
if (!flock($fp, LOCK_EX | LOCK_NB)) {
	echo "$PHPname is already running (locked)\n...exiting\n";
    exit;
} else echo "$PHPname not locked\n";

echo date("d.m.Y H:i:s") . " $PHPname started\n";

$log = FALSE;
$echo = TRUE;

include "locals/locals.php";
include "const.php";
include "locals/mySqlConnect.php";

function wrSollCalc($speed) {		// bestimme soll-Wirkungsgrad
	global $echo, $tableName;
	$WrSoll = 0;
	$found = 0;
	
	for ($h=0; $h<=23; $h++) {
		for ($d=0; $d<=10; $d+=10) {
			$from = $speed-$d-3;
			$til = $speed+$d+3;
			$qry = "SELECT (AVG((Zuluft-Aussenluft) / (Abluft-Aussenluft)) + AVG((Abluft-Fortluft) / (Abluft-Aussenluft))) / 2 as WrSoll from $tableName " 
				. "WHERE ABS(Abluft-Aussenluft) > 3 AND ABS(Abluft-Aussenluft) < 40 AND timeStamp > " . (intval(time()/60)-14*24*60) . " AND LuefterleistungSoll >= $from AND LuefterleistungSoll <= $til "
				. "AND hourOfDay = $h";
			//echo "qry: $qry\n";
			$res = mysql_query($qry);
			$mes = mysql_fetch_assoc($res);
			if (($mes['WrSoll'] > 0.75) && ($mes['WrSoll'] < 0.99)) {
				$WrSoll+= $mes['WrSoll'];
				$found++;
				break;
			}
		}
	}
	if ($found > 0) $WrSoll = $WrSoll / $found;
	else $WrSoll = 0.9;
	//if ($echo) echo sprintf("WrSoll: %.3f  (found: %d)\n", $WrSoll, $found);
	return $WrSoll;
}


/*function avgdSpeed($totSpeed) {

	global $tableName;

	$min2 = intval(time() / 60) - 10;
	$min1 = $min2 - 7*24*60;
	
	$avgLuefterleistungAbluft = 0;
	
	for ($d=0; $d<=40; $d+=10) {	
		$tS = $totSpeed + $d;	
		$qry = "SELECT AVG(LuefterleistungAbluft) as avgLuefterleistungAbluft from $tableName WHERE LuefterleistungSoll = $tS AND timeStamp >= $min1 AND timeStamp <= $min2";
		$res = mysql_query($qry);
		$mes = mysql_fetch_assoc($res);
		if (($mes['avgLuefterleistungAbluft'] > 10) && ($mes['avgLuefterleistungAbluft'] < 100)) {
			$avgLuefterleistungAbluft = $mes['avgLuefterleistungAbluft'] - $tS;
			break;
		}
		$tS = $totSpeed - $d;
		$qry = "SELECT AVG(LuefterleistungAbluft) as avgLuefterleistungAbluft from $tableName WHERE LuefterleistungSoll = $tS AND timeStamp >= $min1 AND timeStamp <= $min2";
		$res = mysql_query($qry);
		$mes = mysql_fetch_assoc($res);
		if (($mes['avgLuefterleistungAbluft'] > 10) && ($mes['avgLuefterleistungAbluft'] < 100)) {
			$avgLuefterleistungAbluft = $mes['avgLuefterleistungAbluft'] - $tS;
			break;
		}
	}	
	return -$avgLuefterleistungAbluft;
} */


function avgdSpeed($totSpeed) {

	global $tableName;
	$min2 = intval(time() / 60) - 2;
	$min1 = $min2 - 3*24*60;
	
	$avgdSpeed = 0;
	for ($d=0; $d<=40; $d+=10) {
		$tS1 = $totSpeed - $d-2;	
		$tS2 = $totSpeed + $d+2;	
		//echo "tS: $tS\n";
		$qry = "SELECT AVG(dSpeed) as avgdSpeed from $tableName WHERE dSpeed <> 999 AND LuefterleistungSoll >= $tS1 AND LuefterleistungSoll <= $tS2 AND timeStamp >= $min1 AND timeStamp <= $min2";
		//echo "qry: $qry\n";
		$res = mysql_query($qry);
		$mes = mysql_fetch_assoc($res);
		if (abs($mes['avgdSpeed']) > 0) {
			$avgdSpeed = $mes['avgdSpeed'];
			//$found++;
			break;
		}
	}
	//echo "avgLuefterleistungAbluft = $avgLuefterleistungAbluft \n";
	return $avgdSpeed ;
}


include "versionControl.php";

$res = mysql_query("SELECT * FROM $tableName_vars");
$vars = mysql_fetch_assoc($res);

$res = mysql_query("SELECT * FROM $tableName_config");
$config = mysql_fetch_assoc($res);

if (($vars['Icomponent'] == 0) && ($vars['dSpeed'] == 0)){
	$Icomponent = $config['Icomponent']; 
	if ($Icomponent > 15) $Icomponent = 15;
	if ($Icomponent < -15) $Icomponent = -15;
	$dSpeed		= $Icomponent;
	mysql_query("UPDATE $tableName_vars SET dSpeed = $dSpeed WHERE vars = 1");
} else {
	$Icomponent = $vars['Icomponent'];
	$dSpeed		= $vars['dSpeed'];
}

$Dcomponent = $vars['Dcomponent'];
$totSpeed	= $vars['totSpeed'];

//$timeConst = $config['timeConst'];	
$loopInterval = 5;	// 5 secs

$time = time();
$Ddelta1 = 0;
$State = $Idelta = 0;
$WrSoll = wrSollCalc($totSpeed);
$wrSollCalcTime = time();

$init_t_1 = TRUE;	// init $delta_1

$doWork = TRUE;
while ($doWork == TRUE) {

	if ($echo) echo "\r\n" . date("H:i:s ") . "Regler loop start\r\n";

	$res = mysql_query("SELECT * FROM $tableName_config");
	$config = mysql_fetch_assoc($res);

	$res = mysql_query("SELECT * FROM $tableName_av WHERE actualValue=1");
	$mesures = mysql_fetch_assoc($res);
	
	$res = mysql_query("SELECT * FROM $tableName_vars");
	$vars = mysql_fetch_assoc($res);
  	
	$newSpeed = $vars['totSpeed'];
	if (abs($totSpeed - $newSpeed) > 5) {
		if ($echo) echo "newSpeed: $newSpeed\n";
		$supRegTime = time() + $config['timeConst'] * 2/3;	// suppress regler for 2/3 $timeConst secs if totSpeed varies more than 5%
		$avgdSpeed = avgdSpeed($newSpeed);
		if ($avgdSpeed) $dSpeed = $Icomponent = avgdSpeed($newSpeed);
		echo "dSpeed: $dSpeed\n";
		$Dcomponent = 0;
		$init_t_1 = TRUE;
		mysql_query("UPDATE $tableName_vars SET Dcomponent = $Dcomponent, Icomponent = $Icomponent, dSpeed = $dSpeed WHERE vars = 1");
		$WrSoll= wrSollCalc($newSpeed);
	}

	$totSpeed = $newSpeed;
	if ($echo) echo "totSpeed: $totSpeed\n";

	if ($totSpeed <= 70) $ts = $totSpeed; else $ts = 70;
	if ($ts < 15) $k= 0.4;
	else if ($ts < 25) $k = 0.4;
	//else $k = (0.3 + ($ts-10) * (1-0.3)/(30-10));
	else $k = (1 - 30 * 0.15/40) + $ts * 0.15/40;
	
	echo "k: $k\n";
	$timeFactor = $loopInterval/$config['timeConst'] * $k;	// Basis 30% Leistung
	echo sprintf("timeFactor: %.3f\n",$timeFactor);

	if ($config['sym'] == FALSE) {
		echo date("d.m.Y H:i:s") . " Symmetrie Regelung in Config deaktiviert\n";
		$dSpeed = -$config['uSym'];
		mysql_query("UPDATE $tableName_vars SET dSpeed = $dSpeed WHERE vars = 1");		
	} else if ($supRegTime > time()) {	// suppress Regler
		echo date("d.m.Y H:i:s") . " suppress regler till: " . date("d.m.Y H:i:s", $supRegTime) . "\n";
	} else if (time() - $mesures['timeStamp'] > 60) {
		echo date("d.m.Y H:i:s") . " no actual mesures available --> regler disabled\n";
		$Dcomponent = 0;
		$init_t_1 = TRUE;
	} else {
		$diffTemp = abs($mesures['Abluft'] - $mesures['Aussenluft']);
		if ($diffTemp < 1) {	 			//  if diff-temperature < 0.2° --> no WR-reg
			if ($echo) echo sprintf("diffTemp: %.3f < 1° --> no WR-reg\n", $diffTemp);
			$delta = 0;
			if ($echo) echo sprintf("--> delta forced to 0\n");
			$Dcomponent = 0;
			$init_t_1 = TRUE;
		} else { // diff-temperature >= 1° --> do WR-reg
			if ($echo) echo sprintf("diffTemp: %.3f >= 1° --> does WR-reg\n", $diffTemp);
			
			// berechne ist-Wirkungsgrad
			$WrIst = 		($mesures['Zuluft'] - $mesures['Aussenluft']) / ($mesures['Abluft'] - $mesures['Aussenluft']);
			if      ($WrIst > 1)   $WrIst = 1;	// limit WrIst
			else if ($WrIst < 0.5) $WrIst = 0.5;
			if ($echo) echo sprintf("WrIst:  %.3f\r\n",$WrIst);

			if ($echo) echo sprintf("WrSoll: %.3f", $WrSoll);

			// delta
			//$delta = $deltaL = $WrIst - $WrSoll;
			$delta = $deltaL = ($WrIst - $WrSoll) * abs($mesures['Abluft'] - $mesures['Aussenluft'])/10;
			if      ($deltaL >  0.03) $deltaL =  0.03;	// limitiere deltaL auf 3%
			else if ($deltaL < -0.03) $deltaL = -0.03;
			if ($echo) echo sprintf(" --> delta: %f  deltaL: %f\n", $delta, $deltaL);			
		}

			
		// Proportional component
		$dSpeed = $config['Pvalue'] * $deltaL * 24000 * $timeFactor;
		if ($echo) echo sprintf("Pcomponent: %8.4f\r\n",$dSpeed);

		// Differential component
		if ($init_t_1) {$delta_1 = $delta; $init_t_1 = FALSE;} // setzen von $delta_1 beim ersten Schlaufendurchgang
		$Ddelta = $delta - $delta_1;
		//echo sprintf("Ddelta: %.5f\n", $Ddelta);
		$delta_1 = $delta;	// setzen von $delta_1 für den nächsten Schlaufendurchgang
		//$Ddelta1 = $Ddelta * $config['Dvalue'] * 72000 * $timeFactor; 	
		$Ddelta1 = $Ddelta * $config['Dvalue'] * 120000 * $timeFactor; 	
		//$Ddelta2 = 								 -8 * $Dcomponent * $timeFactor; 	// Wirkung über ca. $timeConst (sec)
		$Ddelta2 = 								-12 * $Dcomponent * $timeFactor; 	// Wirkung über ca. timeConst (sec)
		$Dcomponent+= $Ddelta1 + $Ddelta2;
		if ($Dcomponent > 20) $Dcomponent = 20;		// limit Dcomponent
		if ($Dcomponent < -20) $Dcomponent = -20;
		if ($echo) echo sprintf("Dcomponent: %8.4f (%9f, %9f)\r\n",$Dcomponent, $Ddelta1, $Ddelta2);
		$dSpeed+= $Dcomponent;

		// DI-component
		$Case = ""; $Idelta = 0;
		if ($Ddelta * $delta > 0) {	// delta und Dcomponent zeigen in gleiche Richtung
			$Case = sprintf("Beschleunigung");
		} else if ($Ddelta * $delta < 0) {
			$Case = sprintf("Verzögerung");
		}	
		//$Idelta1 = $Ddelta * $config['DIvalue'] * 32000 * $timeFactor;
		$Idelta1 = $Ddelta * $config['DIvalue'] * 50000 * $timeFactor;
			
		// Integral Component
		$Case = sprintf("%s + mässige Beschleunigung", $Case);
		//$Idelta2 = $deltaL  * $config['Ivalue'] * 400 * $timeFactor;
		$Idelta2 = $deltaL  * $config['Ivalue'] * 600 * $timeFactor;
		
		$Icomponent += $Idelta1 + $Idelta2;	
		if ($Icomponent > 40) $Icomponent = 40;		// limit Icomponent
		if ($Icomponent < -40) $Icomponent = -40;
		if ($Icomponent >  10  + 3 * $diffTemp) $Icomponent =  10 + 3 * $diffTemp;
		if ($Icomponent <  -10 - 3 * $diffTemp) $Icomponent = -10 - 3 * $diffTemp;
		if ($echo) echo sprintf("Icomponent: %8.4f (%9f, %9f) %s\n", $Icomponent, $Idelta1, $Idelta2, $Case);
		$dSpeed+= $Icomponent;
							
		//limit delta Speed
		if ($dSpeed > 40) $dSpeed = 40; 
		if ($dSpeed < -40) $dSpeed = -40;
		if ($dSpeed >  10  + 3 * $diffTemp) $dSpeed =  10 + 3 * $diffTemp;
		if ($dSpeed <  -10 - 3 * $diffTemp) $dSpeed = -10 - 3 * $diffTemp;
		
		if (abs($config['Icomponent'] - $Icomponent) > 3) mysql_query("UPDATE $tableName_config SET Icomponent = $Icomponent WHERE config = 1");
		
	}

	mysql_query("UPDATE $tableName_vars SET Dcomponent = $Dcomponent, Icomponent = $Icomponent, dSpeed = $dSpeed WHERE vars = 1");
	if ($echo) echo sprintf("dSpeed:     %8.4f\r\n", $dSpeed);

	shell_exec("/usr/bin/php $path/chkTimers.php &");
	echo shell_exec("/usr/bin/php $path/updateSpeed.php &");
	
	// bestimme soll-Wirkungsgrad
	if ($wrSollCalcTime <= time()) {
		$wrSollCalcTime = time() + 10*60; // interval 10 Min
		$WrSoll = wrSollCalc($totSpeed);
		echo sprintf("%s wrSollCalc WrSoll: %.3f\n   --> next wrSollCalcTime: %s\n", date("d.m.Y H:i:s"), $WrSoll, date("d.m.Y H:i:s", $wrSollCalcTime));
	}

	$time += $loopInterval;
	if ($time - time() > 0) {
		if ($echo) echo "\nsleeping " . ($time - time()) . " secs\n";
		sleep($time -time());
	}
}

fclose($fp); // close lock file
?>
