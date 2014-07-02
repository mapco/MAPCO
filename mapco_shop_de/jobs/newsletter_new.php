<?php
	include("../config.php");
	include("../functions/cms_newsletter_new.php");
	
	$query="SELECT * FROM cms_users WHERE NOT usermail='' AND newsletter>0;";
//	$query="SELECT * FROM cms_users WHERE NOT usermail='' AND newsletter>0 LIMIT 3;";
	$results2=q($query, $dbweb, __FILE__, __LINE__);
	while($row2=mysqli_fetch_array($results2))
	{
		$id_user=$row2["id_user"];

		//UMSTELLUNG AUF NEUE GESETZE
		$results3=q($query="SELECT * FROM cms_newsletter WHERE email='".$row2["usermail"]."' ;", $dbweb, __FILE__, __LINE__);
		if(mysqli_num_rows($results3)==0)
		{
			q("INSERT INTO cms_newsletter (email, insert_stamp) VALUES ('".$row2["usermail"]."', ".time().");", $dbweb, __FILE__, __LINE__);
			$results4=q($query="SELECT * FROM cms_newsletter WHERE email='".$row2["usermail"]."' ;", $dbweb, __FILE__, __LINE__);
			$row4=mysqli_fetch_array($results4);
			
			$id_user=$row2["id_user"];
			$receiver=$row4["email"];
			
			$header = 'MIME-Version: 1.0' . "\r\n";
			$header = 'Content-type: text/html; charset=utf-8' . "\r\n";
			$header .= 'From: MAPCO Autotechnik GmbH <newsletter@mapco.de>' . "\r\n";
			
			$mail = newsletter_new($row4["email"], $row4["id"]);
			
			mail($receiver, "Aktualisieren Sie jetzt Ihre Newsletter-Anmeldung!", $mail, $header);
			mail("developer@mapco.de", $receiver." - Aktualisieren Sie jetzt Ihre Newsletter-Anmeldung!", $mail, $header) or die("E-MAIL-VERSANDFEHLER: ".$receiver);

			q("UPDATE cms_newsletter SET newsletter_id=1, newsletter_stamp=".time()." WHERE id=".$row4["id"].";", $dbweb, __FILE__, __LINE__);
//			echo 'Newsletter an '.$receiver.' versendet.<br />';
		}
		q("UPDATE cms_users SET newsletter=-1 WHERE id_user=".$id_user.";", $dbweb, __FILE__, __LINE__);
	}
?>