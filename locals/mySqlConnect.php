<?php

$con = mysql_connect("localhost","root","pw");
if (!$con) {
	echo date("d.m.Y H:i:s") . "Could not connect: " . mysql_error() . "\n";
}
mysql_select_db("easyLuefter", $con);

?>
