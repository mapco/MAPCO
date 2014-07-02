<?php
	include("../config.php");
	
	/******************************************************
	 * Überprüft Product Manager Berichte in der Pipeline *
	 ******************************************************/
	 
	$pm=array();
	$i=0;
	$results=q("SELECT * FROM cms_users WHERE userrole_id=3 AND active;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$pm[$i]["usermail"]=$row["usermail"];
		$pm[$i]["id_user"]=$row["id_user"];
		$pm[$i]["username"]=$row["username"];
		$i++;
	}


	//mail to product manager
	$header = 'From: Jens Habermann <jhabermann@mapco.de>' . "\r\n" .
    'Reply-To: Jens Habermann <jhabermann@mapco.de>' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();
	$missing=array();
	$total=0;
	for($i=0; $i<sizeof($pm); $i++)
	{
		$total+=$missing[$i];
		$results=q("SELECT * FROM cms_articles WHERE firstmod_user=".$pm[$i]["id_user"]." AND published=0;", $dbweb, __FILE__, __LINE__);
		$missing[$i]=4-mysqli_num_rows($results);
		if ($missing[$i]<0) $missing[$i]=0;
		if ($missing[$i]>0)
		{
			$msg = "Es fehlen ".$missing[$i]." Berichte für die Webseite!";
			$msg .= "\n\nEs müssen mindestens 4 unveröffentlichte Berichte pro Product Manager im System hinterlegt sein. Bitte schreiben Sie die Berichte stichpunktartig und achten Sie darauf, dass zu den angesprochene Artikel auch Fotos zur Verfügung stehen.";
			$msg .= "\n\nDiese Nachricht wird vom System automatisch erzeugt.\n\n";
			mail($pm[$i]["usermail"], "Fehlende Berichte", $msg, $header);
		}
	}
	
	
	//mail to management
	$miss=false;
	for($i=0; $i<sizeof($missing); $i++)
	{
		if ($missing[$i]>0)
		{
			$miss=true;
		}
	}
	if ($miss and date("N", time())==1)
	{
		$msg = "Es fehlen Berichte für die Webseite!";
		for($i=0; $i<sizeof($missing); $i++)
		{
			if ($missing[$i]>0)
			{
				$msg .= "\n\nVon Product Manager ".$pm[$i]["usermail"]." fehlen noch ".$missing[$i]." Berichte.";
			}
		}
		$msg .= "\n\nDiese Nachricht wird vom System automatisch erzeugt.\n\n";
		mail("ds@mapco.de", "Fehlende Berichte der Product Manager", $msg, $header);
		mail("habermann.jens@googlemail.com", "Fehlende Berichte der Product Manager", $msg, $header);
	}
?>