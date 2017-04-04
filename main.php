<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<script language="JavaScript">
	void(setInterval("window.location = window.location.href",30000));
</script><title>easyLüfter WebControl</title>

<style type="text/css">;
h3 {
  font-size:14pt;
  font-family:arial,helvetica;
}
body {
  background-color: #fffeec;
  padding-left:10px;
  color:black;
  font-size:12pt;
  font-family:arial,helvetica;
}
.auto-style1 {
	font-family: Arial, Helvetica, sans-serif;
}
</style>
</head>
<body>

<?php
$debug = FALSE;
if ($debug) {echo 'POST: '; print_r($_POST); echo '<br />';}
if ($debug) {echo 'GET: '; print_r($_GET); echo'<br />';}

include "locals/locals.php";
include "const.php";
include "locals/mySqlConnect.php";



if ((count($_POST) > 0) || (count($_GET) > 0)) {

	$qry = "";
	foreach($_POST as $key => $value) 	{
		$qry .= $key . "='" .  $_POST[$key] . "',"; 
	}
	foreach($_GET as $key => $value) 	{
		$qry .= $key . "='" .  $value . "',"; 
	}
	$qry = trim($qry,",");
	if ($debug) echo 'qry: ' . $qry . '<br />';
	
	mysql_query("UPDATE $tableName_config SET " . $qry . " WHERE config = 1");

	// reset FTRmode
	mysql_query("UPDATE $tableName_config SET FTRmode= '    ' WHERE config = 1");
	mysql_query("UPDATE $tableName_vars SET FS1nCount = 0, enfnCount = 0, RHSpeed = 0 WHERE vars = 1"); // ..and do nothing
			
	$res = mysql_query("SELECT * FROM $tableName_config");
	$config = mysql_fetch_assoc($res);
  	if ($config['Mode'] == "MIN") {
  		mysql_query("UPDATE $tableName_vars SET minMaxTimer = " . $TimerIntervalValue[$config['minTimerInterval']] . " WHERE vars = 1");
  		exec("php minMaxTimerHandler.php >/dev/null &");
  	} else if ($config['Mode'] == "MAX") {
  		mysql_query("UPDATE $tableName_vars SET minMaxTimer = " . $TimerIntervalValue[$config['maxTimerInterval']] . " WHERE vars = 1");
  		exec("php minMaxTimerHandler.php >/dev/null &");
		$res = mysql_query("SELECT * FROM $tableName_config");
	} else if ($config['Mode'] == "MAN") {
  		mysql_query("UPDATE $tableName_config SET setSpeed = $config[manSoll] WHERE config = 1");
 	} else if ($config['Mode'] == "TIM") {  	
  		mysql_query("UPDATE $tableName_tmr SET Soll = $config[tmrSpeed] WHERE tmrNr = $config[tmrNr]");
  		mysql_query("UPDATE $tableName_config SET setSpeed = $config[tmrSpeed] WHERE config = 1");
  	}

	echo shell_exec("php updateSpeed.php >/dev/null");
}



/*
if (count($_POST) > 0) {
	foreach($_POST as $key => $value) {
		if 		($key == 'conf') {$tableName = $tableName_config; $line = "config";}
		else if ($key == 'vars') {$tableName = $tableName_vars;   $line = "vars";}
		else mysql_query("UPDATE $tableName SET $key='$value' WHERE $line = 1");
	} 
}
*/


	
$res = mysql_query("SELECT * FROM $tableName_config");
$config = mysql_fetch_assoc($res);

$res = mysql_query("SELECT * FROM $tableName_vars");
$vars = mysql_fetch_assoc($res);

	

echo '<h3>easyLüfter Web Control</h3>
	<p>
		' . $WeekDays[date("w")] . '&nbsp;&nbsp;&nbsp;' . date("d.m.Y H:i") . '<br />
		Lüfterleistung: ' . $vars['totSpeed'] . '%<br />
		Mode: ' . $config['Mode'] . '&nbsp;<span style="color:#FF0000">' . $config['FTRmode'] . '</span>
	</p>';

	// MIN
	echo '
	<table>
	<tr>
		<td style="width: 170px height: 60px">
			<form action="main.php" method="post">
				<button type="submit" name="Mode" value="MIN" style="width: 130px; height: 35px; font-size: 120%;">MIN</button>
			</form>
		</td>';
	if ($config["Mode"] == "MIN") {
		echo '
		<td>
			<form action="main.php" method="post">
				<select name="minTimerInterval" size="1" style="width: 100px;" onChange="this.form.submit()">';
		for ($i = 0; $i < count($TimerIntervals); $i++) {
		  	echo '
			  		<option value="' . $i . '"' . ($i == $config["minTimerInterval"] ? ' selected>' : '>') . $TimerIntervals[$i] . '</option>';
		}
		echo '
				</select>
			</form></td> 
		<td>&nbsp;&nbsp;' . intval($vars['minMaxTimer']/60) . ' Std ' . $vars['minMaxTimer']%60 . ' Min</td>';
	}
	// MAX
	echo '
	</tr><tr>
		<td>
			<form action="main.php" method="post">
				<button type="submit" name="Mode" value="MAX" style="width: 130px; height: 35px; font-size: 120%;">MAX</button>
			</form>
		</td>';
	if ($config["Mode"] == "MAX") {
		echo '
		<td>
			<form action="main.php" method="post">
				<select name="maxTimerInterval" size="1" style="width: 100px;" onChange="this.form.submit()">';
		for ($i = 0; $i < count($TimerIntervals); $i++) {
		  	echo '
			  		<option value="' . $i . '"' . ($i == $config["maxTimerInterval"] ? ' selected>' : '>') . $TimerIntervals[$i] . '</option>';
		}
		echo '
				</select>
			</form>
			</td> 
			<td>&nbsp;&nbsp;' . intval($vars['minMaxTimer']/60) . ' Std ' . $vars['minMaxTimer']%60 . ' Min</td>';
	} 
	// MAN
	echo '
	</tr><tr>
		<td>
			<form action="main.php" method="post">
				<button type="submit" name="Mode" value="MAN" style="width: 130px; height: 35px; font-size: 120%;">MAN</button>
				<input type="hidden" name="lastMode" value="MAN" />
			</form>
		</td>';
	if ($config["Mode"] == "MAN") {
		echo '
		<td>
			<form action="main.php" method="post">
				<select name="manSoll" size="1" style="width: 60px;" onChange="this.form.submit()">';
		for ($i = 10; $i <= 100 ; $i+=10) {
			echo '
					<option value="' . $i . '"' . ($config["manSoll"] == $i ? ' selected>' : '>') . $i . '%</option>';	
		}
		echo '
		 		</select>
		 	</form></td>';
	}
	// TIM
	echo '
	</tr><tr>
		<td>
			<form action="main.php" method="post">
				<button type="submit" name="Mode" value="TIM" style="width: 130px; height: 35px; font-size: 120%;">TIM</button>
				<input type="hidden" name="lastMode" value="TIM" />
			</form>
		</td>';
	if ($config["Mode"] == "TIM") {
		echo '
		<td>
			<form action="main.php" method="post">
				<select name="tmrSpeed" size="1" style="width: 60px;" onChange="this.form.submit()">';
		for ($i = 10; $i <= 100; $i+=10) {
			echo '
					<option value="' . $i . '"' . ($config["tmrSpeed"] == $i ? ' selected>' : '>') . $i . '%</option>';	 
		}
		echo '
				</select>
				<input type="hidden" name="Mode" value="TIM" />
			</form>
		</td>
		<td>&nbsp;&nbsp;(Timer ' . $config['tmrNr'] . ')</td>';
	} 
	echo '
	</tr>
   	</table>
	<p><a href="mesures.php">Messwerte</a>&nbsp;&nbsp;&nbsp;<a href="config.php">Konfiguration</a></p>
	<p><a href="easyweb.php" target="_blank">webChart</a></p>';

?>

</body>
</html>
