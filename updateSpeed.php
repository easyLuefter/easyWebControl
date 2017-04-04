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

$res = mysql_query("SELECT * FROM $tableName_fanControl");
$fanControl = mysql_fetch_assoc($res);

//get setSpeed according to actual Mode
if ($config['Mode'] == "MIN") {
	$setSpeed = 10;
} else if ($config['Mode'] == "MAX") {
	$setSpeed = 100;
} else if ($config['Mode'] == "MAN") {
	$setSpeed = $config['manSoll'];
} else if ($config['Mode'] == "TIM") {  	
	$setSpeed = $config['tmrSpeed'];
}

$totSpeed = $setSpeed + $vars['RHSpeed'];
if ($totSpeed > 100) $totSpeed= 100;
if ($totSpeed < 10)  $totSpeed=  10;

$LuefterleistungAbluft = $totSpeed - $vars['dSpeed'];	
$LuefterleistungZuluft = $totSpeed + $vars['dSpeed'];	


//if ($LuefterleistungZuluft < 10)  $LuefterleistungAbluft += (10-$LuefterleistungZuluft);
//if ($LuefterleistungAbluft < 10)  $LuefterleistungZuluft += (10-$LuefterleistungAbluft);
//if ($LuefterleistungZuluft > 100) $LuefterleistungAbluft -= ($LuefterleistungZuluft-100);
//if ($LuefterleistungAbluft > 100) $LuefterleistungZuluft -= ($LuefterleistungAbluft-100);
		
//if ($LuefterleistungZuluft < $config['minLLZuluft']) $LuefterleistungZuluft = $config['minLLZuluft'];
//if ($LuefterleistungZuluft > 100) $LuefterleistungZuluft = 100;
//if ($LuefterleistungAbluft < $config['minLLAbluft'])  $LuefterleistungAbluft = $config['minLLAbluft'];
//if ($LuefterleistungAbluft > 100) $LuefterleistungAbluft = 100;

if ($LuefterleistungZuluft < $config['minLLZuluft']) $LuefterleistungAbluft += ($config['minLLZuluft'] - $LuefterleistungZuluft);
if ($LuefterleistungAbluft < $config['minLLAbluft']) $LuefterleistungZuluft += ($config['minLLAbluft'] - $LuefterleistungAbluft);
if ($LuefterleistungZuluft > $config['maxLLZuluft']) $LuefterleistungAbluft -= ($LuefterleistungZuluft - $config['maxLLZuluft']);
if ($LuefterleistungAbluft > $config['maxLLAbluft']) $LuefterleistungZuluft -= ($LuefterleistungAbluft - $config['maxLLAbluft']);
		
if ($LuefterleistungZuluft < $config['minLLZuluft']) $LuefterleistungZuluft = $config['minLLZuluft'];
if ($LuefterleistungZuluft > $config['maxLLZuluft']) $LuefterleistungZuluft = $config['maxLLZuluft'];
if ($LuefterleistungAbluft < $config['minLLAbluft'])  $LuefterleistungAbluft = $config['minLLAbluft'];
if ($LuefterleistungAbluft > $config['maxLLAbluft']) $LuefterleistungAbluft = $config['maxLLAbluft'];


for ($n = 1; $n <=3; $n+=2) {
	$fromSpeed = "abluftSpeed" . $n;
	$tillSpeed = "abluftSpeed" . ($n+1);
	if ($fanControl[$fromSpeed] != 0) {
		if      (($LuefterleistungAbluft >= $fanControl[$fromSpeed]) && ($LuefterleistungAbluft <= ($fanControl[$fromSpeed] + $fanControl[$tillSpeed])/2)) $LuefterleistungAbluft = $fanControl[$fromSpeed] -0.1;
		else if (($LuefterleistungAbluft >= $fanControl[$fromSpeed]) && ($LuefterleistungAbluft <= $fanControl[$tillSpeed]))                               $LuefterleistungAbluft = $fanControl[$tillSpeed] +0.1;
	}
	$fromSpeed = "zuluftSpeed" . $n;
	$tillSpeed = "zuluftSpeed" . ($n+1);
	if ($fanControl[$fromSpeed] != 0) {
		if      (($LuefterleistungZuluft >= $fanControl[$fromSpeed]) && ($LuefterleistungZuluft <= ($fanControl[$fromSpeed] + $fanControl[$tillSpeed])/2)) $LuefterleistungZuluft = $fanControl[$fromSpeed] -0.1;
		else if (($LuefterleistungZuluft >= $fanControl[$fromSpeed]) && ($LuefterleistungZuluft <= $fanControl[$tillSpeed]))                               $LuefterleistungZuluft = $fanControl[$tillSpeed] +0.1;
	}
}


$LLA = intval($LuefterleistungAbluft * 10000);
$LLZ = intval($LuefterleistungZuluft * 10000);

echo "pigs hp 12 23000 $LLA (Abluft)\n";		
echo "pigs hp 13 23000 $LLZ (Zuluft)\n";
$result = shell_exec("pigs hp 12 23000 $LLA");
$result = shell_exec("pigs hp 13 23000 $LLZ");		

mysql_query("UPDATE $tableName_vars SET setSpeed = $setSpeed, totSpeed = $totSpeed, LuefterleistungAbluft = $LuefterleistungAbluft, LuefterleistungZuluft = $LuefterleistungZuluft WHERE vars = 1");
mysql_query("UPDATE $tableName_av SET LuefterleistungSoll = $totSpeed, LuefterleistungAbluft = $LuefterleistungAbluft, LuefterleistungZuluft = $LuefterleistungZuluft WHERE actualValue = 1");

fclose($fp); // close lock file
?>