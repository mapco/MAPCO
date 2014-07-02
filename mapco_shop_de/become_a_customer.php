<?php
	include("config.php");
	include("templates/".TEMPLATE."/header.php");
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	include("functions/cms_send_html_mail.php");
	include("functions/cms_t.php");

	
	echo '<div id="mid_column">';

	if (isset($_POST["form_submit"]))
	{
		if($_POST["form_firma"]=="") echo '<div class="failure">'.t("Der Firmenname darf nicht leer sein").'.</div>';
		elseif($_POST["form_name"]=="") echo '<div class="failure">'.t("Der Ansprechpartner darf nicht leer sein").'.</div>';
		elseif($_POST["form_strasse"]=="") echo '<div class="failure">'.t("Die Strasse darf nicht leer sein").'.</div>';
		elseif($_POST["form_plz"]=="") echo '<div class="failure">'.t("Die Postleitzahl darf nicht leer sein").'.</div>';
		elseif($_POST["form_ort"]=="") echo '<div class="failure">'.t("Der Ort darf nicht leer sein").'.</div>';
		elseif($_POST["form_vor_tel"]=="") echo '<div class="failure">'.t("Die Vorwahl darf nicht leer sein").'.</div>';
		elseif($_POST["form_tel"]=="") echo '<div class="failure">'.t("Die Telefonnummer darf nicht leer sein").'.</div>';
		elseif ($_POST["form_mail"]=="") echo '<div class="failure">'.t("Die E-Mail-Adresse darf nicht leer sein").'!</div>';
		elseif (strpos($_POST["form_mail"], "@")==0 or strpos($_POST["form_mail"], ".")==0) echo '<div class="failure">'.t("Sie müssen eine gültige E-Mail-Adresse angeben").'</div>';
		elseif($_POST["form_steuer"]=="") echo '<div class="failure">'.t("Die Steuernummer darf nicht leer sein").'.</div>';
		elseif ($_FILES["form_file"]["tmp_name"]=="") echo '<div class="failure">'.t("Es konnte keine Datei gefunden werden. Bitte Dokument mit hochladen").'!!!</div>';
		else
		{
				
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
			$mail .= '<tr><td>'.t("Firmenname").':</td><td>'.$_POST["form_firma"].'</td></tr>';
			$mail .= '<tr><td>'.t("Ansprechpartner").':</td><td>'.$_POST["form_name"].'</td></tr>';
			$mail .= '<tr><td>'.t("Strasse").':</td><td>'.$_POST["form_strasse"].'</td></tr>';
			$mail .= '<tr><td>'.t("PLZ").':</td><td>'.$_POST["form_plz"].'</td></tr>';
			$mail .= '<tr><td>'.t("Ort").':</td><td>'.$_POST["form_ort"].'</td></tr>';
			$mail .= '<tr><td>'.t("Tel").':</td><td>'.$_POST["form_vor_tel"].' / '.$_POST["form_tel"].'</td></tr>';
			$mail .= '<tr><td>'.t("Fax").':</td><td>'.$_POST["form_vor_fax"].' / '.$_POST["form_fax"].'</td></tr>';
			$mail .= '<tr><td>'.t("E-Mail").':</td><td>'.$_POST["form_mail"].'</td></tr>';
			$mail .= '<tr><td>'.t("Steuer-Nr.").':</td><td>'.$_POST["form_steuer"].'</td></tr>';

			if (!$_POST["form_lname"]=="")
			{
				$mail .= '<tr><td colspan="2">------------------------------------------------------------</td></tr>';
				$mail .= '<tr><td colspan="2"><h3>'.t("Lieferanschrift").'</h3></td></tr>';
				$mail .= '<tr><td>'.t("Firmenname").':</td><td>'.$_POST["form_lfirma"].'</td></tr>';
				$mail .= '<tr><td>'.t("Ansprechpartner").':</td><td>'.$_POST["form_lname"].'</td></tr>';
				$mail .= '<tr><td>'.t("Strasse").':</td><td>'.$_POST["form_lstrasse"].'</td></tr>';
				$mail .= '<tr><td>'.t("PLZ").':</td><td>'.$_POST["form_lplz"].'</td></tr>';
				$mail .= '<tr><td>'.t("Ort").':</td><td>'.$_POST["form_lort"].'</td></tr>';
				$mail .= '<tr><td>'.t("Tel").':</td><td>'.$_POST["form_vor_ltel"].' / '.$_POST["form_ltel"].'</td></tr>';
				$mail .= '<tr><td>'.t("Fax").':</td><td>'.$_POST["form_vor_lfax"].' / '.$_POST["form_lfax"].'</td></tr>';
			}
		    $mail .= '</table>';
  		  	$mail .= '</body>';
  			$mail .= '</html>';
			SendMail('bestellung@mapco-shop.de', $_POST["form_mail"], 'MAPCO '.t("Online Neukunde"), $mail, $_FILES["form_file"]["tmp_name"], $_FILES["form_file"]["name"]);
			SendMail('pm@mapco.eu', $_POST["form_mail"], 'MAPCO '.t("Online Neukunde"), $mail, $_FILES["form_file"]["tmp_name"], $_FILES["form_file"]["name"]);
			SendMail('pfunke@mapco.de', $_POST["form_mail"], 'MAPCO '.t("Online Neukunde"), $mail, $_FILES["form_file"]["tmp_name"], $_FILES["form_file"]["name"]);

			if(isset($_POST["form_newsletter"]) and $_POST["form_newsletter"]==1)
			{
				$results=q("SELECT * FROM cms_newsletter WHERE email='".$_POST["form_mail"]."' LIMIT 1;", $dbweb, __FILE__, __LINE__);
				if (mysqli_num_rows($results)==0) 
				{
				q("INSERT INTO cms_newsletter (email, insert_stamp, confirmed, confirmed_stamp) VALUES ('".$_POST["form_mail"]."', ".time().", 1, ".time().");", $dbweb, __FILE__, __LINE__);
				}
			}

			unset ($_POST);
			echo '<div class="success">'.t("Vielen Dank! Ihr Account wird angelegt").'.</div>';
		}
	}
	
	if ($reg_success!="") echo '<div class="success">'.$reg_success.'</div>';
	
	if (!($reg_success!="") and $_SESSION["id_user"]>0)
	{
		echo '<div class="failure">'.t("Sie sind bereits mit einem Konto angemeldet. Wenn Sie ein neues Konto registrieren möchten, loggen Sie sich bitte zunächst aus").'!</div>';
	}
	else
	{
		//show errors
		if ($reg_error!="") echo '<div class="failure">'.$reg_error.'</div>';
		echo '<form method="post">';
		echo '<img src="'.PATH.'images/mapco_shop.jpg" alt="" title="" />';
		echo '<h2>'.t("Wir heißen Sie herzlich willkommen").'!</h2>';
		echo '<table class="hover" style="width:530px">';
		echo '	<tr>';
		echo '		<th colspan="2">'.t("Registrierung als Endkunde").'</th>';
		echo '	</tr>';
		echo '</table>';
		echo '<table>';
		echo '	<tr>';
		echo '		<td style="width:160px">'.t("Anrede").'</td>';
		echo '		<td>';
		echo '			<select name="reg_gender">';
							if($_POST["reg_gender"]==0) $selected=' selected="selected"'; else $selected='';
		echo '				<option'.$selected.'  value="0">'.t("Herr").'</option>';
							if($_POST["reg_gender"]==1) $selected=' selected="selected"'; else $selected='';
		echo '				<option'.$selected.' value="1">'.t("Frau").'</option>';
		echo '			</select>';
		echo '		<span class="customer_span">*</span></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td style="width:160px">'.t("Vorname").'</td><td><input class="customer" type="text" name="reg_firstname" value="'.$_POST["reg_firstname"].'" /></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td style="width:160px">'.t("Nachname").'</td><td><input class="customer" type="text" name="reg_lastname" value="'.$_POST["reg_lastname"].'" /> <span class="customer_span">*</span></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td style="width:160px">'.t("Benutzername").'</td><td><input class="customer" type="text" name="reg_username" value="'.$_POST["reg_username"].'" /> <span class="customer_span">*</span></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Passwort").'</td><td><input class="customer" type="password" name="reg_password" value="'.$_POST["reg_password"].'" /> <span class="customer_span">*</span></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Passwort wiederholen").'</td><td><input class="customer" type="password" name="confirm_password" value="'.$_POST["confirm_password"].'" /> <span class="customer_span">*</span></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("E-Mail").'</td><td><input class="customer" type="text" name="reg_mail" value="'.$_POST["reg_mail"].'" /> <span class="customer_span">*</span></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Newsletter").'</td><td ><input type="checkbox" checked="checked" name="reg_newsletter" value="1"/> '.t("Ja ich möchte den MAPCO-Newsletter empfangen", __FILE__, __LINE__).'!</td>';
		echo '	</tr>';
		echo '  <tr><td></td><td><span class="customer_span">*</span> = '.t("Pflichtfelder bei Endkunden").'</td></tr>';
		echo '</table>';
		echo '<input type="submit" name="reg_button" value="'.t("Registrierung als Endkunde").'" />';
		echo '</form><br /><br />';
		
		echo '<table class="hover" style="width:530px">';
		echo '	<tr>';
		echo '		<th colspan="2">'.t("Registrierung als Gewerbekunde").'</th>';
		echo '	</tr>';
//		echo '	<tr>';
//		echo '		<td colspan="2">'.t("Als Gewerbekunde können Sie sich").' <a href="become_a_customer.php">'.t("hier registrieren!").'</a></td>';
//		echo '	</tr>';
		echo '</table>';
//		echo '<img src="images/mapco_shop.jpg" alt="" title="" />';
		echo '<p><h1 style="width:530px; text-align:center;">'.t("Als gewerblicher Neukunde erhalten Sie in den ersten 90 Tagen auf alle Artikel zusätzlich 10% Sonderrabatt!").'*</h1></p>';
	
		echo '<p>  *'.t("Alle im Online-Shop ersichtlichen Preise sind ohne etwaige Sonderrabatte").',<br />';
		echo t("diese werden erst beim Erstellen der Rechnung in unserer Warenwirtschaft berücksichtigt").'.';
		echo '<p>  '.t("Als registrierter Gewerbekunde haben Sie viele Vorteile").':</p>';
		echo '<ul>';
		echo ' <li>'.t("Kauf auf Rechnung").'</li>';
		echo ' <li>'.t("Vorzugspreise").'</li>';
		echo ' <li>'.t("Sonderaktionen").'</li>';
		echo ' <li>'.t("Außendienstbetreuung").'</li>';
		echo ' <li>'.t("Produktschulung").'</li>';
		echo '</ul>';
		echo '<b>'.t("Nun bitten wir Sie um folgende Angaben").':</b><br />';
		echo '<form method="post" enctype="multipart/form-data">';
		echo '<table>';
		echo '	<tr><td>'.t("Firmenname").'</td><td><input class="customer" type="text" name="form_firma" value="'.$_POST["form_firma"].'" /> <span class="customer_span">*</span></td></tr>';
		echo '	<tr><td>'.t("Ansprechpartner").'</td><td><input class="customer" type="text" name="form_name" value="'.$_POST["form_name"].'" /> <span class="customer_span">*</span></td></tr>';
		echo '	<tr><td>'.t("Strasse/Nr.").'</td><td><input class="customer" type="text" name="form_strasse" value="'.$_POST["form_strasse"].'" /> <span class="customer_span">*</span></td></tr>';
		echo '	<tr><td>'.t("PLZ").'</td><td><input class="customer" type="text" name="form_plz" value="'.$_POST["form_plz"].'" /> <span class="customer_span">*</span></td></tr>';
		echo '	<tr><td>'.t("Ort").'</td><td><input class="customer" type="text" name="form_ort" value="'.$_POST["form_ort"].'" /> <span class="customer_span">*</span></td></tr>';
		echo '	<tr><td>'.t("Vorwahl/Telefon").'</td><td><input class="customer_vor" type="text" name="form_vor_tel" value="'.$_POST["form_vor_tel"].'" /> / <input class="customer_tel" type="text" name="form_tel" value="'.$_POST["form_tel"].'" /> <span class="customer_span">*</span></td></tr>';
		echo '	<tr><td>'.t("Vorwahl/Fax").'</td><td><input class="customer_vor" type="text" name="form_vor_fax" value="'.$_POST["form_vor_fax"].'" /> / <input class="customer_tel" type="text" name="form_fax" value="'.$_POST["form_fax"].'" /></td></tr>';
		echo '	<tr><td>'.t("E-Mail").'</td><td><input class="customer" type="text" name="form_mail" value="'.$_POST["form_mail"].'" /> <span class="customer_span">*</span></td></tr>';
		echo '	<tr><td>'.t("Steuer-Nr.").'</td><td><input class="customer" type="text" name="form_steuer" value="'.$_POST["form_steuer"].'" /> <span class="customer_span">*</span></td></tr>';
		echo '	<tr><td>'.t("Datei (Gewerbeanmeldung)").'</td><td><input class="customer" type="file" name="form_file" value="'.$_POST["form_file"].'" /> <span class="customer_span">*</span></td></tr>';
		echo '  <tr><td></td><td><span class="customer_span">*</span> = '.t("Pflichtfelder bei Gewerbekunden").'</td></tr>';
		echo '  <tr><td colspan="2"></td></tr>';
		echo '  <tr><td colspan="2"><h3>'.t("Lieferanschrift (falls abweichend)").'</h3></td></tr>';
		echo '	<tr><td>'.t("Firmenname").'</td><td><input class="customer" type="text" name="form_lfirma" value="'.$_POST["form_lfirma"].'" /></td></tr>';
		echo '	<tr><td>'.t("Ansprechpartner").'</td><td><input class="customer" type="text" name="form_lname" value="'.$_POST["form_lname"].'" /></td></tr>';
		echo '	<tr><td>'.t("Strasse/Nr.").'</td><td><input class="customer" type="text" name="form_lstrasse" value="'.$_POST["form_lstrasse"].'" /></td></tr>';
		echo '	<tr><td>'.t("PLZ").'</td><td><input class="customer" type="text" name="form_lplz" value="'.$_POST["form_lplz"].'" /></td></tr>';
		echo '	<tr><td>'.t("Ort").'</td><td><input class="customer" type="text" name="form_lort" value="'.$_POST["form_lort"].'" /></td></tr>';
		echo '	<tr><td>'.t("Vorwahl/Telefon").'</td><td><input class="customer_vor" type="text" name="form_vor_ltel" value="'.$_POST["form_vor_ltel"].'" /> / <input class="customer_tel" type="text" name="form_ltel" value="'.$_POST["form_ltel"].'" /></td></tr>';
		echo '	<tr><td>'.t("Vorwahl/Fax").'</td><td><input class="customer_vor" type="text" name="form_vor_lfax" value="'.$_POST["form_vor_lfax"].'" /> / <input class="customer_tel" type="text" name="form_lfax" value="'.$_POST["form_lfax"].'" /></td></tr>';
		echo '</table>';
		echo '<p><input type="checkbox" checked="checked" name="form_newsletter" value="1"/> '.t("Ja ich möchte den MAPCO-Newsletter empfangen", __FILE__, __LINE__).'!</p>';
		echo '<input type="submit" name="form_submit"  value="'.t("Registrierung als Gewerbekunde").'" />';
		echo '</form>';
		echo '<p>'.t("Oder senden Sie uns Ihre Firmendaten mit Gewerbeanmeldung und/oder HRB-Eintrag, Umsatzsteuerdaten, Rechnungs- und Lieferadresse").' <b>'.t("als Fax").'</b>: +49 (0) 33845 / 4 10 32 <br />'.t("oder").' <b>'.t("per Mail").'</b> '.t("an").': <a href="mailto:info@mapco.de">info@mapco.de</a>';
		echo '</p>';
		echo '<p>'.t("Sie erhalten dann von uns eine Bestätigung und können rund um die Uhr Bestellungen aus unserem qualitativ hochwertigen Angebot vornehmen").'. '.t("Denn bei uns erhalten Sie").' <b>'.t("Autoteile vom Hersteller").'!</b>';
		echo '</p>';
	

	}

	if ($reg_success!="")
	{
		/*$text='<p>'.t("Willkommen auf mapco.de!").'</p>';
		$text.='<p>'.t("Vielen Dank für Ihre Registrierung. Ihr Konto wurde erfolgreich angelegt. Ab sofort können Sie").'</p>';
		$text.='<ul>';
		$text.='<li>'.t("bequem online bestellen").'</li>';
		$text.='<li>'.t("den Bestellstatus Ihrer Bestellungen einsehen").'</li>';
//		$text.='<li>ihre bisherigen Bestellungen inkl. Rechnung und Lieferschein einsehen</li>';
		$text.='<li>'.t("ihre Kundendaten bearbeiten").'</li>';
		$text.='</ul>';
		$text.='<p>'.t("Wir wünschen Ihnen weiterhin Viel Spaß auf mapco.de!").'</p>';
		send_html_mail($_POST["reg_mail"], t("Registrierung erfolgreich"), $text);*/
		$text='<p>'.t("Willkommen auf mapco.de!").'</p>';
		$text.='<p>'.t("Vielen Dank für Ihre Registrierung. Ihr Konto wurde erfolgreich angelegt.").'</p>';
		$text.='<p>'.t("Sie haben sich mit folgenden Daten registriert:").'</p>';
		$text.='<table>';
		$text.='	<tr>';
		$text.='		<td><p style="display: inline">'.t("Benutzername: ").'</p></td>';
		$text.='		<td style="padding-left: 50px"><p style="display: inline">'.$_POST["reg_username"].'</p></td>';
		/*$text.='<p>'.t("Benutzername: ").$_POST["reg_username"].'</p>';
		$text.='<p>'.t("Passwort: ").$_POST["reg_password"].'</p>';
		$text.='<p>'.t("Emailadresse: ").$_POST["reg_mail"].'</p>';*/
		$text.='	</tr>';
		/*$text.='	<tr>';
		$text.='		<td><p style="display: inline">'.t("Passwort: ").'</p></td>';
		$text.='		<td style="padding-left: 50px"><p style="display: inline">'.$_POST["reg_password"].'</p></td>';
		$text.='	</tr>';*/
		$text.='	<tr>';
		$text.='		<td><p style="display: inline">'.t("Email-Adresse: ").'</p></td>';
		$text.='		<td style="padding-left: 50px"><p style="display: inline">'.$_POST["reg_mail"].'</p></td>';
		$text.='	</tr>';
		$text.='</table>';
		$text.='<p>'.t("Ab sofort können Sie").'</p>';
		$text.='<ul>';
		$text.='<li>'.t("bequem online bestellen").'</li>';
		$text.='<li>'.t("den Bestellstatus Ihrer Bestellungen einsehen").'</li>';
//		$text.='<li>ihre bisherigen Bestellungen inkl. Rechnung und Lieferschein einsehen</li>';
		$text.='<li>'.t("ihre Kundendaten bearbeiten").'</li>';
		$text.='</ul>';
		$text.='<p>'.t("Wir wünschen Ihnen weiterhin Viel Spaß auf mapco.de!").'</p>';
		send_html_mail($_POST["reg_mail"], t("Registrierung erfolgreich"), $text);
	}

	echo '</div>';
	
	include("templates/".TEMPLATE."/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>

