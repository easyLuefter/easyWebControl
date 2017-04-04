<?php
$debug = FALSE;
if ($debug) {echo 'POST: '; print_r($_POST); echo '<br />';}
if ($debug) {echo 'GET: '; print_r($_GET); echo'<br />';}

include "locals/locals.php";
include "const.php";
include "locals/mySqlConnect.php";

$res = mysql_query("SELECT * FROM $tableName_av WHERE actualValue=1");
$mesures = mysql_fetch_assoc($res);

$res = mysql_query("SELECT * FROM $tableName_config");
$config = mysql_fetch_assoc($res);

$res = mysql_query("SELECT * FROM $tableName_vars");
$vars = mysql_fetch_assoc($res);

echo '<br />
Abluft ' . intval($mesures['Abluft']*10) . '<br />
AbluftRH ' . intval($mesures['AbluftRH']*10) . '<br />
AbluftAH ' . intval($mesures['AbluftAH']*10) . '<br />
Zuluft ' .   intval($mesures['Zuluft']*10) . '<br />
ZuluftRH ' . intval($mesures['ZuluftRH']*10) . '<br />
ZuluftAH ' . intval($mesures['ZuluftAH']*10) . '<br />
Fortluft ' . intval($mesures['Fortluft']*10) . '<br />
FortluftRH ' . intval($mesures['FortluftRH']*10) . '<br />
FortluftAH ' . intval($mesures['FortluftAH']*10) . '<br />
Aussenluft ' . intval($mesures['Aussenluft']*10) . '<br />
AussenluftRH ' . intval($mesures['AussenluftRH']*10) . '<br />
AussenluftAH ' . intval($mesures['AussenluftAH']*10) . '<br />
Feuchtigkeitsabfuhr ' . round($vars['Feuchtigkeitsabfuhr'], 1) . '<br />
Kondensierend ' . round($vars['Kondensierend'], 1) . '<br />
Feuchterueckgewinnung ' . round($vars['Feuchterueckgewinnung']*100, 0) . '<br />
Feuchterueckgewinnungsgrad ' . round($vars['Feuchterueckgewinnungsgrad'], 1) . '<br />

WirkungsgradAbluft ' . round($vars['WirkungsgradAbluft']*10, 0) . '<br />
WirkungsgradZuluft ' . round($vars['WirkungsgradZuluft']*10, 0) . '<br />

Luefterleistung ' . $vars['totSpeed'] . '<br />
LuefterleistungAbluft ' . $mesures['LuefterleistungAbluft'] . '<br />
LuefterleistungZuluft ' . $mesures['LuefterleistungZuluft'] . '<br />
Mode ' . $config['Mode'] . '<br />
FTRmode ' . $config['FTRmode'] . '<br />
AS_AH ' . round($vars['AS_AH'], 1) . '<br />';
	
?>
