<?php

$res = mysql_query("SELECT * FROM $tableName_config");
$config = mysql_fetch_assoc($res);

if (!isset($config['SWversion'])) {
	mysql_query("ALTER TABLE `easy_config1` ADD `SWversion` FLOAT NOT NULL AFTER `config`");
	mysql_query("UPDATE `easyLuefter`.`easy_config1` SET `SWversion` = '0.9' WHERE `easy_config1`.`config` = 1");
	$res = mysql_query("SELECT * FROM $tableName_config");
	$config = mysql_fetch_assoc($res);
	$res = mysql_query("SELECT * FROM $tableName_config");
	$config = mysql_fetch_assoc($res);
}

if ($config['SWversion'] < 1)  {
	mysql_query("ALTER TABLE `easy_config1` CHANGE `Pvalue` `Pvalue` FLOAT(11) NOT NULL"); 
	mysql_query("ALTER TABLE `easy_config1` CHANGE `Ivalue` `Ivalue` FLOAT(11) NOT NULL"); 
	mysql_query("ALTER TABLE `easy_config1` CHANGE `Dvalue` `Dvalue` FLOAT(11) NOT NULL"); 
	mysql_query("UPDATE `easyLuefter`.`easy_config1` SET `SWversion` = '1' WHERE `easy_config1`.`config` = 1");
	mysql_query("UPDATE $tableName_config SET Pvalue = " . $config['Pvalue']/10 . " WHERE config = 1");
	mysql_query("UPDATE $tableName_config SET Ivalue = " . $config['Ivalue']/10 . " WHERE config = 1");
	mysql_query("UPDATE $tableName_config SET Dvalue = " . $config['Dvalue']/10 . " WHERE config = 1");
	mysql_query("UPDATE `easyLuefter`.`easy_config1` SET `SWversion` = '1' WHERE `easy_config1`.`config` = 1");
	$res = mysql_query("SELECT * FROM $tableName_config");
	$config = mysql_fetch_assoc($res);
}

if ($config['SWversion'] < 1.001)  {
	mysql_query("ALTER TABLE `$tableName_config` ADD `timeConst` INT(11) NOT NULL AFTER `Dvalue`");
	mysql_query("UPDATE `easyLuefter`.`$tableName_config` SET `timeConst` = '420' WHERE `easy_config1`.`config` = 1");
	mysql_query("UPDATE `easyLuefter`.`$tableName_config` SET `SWversion` = '1.001' WHERE `easy_config1`.`config` = 1");
	$res = mysql_query("SELECT * FROM $tableName_config");
	$config = mysql_fetch_assoc($res);
}
		
if ($config['SWversion'] < 1.002)  {
	mysql_query("ALTER TABLE `$tableName_chartOpt` ADD `XScale` INT(11) NOT NULL AFTER `chartOpt`");
	mysql_query("UPDATE `easyLuefter`.`$tableName_chartOpt` SET `XScale` = '4' WHERE `$tableName_chartOpt`.`chartOpt` = 1");
	mysql_query("ALTER TABLE `$tableName_config` ADD `minLLAbluft` INT(11) NOT NULL AFTER `maxTimerInterval`");
	mysql_query("UPDATE `easyLuefter`.`$tableName_config` SET `minLLAbluft` = '10' WHERE `$tableName_config`.`config` = 1");
	mysql_query("UPDATE `easyLuefter`.`$tableName_config` SET `SWversion` = '1.002' WHERE `$tableName_config`.`config` = 1");
	$res = mysql_query("SELECT * FROM $tableName_config");
	$config = mysql_fetch_assoc($res);
}

if ($config['SWversion'] < 1.003)  {
	mysql_query("ALTER TABLE `$tableName_config` ADD `minLLZuluft` INT(11) NOT NULL AFTER `minLLAbluft`");
	mysql_query("UPDATE `easyLuefter`.`$tableName_config` SET `minLLZuluft` = '10' WHERE `$tableName_config`.`config` = 1");
	mysql_query("UPDATE `easyLuefter`.`$tableName_config` SET `SWversion` = '1.003' WHERE `$tableName_config`.`config` = 1");
	$res = mysql_query("SELECT * FROM $tableName_config");
	$config = mysql_fetch_assoc($res);
}

if ($config['SWversion'] < 1.004)  {
	mysql_query("ALTER TABLE `$tableName_config` ADD `DIvalue` FLOAT(11) NOT NULL AFTER `Dvalue`");
	mysql_query("UPDATE `easyLuefter`.`$tableName_config` SET `DIvalue` = '1' WHERE `$tableName_config`.`config` = 1");
	mysql_query("ALTER TABLE `$tableName_config` DROP `Corr10_40`");
	mysql_query("ALTER TABLE `$tableName_config` DROP `Corr41_70`");
	mysql_query("ALTER TABLE `$tableName_config` DROP `Corr71_100`");
	mysql_query("UPDATE `easyLuefter`.`$tableName_config` SET `SWversion` = '1.004' WHERE `$tableName_config`.`config` = 1");
	$res = mysql_query("SELECT * FROM $tableName_config");
	$config = mysql_fetch_assoc($res);
}

if ($config['SWversion'] < 1.005)  {
	mysql_query("ALTER TABLE `$tableName_config` ADD `maxLLAbluft` INT(11) NOT NULL AFTER `minLLZuluft`");
	mysql_query("ALTER TABLE `$tableName_config` ADD `maxLLZuluft` INT(11) NOT NULL AFTER `maxLLAbluft`");
	mysql_query("UPDATE `easyLuefter`.`$tableName_config` SET `maxLLAbluft` = '100' WHERE `$tableName_config`.`config` = 1");
	mysql_query("UPDATE `easyLuefter`.`$tableName_config` SET `maxLLZuluft` = '100' WHERE `$tableName_config`.`config` = 1");
	mysql_query("ALTER TABLE `$tableName_vars` ADD `setSpeed` INT(11) NOT NULL AFTER `vars`");
	mysql_query("ALTER TABLE `$tableName_config` DROP `setSpeed`");
	mysql_query("UPDATE `easyLuefter`.`$tableName_config` SET `SWversion` = '1.005' WHERE `$tableName_config`.`config` = 1");
	$res = mysql_query("SELECT * FROM $tableName_config");
	$config = mysql_fetch_assoc($res);
}

if ($config['SWversion'] < 1.006)  {
	mysql_query("ALTER TABLE `$tableName` ADD `dSpeed` FLOAT(11) NOT NULL AFTER `LuefterleistungSoll`");
	mysql_query("UPDATE `easyLuefter`.`$tableName` SET `dSpeed` = '999'");
	mysql_query("UPDATE `easyLuefter`.`$tableName_config` SET `SWversion` = '1.006' WHERE `$tableName_config`.`config` = 1");
	$res = mysql_query("SELECT * FROM $tableName_config");
	$config = mysql_fetch_assoc($res);
}

if ($config['SWversion'] < 1.007)  {
	mysql_query("ALTER TABLE `$tableName_chartOpt` ADD `YScale` INT(11) NOT NULL AFTER `XScale`");
	mysql_query("UPDATE `easyLuefter`.`$tableName_chartOpt` SET `YScale` = '5'");
	mysql_query("UPDATE `easyLuefter`.`$tableName_config` SET `SWversion` = '1.007' WHERE `$tableName_config`.`config` = 1");
	$res = mysql_query("SELECT * FROM $tableName_config");
	$config = mysql_fetch_assoc($res);
}

if ($config['SWversion'] < 1.008)  {
	mysql_query("ALTER TABLE `$tableName_chartOpt` ADD `ZuluftRH` INT(11) NOT NULL AFTER `AbluftRH`");
	mysql_query("ALTER TABLE `$tableName_chartOpt` ADD `FortluftRH` INT(11) NOT NULL AFTER `ZuluftRH`");
	mysql_query("ALTER TABLE `$tableName_chartOpt` ADD `ZuluftAH` INT(11) NOT NULL AFTER `AbluftAH`");
	mysql_query("ALTER TABLE `$tableName_chartOpt` ADD `FortluftAH` INT(11) NOT NULL AFTER `ZuluftAH`");
	mysql_query("UPDATE `easyLuefter`.`$tableName_config` SET `SWversion` = '1.008' WHERE `$tableName_config`.`config` = 1");
	$res = mysql_query("SELECT * FROM $tableName_config");
	$config = mysql_fetch_assoc($res);
}

if ($config['SWversion'] < 1.009)  {
	mysql_query("ALTER TABLE `$tableName_config` DROP `DIvalue`");
	exec("sudo chmod -R 777 locals");
	exec("cp -r easyControlPi/locals/startUp.php locals");
	mysql_query("UPDATE `easyLuefter`.`$tableName_config` SET `SWversion` = '1.009' WHERE `$tableName_config`.`config` = 1");
	$res = mysql_query("SELECT * FROM $tableName_config");
	$config = mysql_fetch_assoc($res);
}

?>
