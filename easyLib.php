<?php


function createAndInitTables() {

	include "locals/locals.php";
	include "const.php";

	// actual values table (MEMORY)
	$tblName = $tableName_av;
	$sql = "DROP TABLE IF EXISTS easyLuefter.$tblName";
	mysql_query($sql);
	$sql = 'CREATE TABLE IF NOT EXISTS `' . $tblName . '` (
	  `actualValue` int(11) DEFAULT 0,
	  `timeStamp` int(11) DEFAULT 0,
	  `dateTime` varchar(13) DEFAULT 0,
	  `hourOfDay` int(11) DEFAULT 0,
	  `Abluft` float DEFAULT 0,
	  `Zuluft` float DEFAULT 0,
	  `Fortluft` float DEFAULT 0,
	  `Aussenluft` float DEFAULT 0,
	  `AbluftRH` float DEFAULT 0,
	  `ZuluftRH` float DEFAULT 0,
	  `FortluftRH` float DEFAULT 0,
	  `AussenluftRH` float DEFAULT 0,
	  `AbluftAH` float DEFAULT 0,
	  `ZuluftAH` float DEFAULT 0,
	  `FortluftAH` float DEFAULT 0,
	  `AussenluftAH` float DEFAULT 0,
	  `Kondensierend` float DEFAULT 0,
	  `Feuchtigkeitsabfuhr` float DEFAULT 0,
	  `Wirkungsgrad` float DEFAULT 0,
	  `LuefterleistungSoll` int(11) DEFAULT 0,
	  `LuefterleistungAbluft` int(11) DEFAULT 0,
	  `LuefterleistungZuluft` int(11) DEFAULT 0,
	  `AS_AH` float DEFAULT 0
	) ENGINE=MEMORY DEFAULT CHARSET=latin1;';
	//echo $sql . "\n";
	mysql_query($sql);
	$sql = 'ALTER TABLE `' . $tblName . '` 
	  ADD PRIMARY KEY (`actualValue`)';
	//echo $sql . "\n";
	mysql_query($sql);
	mysql_query("INSERT INTO $tblName (actualValue) VALUES(1)");	
	
	
	// last mesures table (MEMORY)
	$tblName = $tableName_lm;
	$sql = "DROP TABLE IF EXISTS easyLuefter.$tblName";
	mysql_query($sql);
	$sql = 'CREATE TABLE IF NOT EXISTS `' . $tblName . '` (
	  `timeStamp` int(11) DEFAULT 0,
	  `Abluft` float DEFAULT 0,
	  `Zuluft` float DEFAULT 0,
	  `Fortluft` float DEFAULT 0,
	  `Aussenluft` float DEFAULT 0,
	  `AbluftRH` float DEFAULT 0,
	  `ZuluftRH` float DEFAULT 0,
	  `FortluftRH` float DEFAULT 0,
	  `AussenluftRH` float DEFAULT 0
	) ENGINE=MEMORY DEFAULT CHARSET=latin1;';
	//echo $sql . "\n";
	mysql_query($sql);
	$sql = 'ALTER TABLE `' . $tblName . '` 
	  ADD PRIMARY KEY (`timeStamp`)';
	//echo $sql . "\n";
	mysql_query($sql);
	
	
	// variables table (MEMORY)
	$tblName = $tableName_vars;
	$sql = "DROP TABLE IF EXISTS easyLuefter.$tblName";
	mysql_query($sql);
	$sql = 'CREATE TABLE IF NOT EXISTS `' . $tblName . '` (
	  `vars` int(11) DEFAULT 0,
	  `setSpeed` int(11) DEFAULT 0,
	  `totSpeed` int(11) DEFAULT 0,
	  `minMaxTimer` int(11) DEFAULT 0,
	  `LuefterleistungAbluft` int(11) DEFAULT 0,
	  `LuefterleistungZuluft` int(11) DEFAULT 0,
	  `WirkungsgradZuluft` float DEFAULT 0,
	  `WirkungsgradAbluft` float DEFAULT 0,
	  `Feuchtigkeitsabfuhr` float DEFAULT 0,
	  `Feuchterueckgewinnung` float DEFAULT 0,
	  `Kondensierend` float DEFAULT 0,
	  `Feuchterueckgewinnungsgrad` float DEFAULT 0,
	  `FSavRH01` float DEFAULT 0,
	  `FS1nCount` int(11) DEFAULT 0,
	  `FSavRH02` float DEFAULT 0,
	  `AStemp` float DEFAULT 0,
	  `AS_AH` float DEFAULT 0,
	  `Icomponent` float DEFAULT 0,
	  `Dcomponent` float DEFAULT 0,
	  `dSpeed` float DEFAULT 0,
	  `RHSpeed` int(11) DEFAULT 0,
	  `enfnCount` int(11) DEFAULT 0
	) ENGINE=MEMORY DEFAULT CHARSET=latin1;';
	//echo $sql . "\n";
	mysql_query($sql);
	$sql = 'ALTER TABLE `' . $tblName . '` 
	  ADD PRIMARY KEY (`vars`)';
	//echo $sql . "\n";
	mysql_query($sql);
	mysql_query("INSERT INTO $tblName (vars) VALUES(1)");	
	
	
	// mesures table (if not exist)
	$tblName = $tableName;
	$sql = 'CREATE TABLE IF NOT EXISTS `' . $tblName . '` (
	  `timeStamp` int(11) DEFAULT 0,
	  `hourOfDay` int(11) DEFAULT 0,
	  `dateTime` varchar(13) DEFAULT 0,
	  `Abluft` float DEFAULT 0,
	  `Zuluft` float DEFAULT 0,
	  `Fortluft` float DEFAULT 0,
	  `Aussenluft` float DEFAULT 0,
	  `AbluftRH` float DEFAULT 0,
	  `ZuluftRH` float DEFAULT 0,
	  `FortluftRH` float DEFAULT 0,
	  `AussenluftRH` float DEFAULT 0,
	  `AbluftAH` float DEFAULT 0,
	  `ZuluftAH` float DEFAULT 0,
	  `FortluftAH` float DEFAULT 0,
	  `AussenluftAH` float DEFAULT 0,
	  `Kondensierend` float DEFAULT 0,
	  `Feuchtigkeitsabfuhr` float DEFAULT 0,
	  `Wirkungsgrad` float DEFAULT 0,
	  `LuefterleistungSoll` int(11) DEFAULT 0,
	  `dSpeed` float DEFAULT 999,
	  `LuefterleistungAbluft` int(11) DEFAULT 0,
	  `LuefterleistungZuluft` int(11) DEFAULT 0,
	  `AS_AH` float DEFAULT 0
	) ENGINE=InnoDB DEFAULT CHARSET=latin1;';
	//echo $sql . "\n";
	mysql_query($sql);
	$sql = 'ALTER TABLE `' . $tblName . '` 
	  ADD PRIMARY KEY (`timeStamp`)';
	//echo $sql . "\n";
	mysql_query($sql);
	
	
	// chart options table (if not exist)
	$tblName = $tableName_chartOpt;
	$sql = 'CREATE TABLE IF NOT EXISTS `' . $tblName . '` (
	  `chartOpt` int(11) NOT NULL DEFAULT 0,
	  `XScale` int(11) NOT NULL DEFAULT 0,
	  `YScale` int(11) NOT NULL DEFAULT 0,
	  `tempOffset` int(11) NOT NULL DEFAULT 0,
	  `leistung` int(10) NOT NULL DEFAULT 0,
	  `leistungAbluft` int(10) NOT NULL DEFAULT 0,
	  `waermeRueckgew` int(11) NOT NULL DEFAULT 0,
	  `AbluftRH` int(11) NOT NULL DEFAULT 0,
	  `AussenluftRH` int(11) NOT NULL DEFAULT 0,
	  `AbluftAH` int(11) NOT NULL DEFAULT 0,
	  `AussenluftAH` int(11) NOT NULL DEFAULT 0,
	  `AS_AH` int(11) NOT NULL DEFAULT 0
	) ENGINE=InnoDB DEFAULT CHARSET=latin1;';
	//echo $sql . "\n";
	mysql_query($sql);
	$sql = 'ALTER TABLE `' . $tblName . '` 
	  ADD PRIMARY KEY (`chartOpt`)';
	//echo $sql . "\n";
	mysql_query($sql);
	mysql_query("INSERT INTO $tblName (chartOpt) VALUES(1)");	
	
		
	// config table (if not exist)
	$tblName = $tableName_config;
	$sql = 'CREATE TABLE IF NOT EXISTS `' . $tblName . '` (
	  `config` int(11) NOT NULL DEFAULT 0,
	  `Mode` varchar(3) NOT NULL DEFAULT 0,
	  `lastMode` varchar(3) NOT NULL DEFAULT 0,
	  `FTRmode` varchar(5) NOT NULL DEFAULT 0,
	  `manSoll` int(11) NOT NULL DEFAULT 0,
	  `tmrNr` int(11) NOT NULL DEFAULT 0,
	  `tmrSpeed` int(11) NOT NULL DEFAULT 0,
	  `minTimerInterval` int(11) NOT NULL DEFAULT 0,
	  `maxTimerInterval` int(11) NOT NULL DEFAULT 0,
	  `minLLAbluft` int(11) NOT NULL DEFAULT 0,
	  `minLLZuluft` int(11) NOT NULL DEFAULT 0,
	  `maxLLAbluft` int(11) NOT NULL DEFAULT 0,
	  `maxLLZuluft` int(11) NOT NULL DEFAULT 0,
	  `tempCal1` float NOT NULL DEFAULT 0,
	  `tempCal2` float NOT NULL DEFAULT 0,
	  `tempCal3` float NOT NULL DEFAULT 0,
	  `tempCal4` float NOT NULL DEFAULT 0,
	  `RHCal1` float NOT NULL DEFAULT 0,
	  `RHCal2` float NOT NULL DEFAULT 0,
	  `RHCal3` float NOT NULL DEFAULT 0,
	  `RHCal4` float NOT NULL DEFAULT 0,
	  `sym` int(11) NOT NULL DEFAULT 0,
	  `uSym` int(11) NOT NULL DEFAULT 0,
	  `Pvalue` float NOT NULL DEFAULT 0,
	  `Ivalue` float NOT NULL DEFAULT 0,
	  `Dvalue` float NOT NULL DEFAULT 0,
	  `timeConst` int(11) NOT NULL DEFAULT 0,
	  `Icomponent` float NOT NULL DEFAULT 0
	) ENGINE=InnoDB DEFAULT CHARSET=latin1;';
	//echo $sql . "\n";
	mysql_query($sql);
	$sql = 'ALTER TABLE `' . $tblName . '` 
	  ADD PRIMARY KEY (`config`)';
	//echo $sql . "\n";
	mysql_query($sql);
	mysql_query("INSERT INTO $tblName (config) VALUES(1)");	
	//mysql_query("UPDATE $tblName SET `SWversion` = '1.007' WHERE `$tableName_config`.`config` = 1");
	
	
	// humidity functions config table (if not exist)
	$tblName = $tableName_hum;
	$sql = 'CREATE TABLE IF NOT EXISTS `' . $tblName . '` (
	  `hum` int(11) NOT NULL DEFAULT 0,
	  `FSavCount1` int(11) NOT NULL DEFAULT 0,
	  `FSgap1` float NOT NULL DEFAULT 0,
	  `FSincL1` int(11) NOT NULL DEFAULT 0,
	  `FSmaxPosOffset1` int(11) NOT NULL DEFAULT 0,
	  `FS1n` int(11) NOT NULL DEFAULT 0,
	  `FSavCount2` int(11) NOT NULL DEFAULT 0,
	  `FSgap2` float NOT NULL DEFAULT 0,
	  `FSincL2` int(11) NOT NULL DEFAULT 0,
	  `FSmaxPosOffset2` int(11) NOT NULL DEFAULT 0,
	  `ASpercent` int(11) NOT NULL DEFAULT 0,
	  `ASmaxRH` float NOT NULL DEFAULT 0,
	  `ASposOffset` int(11) NOT NULL DEFAULT 0,
	  `ASnegOffset` int(11) NOT NULL DEFAULT 0,
	  `RHmax` int(11) NOT NULL DEFAULT 0,
	  `RHmaxPosOffset` int(11) NOT NULL DEFAULT 0,
	  `RHmaxNegOffset` int(11) NOT NULL DEFAULT 0,
	  `RHmin` int(11) NOT NULL DEFAULT 0,
	  `RHminPosOffset` int(11) NOT NULL DEFAULT 0,
	  `RHminNegOffset` int(11) NOT NULL DEFAULT 0,
	  `tempMax` float NOT NULL DEFAULT 0,
	  `tempMaxPosOffset` int(11) NOT NULL DEFAULT 0,
	  `tempMaxNegOffset` int(11) NOT NULL DEFAULT 0,
	  `tempMin` float NOT NULL DEFAULT 0,
	  `tempMinPosOffset` int(11) NOT NULL DEFAULT 0,
	  `tempMinNegOffset` int(11) NOT NULL DEFAULT 0,
	  `enfMaxPosOffset` int(11) NOT NULL DEFAULT 0,
	  `enfN` int(11) NOT NULL DEFAULT 0
	) ENGINE=InnoDB DEFAULT CHARSET=latin1;';
	//echo $sql . "\n";
	mysql_query($sql);
	$sql = 'ALTER TABLE `' . $tblName . '` 
	  ADD PRIMARY KEY (`hum`)';
	//echo $sql . "\n";
	mysql_query($sql);
	mysql_query("INSERT INTO $tblName (hum) VALUES(1)");	
	
	
	// timer functions config table (if not exist)
	$tblName = $tableName_tmr;
	$sql = 'CREATE TABLE IF NOT EXISTS `' . $tblName . '` (
	  `tmrNr` int(11) DEFAULT 0,
	  `einAus` int(11) DEFAULT 0,
	  `hour` int(11) DEFAULT 10,
	  `min` int(11) DEFAULT 0,
	  `DoW` int(11) DEFAULT 0,
	  `Soll` int(11) DEFAULT 40
	) ENGINE=InnoDB DEFAULT CHARSET=latin1;';
	//echo $sql . "\n";
	mysql_query($sql);
	$sql = 'ALTER TABLE `' . $tblName . '` 
	  ADD PRIMARY KEY (`tmrNr`)';
	//echo $sql . "\n";
	mysql_query($sql);
	mysql_query("INSERT INTO $tblName (tmrNr, einAus) VALUES(1,1)");	
	mysql_query("INSERT INTO $tblName (tmrNr, einAus, hour, Soll) VALUES(2, 1, 22, 30)");	
	mysql_query("INSERT INTO $tblName (tmrNr) VALUES(3)");	
	mysql_query("INSERT INTO $tblName (tmrNr) VALUES(4)");	
	mysql_query("INSERT INTO $tblName (tmrNr) VALUES(5)");	
	mysql_query("INSERT INTO $tblName (tmrNr) VALUES(6)");	
	
	// fan control table
	$tblName = $tableName_fanControl;
	$sql = 'CREATE TABLE IF NOT EXISTS `' . $tblName . '` (
	  `fanControl` int(11) NOT NULL DEFAULT 0,
	  `abluftSpeed1` int(11) NOT NULL DEFAULT 0,
	  `abluftSpeed2` int(11) NOT NULL DEFAULT 0,
	  `abluftSpeed3` int(11) NOT NULL DEFAULT 0,
	  `abluftSpeed4` int(11) NOT NULL DEFAULT 0,
	  `zuluftSpeed1` int(11) NOT NULL DEFAULT 0,
	  `zuluftSpeed2` int(11) NOT NULL DEFAULT 0,
	  `zuluftSpeed3` int(11) NOT NULL DEFAULT 0,
	  `zuluftSpeed4` int(11) NOT NULL DEFAULT 0
	) ENGINE=InnoDB DEFAULT CHARSET=latin1;';
	//echo $sql . "\n";
	mysql_query($sql);
	$sql = 'ALTER TABLE `' . $tblName . '` 
	  ADD PRIMARY KEY (`fanControl`)';
	//echo $sql . "\n";
	mysql_query($sql);
	mysql_query("INSERT INTO $tblName (fanControl) VALUES(1)");	
	
}


function calcAH($T,$r) {

	/* Magnus Formel
	r = relative Luftfeuchte
	T = Temperatur in Â°C
	TK = Temperatur in Kelvin (TK = T + 273.15)
	TD = Taupunkttemperatur in Â°C
	DD = Dampfdruck in hPa
	SDD = SÃ¤ttigungsdampfdruck in hPa
	
	Parameter:
	a = 7.5, b = 237.3 fÃ¼r T >= 0
	a = 7.6, b = 240.7 fÃ¼r T < 0 Ã¼ber Wasser (Taupunkt)
	a = 9.5, b = 265.5 fÃ¼r T < 0 Ã¼ber Eis (Frostpunkt)
	
	R* = 8314.3 J/(kmol*K) (universelle Gaskonstante)
	mw = 18.016 kg/kmol (Molekulargewicht des Wasserdampfes)
	AF = absolute Feuchte in g Wasserdampf pro m3 Luft
	
	Formeln:
	1.SDD(T) = 6.1078 * 10^((a*T)/(b+T))
	2.DD(r,T) = r/100 * SDD(T)
	3.r(T,TD) = 100 * SDD(TD) / SDD(T)
	4.TD(r,T) = b*v/(a-v) mit v(r,T) = log10(DD(r,T)/6.1078)
	5.AF(r,TK) = 10^5 * mw/R* * DD(r,T)/TK; AF(TD,TK) = 10^5 * mw/R* * SDD(TD)/TK
	*/
	if ($T >= 0 ) {
		$a = 7.5;
		$b = 237.3;
	} else { // bei Temp unter Null und Ã¼ber Wasser
    	$a = 7.6; 
    	$b = 240.7;
    }
	$R = 8314.3;
	$mw = 18.016;

	$TK = $T + 273.15;
	
	$SDD_T = 6.1078 * pow(10,($a*$T)/($b+$T));
	$DD  = $r/100 * $SDD_T;
	$AH = 100000 * $mw/$R * $DD/$TK;			
	//echo "Temp: $T\r\n";
	//echo "RH: $r\r\n";
	//echo "AH: $AH\r\n";
	return $AH;
}


function calcAS_AH($Aussenluft, $Abluft) {

include "locals/locals.php";
include "const.php";
 

	$res = mysql_query("SELECT * FROM $tableName_hum");
	$hum = mysql_fetch_assoc($res);


	$AStemp = $Aussenluft + ($Abluft - $Aussenluft) * $hum['ASpercent'] /100;
	//echo "AStemp: $AStemp \r\n";

	$AS_AH = calcAH($AStemp, $hum['ASmaxRH']);
	//echo "AbluftAH: $AS_AH\r\n";
	
	mysql_query("UPDATE $tableName_vars SET AStemp=$AStemp, AS_AH=$AS_AH WHERE vars = 1");
	
	return $AS_AH;
}

?>