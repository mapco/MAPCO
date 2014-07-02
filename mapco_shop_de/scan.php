<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Malware-Scanner</title>
</head>

<body>
<?php
	$dbweb=mysqli_connect("localhost", "admapco_1", "G7kCp4m8", "admapco_db1");
	mysqli_query($dbweb, "SET NAMES utf8");

	if (!isset($_GET["key"]) or $_GET["key"]!="merci2664")
	{
		echo 'aaa';
		exit;
	}
	
	if (isset($_GET["mode"]) && $_GET["mode"]=="auto")
	{
		$DIR="../.";
		$API="../../APIs";
	}
	else 
	{
		$DIR=".";
		$API="../APIs";
	}
	/****************************
	 * Scans server for malware *
	 ****************************/

	$malware=array();
	$results=mysqli_query($dbweb, "SELECT * FROM cms_malware;");
	while( $row=mysqli_fetch_array($results) )
	{
		$malware[]=$row;
	}
	
	$msg="";

	function trimall($content)
	{
		$content=str_replace("\n", "", $content);
		$content=str_replace("\r", "", $content);
		$content=str_replace(" ", "", $content);
		return($content);
	}

	function compare($a, $b)
	{
		for($i=0; $i<strlen($a); $i++)
		{
			if($a[$i]!=$b[$i]) echo($i.' '.$a[$i].'<-->'.$b[$i].'<br />');
		}
	}

	function scan($dir)
	{
//		echo $dir.'<br />';
		global $msg;
		global $malware;

		if ($handle = opendir($dir))
		{
			while (false !== ($file = readdir($handle)))
			{
				$filename=$dir.'/'.$file;
				if ($file!="." && $file!="..")
				{
					if ( is_dir($filename) )
					{
						scan($filename);
					}
					else
					{
						$content_read=false;
						for($i=0; $i<sizeof($malware); $i++)
						{
							if( strpos($file, $malware[$i]["extensions"]) !== false )
							{
								if( !$content_read )
								{
									$content=file_get_contents($filename);
									$content2=trimall($content);
									$content_read=true;
								}
								$malware2=trimall(utf8_decode($malware[$i]["ident"]));
								
								if( strpos($content2, $malware2) !== false )
								{
									$msg .= 'Datei '.$filename.' ist mit '.$malware[$i]["title"].' VERSEUCHT.<br />'."\n";
									//backup file
									/*
									$backup=$dir.'/'.$file.".bak";
									$status=copy($filename, $backup);
									$msg .= 'Sicherhietskopie ('.$backup.') anlegen: '.$status.'<br />';
									*/
									//remove malware
									if ( $malware[$i]["remove"]==1 )
									{
										$handle2=fopen($filename, "w");
										fwrite($handle2, str_replace(utf8_decode($malware[$i]["ident"]), "", $content));
										fclose($handle2);
										$msg .= 'Entferne Malware...OK.<br />'."\n";
									}
								}
							}
						}
					}
				}
			}
			closedir($handle);
		}
	}
	
	echo 'Starte Website-Scan...';
	scan($DIR);
	echo 'FERTIG.<br />';
	echo 'Starte API-Scan...';
	scan($API);
	echo 'FERTIG.<br />';

	if ($msg!="")
	{
		mail("habermann.jens@gmail.com", "Malware auf mapco.de gefunden (".$_SERVER['HTTP_REFERER'].")", $msg);
		mail("nputzing@mapco.de", "Malware auf mapco.de gefunden (".$_SERVER['HTTP_REFERER'].")", $msg);

		echo 'Malware-Warnung per E-Mail versendet. '.$msg;
	}
?>
</body>
</html>