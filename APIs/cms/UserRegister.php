<?php

	include("../functions/cms_createPassword.php");
	include("../functions/cms_send_html_mail.php");
	include("../functions/cms_t.php");
	include("../functions/mapco_gewerblich.php");
	
	check_man_params( array( "mode" => "text"));
	
	if($_POST["mode"]=="b2c")
	{
		check_man_params( array( "gender" 		=> "numeric",
								 "firstname" 	=> "text",
								 "lastname"		=> "text",
								 "username"		=> "text",
								 "password"		=> "text",
								 "usermail"		=> "text",
								 "newsletter"	=> "numeric"));
	
		$username_exist=0;
		$usermail_exist=0;
		$reg_success=0;
		
		$results=q("SELECT * FROM cms_users AS a, cms_users_sites AS b WHERE (a.username='".$_POST["username"]."' OR a.usermail='".$_POST["username"]."') AND b.site_id=".$_SESSION["id_site"]." AND a.id_user=b.user_id LIMIT 1;", $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows($results)>0) $username_exist=1;// $reg_error='Dieser Benutzername ist bereits vergeben!';
		else
		{
			$results=q("SELECT * FROM cms_users AS a, cms_users_sites AS b WHERE a.usermail='".$_POST["usermail"]."' AND b.site_id=".$_SESSION["id_site"]." AND a.id_user=b.user_id LIMIT 1;", $dbweb, __FILE__, __LINE__);
			if (mysqli_num_rows($results)>0) $usermail_exist=1;//$reg_error='Die E-Mail-Adresse ist bereits registriert! Vielleicht haben Sie schon ein Konto?';
			else
			{
				if (!($_SESSION["pid"]>0)) $pid=0; else $pid=$_SESSION["pid"];
				
				$salt=createPassword(32);
				$pw=$_POST["password"];
				$pw=md5($pw);
				$pw=md5($pw.$salt);
				$pw=md5($pw.PEPPER);
				
				q("INSERT INTO cms_users (username, usermail, gender, firstname, lastname, password, user_token, user_salt, userrole_id, language_id, origin, lastlogin, lastvisit, session_id, active, partner_id, partner_id_registration, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".addslashes(stripslashes($_POST["username"]))."', '".$_POST["usermail"]."', '".$_POST["gender"]."', '".addslashes(stripslashes($_POST["firstname"]))."', '".addslashes(stripslashes($_POST["lastname"]))."', '".addslashes(stripslashes($pw))."', '".mysqli_real_escape_string($dbweb,createPassword(50))."', '".mysqli_real_escape_string($dbweb,$salt)."', 5, 1, '".ip2country($ip)."', ".time().", ".time().", '".session_id()."', 1, ".$pid.", ".$pid.", ".time().", 0, ".time().", 0);", $dbweb, __FILE__, __LINE__);
		
				$_SESSION["id_user"]=mysqli_insert_id($dbweb);
				$_SESSION["userrole_id"]=5;
				$_SESSION["origin"]=ip2country($ip);
				$reg_success=1;
				//$reg_success='Vielen Dank für Ihre Registrierung!';
				q("UPDATE cms_users SET firstmod_user=".$_SESSION["id_user"].", lastmod_user=".$_SESSION["id_user"]." WHERE id_user=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
				if(isset($_POST["newsletter"]) and $_POST["newsletter"]==1)
				{
					q("UPDATE cms_users SET newsletter=1 WHERE id_user=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
				}
				//insert site
				q("INSERT INTO cms_users_sites (user_id, site_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$_SESSION["id_user"].", ".$_SESSION["id_site"].", ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
				
				//$cart_merge
				if(isset($_POST["cart"]))
				{
					$responseXml = post(PATH."soa2/", array("API" => "shop", "APIRequest" => "CartMerge", "mode" => $_POST["cart"]));
					$use_errors = libxml_use_internal_errors(true);
					try
					{
						$response = new SimpleXMLElement($responseXml);
					}
					catch(Exception $e)
					{
					}
					libxml_clear_errors();
					libxml_use_internal_errors($use_errors);
					//$stock=$response->Stock[0];
				}
				
				//send mail
/*				
				$text='<p>'.t("Willkommen auf mapco.de!").'</p>';
				$text.='<p>'.t("Vielen Dank für Ihre Registrierung. Ihr Konto wurde erfolgreich angelegt.").'</p>';
				$text.='<p>'.t("Sie haben sich mit folgenden Daten registriert:").'</p>';
				$text.='<table>';
				$text.='	<tr>';
				$text.='		<td><p style="display: inline">'.t("Benutzername: ").'</p></td>';
				$text.='		<td style="padding-left: 50px"><p style="display: inline">'.$_POST["username"].'</p></td>';
				$text.='	</tr>';
				$text.='	<tr>';
				$text.='		<td><p style="display: inline">'.t("Email-Adresse: ").'</p></td>';
				$text.='		<td style="padding-left: 50px"><p style="display: inline">'.$_POST["usermail"].'</p></td>';
				$text.='	</tr>';
				$text.='</table>';
				$text.='<p>'.t("Ab sofort können Sie").'</p>';
				$text.='<ul>';
				$text.='<li>'.t("bequem online bestellen").'</li>';
				$text.='<li>'.t("den Bestellstatus Ihrer Bestellungen einsehen").'</li>';
				$text.='<li>'.t("ihre Kundendaten bearbeiten").'</li>';
				$text.='</ul>';
				$text.='<p>'.t("Wir wünschen Ihnen weiterhin Viel Spaß auf mapco.de!").'</p>';
				send_html_mail($_POST["usermail"], t("Registrierung erfolgreich"), $text);
				send_html_mail('mwosgien@mapco.de', "Kopie-alt---".t("Registrierung erfolgreich"), $text);
*/				
				//SEND MAIL
				$post_data=array();
				$post_data["API"]="cms";
				$post_data["APIRequest"]="MailRegisterConfirmation";	
				$post_data["username"]=$_POST["username"];
				$post_data["usermail"]=$_POST["usermail"];
				
				$postdata=http_build_query($post_data);
				
				$response=soa2($postdata);				
			}
		}
	
		$xml='';
		
		$xml.='<username_exist>'.$username_exist.'</username_exist>';
		$xml.='<usermail_exist>'.$usermail_exist.'</usermail_exist>';
		$xml.='<reg_success>'.$reg_success.'</reg_success>';
		
		echo $xml;
	}
	
	if($_POST["mode"]=="b2b")
	{
		check_man_params( array( "company"			=> "text",
								 "company_voice"	=> "text",
								 "street"			=> "text",
								 "zip"				=> "text",
								 "city"				=> "text",
								 "tel"				=> "text",
								 "tax_number"		=> "text",
								 "ship_adr"			=> "numeric"));
								 
		$b2b_already=0;
		$reg_success=0;
		
		//check, if already registred as b2b-customer
		$results=q("SELECT * FROM mapco_b2b_registration WHERE user_id=".$_SESSION["id_user"]." AND site_id=".$_SESSION["id_site"].";", $dbshop, __FILE__, __LINE__);
		if(mysqli_num_rows($results)>0) $b2b_already=1;
		if(gewerblich($_SESSION["id_user"])) $b2b_already=1;
		
		if($b2b_already==0)
		{
			//cms_files file-directory
			$filename=substr($_POST["trade_filename"], strpos($_POST["trade_filename"], "/"), strlen($_POST["trade_filename"]));
			$filename=substr($filename, 0, strrpos($filename, "."));
			$extension=substr($_POST["trade_filename"], strrpos($_POST["trade_filename"], ".")+1, strlen($_POST["trade_filename"]));
			$filesize=filesize($_POST["trade_filename_temp"]);
			$description="trade-registration";
			$imageformat_id=0;
			$original_id=0;
			$EPS_link="";
			$firstmod=time();
			$firstmod_user=$_SESSION["id_user"];
			$lastmod=time();
			$lastmod_user=$_SESSION["id_user"];
			q("INSERT INTO cms_files (filename, extension, filesize, description, imageformat_id, original_id, EPS_link, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".mysqli_real_escape_string($dbweb,$filename)."', '".mysqli_real_escape_string($dbweb,$extension)."', '".mysqli_real_escape_string($dbweb,$filesize)."', '".mysqli_real_escape_string($dbweb,$description)."', ".$imageformat_id.", ".$original_id.", '".mysqli_real_escape_string($dbweb,$EPS_link)."', ".$firstmod.", ".$firstmod_user.", ".$lastmod.", ".$lastmod_user.");", $dbweb, __FILE__, __LINE__);
			$id_file=mysqli_insert_id($dbweb);
			$folder=floor($id_file/1000);
			if (!file_exists("../files/".$folder)) mkdir("../files/".$folder);
			$destination="../files/".$folder."/".$id_file.".".$extension;
			copy($_POST["trade_filename_temp"], $destination);
			unlink($_POST["trade_filename_temp"]);
			
			q("INSERT INTO mapco_b2b_registration (user_id, site_id, company, company_voice, street, zip, city, tel, fax, tax_number, trade_registration, ship_adr, ship_company, ship_company_voice, ship_street, ship_zip, ship_city, ship_tel, ship_fax, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$_SESSION["id_user"].", ".$_SESSION["id_site"].", '".mysqli_real_escape_string($dbshop,$_POST["company"])."', '".mysqli_real_escape_string($dbshop,$_POST["company_voice"])."', '".mysqli_real_escape_string($dbshop,$_POST["street"])."', '".mysqli_real_escape_string($dbshop,$_POST["zip"])."', '".mysqli_real_escape_string($dbshop,$_POST["city"])."', '".mysqli_real_escape_string($dbshop,$_POST["tel"])."', '".mysqli_real_escape_string($dbshop,$_POST["fax"])."', '".mysqli_real_escape_string($dbshop, $_POST["tax_number"])."', '".$id_file."', ".$_POST["ship_adr"].", '".mysqli_real_escape_string($dbshop,$_POST["ship_company"])."', '".mysqli_real_escape_string($dbshop,$_POST["ship_company_voice"])."', '".mysqli_real_escape_string($dbshop,$_POST["ship_street"])."', '".mysqli_real_escape_string($dbshop,$_POST["ship_zip"])."', '".mysqli_real_escape_string($dbshop,$_POST["ship_city"])."', '".mysqli_real_escape_string($dbshop,$_POST["ship_tel"])."', '".mysqli_real_escape_string($dbshop,$_POST["ship_fax"])."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
			$reg_success=1;
			
			//get email-address and username
			$usermail='';
			$username='';
			$results=q("SELECT * FROM cms_users WHERE id_user=".$_SESSION["id_user"].";", $dbweb, __FILE__, __LINE__);
			if(mysqli_num_rows($results)>0)
			{
				$row=mysqli_fetch_array($results);
				$usermail=$row["usermail"];
				$username=$row["username"];
			}
			
			//send mails
			$shop='';
			switch ($_SESSION["id_site"])
			{
				case 1: $shop="mapco.de"; break;	
				case 2: $shop="ihr-autopartner.de"; break;	
				case 7: $shop="lenkung24.de"; break;	
				case 8: $shop="mapco-neubrandenburg.de"; break;	
				case 9: $shop="mapco-leipzig.de"; break;	
				case 10: $shop="mapco-soemmerda.de"; break;	
				case 11: $shop="mapco-dresden.de"; break;	
				case 12: $shop="mapco-magdeburg.de"; break;	
				case 13: $shop="mapco-frankfurt.de"; break;	
				case 14: $shop="mapco-berlin.de"; break;	
				case 15: $shop="mapco-essen.de"; break;	
				case 16: $shop="mapco-roma.eu"; break;	
				case 17: $shop="mapco-handel.de"; break;				
			}
			$mail = '<html>';
			$mail .= '<head>';
			$mail .= '<title>'.t("Neukunde").'</title>';
			$mail .= '</head>';
			$mail .= '<body>';
			$mail .= '<p><h2>'.t("Neukundenanlage").':</h2></p>';
			$mail .= '<p><h3>Kunde hat sich über '.$shop.' registriert.</h3></p>';
			$mail .= '<table>';
			$mail .= '<tr><td>'.t("Online-Username").':</td><td>'.$username.'</td></tr>';
			$mail .= '<tr><td>'.t("Firmenname").':</td><td>'.$_POST["company"].'</td></tr>';
			$mail .= '<tr><td>'.t("Ansprechpartner").':</td><td>'.$_POST["company_voice"].'</td></tr>';
			$mail .= '<tr><td>'.t("Strasse").':</td><td>'.$_POST["street"].'</td></tr>';
			$mail .= '<tr><td>'.t("PLZ").':</td><td>'.$_POST["zip"].'</td></tr>';
			$mail .= '<tr><td>'.t("Ort").':</td><td>'.$_POST["city"].'</td></tr>';
			$mail .= '<tr><td>'.t("Tel").':</td><td>'.$_POST["tel"].'</td></tr>';
			$mail .= '<tr><td>'.t("Fax").':</td><td>'.$_POST["fax"].'</td></tr>';
			$mail .= '<tr><td>'.t("E-Mail").':</td><td>'.$usermail.'</td></tr>';
			$mail .= '<tr><td>'.t("Steuer-Nr.").':</td><td>'.$_POST["tax_number"].'</td></tr>';
			
			$mail .= '<tr><td>'.t("Gewerbeanmeldung").':</td><td><a href="'.PATH.'files/'.$folder.'/'.$id_file.'.'.$extension.'">Bitte diesem Link folgen</a></td></tr>';
//			$mail .= '<tr><td>'.t("trade_filename_temp").':</td><td>'.$_POST["trade_filename_temp"].'</td></tr>';//wieder raus
//			$mail .= '<tr><td>'.t("trade_filename").':</td><td>'.$_POST["trade_filename"].'</td></tr>';//wieder raus
//			$mail .= '<tr><td>'.t("Pfad(evtl. als link?)").':</td><td>'.PATH."files/".$folder."/".$id_file.".".$extension.'</td></tr>';//wieder raus

			if ($_POST["ship_adr"]==1)
			{
				$mail .= '<tr><td colspan="2">------------------------------------------------------------</td></tr>';
				$mail .= '<tr><td colspan="2"><h3>'.t("Lieferanschrift").'</h3></td></tr>';
				$mail .= '<tr><td>'.t("Firmenname").':</td><td>'.$_POST["ship_company"].'</td></tr>';
				$mail .= '<tr><td>'.t("Ansprechpartner").':</td><td>'.$_POST["ship_company_voice"].'</td></tr>';
				$mail .= '<tr><td>'.t("Strasse").':</td><td>'.$_POST["ship_street"].'</td></tr>';
				$mail .= '<tr><td>'.t("PLZ").':</td><td>'.$_POST["ship_zip"].'</td></tr>';
				$mail .= '<tr><td>'.t("Ort").':</td><td>'.$_POST["ship_city"].'</td></tr>';
				$mail .= '<tr><td>'.t("Tel").':</td><td>'.$_POST["ship_tel"].'</td></tr>';
				$mail .= '<tr><td>'.t("Fax").':</td><td>'.$_POST["ship_fax"].'</td></tr>';
			}
		    $mail .= '</table>';
  		  	$mail .= '</body>';
  			$mail .= '</html>';
			//SendMail('bestellung@mapco-shop.de', $_POST["form_mail"], 'MAPCO '.t("Online Neukunde"), $mail, $_FILES["form_file"]["tmp_name"], $_FILES["form_file"]["name"]);
			//SendMail('pm@mapco.eu', $_POST["form_mail"], 'MAPCO '.t("Online Neukunde"), $mail, $_FILES["form_file"]["tmp_name"], $_FILES["form_file"]["name"]);
			//SendMail('pfunke@mapco.de', $_POST["form_mail"], 'MAPCO '.t("Online Neukunde"), $mail, $_FILES["form_file"]["tmp_name"], $_FILES["form_file"]["name"]);
			//SendMail('mwosgien@mapco.de', $usermail, 'MAPCO '.t("Online Neukunde"), $mail,substr($_POST["trade_filename_temp"], 3),$_POST["trade_filename"]);
			
			SendMail('bestellung@mapco-shop.de', $_POST["form_mail"], 'MAPCO '.t("Online Neukunde"), $mail);
			SendMail('pm@mapco.eu', $_POST["form_mail"], 'MAPCO '.t("Online Neukunde"), $mail);
//			SendMail('pfunke@mapco.de', $_POST["form_mail"], 'MAPCO '.t("Online Neukunde"), $mail);
//			SendMail('mwosgien@mapco.de', $usermail, 'MAPCO '.t("Online Neukunde"), $mail);
			
		}
				
		$xml='';
		
		$xml.='<b2b_already>'.$b2b_already.'</b2b_already>'."\n";
		$xml.='<reg_success>'.$reg_success.'</reg_success>'."\n";
		
		echo $xml;
	}
?>