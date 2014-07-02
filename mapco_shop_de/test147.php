<?php
	//include("config2.php");
	echo 'Startzeitpunkt: '.$starttime=time()+microtime()."<br />\n";

	echo 'Endzeitpunkt: '.$stoptime=time()+microtime()."<br />\n";
	$time=$stoptime-$starttime;
	echo 'Skriptlaufzeit: '.round($time, 2)."<br />\n";
?>