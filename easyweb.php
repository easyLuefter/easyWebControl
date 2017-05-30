<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
<title>easyLüfter WebChart</title>

<style type="text/css">
.auto-style1 {
	font-family: Arial, Helvetica, sans-serif;
}
</style>

<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css"/>
<script src="http://code.jquery.com/jquery-1.10.2.js"></script>
<script src="http://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
<script>
	$(function() {$( "#datepicker" ).datepicker({dateFormat: "dd.mm.yy"});});
</script>

</head>

<body>
<?php
$debug = FALSE;
if ($debug) {echo 'POST: '; print_r($_POST); echo '<br />';}
if ($debug) {echo 'GET: '; print_r($_GET); echo'<br />';}
if ($debug) {echo "FILES: "; print_r($_FILES); echo "<br />";}


$chartDir = "easyWebCharts";
$chartFilenameBase = "easyWebChart";
$phpFileName = "easyweb.php";

include 'webChartIncl.php';

?>

</body>
</html>
