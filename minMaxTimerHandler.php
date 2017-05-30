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
} else echo "$PHPname not locked\n";

include "locals/locals.php";
include "const.php";
include "locals/mySqlConnect.php";

$interval = 60; //60 sec
//$time = intval(time()/$interval) * $interval;	// synch auf Minutenwechsel
$time = time();  // start ab mode set

$res = mysql_query("SELECT * FROM $tableName_vars");
$vars = mysql_fetch_assoc($res);

while ($vars['minMaxTimer'] > 0) {
	$time += $interval;
	if ($time - time() > 0) sleep($time -time());
	$res = mysql_query("SELECT * FROM $tableName_vars");
	$vars = mysql_fetch_assoc($res);
	$vars['minMaxTimer']--;
	echo "minMaxTimer: $vars[minMaxTimer]\r\n";
	mysql_query("UPDATE $tableName_vars SET minMaxTimer= $vars[minMaxTimer] WHERE vars = 1");			
}

$res = mysql_query("SELECT * FROM $tableName_config");
$config = mysql_fetch_assoc($res);

if (($config['Mode'] == "MIN") || ($config['Mode'] == "MAX")) {
	mysql_query("UPDATE $tableName_config SET Mode='$config[lastMode]' WHERE config = 1");			
	//exec("php chkTimers.php");
	exec("php updateSpeed.php");
}

fclose($fp);
?>
