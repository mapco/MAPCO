<?php

	include("../functions/mapco_gewerblich.php");

	check_man_params(array( "user_name_mail" => "text"));
	
	$xml='';
	
	//user-Daten holen
	$user_id=0;
	$results=q("SELECT * FROM cms_users WHERE username='".mysqli_real_escape_string($dbweb,$_POST["user_name_mail"])."' OR usermail='".mysqli_real_escape_string($dbweb,$_POST["user_name_mail"])."' AND active=1;", $dbweb, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		$xml.='<mail_send>0</mail_send>';
//		show_error();
//		exit;
	}
	while($row=mysqli_fetch_array($results))
	{
		$results2=q("SELECT * FROM cms_users_sites WHERE site_id=".$_SESSION["id_site"]." AND user_id=".$row["id_user"].";", $dbweb, __FILE__, __LINE__);
		if(mysqli_num_rows($results2)>0)
		{
			$row2=mysqli_fetch_array($results2);
			$user_id=$row2["user_id"];	
			break;
		}
	}
	if($user_id==0)
	{
		$xml.='<mail_send>0</mail_send>';
//		show_error();
//		exit;
	}
	
	//user-token und email-Adresse besorgen
	$user_token='';
	$receiver='';
	$results3=q("SELECT * FROM cms_users WHERE id_user=".$user_id.";", $dbweb, __FILE__, __LINE__);
	if(mysqli_num_rows($results3)>0)
	{
		$row3=mysqli_fetch_array($results3);
		$user_token=$row3["user_token"];
		$receiver=$row3["usermail"];
		if( $receiver=="" )
		{
			show_error(9785, 1, __FILE__, __LINE__, print_r($_POST, true)."\n".print_r($_SESSION, true));
			exit;
		}
		$username=$row3["username"];
	}
	
	//Link auf Passwort-Eingabeseite bauen
	$link=PATH.'autologin/'.$user_token.'/neues-passwort/';
	$xml.='<token_link>'.$link.'</token_link>';
	echo $xml;
	
	$subject='MAPCO Password Request';
	
	//message bauen
	if(gewerblich($user_id))
	{
		$results4=q("SELECT * FROM cms_articles WHERE id_article=33171;", $dbweb, __FILE__, __LINE__);
		$row4=mysqli_fetch_array($results4);
		$results5=q("SELECT * FROM fa_user_login WHERE nic='".$username."';", $dbshop, __FILE__, __LINE__);
		$row5=mysqli_fetch_array($results5);
		$message=$row4["article"];
		$message=str_replace("<!-- PW -->", $row5["pass"], $message);
	}
	else
	{
		$results4=q("SELECT * FROM cms_articles WHERE id_article=33169;", $dbweb, __FILE__, __LINE__);
		$row4=mysqli_fetch_array($results4);
		$message=$row4["article"];
		$message=str_replace("<!-- PW_LINK -->", $link, $message);
	}
	
	//Absender
	$sender='FROM: noreply@mapco.de'."\r\n".'Content-Type: text/html; charset=utf-8' ."\n\n";
	
	//mail versenden
	$mail_send=mail($receiver, $subject, $message, $sender);

?>