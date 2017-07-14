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

	sleep(6);
	echo shell_exec("sudo pigpiod");
	//echo shell_exec("sudo pigs hp 12 23000 200000");
	//echo shell_exec("sudo pigs hp 13 23000 200000");
	sleep(30);
	echo shell_exec("php $path/../getHumTmp.php >/dev/null 2>&1 &");
	sleep(10);
	//echo shell_exec("$path/../dht22/easydht 0 >/dev/null 2>&1 &");
	echo shell_exec("php $path/../regler.php d >/dev/null 2>&1 &");
	echo shell_exec("php $path/../humTemp.php d >/dev/null 2>&1 &");
	sleep(10);
	echo shell_exec("sudo cp $path/../dht22/easydht_new $path/../dht22/easydht");
	echo shell_exec("sudo $path/../dht22/easydht 0 >/dev/null 2>&1 &");
	
?>
