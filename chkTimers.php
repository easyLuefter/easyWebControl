<?php

//echo date("d.m.Y H:i:s", time()) . " var_dump argv:\r\n";
//var_dump($argv); echo "\r\n";

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
} else //echo "$PHPname not locked\n";

echo date("\nd.m.Y H:i:s") . " $PHPname started\n";

include "locals/locals.php";
include "const.php";
include "locals/mySqlConnect.php";

$res = mysql_query("SELECT * FROM $tableName_vars");
$vars = mysql_fetch_assoc($res);

$res = mysql_query("SELECT * FROM $tableName_config");
$config = mysql_fetch_assoc($res);



/*
  struct tm time;
  int16_t i,k;
  int16_t dMin;
  int16_t diffTime;
  int16_t deltaT; */

$diffTime = 60*24*7;
$time = time();

//$dMin = 60*24 * time.tm_wday + 60 * time.tm_hour + time.tm_min + 60*24*7;
$dMin = 60*24 * date("w") + 60 * date("G") + date("i") + 60*24*7;
//echo "date(w): " . date("w") . "  date(G): " . date("G") . "  date(i): " . date("i") . "\r\n"; 
//$diffTime = 60*24*7;
//TimNr = 0;
//echo "dMin: $dMin\r\n";
//echo "diffTime: $diffTime\r\n";

for ($k=0; $k<7;$k++) {

	$res = mysql_query("SELECT * FROM $tableName_tmr");
	$num = mysql_num_rows($res);
	if ($num > 0) {
		while ($tmr = mysql_fetch_assoc($res)) {
			if ($tmr['einAus'] == 1) { 
				//echo sprintf("k: %X\r\n",$k );
				//echo "tmr['DoW']: $tmr[DoW]\r\n";
				//echo sprintf("DoWMap[$tmr[DoW]]: %X\r\n", $DoWMap[$tmr['DoW']]);
				//echo sprintf("1<<k: %X\r\n",(1<<$k));
				if ((1<<$k) & $DoWMap[$tmr['DoW']]) {
					//echo "OK\r\n";
					$deltaT = ($dMin - ($tmr['hour'] *60 + $tmr['min'] + $k*60*24)) % (60*24*7);
					//echo sprintf("deltaT: %d\r\n", $deltaT);
					if ($deltaT <= $diffTime ) {
						$diffTime = $deltaT;
						//echo "diffTime: $diffTime\r\n";
						$tmrSpeed = $tmr['Soll'];
						$tmrNr = $tmr['tmrNr'];
					}
				}
			}  
		}
	}
}

if (isset($tmrNr)) {
	echo sprintf("tmrNr: %d tmrSpeed: %d\n", $tmrNr, $tmrSpeed );
	mysql_query("UPDATE $tableName_config SET tmrNr = $tmrNr, tmrSpeed = $tmrSpeed WHERE config = 1");
} else { 	//all Timer auf Aus
	echo "alle Timer auf Aus\r\n";
	if ($config['Mode'] == "TIM") {
		//ZuluftAbluft[actualMODE] = ZuluftAbluft[lastMODE];
		//do_eeStore = TRUE;
		mysql_query("UPDATE $tableName_config SET Mode = '$config[lastMode]' WHERE config = 1");		
	}
}

fclose($fp); // close lock file
?>