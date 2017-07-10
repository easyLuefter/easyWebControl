<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
<meta name="viewport" content="width=device-width, initial-scale=0.8" />
<title>Config2</title>

<script type="text/javascript">
function sicher() {
	var chk = window.confirm("sind Sie sicher?");
	return(chk);
}
</script>

<style type="text/css">
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
include "locals/mySqlConnect.php";
include "const.php";
include "easyLib.php";

$fileName = "easyKonfiguration_" . date("Ymd_Hi") . ".txt";  // filename for config export/import

/*
// import Konfiguration
if (isset($_FILES["fileToUpload"])) {
	$tmrNr = 0;
	$target_dir = "import/";
	$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
	$uploadOk = 1;
	$FileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
	$msg = ""; 
	if ($_FILES["fileToUpload"]["size"] > 10000) {
	    $msg .= "File ist zu gross<br />";
	    $uploadOk = 0;
	 }
	// Allow certain file formats
	if ($FileType != "txt" ) {
	    $msg .= "'$FileType' kein gültiges Konfigurations-File<br />";
	    $uploadOk = 0;
	}
	// Check if $uploadOk is set to 0 by an error
	if ($uploadOk == 1) {   // if everything is ok, try to upload file
		$target_file = $target_dir . "easyKonfiguration.txt";
	    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
			$myfile = fopen($target_file, "r") or die("Unable to open file '$target_file'!");
			$line = trim(fgets($myfile)," \r\n");
			if (strpos($line,"easyKonfiguration")) {
		        while((!strpos( $line = trim(fgets($myfile)," \r\n") , "interfaces.txt:") ) && (!feof($myfile))) {
					if ($pos = strpos($line," ")) {
						$key = substr($line,0,$pos);
						$value = substr($line,$pos+1);
						if      ($key == "config") {$tableName = $tableName_config; $rowName = "config"; $rowNr = "1";}
						else if ($key == "hum")    {$tableName = $tableName_hum;    $rowName = "hum";    $rowNr = "1";}
						else if ($key == "tmrNr")  {$tableName = $tableName_tmr;    $rowName = "tmrNr";  $rowNr = $value;}
						else {
							mysql_query("UPDATE $tableName SET $key='$value' WHERE $rowName = $rowNr");
							//echo ("UPDATE $tableName SET $key='$value' WHERE $rowName = $rowNr") . "<br />";
						}
					}
		        }
		        //echo "line: $line<br />";
	        	if (strpos($line,"interfaces.txt:")) {
					$wFile = fopen("network/interfaces.txt","w") or die("Unable to open file 'network/interfaces.txt'!");
	        		while ((!strpos($line = str_replace("\r", '', fgets($myfile)),"hostapd.conf.txt:")) && (!feof($myfile))) fwrite($wFile, $line);
	        		fclose($wFile);
					$wFile = fopen("network/hostapd.conf.txt","w") or die("Unable to open file 'network/hostapd.conf.txt'!");
	        		while ((!strpos($line = str_replace("\r", '', fgets($myfile)),"interfaces_ap.txt:")) && (!feof($myfile))) fwrite($wFile, $line);
	        		fclose($wFile);
					$wFile = fopen("network/interfaces_ap.txt","w") or die("Unable to open file 'network/interfaces_ap.txt'!");
	        		while (($line = str_replace("\r", '', fgets($myfile))) && (!feof($myfile))) fwrite($wFile, $line);
	        		fclose($wFile);
	        	}
				$msg .= "Konfiguration 2 wurde wiederhergestellt";
			} else $msg .= "ungültiges Konfiguration Format<br />";
			fclose($myfile);
	    } else $msg .= "Fehler beim Transfer der Konfigurations-Datei";
	} else $msg .= "Konfiguration wurde nicht wiederhergestellt<br />";
}

*/

/*
	$read = doReadSerial($serial);
	if ($debug) echo 'read: ' . $read . '<br />';
	$valueTable = explode("\n", $read);
	if ($debug) {echo "valueTable: "; print_r($valueTable); echo "<br />";}
	
	for($i = 1; $i < count($valueTable)-1; $i++) {
		$ValueEntry = explode(' ', $valueTable[$i]);
		if (isset($ValueEntry[1])) $ValueArray[$ValueEntry[0]] = $ValueEntry[1];
	}

	if ($debug) {echo "ValueArray: "; print_r($ValueArray); echo "<br />";}

	//write config data to file (for config export)
	$myfile = fopen("export/" . $fileName, "w") or die("Unable to open file '$fileName'!");
	fwrite($myfile, "easyKonfiguration2\r\n");
	foreach ($ValueArray as $key => $value) {
	    fwrite($myfile, "$key $value\r\n");
	}
	fclose($myfile); */

if (count($_POST) > 0) {
	if (isset($_POST['button'])) {
		if ($_POST['button'] == "reset") {
			mysql_query("UPDATE $tableName_config SET tempCal1=0, tempCal2=0, tempCal3=0, tempCal4=0, RHCal1=0, RHCal2=0, RHCal3=0, RHCal4=0 WHERE config = 1");
		} else if ($_POST['button'] == "autoCal") {
			$res = mysql_query("SELECT * FROM $tableName_av WHERE actualValue=1");
			$mesures = mysql_fetch_assoc($res);
			$res = mysql_query("SELECT * FROM $tableName_config");
			$config = mysql_fetch_assoc($res);

			$avVal = ($mesures["Abluft"] + $mesures["Zuluft"] + $mesures["Fortluft"] + $mesures["Aussenluft"])/4;
			$tempCal1 = $config['tempCal1'] + $avVal - $mesures["Abluft"];
			$tempCal2 = $config['tempCal2'] + $avVal - $mesures["Zuluft"];
			$tempCal3 = $config['tempCal3'] + $avVal - $mesures["Fortluft"];
			$tempCal4 = $config['tempCal4'] + $avVal - $mesures["Aussenluft"];
			$avVal = ($mesures["AbluftRH"] + $mesures["ZuluftRH"] + $mesures["FortluftRH"] + $mesures["AussenluftRH"])/4;
			$RHCal1 = $config['RHCal1'] + $avVal - $mesures["AbluftRH"];
			$RHCal2 = $config['RHCal2'] + $avVal - $mesures["ZuluftRH"];
			$RHCal3 = $config['RHCal3'] + $avVal - $mesures["FortluftRH"];
			$RHCal4 = $config['RHCal4'] + $avVal - $mesures["AussenluftRH"];
			mysql_query("UPDATE $tableName_config SET tempCal1=$tempCal1, tempCal2=$tempCal2, tempCal3=$tempCal3, tempCal4=$tempCal4, " . 
													 "RHCal1=$RHCal1, RHCal2=$RHCal2, RHCal3=$RHCal3, RHCal4=$RHCal4 WHERE config = 1");
		} else if ($_POST['button'] == "update") {
			$message = "";
			if ($conn_id = ftp_connect($ftp_server1)) {
				if (ftp_login($conn_id, $ftp_user_name1, $ftp_user_pass1)) {
					if (ftp_get($conn_id, "test.test", "proc/main.php", FTP_BINARY)) {
						//echo "file download OK\n";
						ftp_sync($conn_id, "proc");
						exec("cp proc/* .");
						exec("rm test.test");
					} else {
						echo "file download nicht möglich\n";
					}
				} else {
					$message.= date("d.m.Y H:i:s") . " ftp_login() failed\n";
				}
				ftp_close($conn_id);			
			} else {
				$message.= date("d.m.Y H:i:s") . " ftp_connect($ftp_server1) failed\n";
			}
		} else if ($_POST['button'] == "update2") {
			exec("rm -fr easyControlPi");
			exec("git clone https://github.com/easyLuefter/easyWebControl.git easyControlPi");
			exec("cp easyControlPi/* .");
			exec("cp -r easyControlPi/dht22 .");
		}
	} else if (isset($_POST['conf'])) {
		$lLimit = -10;
		$uLimit = 10;
		foreach($_POST as $key => $value) {
			if      ($key == 'uLimit') $uLimit = $value;
			else if ($key == 'lLimit') $lLimit = $value;
			else if ($key != 'conf') {
				if ($value > $uLimit) $value = $uLimit; else if ($value < $lLimit) $value = $lLimit; 
				mysql_query("UPDATE $tableName_config SET $key='$value' WHERE config = 1");
			}
		}
	} else if (isset($_POST['fanControl'])) {
		foreach($_POST as $key => $value) {
			if ($key != 'fanControl') {
				//if ($value > $uLimit) $value = $uLimit; else if ($value < $lLimit) $value = $lLimit; 
				mysql_query("UPDATE $tableName_fanControl SET $key='$value' WHERE fanControl = 1");
			}
		}
	} else {
		foreach($_POST as $key => $value) {
			mysql_query("UPDATE $tableName_hum SET $key='$value' WHERE hum = 1");
		}
	}
}

// fetch values from database
$res = mysql_query("SELECT * FROM $tableName_config");
$config = mysql_fetch_assoc($res);
$res = mysql_query("SELECT * FROM $tableName_hum");
$hum = mysql_fetch_assoc($res);
$res = mysql_query("SELECT * FROM $tableName_vars");
$vars = mysql_fetch_assoc($res);
$res = mysql_query("SELECT * FROM $tableName_fanControl");
$fanControl = mysql_fetch_assoc($res);

/*
// prepare file for save config
exec ("rm export/*");
$myfile = fopen("export/" . $fileName, "w") or die("Unable to open file '$fileName'!");
fwrite($myfile, "easyKonfiguration\r\n");
foreach ($config as $key => $value) fwrite($myfile, "$key $value\r\n");
foreach ($hum    as $key => $value) fwrite($myfile, "$key $value\r\n");
$res = mysql_query("SELECT * FROM $tableName_tmr WHERE tmrNr < 20");
while ($tmr = mysql_fetch_assoc($res)) {
	foreach ($tmr    as $key => $value) fwrite($myfile, "$key $value\r\n");
}
// interfaces.txt
fwrite($myfile, "#interfaces.txt:\r\n");
$nwFile = fopen("network/interfaces.txt","r") or die("Unable to open file 'network/interfaces.txt'!");
while(!feof($nwFile)) fwrite($myfile, trim(fgets($nwFile)," \r\n") . "\r\n");
fclose($nwFile);
fwrite($myfile, "#hostapd.conf.txt:\r\n");
// hostapd.conf.txt
$nwFile = fopen("network/hostapd.conf.txt","r") or die("Unable to open file 'network/hostapd.conf.txt'!");
while(!feof($nwFile))  fwrite($myfile, trim(fgets($nwFile)," \r\n") . "\r\n");
fclose($nwFile);
fwrite($myfile, "#interfaces_ap.txt:\r\n");
// interfaces_ap.txt
$nwFile = fopen("network/interfaces_ap.txt","r") or die("Unable to open file 'network/interfaces_ap.txt'!");
while(!feof($nwFile)) fwrite($myfile, trim(fgets($nwFile)," \r\n") . "\r\n");
fclose($nwFile);
fclose($myfile); 
*/


	echo '<h3>Konfiguration 2 (Klimafunktionen)</h3>
	<table>';

	// Feuchte-Sprung 1
	echo '
	<tr><td>Feuchte-Sprung</td></tr>
	<tr><td>Mittelwertbildung<br>über:</td><td>RH-<br>Mittelwert</td><td>Schwelle</td><td>Schritt</td><td>L+</td><td>FS1n</td</tr>
	<tr>
	
	<td><form action="config2.php" method="post">
		<select name="FSavCount1" style="width: 45px" onChange="this.form.submit()">';
		for ($i = 1; $i <= 15; $i++) {
		  echo '
		  		<option value="' . sprintf("%d", $i) . '"' . ($hum['FSavCount1'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
		}
	echo '
	</select> Min
	</form></td>
	
	<td style="font-size:10pt">';
	if ($vars['FSavRH01'] != 9999) echo sprintf("%02.1f %%",$vars['FSavRH01']);
	else echo '-.-%';
	echo '
	</td>
	
	<td><form action="config2.php" method="post">
		<select name="FSgap1" style="width: 45px" onChange="this.form.submit()">';
		for ($i = 0.1; $i <= 1.5; $i= round($i+0.1,1)) {
		  echo '
		  		<option value="' . sprintf("%01.1f", $i) . '"' . ($hum['FSgap1'] == $i ? ' selected>' : '>') . sprintf("%01.1f", $i) . '</option>';	 
		}
	echo '
	</select>%
	</form>

	<td><form action="config2.php" method="post">
		<select name="FSincL1" style="width: 45px" onChange="this.form.submit()">';
		for ($i = 0; $i <= 90; $i+=5) {
		  echo '
		  		<option value="' . sprintf("%d", $i) . '"' . ($hum['FSincL1'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
		}
	echo '
	</select>%
	</form></td>

	<td><form action="config2.php" method="post">
		<select name="FSmaxPosOffset1" style="width: 45px" onChange="this.form.submit()">';
		for ($i = 0; $i <= 90; $i+=10) {
		  echo '
		  		<option value="' . sprintf("%d", $i) . '"' . ($hum['FSmaxPosOffset1'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
		}
	echo '
	</select>%
	</form></td>

	<td><form action="config2.php" method="post">
		<select name="FS1n" style="width: 50px" onChange="this.form.submit()">';
		for ($i = 0; $i <= 240; $i+=5) {
		  echo '
		  		<option value="' . sprintf("%d", $i) . '"' . ($hum['FS1n'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
		}
	echo '
	</select> Min
	</form></td>
	</tr>';	  



	// Feuchte-Sprung 2
	echo '
	<td><form action="config2.php" method="post">
		<select name="FSavCount2" style="width: 45px" onChange="this.form.submit()">';
		for ($i = 1; $i <= 15; $i++) {
		  echo '
		  		<option value="' . sprintf("%d", $i) . '"' . ($hum['FSavCount2'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
		}
	echo '
	</select> Tage
	</form></td>
	
	<td style="font-size:10pt">';
	if ($vars['FSavRH02'] != 9999) echo sprintf("%02.1f %%",$vars['FSavRH02']);
	else echo '-.-%';
	echo '
	</td>
	
	<td><form action="config2.php" method="post">
		<select name="FSgap2" style="width: 45px" onChange="this.form.submit()">';
		for ($i = 1; $i <= 15; $i++) {
		  echo '
		  		<option value="' . sprintf("%d", $i) . '"' . ($hum['FSgap2'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
		}
	echo '
	</select>%
	</form>	

	<td><form action="config2.php" method="post">
		<select name="FSincL2" style="width: 45px" onChange="this.form.submit()">';
		for ($i = 0; $i <= 90; $i+=5) {
		  echo '
		  		<option value="' . sprintf("%d", $i) . '"' . ($hum['FSincL2'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
		}
	echo '
	</select>%
	</form></td>

	<td><form action="config2.php" method="post">
		<select name="FSmaxPosOffset2" style="width: 45px" onChange="this.form.submit()">';
		for ($i = 0; $i <= 90; $i+=10) {
		  echo '
		  		<option value="' . sprintf("%d", $i) . '"' . ($hum['FSmaxPosOffset2'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
		}
	echo '
	</select>%
	</form>
	</tr>';	  


	// anti Schimmel
	echo '
	<tr></tr>
	<tr><td>Anti-Schimmel&nbsp</td></tr>
	<tr><td>AS-Temperatur<br></td><td>AS-RH</td><td>AS-AH</td><td>L+</td><td>L-</td></tr>
	
	<tr>
	<td><form action="config2.php" method="post">
		<select name="ASpercent" style="width: 45px" onChange="this.form.submit()">';
		for ($i = 99; $i >= 40; $i--) {
		  echo '
		  		<option value="' . sprintf("%d", $i) . '"' . ($hum['ASpercent'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
		}
	echo '
	</select>%
	<span style="font-size:10pt">&nbsp;' . sprintf("%2.1f",$vars['AStemp']) . '°C</span>
	</form>
	</td>
	
	<td><form action="config2.php" method="post">
		<select name="ASmaxRH" style="width: 45px" onChange="this.form.submit()">';
		for ($i = 90; $i >= 50; $i--) {
		  echo '
		  		<option value="' . sprintf("%d", $i) . '"' . ($hum['ASmaxRH'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
		}
	echo '
	</select>%
	</form>
	</td>

	<td>
		<span style="font-size:10pt">' . sprintf("%2.1f",$vars['AS_AH']) . ' g/m&sup3;</span>
	</td>
		
	<td><form action="config2.php" method="post">
		<select name="ASposOffset" style="width: 45px" onChange="this.form.submit()">';
		for ($i = 0; $i <= 90; $i+=10) {
		  echo '
		  		<option value="' . sprintf("%d", $i) . '"' . ($hum['ASposOffset'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
		}
	echo '
	</select>%
	</form>
		
	<td><form action="config2.php" method="post">
		<select name="ASnegOffset" style="width: 45px" onChange="this.form.submit()">';
		for ($i = 0; $i >= -90; $i-=10) {
		  echo '
		  		<option value="' . sprintf("%d", $i) . '"' . ($hum['ASnegOffset'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
		}
	echo '
	</select>%
	</form>
	</tr>';	  



	// RH Limite
	echo '<tr><td>RH Limit&nbsp</td><td><br>RH</td><td><br>L+</td><td><br>L-</td></tr>';
	// RHmax
	echo '
	<tr>
	<td>RHmax</td>
	
	<td><form action="config2.php" method="post">
		<select name="RHmax" style="width: 45px" onChange="this.form.submit()">';
		for ($i = 99; $i >= 30; $i--) {
		  echo '
		  		<option value="' . sprintf("%d", $i) . '"' . ($hum['RHmax'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
		}
	echo '
	</select>%
	</form>
	</td>
	
	<td><form action="config2.php" method="post">
		<select name="RHmaxPosOffset" style="width: 45px" onChange="this.form.submit()">';
		for ($i = 0; $i <= 90; $i+=10) {
		  echo '
		  		<option value="' . sprintf("%d", $i) . '"' . ($hum['RHmaxPosOffset'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
		}
	echo '
	</select>%
	</form>

	<td><form action="config2.php" method="post">
		<select name="RHmaxNegOffset" style="width: 45px" onChange="this.form.submit()">';
		for ($i = 0; $i >= -90; $i-=10) {
		  echo '
		  		<option value="' . sprintf("%d", $i) . '"' . ($hum['RHmaxNegOffset'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
		}
	echo '
	</select>%
	</form>
	</tr>';	  
	
	// RHmin
	echo '
	<tr>
	<td>RHmin</td>

	<td><form action="config2.php" method="post">
		<select name="RHmin" style="width: 45px" onChange="this.form.submit()">';
		for ($i = 99; $i >= 30; $i--) {
		  echo '
		  		<option value="' . sprintf("%d", $i) . '"' . ($hum['RHmin'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
		}
	echo '
	</select>%
	</form>
	</td>

	<td><form action="config2.php" method="post">
		<select name="RHminPosOffset" style="width: 45px" onChange="this.form.submit()">';
		for ($i = 0; $i <= 90; $i+=10) {
		  echo '
		  		<option value="' . sprintf("%d", $i) . '"' . ($hum['RHminPosOffset'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
		}
	echo '
	</select>%
	</form>

	<td><form action="config2.php" method="post">
		<select name="RHminNegOffset" style="width: 45px" onChange="this.form.submit()">';
		for ($i = 0; $i >= -90; $i-=10) {
		  echo '
		  		<option value="' . sprintf("%d", $i) . '"' . ($hum['RHminNegOffset'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
		}
	echo '
	</select>%
	</form>
	</tr>';	  
	
	
	
	// Temperatur Limite
	echo '<tr><td>Temp Limit<br /></td><td><br>Temp</td><td><br>L+</td><td><br>L-</td></tr>';
	// tempMax
	echo '
	<tr>
	<td>TempMax</td>
	
	<td><form action="config2.php" method="post">
		<select name="tempMax" style="width: 52px" onChange="this.form.submit()">';
		for ($i = 40; $i >= 0; $i= round($i-0.1,1)) {
		  echo '
		  		<option value="' . sprintf("%01.1f", $i) . '"' . ($hum['tempMax'] == $i ? ' selected>' : '>') . sprintf("%01.1f", $i) . '</option>';	 
		}
	echo '
	</select> °C
	</form>
	</td>
	
	<td><form action="config2.php" method="post">
		<select name="tempMaxPosOffset" style="width: 45px" onChange="this.form.submit()">';
		for ($i = 0; $i <= 90; $i+=10) {
		  echo '
		  		<option value="' . sprintf("%d", $i) . '"' . ($hum['tempMaxPosOffset'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
		}
	echo '
	</select>%
	</form>

	<td><form action="config2.php" method="post">
		<select name="tempMaxNegOffset" style="width: 45px" onChange="this.form.submit()">';
		for ($i = 0; $i >= -90; $i-=10) {
		  echo '
		  		<option value="' . sprintf("%d", $i) . '"' . ($hum['tempMaxNegOffset'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
		}
	echo '
	</select>%
	</form>
	</tr>';	  
	
	// tempMin
	echo '
	<tr>
	<td>TempMin</td>
	
	<td><form action="config2.php" method="post">
		<select name="tempMin" style="width: 52px" onChange="this.form.submit()">';
		for ($i = 40; $i >= 0; $i= round($i-0.1,1)) {
		  echo '
		  		<option value="' . sprintf("%01.1f", $i) . '"' . ($hum['tempMin'] == $i ? ' selected>' : '>') . sprintf("%01.1f", $i) . '</option>';	 
		}
	echo '
	</select> °C
	</form>
	</td>
	
	<td><form action="config2.php" method="post">
		<select name="tempMinPosOffset" style="width: 45px" onChange="this.form.submit()">';
		for ($i = 0; $i <= 90; $i+=10) {
		  echo '
		  		<option value="' . sprintf("%d", $i) . '"' . ($hum['tempMinPosOffset'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
		}
	echo '
	</select>%
	</form>

	<td><form action="config2.php" method="post">
		<select name="tempMinNegOffset" style="width: 45px" onChange="this.form.submit()">';
		for ($i = 0; $i >= -90; $i-=10) {
		  echo '
		  		<option value="' . sprintf("%d", $i) . '"' . ($hum['tempMinNegOffset'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
		}
	echo '
	</select>%
	</form>
	</tr>';	  



	
	
	// entfeuchte-Funktion
	echo '
	<tr><td>Entfeuchte-Funktion<br /></td><td><br>L+</td><td><br>Enfn</td></tr>
	<tr>
	<td></td>
	
	<td><form action="config2.php" method="post">
		<select name="enfMaxPosOffset" style="width: 45px" onChange="this.form.submit()">';
		for ($i = 90; $i >= 0; $i-=10) {
		  echo '
		  		<option value="' . sprintf("%d", $i) . '"' . ($hum['enfMaxPosOffset'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
		}
	echo '
	</select>%
	</form></td>

	<td><form action="config2.php" method="post">
		<select name="enfN" style="width: 50px" onChange="this.form.submit()">';
		for ($i = 600; $i >= 0; $i-=10) {
		  echo '
		  		<option value="' . sprintf("%d", $i) . '"' . ($hum['enfN'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
		}
	echo '
	</select> Min
	</form></td>
	</tr>';	  
 

	// Sensor Cal - tempCal RHCal  
	echo '<tr><td>Sensor Cal</td><td><br>Abluft</td><td><br>Zuluft</td><td><br>Fortluft</td><td><br>Aussenl.</td></tr>
	<tr><form action="config2.php" method="post">
		<input type="hidden" name="lLimit" value="-10" />
		<input type="hidden" name="uLimit" value="10" />
		<input type="hidden" name="conf" value="SET" />
		<td>tempCal</td>
		<td><input type="text" size="2" value="' . sprintf("%.2f", $config['tempCal1']) . '" name="tempCal1"></td>
		<td><input type="text" size="2" value="' . sprintf("%.2f", $config['tempCal2']) . '" name="tempCal2"></td>
		<td><input type="text" size="2" value="' . sprintf("%.2f", $config['tempCal3']) . '" name="tempCal3"></td>
		<td><input type="text" size="2" value="' . sprintf("%.2f", $config['tempCal4']) . '" name="tempCal4"></td>
		<td><input type="submit" value="speichern"></td>
	</tr><tr>
		<input type="hidden" name="conf" value="SET" />
		<td>RHCal</td>
		<td><input type="text" size="2" value="' . sprintf("%.1f", $config['RHCal1']) . '" name="RHCal1"></td>
		<td><input type="text" size="2" value="' . sprintf("%.1f", $config['RHCal2']) . '" name="RHCal2"></td>
		<td><input type="text" size="2" value="' . sprintf("%.1f", $config['RHCal3']) . '" name="RHCal3"></td>
		<td><input type="text" size="2" value="' . sprintf("%.1f", $config['RHCal4']) . '" name="RHCal4"></td>
	</form>';

	// reset and sensorCal button
	echo '
	<td><form action="config2.php" method="post" onSubmit="return sicher()">
	<input type="submit" name="button" value="reset">
	<button type="submit" name="button" value="autoCal">autoCal</button>
	</form></td></tr>';

	// Regler
	echo '<tr><td>Regler</td><td><br />P</td><td><br />I</td><td><br />D</td></tr>
	<tr><form action="config2.php" method="post">
		<input type="hidden" name="conf" value="SET" />
		<input type="hidden" name="lLimit" value="0" />
		<input type="hidden" name="uLimit" value="4" />
		<td></td>
		<td><input type="text" size="2" value="' . $config['Pvalue']  . '" name="Pvalue"></td>
		<td><input type="text" size="2" value="' . $config['Ivalue']  . '" name="Ivalue"></td>
		<td><input type="text" size="2" value="' . $config['Dvalue']  . '" name="Dvalue"></td>
		<td><input type="submit" value="speichern"></td>
	</form></tr>
	<tr><form action="config2.php" method="post">
		<input type="hidden" name="conf" value="SET" />
		<input type="hidden" name="lLimit" value="180" />
		<input type="hidden" name="uLimit" value="1200" />
		<td></td><td>Zeitkonstante:</td>
		<td><input type="text" size="2" value="' . $config['timeConst'] . '" name="timeConst"> Sek</td><td></td>
		<td><input type="submit" value="speichern"></td>
	</form></tr>';


	// Sym
	echo '
	<tr><td>Symmmetrie</td><td><br />EIN/AUS</td>' . ($config["sym"] == 0 ? "<td><br />Abl</td><td><br />Zul</td>" : "") . '</tr>
	<tr><form action="config2.php" method="post">
		<input type="hidden" name="conf" value="SET" />
		<input type="hidden" name="lLimit" value="-50" />
		<input type="hidden" name="uLimit" value="50" />
		<td></td>
		<td><select name="sym" size="1" style="width: 60px" onChange="this.form.submit()">'
		. ($config["sym"] == 0 ? '
			<option value="0" selected>AUS</option>
			<option value="1">EIN</option>'
		  : '
			<option value="0">AUS</option>
			<option value="1" selected>EIN</option>') . '
		</select></td>';
		
	if ($config["sym"] == 0) {
		echo '
		<td><select name="uSym" style="width: 50px" onChange="this.form.submit()">';
		for ($i = -50; $i <= 50; $i++) {
		  echo '
		  		<option value="' . sprintf("%d", $i) . '"' . ($config['uSym'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
		}
		echo '
		</select>%</td>
		
		<td>' . -$config["uSym"] . '</td>';
	}
	echo '
	</form></tr>';
	

	// minimale maximale Lüfterleistungen
	echo '
	<tr><td>min/max<br />Lüfterleistung</td><td><br />minAbluft</td><td><br />minZuluft</td><td><br />maxAbluft</td><td><br />maxZuluft</td></tr>
	<tr><td></td><td>
		<form action="config2.php" method="post">
			<input type="hidden" name="conf" value="SET" />
			<input type="hidden" name="lLimit" value="10" />
			<input type="hidden" name="uLimit" value="100" />
			<select name="minLLAbluft" style="width: 45px" onChange="this.form.submit()">';
			for ($i = 40; $i >= 10; $i-=5) echo '
			  		<option value="' . sprintf("%d", $i) . '"' . ($config['minLLAbluft'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
			echo '
			</select>%
		</td><td>
			<select name="minLLZuluft" style="width: 45px" onChange="this.form.submit()">';
			for ($i = 40; $i >= 10; $i-=5) echo '
			  		<option value="' . sprintf("%d", $i) . '"' . ($config['minLLZuluft'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
			echo '
			</select>%
		</td><td>
			<select name="maxLLAbluft" style="width: 50px" onChange="this.form.submit()">';
			for ($i = 100; $i >= 60; $i-=5) echo '
			  		<option value="' . sprintf("%d", $i) . '"' . ($config['maxLLAbluft'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
			echo '
			</select>%
		</td><td>
			<select name="maxLLZuluft" style="width: 50px" onChange="this.form.submit()">';
			for ($i = 100; $i >= 60; $i-=5) echo '
			  		<option value="' . sprintf("%d", $i) . '"' . ($config['maxLLZuluft'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
			echo '
			</select>%
		</td></form>
	</tr>';	  



	// fanControl
	echo '
	<tr><td>Resonnanz-<br />Frequenzen</td><td><br />von</td><td><br />bis</td><td><br />von</td><td><br />bis</td></tr>
	<tr><td>Abluft</td><td>
		<form action="config2.php" method="post">
			<input type="hidden" name="fanControl" value="SET" />
			<input type="hidden" name="lLimit" value="10" />
			<input type="hidden" name="uLimit" value="100" />
			<select name="abluftSpeed1" style="width: 45px" onChange="this.form.submit()">';
			for ($i = 0; $i <= 100; ($i == 0 ? $i+=10 : $i++)) echo '
			  		<option value="' . sprintf("%d", $i) . '"' . ($fanControl['abluftSpeed1'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
			echo '
			</select>%
		</td><td>
			<select name="abluftSpeed2" style="width: 45px" onChange="this.form.submit()">';
			for ($i = 0; $i <= 100; ($i == 0 ? $i+=10 : $i++)) echo '
			  		<option value="' . sprintf("%d", $i) . '"' . ($fanControl['abluftSpeed2'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
			echo '
			</select>%
		</td><td>
			<select name="abluftSpeed3" style="width: 45px" onChange="this.form.submit()">';
			for ($i = 0; $i <= 100; ($i == 0 ? $i+=10 : $i++)) echo '
			  		<option value="' . sprintf("%d", $i) . '"' . ($fanControl['abluftSpeed3'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
			echo '
			</select>%
		</td><td>
			<select name="abluftSpeed4" style="width: 45px" onChange="this.form.submit()">';
			for ($i = 0; $i <= 100; ($i == 0 ? $i+=10 : $i++)) echo '
			  		<option value="' . sprintf("%d", $i) . '"' . ($fanControl['abluftSpeed4'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
			echo '
			</select>%
		</td></form>
	</tr>	  
	<tr><td>Zuluft</td><td>
		<form action="config2.php" method="post">
			<input type="hidden" name="fanControl" value="SET" />
			<input type="hidden" name="lLimit" value="10" />
			<input type="hidden" name="uLimit" value="100" />
			<select name="zuluftSpeed1" style="width: 45px" onChange="this.form.submit()">';
			for ($i = 0; $i <= 100; ($i == 0 ? $i+=10 : $i++)) echo '
			  		<option value="' . sprintf("%d", $i) . '"' . ($fanControl['zuluftSpeed1'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
			echo '
			</select>%
		</td><td>
			<select name="zuluftSpeed2" style="width: 45px" onChange="this.form.submit()">';
			for ($i = 0; $i <= 100; ($i == 0 ? $i+=10 : $i++)) echo '
			  		<option value="' . sprintf("%d", $i) . '"' . ($fanControl['zuluftSpeed2'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
			echo '
			</select>%
		</td><td>
			<select name="zuluftSpeed3" style="width: 45px" onChange="this.form.submit()">';
			for ($i = 0; $i <= 100; ($i == 0 ? $i+=10 : $i++)) echo '
			  		<option value="' . sprintf("%d", $i) . '"' . ($fanControl['zuluftSpeed3'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
			echo '
			</select>%
		</td><td>
			<select name="zuluftSpeed4" style="width: 45px" onChange="this.form.submit()">';
			for ($i = 0; $i <= 100; ($i == 0 ? $i+=10 : $i++)) echo '
			  		<option value="' . sprintf("%d", $i) . '"' . ($fanControl['zuluftSpeed4'] == $i ? ' selected>' : '>') . sprintf("%d", $i) . '</option>';	 
			echo '
			</select>%
		</td></form>
	</tr>';
	
	
	echo '
	<tr><td></td><td><br />
		<form action="config2.php" method="post">
			<button type="submit" name="button" value="update2">Software update</button>		
		</form>
	</td></tr>';



	echo '	
	</table>';
	
	echo '
	<p><a href="main.php">Startseite</a>&nbsp;&nbsp;<a href="mesures.php">Messwerte</a>&nbsp;&nbsp;<a href="config.php">zurück zu Konfiguration</a></p>
	';
   
?>

</body>
</html>
