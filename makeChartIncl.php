<?php	

function makeChart($ChartTitle, $webChartDir, $XScale = 4, $YScale = 5, $YOffset = 0, $chartDate = "actualMinute", $suffix = "") {

	include "locals/locals.php";
	include "const.php";
	
	$min = intval(time()/60);
	$result = mysql_query("SELECT * FROM $tableName WHERE (timeStamp > $min - 60) ORDER BY timeStamp DESC LIMIT 1");
	//echo "mysql_num_rows: " . mysql_num_rows($result) . "\r\n";
	
	if (mysql_num_rows($result) == 1) {
		$lastRecord =  mysql_fetch_assoc($result);
		$result = mysql_query("SELECT * FROM $tableName_av WHERE actualValue = 1");
		$lastValues =  mysql_fetch_assoc($result);
		//echo "dateTime: $lastValues[dateTime]\r\n";
		//echo "timeStamp: $lastRecord[timeStamp]\r\n";
	
		$letzteAktualisierung = date("d.m.Y H:i", $lastRecord['timeStamp']*60);
		//echo "letzteAktualisierung: $letzteAktualisierung\r\n";
	
		$result = mysql_query("SELECT * FROM $tableName_config WHERE config = 1");
		$config = mysql_fetch_assoc($result);
		
		$result = mysql_query("SELECT * FROM $tableName_chartOpt WHERE chartOpt = 1");
		$chartOpt = mysql_fetch_assoc($result);	
		
		//$YOffset = $chartOpt['tempOffset'];
		
		if ($chartDate == "actualMinute") $Min2 = $lastRecord['timeStamp'];	
		else $Min2 = intval(DateTime::createFromFormat("d.m.Y H:i", $chartDate)->getTimestamp() / 60);
		//$XScale = $chartOpt['XScale'];
		$XRange = 14*60 * $XScale;
		$Min1 = $Min2 -$XRange;

		$ImageXSize  = 1040;
		$ImageYSize  = 585;
		$LeftBorder  = 30;
		$RightBorder = 60;
		$TopBorder   = 30;
		$BotBorder   = 80;
		$ChartWith   = 14*60;
		$ChartHighth = 20*10*2;

		//$im = imagecreatefromjpeg("/mnt/share/proto/images/backgroundWaermetauscher_1040px.jpg");
		$im = imagecreate($ImageXSize, $ImageYSize);

		// Farben, Schriftart
		$grau = imagecolorallocate($im, 120, 120, 120);
		$hellgrau = imagecolorallocate($im, 150, 150, 150);
		$LL2color = imagecolorallocate($im, 180, 180, 180);
		$bgColor = imagecolorallocate($im, 230, 230, 230);
		//imagefill ($im, 0, 0, $grau);
		$s = imagecolorallocate($im, 0, 0, 0);
		$rot = imagecolorallocate($im, 255, 0, 0);
		$grün = imagecolorallocate($im, 0, 200, 0);
		$gelb = imagecolorallocate($im, 200, 180, 0);
		$blau = imagecolorallocate($im, 0, 0, 255);
		$darkBlue = imagecolorallocate($im, 0, 0, 0x8B);
		$Cyan = imagecolorallocate($im, 0, 0xFF, 0xFF);
		$Teal = imagecolorallocate($im, 0, 0x80, 0x80);
		$schriftart = "/usr/share/fonts/truetype/freefont/FreeSansOblique.ttf";

		imagefill($im,0,0,$bgColor);		

		// Gitternetz, Beschriftung
		for($i=0; $i<5; $i++) {
			imageline($im, $LeftBorder, $ImageYSize - $BotBorder - $i * 100, $LeftBorder + $ChartWith,  $ImageYSize - $BotBorder - $i * 100, $hellgrau);
			imagettftext($im, 11, 0 , 5, $ImageYSize - $BotBorder - $i * 100, $s, $schriftart, $i*$YScale + $YOffset);
			imagettftext($im, 11, 0 , 25, $ImageYSize - $BotBorder - $i * 100 -2, $grau, $schriftart, ($i*5*4+ 10) . " %");
		}
		imagettftext($im, 12, 0, 3, $TopBorder, $s, $schriftart, "°C g/m&sup3;");

		$time2 = $Min2 % 60;
		$timestamp =  $Min2*60 - 14*60* 60 * $XScale - $time2 * 60;
		for($i=1; $i<=14; $i++) {
		    imageline($im, $LeftBorder + $i * $ChartWith/14 - $time2/$XScale, $TopBorder, $LeftBorder + $i * $ChartWith/14 - $time2/$XScale , $ImageYSize - $BotBorder +35, $hellgrau);
			imagettftext($im, 10, 0, 12 + $i * $ChartWith/14 - $time2/$XScale, $ImageYSize - $BotBorder + 50, $s, $schriftart, date("H:i", $timestamp+= 60*60*$XScale));
			if (strcmp(date("d.m", $timestamp),date("d.m", $timestamp-60*60*$XScale))) {
				imagettftext($im, 12, 0, 12 + $i * $ChartWith/14 - $time2/$XScale, $ImageYSize - $BotBorder + 70, $s, $schriftart, date("d.m.", $timestamp));	
			}
		}


		// draw all lines
		$YOffset *= -10;
		$res = mysql_query("SELECT * FROM " . $tableName . " WHERE timeStamp >= $Min1 AND timeStamp <= $Min2");
		if ($dsatz = mysql_fetch_assoc($res)) {
			
			$X0 = 0;			

			$LuefterleistungAbluft = $dsatz['LuefterleistungAbluft'];
			$LuefterleistungSoll = $dsatz['LuefterleistungSoll'];
			$Abluft       = $dsatz['Abluft'];
			$Zuluft       = $dsatz['Zuluft'];
			$Fortluft     = $dsatz['Fortluft'];
			$Aussenluft   = $dsatz['Aussenluft'];
			$Wirkungsgrad = $dsatz['Wirkungsgrad'];
			$AbluftRH     = $dsatz['AbluftRH'];
			$ZuluftRH     = $dsatz['ZuluftRH'];
			$FortluftRH   = $dsatz['FortluftRH'];
			$AussenluftRH = $dsatz['AussenluftRH'];
			$AbluftAH     = $dsatz['AbluftAH'];
			$ZuluftAH     = $dsatz['ZuluftAH'];
			$FortluftAH   = $dsatz['FortluftAH'];
			$AussenluftAH = $dsatz['AussenluftAH'];
			$AS_AH        = $dsatz['AS_AH'];
			
			while ($dsatz = mysql_fetch_assoc($res)) {
				if (intval(($dsatz['timeStamp'] - $Min1)/$XScale) >= $X0) {
					$X = intval(($dsatz['timeStamp'] - $Min1)/$XScale);
					if ($X0 == 0) $X0 = $X;
					if (($X - $X0) > 30) {
						$X0 = $X;	// suppress interpolation
						$LuefterleistungAbluft = $dsatz['LuefterleistungAbluft'];
						$LuefterleistungSoll = $dsatz['LuefterleistungSoll'];
						$Abluft       = $dsatz['Abluft'];
						$Zuluft       = $dsatz['Zuluft'];
						$Fortluft     = $dsatz['Fortluft'];
						$Aussenluft   = $dsatz['Aussenluft'];
						$Wirkungsgrad = $dsatz['Wirkungsgrad'];
						$AbluftRH     = $dsatz['AbluftRH'];
						$ZuluftRH     = $dsatz['ZuluftRH'];
						$FortluftRH   = $dsatz['FortluftRH'];
						$AussenluftRH = $dsatz['AussenluftRH'];
						$AbluftAH     = $dsatz['AbluftAH'];
						$ZuluftAH     = $dsatz['ZuluftAH'];
						$FortluftAH   = $dsatz['FortluftAH'];
						$AussenluftAH = $dsatz['AussenluftAH'];
						$AS_AH        = $dsatz['AS_AH'];
					}
					// draw optional lines
					if ($chartOpt['leistungAbluft']) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($LuefterleistungAbluft*5 -50), $LeftBorder + $X +1,  $ImageYSize - $BotBorder  - ($dsatz['LuefterleistungAbluft']*5-50), $LL2color);
					$LuefterleistungAbluft = $dsatz['LuefterleistungAbluft'];
					if ($chartOpt['leistung']) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($LuefterleistungSoll*5 -50), $LeftBorder + $X +1,  $ImageYSize - $BotBorder  - ($dsatz['LuefterleistungSoll']*5-50), $s);
					$LuefterleistungSoll = $dsatz['LuefterleistungSoll'];
					if ($chartOpt['AbluftRH']) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($AbluftRH*5 -50), $LeftBorder + $X +1,  $ImageYSize - $BotBorder  - ($dsatz['AbluftRH']*5-50), $rot);
					$AbluftRH = $dsatz['AbluftRH'];
					if ($chartOpt['ZuluftRH']) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($ZuluftRH*5 -50), $LeftBorder + $X +1,  $ImageYSize - $BotBorder  - ($dsatz['ZuluftRH']*5-50), $gelb);
					$ZuluftRH = $dsatz['ZuluftRH'];
					if ($chartOpt['FortluftRH']) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($FortluftRH*5 -50), $LeftBorder + $X +1,  $ImageYSize - $BotBorder  - ($dsatz['FortluftRH']*5-50), $grün);
					$FortluftRH = $dsatz['FortluftRH'];
					if ($chartOpt['AussenluftRH']) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($AussenluftRH*5 -50), $LeftBorder + $X +1,  $ImageYSize - $BotBorder  - ($dsatz['AussenluftRH']*5-50), $blau);
					$AussenluftRH = $dsatz['AussenluftRH'];
					if ($chartOpt['AbluftAH']) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($AbluftAH*10 + $YOffset)*10/$YScale, $LeftBorder + $X +1,  $ImageYSize - $BotBorder  - ($dsatz['AbluftAH']*10 + $YOffset)*10/$YScale, $rot);
					$AbluftAH = $dsatz['AbluftAH'];
					if ($chartOpt['ZuluftAH']) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($ZuluftAH*10 + $YOffset)*10/$YScale, $LeftBorder + $X +1,  $ImageYSize - $BotBorder  - ($dsatz['ZuluftAH']*10 + $YOffset)*10/$YScale, $gelb);
					$ZuluftAH = $dsatz['ZuluftAH'];
					if ($chartOpt['FortluftAH']) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($FortluftAH*10 + $YOffset)*10/$YScale, $LeftBorder + $X +1,  $ImageYSize - $BotBorder  - ($dsatz['FortluftAH']*10 + $YOffset)*10/$YScale, $grün);
					$FortluftAH = $dsatz['FortluftAH'];
					if ($chartOpt['AussenluftAH']) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($AussenluftAH*10 + $YOffset)*10/$YScale, $LeftBorder + $X +1,  $ImageYSize - $BotBorder  - ($dsatz['AussenluftAH']*10 + $YOffset)*10/$YScale, $blau);
					$AussenluftAH = $dsatz['AussenluftAH'];
					if ($chartOpt['waermeRueckgew']) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($Wirkungsgrad*5 -50), $LeftBorder + $X +1,  $ImageYSize - $BotBorder  - ($dsatz['Wirkungsgrad']*5-50), $Teal);
					$Wirkungsgrad = $dsatz['Wirkungsgrad'];
					if ($chartOpt['AS_AH']) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($AS_AH*10 + $YOffset)*2, $LeftBorder + $X +1,  $ImageYSize - $BotBorder  - ($dsatz['AS_AH']*10 + $YOffset)*2, $grau);
					$AS_AH= $dsatz['AS_AH'];
					// draw main lines
					for ($k= -1; $k<=1; $k++) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($Abluft*10 + $YOffset)*10/$YScale     +$k, $LeftBorder + $X+1, $ImageYSize - $BotBorder  - ($dsatz['Abluft']*10     + $YOffset)*10/$YScale +$k, $rot);
					$Abluft =  $dsatz['Abluft'];
					for ($k= -1; $k<=1; $k++) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($Zuluft*10 + $YOffset)*10/$YScale     +$k, $LeftBorder + $X+1, $ImageYSize - $BotBorder  - ($dsatz['Zuluft']*10     + $YOffset)*10/$YScale +$k, $gelb);
					$Zuluft =  $dsatz['Zuluft'];
					for ($k= -1; $k<=1; $k++) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($Fortluft*10 + $YOffset)*10/$YScale   +$k, $LeftBorder + $X+1, $ImageYSize - $BotBorder  - ($dsatz['Fortluft']*10   + $YOffset)*10/$YScale +$k, $grün);
					$Fortluft =  $dsatz['Fortluft'];
					for ($k= -1; $k<=1; $k++) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($Aussenluft*10 + $YOffset)*10/$YScale +$k, $LeftBorder + $X+1, $ImageYSize - $BotBorder  - ($dsatz['Aussenluft']*10 + $YOffset)*10/$YScale +$k, $blau);
					$Aussenluft =  $dsatz['Aussenluft'];
					$X0 = $X +1;
				}
			}
			
			if ($chartDate == "actualMinute") {	// add actual value to the end of the diagram
				$res = mysql_query("SELECT * FROM " . $tableName_av . " WHERE actualValue = 1");
				if ($dsatz = mysql_fetch_assoc($res)) {
					$X = $X +1;
					// draw optional lines
					if ($chartOpt['leistungAbluft']) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($LuefterleistungAbluft*5 -50), $LeftBorder + $X +1,  $ImageYSize - $BotBorder  - ($dsatz['LuefterleistungAbluft']*5-50), $LL2color);
					if ($chartOpt['leistung']) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($LuefterleistungSoll*5 -50), $LeftBorder + $X +1,  $ImageYSize - $BotBorder  - ($dsatz['LuefterleistungSoll']*5-50), $s);
					if ($chartOpt['AbluftRH']) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($AbluftRH*5 -50), $LeftBorder + $X +1,  $ImageYSize - $BotBorder  - ($dsatz['AbluftRH']*5-50), $rot);
					if ($chartOpt['ZuluftRH']) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($ZuluftRH*5 -50), $LeftBorder + $X +1,  $ImageYSize - $BotBorder  - ($dsatz['ZuluftRH']*5-50), $gelb);
					if ($chartOpt['FortluftRH']) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($FortluftRH*5 -50), $LeftBorder + $X +1,  $ImageYSize - $BotBorder  - ($dsatz['FortluftRH']*5-50), $grün);
					if ($chartOpt['AussenluftRH']) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($AussenluftRH*5 -50), $LeftBorder + $X +1,  $ImageYSize - $BotBorder  - ($dsatz['AussenluftRH']*5-50), $blau);
					if ($chartOpt['ZuluftAH']) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($ZuluftAH*10 + $YOffset)*10/$YScale, $LeftBorder + $X +1,  $ImageYSize - $BotBorder  - ($dsatz['ZuluftAH']*10 + $YOffset)*10/$YScale, $gelb);
					if ($chartOpt['FortluftAH']) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($FortluftAH*10 + $YOffset)*10/$YScale, $LeftBorder + $X +1,  $ImageYSize - $BotBorder  - ($dsatz['FortluftAH']*10 + $YOffset)*10/$YScale, $grün);
					if ($chartOpt['AbluftAH']) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($AbluftAH*10 + $YOffset)*10/$YScale, $LeftBorder + $X +1,  $ImageYSize - $BotBorder  - ($dsatz['AbluftAH']*10 + $YOffset)*10/$YScale, $rot);
					if ($chartOpt['AussenluftAH']) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($AussenluftAH*10 + $YOffset)*10/$YScale, $LeftBorder + $X +1,  $ImageYSize - $BotBorder  - ($dsatz['AussenluftAH']*10 + $YOffset)*10/$YScale, $blau);
					if ($chartOpt['waermeRueckgew']) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($Wirkungsgrad*5 -50), $LeftBorder + $X +1,  $ImageYSize - $BotBorder  - ($dsatz['Wirkungsgrad']*5-50), $Teal);
					if ($chartOpt['AS_AH']) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($AS_AH*10 + $YOffset)*2, $LeftBorder + $X +1,  $ImageYSize - $BotBorder  - ($dsatz['AS_AH']*10 + $YOffset)*2, $grau);
					// draw main lines
					for ($k= -1; $k<=1; $k++) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($Abluft*10 + $YOffset)*10/$YScale     +$k, $LeftBorder + $X+1, $ImageYSize - $BotBorder  - ($dsatz['Abluft']*10     + $YOffset)*10/$YScale +$k, $rot);
					for ($k= -1; $k<=1; $k++) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($Zuluft*10 + $YOffset)*10/$YScale     +$k, $LeftBorder + $X+1, $ImageYSize - $BotBorder  - ($dsatz['Zuluft']*10     + $YOffset)*10/$YScale +$k, $gelb);
					for ($k= -1; $k<=1; $k++) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($Fortluft*10 + $YOffset)*10/$YScale   +$k, $LeftBorder + $X+1, $ImageYSize - $BotBorder  - ($dsatz['Fortluft']*10   + $YOffset)*10/$YScale +$k, $grün);
					for ($k= -1; $k<=1; $k++) imageline($im, $LeftBorder + $X0, $ImageYSize - $BotBorder - ($Aussenluft*10 + $YOffset)*10/$YScale +$k, $LeftBorder + $X+1, $ImageYSize - $BotBorder  - ($dsatz['Aussenluft']*10 + $YOffset)*10/$YScale +$k, $blau);
				}
			}
		}
		
		
	} 
	
	$linePos = 60;
	imagettftext($im, 12, 0, $LeftBorder + $ChartWith/2 - 100, 20, $s, $schriftart,"WebChart  -  " . $ChartTitle);
	imagettftext($im, 12, 0 , $LeftBorder + $ChartWith + 15, $TopBorder + $linePos,     $rot,  $schriftart, "Abluft: " . sprintf("%2.1f °C", $lastValues['Abluft']));
	imagettftext($im, 11, 0 , $LeftBorder + $ChartWith + 18, $TopBorder + $linePos+=16, $rot,  $schriftart, "H: " . sprintf("%2.1f%% %2.1f g/m&sup3;", $lastValues['AbluftRH'], $lastValues['AbluftAH']));
	imagettftext($im, 12, 0 , $LeftBorder + $ChartWith + 15, $TopBorder + $linePos+=24, $gelb, $schriftart, "Zuluft: " . sprintf("%2.1f °C", $lastValues['Zuluft']));
	imagettftext($im, 11, 0 , $LeftBorder + $ChartWith + 18, $TopBorder + $linePos+=16, $gelb, $schriftart, "H: " . sprintf("%2.1f%% %2.1f g/m&sup3;", $lastValues['ZuluftRH'], $lastValues['ZuluftAH']));
	imagettftext($im, 12, 0 , $LeftBorder + $ChartWith + 15, $TopBorder + $linePos+=24, $grün, $schriftart, "Fortluft: " . sprintf("%2.1f °C", $lastValues['Fortluft']));
	imagettftext($im, 11, 0 , $LeftBorder + $ChartWith + 18, $TopBorder + $linePos+=16, $grün, $schriftart, "H: " . sprintf("%2.1f%% %2.1f g/m&sup3;", $lastValues['FortluftRH'], $lastValues['FortluftAH']));
	imagettftext($im, 12, 0 , $LeftBorder + $ChartWith + 15, $TopBorder + $linePos+=24, $blau, $schriftart, "Aussenluft: " . sprintf("%2.1f °C", $lastValues['Aussenluft']));
	imagettftext($im, 11, 0 , $LeftBorder + $ChartWith + 18, $TopBorder + $linePos+=16, $blau, $schriftart, "H: " . sprintf("%2.1f%% %2.1f g/m&sup3;", $lastValues['AussenluftRH'], $lastValues['AussenluftAH']));
	imagettftext($im, 12, 0 , $LeftBorder + $ChartWith + 15, $TopBorder + $linePos+=30, $Teal, $schriftart, "Wärmerückgewinn:");
	imagettftext($im, 12, 0 , $LeftBorder + $ChartWith + 21, $TopBorder + $linePos+=20, $Teal, $schriftart, sprintf("%2.1f %%", $lastValues['Wirkungsgrad']));
	imagettftext($im, 12, 0 , $LeftBorder + $ChartWith + 15, $TopBorder + $linePos+=30, $s,    $schriftart, "Lüfterleistung:");
	imagettftext($im, 12, 0 , $LeftBorder + $ChartWith + 21, $TopBorder + $linePos+=20, $s,    $schriftart, sprintf("Soll:      %d %%", $lastValues['LuefterleistungSoll']));
	imagettftext($im, 12, 0 , $LeftBorder + $ChartWith + 21, $TopBorder + $linePos+=20, $s,    $schriftart, sprintf("Abluft:    %d %%", $lastValues['LuefterleistungAbluft']));
	imagettftext($im, 12, 0 , $LeftBorder + $ChartWith + 21, $TopBorder + $linePos+=20, $s,    $schriftart, sprintf("Zuluft:    %d %%", $lastValues['LuefterleistungZuluft']));
	imagettftext($im, 12, 0 , $LeftBorder + $ChartWith + 15, $TopBorder + $linePos+=30, $s,    $schriftart, "Kondensierend:");
	imagettftext($im, 12, 0 , $LeftBorder + $ChartWith + 21, $TopBorder + $linePos+=20, $s,    $schriftart, sprintf("%2.1f g/m&sup3;", $lastValues['Kondensierend']));
	imagettftext($im, 12, 0 , $LeftBorder + $ChartWith + 15, $TopBorder + $linePos+=30, $s,    $schriftart, "Feuchtigkeitsabfuhr:");
	imagettftext($im, 12, 0 , $LeftBorder + $ChartWith + 21, $TopBorder + $linePos+=20, $s,    $schriftart, sprintf("%2.1f g/m&sup3;", $lastValues['Feuchtigkeitsabfuhr']));
	imagettftext($im, 12, 0 , $LeftBorder + $ChartWith + 15, $TopBorder + $linePos+=30, $s,    $schriftart, $config['Mode']);
	imagettftext($im, 12, 0 , $LeftBorder + $ChartWith + 55, $TopBorder + $linePos    , $rot,  $schriftart, $config['FTRmode']);
	imagettftext($im, 11, 0 , $LeftBorder + $ChartWith + 15, $TopBorder + $linePos+=30, $grau, $schriftart, sprintf("AS_AH: %2.1f g/m&sup3;", $lastValues['AS_AH']));
	
	imagettftext($im, 9, 0 , $LeftBorder + $ChartWith, $ImageYSize - $BotBorder + 78, $s, $schriftart, " aktualisiert: $letzteAktualisierung");
	
		
	// Grafik darstellen
	imagejpeg($im, $webChartDir . "/easyWebChart$suffix.jpg");
	//chmod($webChartDir . "/easyWebChart.jpg", 0777);
	
	// Speicher freigeben
	imagedestroy($im);
}

?>
