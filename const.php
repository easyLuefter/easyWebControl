<?php	

$tableName = "easy1";
$tableName_av = "easy_av1";
$tableName_lm = "easy_lm1";
$tableName_config = "easy_config1";
$tableName_hum = "easy_hum1";
$tableName_tmr = "easy_tmr1";
$tableName_vars = "easy_vars1";
$tableName_chartOpt = "easy_chartOpt1";
$tableName_fanControl = "easy_fanControl1";

$ftp_server1="login-118.hoststar.ch";
$ftp_user_name1="easyControlPi.easyluefter.ch";
$ftp_user_pass1="Easy%111";
		
$tText 				= array("Abluft: ","Zuluft: ","Fortl.: ","Aussenl:");
$DoWIntervals 		= array("MO-SO","MO-FR","SA-SO","MO   ","DI   ","MI   ","DO   ","FR   ","SA   ","SO   ");
$DoWMap       		= array( 0x7F  ,0x3E   ,0x41   ,0x02   ,0x04   ,0x08   ,0x10   ,0x20   ,0x40   ,0x01);
$TimerIntervals     = array("15 Min  ", "30 Min  ", "1 Std   ","2 Std   ","3 Std   ","4 Std   ","6 Std   ","12 Std  ","1 Tag   ","2 Tage  ","3 Tage  ","1 Woche ","4 Wochen");
$TimerIntervalValue = array( 15       , 30        , 1*60      , 2*60     , 3*60     , 4*60     , 6*60     , 12*60    , 24*60    , 2*24*60  , 3*24*60  , 7*24*60  , 4*7*24*60);
$WeekDays 			= array("SO","MO","DI","MI","DO","FR","SA");
$Mode 				= array("MIN", "MAX", "MAN", "TIM");
$EinAus 			= array("AUS","EIN");
$sensName			= array("Abluft", "Zuluft", "Fortluft", "Aussenluft", "AbluftRH", "ZuluftRH", "FortluftRH", "AussenluftRH");
$calName			= array("tempCal1", "tempCal2", "tempCal3", "tempCal4", "RHCal1", "RHCal2", "RHCal3", "RHCal4");
$XScaleChoice		= array(1,4,12);

?>
