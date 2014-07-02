<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Database Backup</title>
</head>

<body>

<?php 
	echo 'Sichere Datenbank "admapco_mapcoshop" ...';
	system("mysqldump -umapcoshop -pmerci2664 -h dedi473.your-server.de -–default-character-set=utf-8 admapco_mapcoshop >  /usr/home/admapco/public_html/mapco_shop_de/jobs/dump_".date("Ymd", time())."_admapco_mapcoshop.sql", $fp); 
	if ($fp==0) echo "Daten exportiert.<br />"; else echo "Es ist ein Fehler aufgetreten! (".$fp.")<br />";
?>

</body>
</html>
