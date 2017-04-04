<?php

function copyChartToServer() {
	echo exec('/usr/bin/sshpass -p Speedy01 scp /tmp/easyWebCharts/easyWebChart.jpg pi@192.168.1.19:/mnt/share/www/proto/webCharts/easy2/easyWebChart.jpg') . "\n";	
}

?>