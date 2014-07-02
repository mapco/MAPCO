<?php
	include("../config.php");
	
	/*********************************************************************
	 * Checks for open translations and informs the translators about it *
	 *********************************************************************/
	 
	$user=array();
	$i=0;
	$results=q("SELECT * FROM cms_users WHERE userrole_id=9 AND active;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$user[$i]["usermail"]=$row["usermail"];
		$user[$i]["id_user"]=$row["id_user"];
		$user[$i]["username"]=$row["username"];
		$results2=q("SELECT * FROM cms_languages WHERE id_language=".$row["language_id"].";", $dbweb, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$user[$i]["lang"]=$row2["code"];
		$i++;
	}


	//mail to translator
	$header = 'From: Patrick Müller <pmueller@mapco.de>' . "\r\n" .
    'Reply-To: Patrick Müller <pmueller@mapco.de>' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();
	$missing=array();
	$total=0;
	for($i=0; $i<sizeof($user); $i++)
	{
		$total+=$missing[$i];
		$results=q("SELECT * FROM cms_translations WHERE ".$user[$i]["lang"]."='';", $dbweb, __FILE__, __LINE__);
		$missing[$i]=mysqli_num_rows($results);
		if ($missing[$i]>0)
		{
			$msg =  "There are ".$missing[$i]." translations missing.";
			$msg .= "\n\nPlease login to http://www.mapco.de/backend_cms_translations.php and add the missing phrases!";
			$msg .= "\n\nThis message was created automatically.\n\n";
			mail($user[$i]["usermail"], "Missing translations on MAPCO.DE", $msg, $header) or die("MAIL ERROR");
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
		$msg = "Es fehlen Übersetzungen für die Webseite!";
		for($i=0; $i<sizeof($missing); $i++)
		{
			if ($missing[$i]>0)
			{
				$msg .= "\n\nVon Übersetzer ".$user[$i]["usermail"]." fehlen noch ".$missing[$i]." Übersetzungen.";
			}
		}
		$msg .= "\n\nDiese Nachricht wird vom System automatisch erzeugt.\n\n";
		mail("pmueller@mapco.de", "Fehlende Übersetzungen", $msg, $header);
	}
?>