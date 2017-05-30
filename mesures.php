<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
<meta name="viewport" content="width=device-width, initial-scale=0.8" />
<title>Mesures</title>

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

<script language="JavaScript">
	void(setInterval("window.location.href = window.location.protocol +'//'+ window.location.host + window.location.pathname;",10000));
</script>
</head>
<body>

<?php
$debug = FALSE;
if ($debug) {echo 'POST: '; print_r($_POST); echo '<br />';}
if ($debug) {echo 'GET: '; print_r($_GET); echo'<br />';}


include "locals/locals.php";
include "const.php";
include "locals/mySqlConnect.php";

$res = mysql_query("SELECT * FROM $tableName_config");
$config = mysql_fetch_assoc($res);

$res = mysql_query("SELECT * FROM $tableName_vars");
$vars = mysql_fetch_assoc($res);

$res = mysql_query("SELECT * FROM $tableName_av WHERE actualValue=1");
$mesures = mysql_fetch_assoc($res);



	echo '<h3>Messwerte</h3>
	<table>
	<tr><th>Sonde&nbsp;&nbsp;</th><th>Temperatur&nbsp;&nbsp;</th><th>Rel. Feuchtigkeit&nbsp;&nbsp;</th><th>Abs. Feuchtigkeit</th></tr>';

	$tColors = array("FF0000","FFBF00","00FF00","0000FF");
	//$tText = array("Abluft: ","Zuluft: ","Fortl.: ","Aussenl:");

		echo '
	<tr style="color:#' . $tColors[0] . '">
		<td>&nbsp;&nbsp;Abluft:</td>
		<td>' . sprintf("%2.1f °C",$mesures["Abluft"]) . '</td>
		<td>' . sprintf("%2.1f",$mesures["AbluftRH"]) . '%</td>
		<td>' . sprintf("%2.1f",$mesures["AbluftAH"]) . ' g/m&sup3;</td>
	</tr>
	<tr style="color:#' . $tColors[1] . '">
		<td>&nbsp;&nbsp;Zuluft:</td>
		<td>' . sprintf("%2.1f °C",$mesures["Zuluft"]) . '</td>
		<td>' . sprintf("%2.1f",$mesures["ZuluftRH"]) . '%</td>
		<td>' . sprintf("%2.1f",$mesures["ZuluftAH"]) . ' g/m&sup3;</td>
	</tr>	
	<tr style="color:#' . $tColors[2] . '">
		<td>&nbsp;&nbsp;Fortl.:</td>
		<td>' . sprintf("%2.1f °C",$mesures["Fortluft"]) . '</td>
		<td>' . sprintf("%2.1f",$mesures["FortluftRH"]) . '%</td>
		<td>' . sprintf("%2.1f",$mesures["FortluftAH"]) . ' g/m&sup3;</td>
	</tr>
	<tr style="color:#' . $tColors[3] . '">
		<td>&nbsp;&nbsp;Aussenl.:</td>
		<td>' . sprintf("%2.1f °C",$mesures["Aussenluft"]) . '</td>
		<td>' . sprintf("%2.1f",$mesures["AussenluftRH"]) . '%</td>
		<td>' . sprintf("%2.1f",$mesures["AussenluftAH"]) . ' g/m&sup3;</td>
	</tr>';
	echo '
	</table>
	<p><table>
		<tr><td>&nbsp;&nbsp;Feuchtigkeitsabfuhr:</td><td>&nbsp;' . sprintf("%2.1f", $vars['Feuchtigkeitsabfuhr']) . ' g/m&sup3;</td></tr>
		<tr><td>&nbsp;&nbsp;Kondensierend:</td><td>&nbsp;' . sprintf("%2.1f", $vars['Kondensierend']) . ' g/m&sup3;</td></tr>
		<tr><td>&nbsp;&nbsp;Feuchterückgewinnung:</td><td>&nbsp;' . sprintf("%2.1f", $vars['Feuchterueckgewinnung']) . ' g/m&sup3;</td></tr>
		<tr><td>&nbsp;&nbsp;Feuchterückgewinnungsgrad:</td><td>&nbsp;' . sprintf("%2.1f", $vars['Feuchterueckgewinnungsgrad']) . '%</td></tr>
	</table></p>
	&nbsp; Wirkungsgrad: (' . sprintf("%2.1f%% / %2.1f%%)", $vars["WirkungsgradZuluft"], $vars["WirkungsgradAbluft"]) . '
	<p>&nbsp;&nbsp;Lüfterleistung: ' . $vars['totSpeed'] . '%&nbsp;<span style="color:#FF0000">' . $config['FTRmode'] . '</span>&nbsp(Abluft: ' . $vars['LuefterleistungAbluft'] . '% / &nbsp;Zuluft: ' . $vars['LuefterleistungZuluft'] . '%)&nbsp;&nbsp;Mode: ' . $config['Mode'] . '</p>
	<p>&nbsp;&nbsp;AS-AH: ' . sprintf("%2.1f",$mesures["AS_AH"]) . ' g/m&sup3;</p>
	<p><a href="main.php">Startseite</a>&nbsp;&nbsp;<a href="config.php">Konfiguration</a></p>
	<p><a href="easyweb.php" target="_blank">webChart</a></p>';

?>


</body>
</html>
