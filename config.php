<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
<meta name="viewport" content="width=device-width, initial-scale=0.8" />
<title>Config</title>

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
if ($debug) {echo "FILES: "; print_r($_FILES); echo "<br />";}

include "locals/locals.php";
include "const.php";
include "locals/mySqlConnect.php";


if (count($_POST) > 0) {
	if (isset($_POST['tmrNr'])) {
	  	mysql_query("UPDATE $tableName_tmr SET einAus = $_POST[einAus], DoW = $_POST[DoW], hour = $_POST[hour], min = $_POST[min], Soll = $_POST[Soll] WHERE tmrNr = $_POST[tmrNr]");
	} else if (isset($_POST['timeDate'])) {
		exec("sudo date --set '" . $_POST['date'] . " " . $_POST['time'] . "'");
	} else {
		foreach($_POST as $key => $value) {
			mysql_query("UPDATE $tableName_config SET $key='$_POST[$key]' WHERE config = 1");
		}
	}
}


// check active Timer
exec("php chkTimers.php");
exec("php updateSpeed.php");

$res = mysql_query("SELECT * FROM $tableName_config");
$config = mysql_fetch_assoc($res);



echo '<h3>Konfiguration</h3>';

	//Version
	echo '
	<p>Version ' . sprintf("%.03f",$config['SWversion']) . 	'</p>';

	//Time Date
	echo '
	<p>
	<form action="config.php" method="post">
		<input type="hidden" name="timeDate" value="SET"/>
		Zeit:
		<input type="time" size="8" value="' . date("H:i") . '" name="time" placeholder="22:22" onChange="this.form.submit()"/>
		Datum:
		<input type="date" value="' . date("Y-m-d") . '" name="date" placeholder="22.22.2222" onChange="this.form.submit()"/>
	</form>
	</p>
	';

	//ManSoll
	echo '
	<p>		
	<form action="config.php" method="post">
		<input type="hidden" name="conf" value="SET" />
		ManSoll:
		<select name="manSoll" size="1" style="width: 60px" onChange="this.form.submit()">';
		for ($i = 10; $i <= 100 ; $i+=10) {
			echo '
			<option value="' . $i . '"' . ($config['manSoll'] == $i ? ' selected>' : '>') . $i . '%</option>';	 
		}
		echo '
		</select>
	</p>';
		
	//MaxTimer
	echo '
	<p>
		MaxTimer:
		<select name="maxTimerInterval" size="1" style="width: 100px" onChange="this.form.submit()">';
		for ($i = 0; $i < count($TimerIntervals); $i++) {
			echo '
			<option value="' . $i . '"' . ($i == $config['maxTimerInterval'] ? ' selected>' : '>') . $TimerIntervals[$i] . '</option>';
		}
		echo '
		</select>';

	//MinTimer
	echo '
		MinTimer:
		<select name="minTimerInterval" size="1" style="width: 100px" onChange="this.form.submit()">';
		for ($i = 0; $i < count($TimerIntervals); $i++) {
			echo '
			<option value="'. $i . '"' . ($i == $config['minTimerInterval'] ? ' selected>' : '>') . $TimerIntervals[$i] . '</option>';
		}
		echo '
		</select>
	</p>
	</form>';
		
		
// Timers
echo '
<table>
	<tr><th>TimerNr</th><th>Ein/Aus</th><th>Std</th><th>Min</th><th>Wochentag</th><th>Soll</th><th> </th></tr>';

//$EinAus = array(0 => 'Aus', 1 => 'Ein');
for ($i = 1; $i <= 6; $i++) {
	$res = mysql_query("SELECT * FROM $tableName_tmr WHERE tmrNr = $i");
	$tmr = mysql_fetch_assoc($res);
	if ($i == $config["tmrNr"]) {echo '
	<tr><td style="color:#FF0000">&nbsp;&nbsp;&nbsp;&nbsp;*' . $i . '*</td>';}
	else {echo '
	<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $i . '</td>';}
	//echo '<td>&nbsp;&nbsp;' . $EinAus[$tmr['einAus']] . '</td>';
	
	// Ein/Aus
	echo '
	<form action="config.php" method="post">
		<input type="hidden" name="tmrNr" value="' . $i . '" />
		<td>
		<select name="einAus" size="1" style="width: 53px" onChange="this.form.submit()">';

	if ($tmr['einAus']  == 0) echo '
			<option value="0" selected>AUS</option>
			<option value="1">EIN</option>';
	else echo '
			<option value="0"> AUS</option>
			<option value="1" selected>EIN</option>';
	echo '
		</select>';
	//</form>';
	
	// hour min
	echo '
	<td>
		&nbsp;<select name="hour" size="1" style="width: 50px" onChange="this.form.submit()">';
		for ($hr = 0; $hr <= 23; $hr+=1) {
			echo '
			<option value="' . $hr . '"' . ($tmr["hour"] == $hr ? ' selected>' : '>') . sprintf("%02d",$hr) . '</option>';	 
		}
	echo '
		</select>
	</td>
	<td>
		:&nbsp;<select name="min" size="1" style="width: 50px" onChange="this.form.submit()">';
		for ($mn = 0; $mn <= 50; $mn+=10) {
			echo '
		  	<option value="' . sprintf("%02d", $mn) . '"' . ($tmr["min"] == $mn ? ' selected>' : '>') . sprintf("%02d", $mn) . '</option>';	 
		}
	echo '
		</select>
	</td>';
	
	// DoW
	echo '
	<td>
		&nbsp;&nbsp;<select name="DoW" size="1" style="width: 70px" onChange="this.form.submit()">';
		for ($dow = 0; $dow < count($DoWIntervals); $dow++) {
			echo '
			<option value="' . $dow . '"' . ($tmr['DoW'] == $dow ? ' selected>' : '>') . $DoWIntervals[$dow] . '</option>';	 
		}
	echo '
		</select>
	</td>';
	
	
	// Speed Sollwert
	echo '
	<td>
		&nbsp;<select name="Soll" size="1" style="width: 55px" onChange="this.form.submit()">';
		for ($spd = 10; $spd <= 100; $spd+=10) {
			echo '
		  	<option value="' . $spd . '"' . ($tmr["Soll"] == $spd ? ' selected>' : '>') . $spd . '%</option>';	 
		}
	echo '
		</select>
	</td>
	</form></tr>
	';

} 
echo '
</table>';

//echo '
//<p><a href="main.php">Startseite</a>&nbsp;&nbsp;<a href="mesures.php">Messwerte</a>&nbsp;&nbsp;<a href="config2.php">Konfiguration 2</a></p>
//<p><a href="nwSetup.php">Netzwerk Konfiguration</a></p>
//';
echo '
<p><a href="main.php">Startseite</a>&nbsp;&nbsp;<a href="mesures.php">Messwerte</a>&nbsp;&nbsp;<a href="config2.php">Konfiguration 2</a></p>
';

?>
</body>
</html>
