<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Database Backup</title>
</head>

<body>

<?php 
	echo 'Spiele Datenbank "admapco_mapcoshop" ein ... ';
	system("c:\xampp\mysql\bin\mysql -hlocalhost -umapcoshop -pmerci2664 admapco_mapcoshop < dump_admapco_mapcoshop.sql", $fp); 
	if ($fp==0) echo "Daten importiert.<br />"; else echo "Es ist ein Fehler aufgetreten! (".$fp.")<br />";
?>

</body>
</html>