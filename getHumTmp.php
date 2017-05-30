<?php

echo date("d.m.Y H:i:s", time()) . " var_dump argv:\r\n";
var_dump($argv); echo "\r\n";

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

sleep(1);
if (isset($argv['1'])) {
	if ($argv['1'] == "d") {
		echo date("d.m.Y H:i:s") . " 30 sec start-up delay\r\n";
		sleep(30);  // start-up delay 30 sec
	}
}

include "locals/locals.php";
include "const.php";
include "locals/mySqlConnect.php";
include "makeChartIncl.php";
include "locals/localInclude.php";
$dht22List = array(24,25,27,28);

//**************************
//* include functions
//**************************
include "easyLib.php";


//*************************
//* create and init tables
//*************************
createAndInitTables();


//*************************
//* update tables
//*************************
include "versionControl.php";


//**************************
//* main
//*************************

echo date("d.m.Y H:i:s") . " $PHPname started\n";

// prepare tmp/easyWebCharts directory and easyWebChart.jpg file with read/write access to all
$old_umask = umask(0);
if (!file_exists("easyWebCharts")) mkdir("easyWebCharts", 0777);
if (!file_exists("easyWebCharts/archiv")) mkdir("easyWebCharts/archiv", 0777);
if (!file_exists("/tmp/easyWebCharts")) mkdir("/tmp/easyWebCharts", 0777);
file_put_contents ("/tmp/easyWebCharts/easyWebChart.jpg", "dummy");
chmod("/tmp/easyWebCharts/easyWebChart.jpg",0666);
if (!file_exists("easyWebCharts/easyWebChart.jpg")) shell_exec("sudo ln -s /tmp/easyWebCharts/easyWebChart.jpg easyWebCharts/easyWebChart.jpg");
umask($old_umask);

// init archive-timer
$copyDailyChartTime = intval(DateTime::createFromFormat("Y.m.j H:i", date("Y.m.d ") . "20:00")->getTimestamp());
if ($copyDailyChartTime <= time()) $copyDailyChartTime = strtotime("+1 day", $copyDailyChartTime);
echo "next copyDailyChartTime: " . date("Y.m.d H:i:s", $copyDailyChartTime) . "\n";

$fAbfuhr = $fRueckg = $fKond = $fRueckgg = 0;
$wtime = time()+3;
$min = 0;
$cpToServerTime = time() + 60;
$doWork = TRUE;

while ($doWork == TRUE) {

	$time = time();
	$dateTime = date("Ymd_Hi",$time);
	$hourOfDay = date("H",$time);

	$reply = shell_exec("sudo $path/dht22/easydht");
	$valueTable = explode("\n", trim($reply,"\n"));
	echo "valueTable: "; print_r($valueTable); echo "\n";	

	if (count($valueTable) == 8) {
	
		$valuesOK = TRUE;
	    for($i = 0; $i < 8; $i++) {
			$ValueEntry = explode(' ', $valueTable[$i]);
			if (isset($$ValueEntry[0])) {
				//echo sprintf("%.2f", $$ValueEntry[0]) . " " . $ValueEntry[1] . "\n";
				if (abs($$ValueEntry[0] - $ValueEntry[1]) > 0.5 + 2*($i%2)) $valuesOK = FALSE;
			}
			$$ValueEntry[0] = $ValueEntry[1];
		}
		if ($valuesOK) {			
			echo("INSERT INTO $tableName_lm VALUES($time, $Abluft, $Zuluft, $Fortluft, $Aussenluft, $AbluftRH, $ZuluftRH, $FortluftRH, $AussenluftRH)") . "\n";
			mysql_query("INSERT INTO $tableName_lm VALUES($time, $Abluft, $Zuluft, $Fortluft, $Aussenluft, $AbluftRH, $ZuluftRH, $FortluftRH, $AussenluftRH)");
		}
	}
			
	mysql_query("DELETE FROM $tableName_lm WHERE timeStamp <= " . ($time-60));
	mysql_query("DELETE FROM $tableName_lm WHERE timeStamp > " . ($time));
	
	
	$res = mysql_query("SELECT * FROM $tableName_lm ORDER BY timeStamp");
	$num = mysql_num_rows($res);
	echo "num: $num\r\n";
	if ($num > 0) {
		$timeStamp0 = 0; $k = 0; for ($i=0; $i<8; $i++) $$sensName[$i] = 0; // init variables
		while ($dsatz = mysql_fetch_assoc($res)) {
			if ($timeStamp0 == 0) $timeStamp0 = $dsatz['timeStamp'];
			$dtime = $dsatz['timeStamp'] - $timeStamp0 +1;
			for ($i=0; $i<8; $i++) $$sensName[$i]  += $dsatz[$sensName[$i]] * $dtime * $dtime;	// weight mesures according time
			$k += $dtime * $dtime;
		}
		
		$res = mysql_query("SELECT * FROM $tableName_config");
		$config = mysql_fetch_assoc($res);
		for ($i=0; $i<8; $i++) {
			$$sensName[$i] = $$sensName[$i] /$k;
			$sensName_C    = $sensName[$i] . "_C";
			$$sensName_C   = $$sensName[$i] + $config[$calName[$i]];
			if ($i >=4) if ($$sensName_C > 99.9) $$sensName_C = 99.9;		// limit RH value
		}
	
		//for ($i=0; $i<8; $i++) echo sprintf("%s %2.2f\n", substr($sensName[$i] . ":            ",0,19), round($$sensName[$i],2));
		
		//calculate efficiency
		$t0= $Abluft_C - $Aussenluft_C;
		$t1= $Zuluft_C - $Aussenluft_C;
		$t2= $Abluft_C - $Fortluft_C;

		if ($t0 != 0) {

			$WirkungsgradZuluft = 100 * $t1 /$t0;
			if ($WirkungsgradZuluft > 99.9) $WirkungsgradZuluft = 99.9;
			else if ($WirkungsgradZuluft < 0)  $WirkungsgradZuluft = 0; 
			//echo sprintf("%s %2.2f\n", substr("WirkungsgradZuluft:            ",0,19), round($WirkungsgradZuluft,2));

			$WirkungsgradAbluft = 100 * $t2 /$t0;
			if ($WirkungsgradAbluft > 99.9) $WirkungsgradAbluft = 99.9;
			else if ($WirkungsgradAbluft < 0) $WirkungsgradAbluft = 0;
			//echo sprintf("%s %2.2f\n", substr("WirkungsgradAbluft:            ",0,19), round($WirkungsgradAbluft,2));
			
			mysql_query("UPDATE $tableName_vars SET WirkungsgradZuluft = $WirkungsgradZuluft, WirkungsgradAbluft = $WirkungsgradAbluft WHERE vars = 1");
			//echo "mysql_affected_rows(): " . mysql_affected_rows() . "\r\n";
		}
			

		// update actual value
		mysql_query("UPDATE $tableName_av SET timeStamp = " . time() . ", hourOfDay = $hourOfDay, dateTime = '$dateTime', Abluft = $Abluft_C, Zuluft = $Zuluft_C, Fortluft = $Fortluft_C, Aussenluft = $Aussenluft_C, " . 
					"AbluftRH = $AbluftRH_C, ZuluftRH = $ZuluftRH_C, FortluftRH = $FortluftRH_C, AussenluftRH = $AussenluftRH_C, Wirkungsgrad = $WirkungsgradZuluft " . 
		            "WHERE actualValue = 1");
		echo "mysql_affected_rows(): " . mysql_affected_rows() . "\r\n";
		
		//if (mysql_affected_rows() != 1)		// create record if it does not exist
			//echo ("INSERT INTO $tableName_av (actualValue, dateTime, Abluft, Zuluft, Fortluft, Aussenluft, AbluftRH, ZuluftRH, FortluftRH, AussenluftRH, Wirkungsgrad) " .
		    //        "VALUES(1, '$dateTime', $Abluft_C, $Zuluft_C, $Fortluft_C, $Aussenluft_C, $AbluftRH_C, $ZuluftRH_C, $FortluftRH_C, $AussenluftRH_C, $WirkungsgradZuluft)") . "\n";
			//mysql_query("INSERT INTO $tableName_av (actualValue, dateTime, Abluft, Zuluft, Fortluft, Aussenluft, AbluftRH, ZuluftRH, FortluftRH, AussenluftRH, Wirkungsgrad) " .
		    //        "VALUES(1, '$dateTime', $Abluft_C, $Zuluft_C, $Fortluft_C, $Aussenluft_C, $AbluftRH_C, $ZuluftRH_C, $FortluftRH_C, $AussenluftRH_C, $WirkungsgradZuluft)");
		
		
		//echo "min: $min\r\n";
		if ($min != intval($time/60)) {
			$min = intval($time/60);
			
			//$res = mysql_query("SELECT * FROM $tableName_config");
			//$config = mysql_fetch_assoc($res);
	
			$AbluftAH     = calcAH($Abluft_C,     $AbluftRH_C);
			$ZuluftAH     = calcAH($Zuluft_C,     $ZuluftRH_C);
			$FortluftAH   = calcAH($Fortluft_C,   $FortluftRH_C);
			$AussenluftAH = calcAH($Aussenluft_C, $AussenluftRH_C);
			$AS_AH 		  = calcAS_AH($Aussenluft_C, $Abluft_C);
			//echo sprintf("%s %2.2f\n", substr("AS_AH:              ",0,19), round($AS_AH,2));
			
													  
					
			// f-Calculations
	
			$Feuchtigkeitsabfuhr = $AbluftAH - $ZuluftAH;
			$Feuchterueckgewinnung = $ZuluftAH - $AussenluftAH;
			$Kondensierend = $AbluftAH - $FortluftAH - ($ZuluftAH - $AussenluftAH);
					 
			if (($AbluftAH - $AussenluftAH) != 0) {
				$Feuchterueckgewinnungsgrad = 100*($ZuluftAH - $AussenluftAH) / ($AbluftAH  -  $AussenluftAH);
				if ($Feuchterueckgewinnungsgrad > 99.9) $Feuchterueckgewinnungsgrad = 99.9;
				else if ($Feuchterueckgewinnungsgrad < -99.9) $Feuchterueckgewinnungsgrad = -99.9;
			}
	
			mysql_query("UPDATE $tableName_vars SET Feuchtigkeitsabfuhr = $Feuchtigkeitsabfuhr, Feuchterueckgewinnung = $Feuchterueckgewinnung, Kondensierend = $Kondensierend, Feuchterueckgewinnungsgrad = $Feuchterueckgewinnungsgrad " .
			            "WHERE vars = 1");
			//echo "mysql_affected_rows(): " . mysql_affected_rows() . "\r\n";
	
			$res = mysql_query("SELECT * FROM $tableName_vars");
			$vars = mysql_fetch_assoc($res);		
			
			// add every minute record with actual timeStamp to table
			mysql_query("INSERT INTO $tableName (timeStamp, hourOfDay , dateTime   , Abluft   ,  Zuluft  ,  Fortluft  ,  Aussenluft  ,  AbluftRH  ,  ZuluftRH  ,  FortluftRH  ,  AussenluftRH  ,  Wirkungsgrad) " .
		                                 "VALUES($min     , $hourOfDay, '$dateTime', $Abluft_C, $Zuluft_C, $Fortluft_C, $Aussenluft_C, $AbluftRH_C, $ZuluftRH_C, $FortluftRH_C, $AussenluftRH_C, $WirkungsgradZuluft)");
			//echo "mysql_affected_rows(): " . mysql_affected_rows() . "\r\n";
			            
			mysql_query("UPDATE $tableName SET AbluftAH = $AbluftAH, ZuluftAH = $ZuluftAH, FortluftAH = $FortluftAH, AussenluftAH = $AussenluftAH, AS_AH = $AS_AH, " . 
						"Kondensierend = $Kondensierend, Feuchtigkeitsabfuhr = $Feuchtigkeitsabfuhr, " .
						"LuefterleistungSoll = $vars[totSpeed], dSpeed = $vars[dSpeed], LuefterleistungAbluft = $vars[LuefterleistungAbluft], LuefterleistungZuluft = $vars[LuefterleistungZuluft] " .
			            "WHERE timeStamp = $min");
			mysql_query("UPDATE $tableName_av SET AbluftAH = $AbluftAH, ZuluftAH = $ZuluftAH, FortluftAH = $FortluftAH, AussenluftAH = $AussenluftAH, AS_AH = $AS_AH, " . 
						"Kondensierend = $Kondensierend, Feuchtigkeitsabfuhr = $Feuchtigkeitsabfuhr, " .
						"LuefterleistungSoll = $vars[totSpeed], LuefterleistungAbluft = $vars[LuefterleistungAbluft], LuefterleistungZuluft = $vars[LuefterleistungZuluft] " .
			            "WHERE actualValue = 1");
			//echo "mysql_affected_rows(): " . mysql_affected_rows() . "\r\n";
	
			// archive processing
			echo "next copyDailyChartTime: " . date("Y.m.d H:i", $copyDailyChartTime) . "\n";
			if ($copyDailyChartTime <= time()) {
				$copyDailyChartTime = strtotime("+1 day", $copyDailyChartTime);
				$rslt = mysql_query("SELECT * FROM $tableName_chartOpt WHERE chartOpt = 1");
				$chartOpt = mysql_fetch_assoc($rslt);	
				makeChart("easyLüfter", "$path/easyWebCharts/archiv", 4, $chartOpt['YScale'], $chartOpt['tempOffset'], date("d.m.Y 20:00"), date("_Ymd_2000") ); 		
			}

			//copy Chart to server (optional)
			if (function_exists("copyChartToServer")) {			
				if ($cpToServerTime <= time()) {
					$cpToServerTime = time() + 10*60;
					$rslt = mysql_query("SELECT * FROM $tableName_chartOpt WHERE chartOpt = 1");
					$chartOpt = mysql_fetch_assoc($rslt);	
					makeChart("easyüfter", "/tmp/easyWebCharts", $chartOpt['XScale'], $chartOpt['YScale'], $chartOpt['tempOffset'], "actualMinute"); 		
					copyChartToServer();
				}
			}

		}

	}

	if (($sleepTime = $wtime - time()) > 1) {
		if ($sleepTime < 6) $sleepTime = 6;
		echo "sleeping " . $sleepTime . " sec\r\n";
		sleep($sleepTime);
	} else {
		echo "minimum sleep: 6 sec\r\n";
		sleep(6);
		$wtime = time();
	}
	$wtime += 8; // 8 secs

}
		
fclose($fp); // close lock file
?>
