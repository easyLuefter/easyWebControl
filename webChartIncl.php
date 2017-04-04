<?php

include "locals/locals.php";
include "const.php";
include "makeChartIncl.php";

$con = mysql_connect("localhost","root","atmel011");
if (!$con) die('Could not connect: ' . mysql_error());
mysql_select_db("easyLuefter", $con);

if (count($_POST) > 0) {
	if (isset($_POST['chartOpt'])) {
		$lLimit = -100;
		$uLimit = 100;
		foreach($_POST as $key => $value) {
			if      ($key == 'uLimit') $uLimit = $value;
			else if ($key == 'lLimit') $lLimit = $value;
			else if ($key != 'chartOpt') {
				if ($value > $uLimit) $value = $uLimit; else if ($value < $lLimit) $value = $lLimit; 
				mysql_query("UPDATE $tableName_chartOpt SET $key='$value' WHERE chartOpt = 1");
			}
		}
	} else {
		//
	}
}

$result = mysql_query("SELECT * FROM $tableName_chartOpt WHERE chartOpt = 1");
$chartOpt = mysql_fetch_assoc($result);	

/* öffnet Handle */
$handle = opendir($chartDir . "/archiv/");
$dirArray = array();
$i = 0;
$chartFilenameBaseLength = strlen($chartFilenameBase);
/* Liest alle Objektnamen */
while ($dname = readdir($handle)) {
   if (!strncmp($dname, $chartFilenameBase, $chartFilenameBaseLength)) {
      $dirArray[$i] = $dname;
      $i++;
   }
}
/* Schliesst Handle */
closedir($handle);

rsort($dirArray);

echo '
<table>
	<tr><td>
		<form action="' . $phpFileName . '" method="post">';

if (!isset($_POST["archiv_filename_key"])) $archiv_filename_key = -1;
else $archiv_filename_key = intval($_POST["archiv_filename_key"]);
//echo "archiv_filename_key: " . $archiv_filename_key . "<br />\r\n";

if (isset($_POST["chart_x"])) {
	$fromDate = "";
	if (intval($_POST["chart_x"]) < 500) $archiv_filename_key++; else $archiv_filename_key--;
	if ($archiv_filename_key >= count( $dirArray)) $archiv_filename_key = count( $dirArray)-1;
	if ($archiv_filename_key < 0) {
		echo '
			<input type="image" id="chart" name="chart"  src="' . $chartDir . '/' . $chartFilenameBase . '.jpg?=' . rand() . '" />';
	} else {
		echo '
			<input type="image" id="chart" name="chart" src="' . $chartDir . '/archiv/' . $dirArray[$archiv_filename_key] . '" />
			<input type="hidden" name="archiv_filename_key" value="' . $archiv_filename_key . '" />'; 
	}
} else if (isset($_POST["archiv_filename_key"])) {
	$fromDate = "";
	if ($archiv_filename_key < 0) $archiv_filename_key = 0; 
	if ($archiv_filename_key >= count( $dirArray)) $archiv_filename_key = count( $dirArray)-1;
	echo '
			<input type="image" id="chart" name="chart" src="' . $chartDir . '/archiv/' . $dirArray[$archiv_filename_key] . '" />
			<input type="hidden" name="archiv_filename_key" value="' . $archiv_filename_key . '" />
	';
} else if (isset($_POST["chartDate"])) {
	$fromDate = $_POST["chartDate"];
	// make Chart
	makeChart("easyLüfter", "/tmp/easyWebCharts", $chartOpt['XScale'], $chartOpt['YScale'], $chartOpt['tempOffset'], $_POST["chartDate"] . " 20:00"); 		
	echo '
			<input type="image" id="chart" name="chart"  src="' . $chartDir . '/' . $chartFilenameBase . '.jpg?=' . rand() . '" />';
} else {
	$fromDate = date("d.m.Y");
	// make Chart
	makeChart("easyLüfter", "/tmp/easyWebCharts", $chartOpt['XScale'], $chartOpt['YScale'], $chartOpt['tempOffset'], "actualMinute"); 		
	echo '
			<input type="image" id="chart" name="chart"  src="' . $chartDir . '/' . $chartFilenameBase . '.jpg?=' . rand() . '" />';
	if (!isset($_POST['tempOffset'])) echo '
			<script language="JavaScript">
				void(setInterval("document.location.reload()",30000));
			</script>';
}


echo '
		</form>
	</td><td>
		<br /><br /><a href="' . $phpFileName . '">aktuelles Chart</a><br />(wird alle 30 Sek aktualisiert)<br /><br />	
		Chart von Datum: 
		<form action="' . $phpFileName . '" method="post">
			<input name="chartDate" value="' . $fromDate . '" type="text" style="width: 80px" id="datepicker" onchange="this.form.submit()" />
		</form>	
		<p>
		<form action="' . $phpFileName . '" method="post">
			Archiv:<br />
			<select name="archiv_filename_key" size="6" onChange="this.form.submit()">';

foreach ($dirArray as $key => $val) {

	$Year = substr($val,$chartFilenameBaseLength+1,4);
	$Month = substr($val,$chartFilenameBaseLength+5,2);
	$Day = substr($val,$chartFilenameBaseLength+7,2);
	if (!strcmp($archiv_filename_key, $key)) {
	   	echo '
			   	<option selected value="' . $key . '">Chart vom ' . $Day . '.' . $Month . '.' . $Year . '&nbsp;</option>';
	   	$selectedKey = $key;
	} else {
	   	echo '
			   	<option value="' . $key . '">Chart vom ' . $Day . '.' . $Month . '.' . $Year . '&nbsp;</option>';
	}

}
echo '
			</select>
		</form>
		</p>';
		
	//echo "SELECT * FROM $tableName_chartOpt WHERE chartOpt = 1";
	$result = mysql_query("SELECT * FROM $tableName_chartOpt WHERE chartOpt = 1");
	$chartOpt = mysql_fetch_assoc($result);	
	//echo "leistung: " . $chartOpt['leistung'];
	
echo '	
		<form action="' . $phpFileName . '" method="post">
			<input type="hidden" name="chartOpt" value="SET" />
			<select name="XScale" onChange="this.form.submit()" >';
		for ($i = 0; $i <= 2; $i++) echo '
				<option ' . ($XScaleChoice[$i] == $chartOpt['XScale'] ? 'selected value="' : 'value="') . $XScaleChoice[$i] .'" >' . $XScaleChoice[$i] . ' Std pro Einheit</option>';
	echo '
			</select> X-Scale<br />
			<select name="YScale" onChange="this.form.submit()" >';
		for ($i = 5; $i <= 6; $i+=1) echo '
				<option ' . ($i == $chartOpt['YScale'] ? 'selected value="' : 'value="') . $i .'" >' . $i . ' °C g/m&sup3; pro Einheit</option>';
	echo '
			</select> Y-Scale<br />
			<select name="tempOffset" onChange="this.form.submit()" >';
		for ($i = 15; $i >= -10; $i-=5) echo '
				<option ' . ($i == $chartOpt['tempOffset'] ? 'selected value="' : 'value="') . $i .'" >' . $i . ' °C g/m&sup3;</option>';
	echo '
			</select> Skalen Offset<br />';
	if (isset($_POST["chartDate"])) echo '
			<input type="hidden" name="chartDate" value="' . $_POST["chartDate"] . '">';
	echo '
			<input type="hidden" name="leistung" value="0" />
			<input type="checkbox" name="leistung" value="1" onChange="this.form.submit()" ' . ($chartOpt['leistung']?"checked":"") . ' />Lüfterleistung<br />
			<input type="hidden" name="leistungAbluft" value="0" />
			<input type="checkbox" name="leistungAbluft" value="1" onChange="this.form.submit()"' . ($chartOpt['leistungAbluft']?" checked":"") . ' />Lüfterleistung Abluft<br />
			<input type="hidden" name="waermeRueckgew" value="0" />
			<input type="checkbox" name="waermeRueckgew" value="1" onChange="this.form.submit()"' . ($chartOpt['waermeRueckgew']?" checked":"") . ' />Wärmerückgewinnung<br />
			<table>
				<tr><td>
					Abluft<br />
					Zuluft<br />
					Fortluft<br />
					Aussenluft<br />
				</td><td>
					<input type="hidden" name="AbluftRH" value="0" />
					<input type="checkbox" name="AbluftRH" value="1" onChange="this.form.submit()"' . ($chartOpt['AbluftRH']?"checked":"") . ' />RH<br />
					<input type="hidden" name="ZuluftRH" value="0" />
					<input type="checkbox" name="ZuluftRH" value="1" onChange="this.form.submit()"' . ($chartOpt['ZuluftRH']?"checked":"") . ' />RH<br />
					<input type="hidden" name="FortluftRH" value="0" />
					<input type="checkbox" name="FortluftRH" value="1" onChange="this.form.submit()"' . ($chartOpt['FortluftRH']?"checked":"") . ' />RH<br />
					<input type="hidden" name="AussenluftRH" value="0" />
					<input type="checkbox" name="AussenluftRH" value="1" onChange="this.form.submit()"' . ($chartOpt['AussenluftRH']?"checked":"") . ' />RH<br />
				</td><td>
					<input type="hidden" name="AbluftAH" value="0" />
					<input type="checkbox" name="AbluftAH" value="1" onChange="this.form.submit()"' . ($chartOpt['AbluftAH']?"checked":"") . ' />AH<br />
					<input type="hidden" name="ZuluftAH" value="0" />
					<input type="checkbox" name="ZuluftAH" value="1" onChange="this.form.submit()"' . ($chartOpt['ZuluftAH']?"checked":"") . ' />AH<br />
					<input type="hidden" name="FortluftAH" value="0" />
					<input type="checkbox" name="FortluftAH" value="1" onChange="this.form.submit()"' . ($chartOpt['FortluftAH']?"checked":"") . ' />AH<br />
					<input type="hidden" name="AussenluftAH" value="0" />
					<input type="checkbox" name="AussenluftAH" value="1" onChange="this.form.submit()"' . ($chartOpt['AussenluftAH']?"checked":"") . ' />AH<br />
				</td></tr>
			</table>
			<input type="hidden" name="AS_AH" value="0" />
			<input type="checkbox" name="AS_AH" value="1" onChange="this.form.submit()"' . ($chartOpt['AS_AH']?"checked":"") . ' />AS_AH<br />
		</form>
	</td></tr>
</table>';
?>
