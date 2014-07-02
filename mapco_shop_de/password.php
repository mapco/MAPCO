<?php
	include("config.php");
	include("templates/".TEMPLATE."/header.php");
	include("functions/cms_createPassword.php");
	include("functions/cms_send_html_mail.php");

	//send mail
	if ( isset($_POST["usermail"]) and $_POST["usermail"]=="" and isset($_POST["username"]) and $_POST["username"]=="" )
	{
		echo '<div class="warning">'.t("Bitte nennen uns Ihren Benutzername oder Ihre E-Mail Adresse.").'</div>';	
	}
	elseif ( isset($_POST["usermail"]) and $_POST["usermail"]!="")
	{
		$results=q("SELECT * FROM cms_users WHERE usermail='".mysqli_real_escape_string($dbweb, $_POST["usermail"])."' LIMIT 1;", $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows($results)>0)
		{
			$row=mysqli_fetch_array($results);
			$password = $row["password"];
//			$password = createPassword(8);
//			q("UPDATE cms_users SET password='".$password."' WHERE id_user=".$row["id_user"].";", $dbweb, __FILE__, __LINE__);
//			$text  = '<p>Sie haben auf MAPCO.de ein neues Kennwort für Ihr Benutzerkonto angefordert.</p>';
//			$text .= '<p>Ihr neues Kennwort lautet: '.$password.'</p>';
//			$text .= '<p>Wenn Sie sich angemeldet haben, können Sie Ihr Passwort im Benutzermenü jederzeit ändern.</p>';
			$text  = '<p>Sie haben auf MAPCO.de das Kennwort fuer Ihr Benutzerkonto angefordert.</p>';
			$text .= '<p>Ihr Kennwort lautet: '.$password.'</p>';
			$text .= '<p>Wenn Sie sich angemeldet haben, koennen Sie Ihr Passwort im Benutzermenue jederzeit aendern.</p>';
//			send_html_mail("developer@mapco.de", "MAPCO Online-Shop Passwort", "Benutzername: ".$row["username"].$text);
			send_html_mail($row["usermail"], "Online-Shop Passwort", $text);
//			echo '<div class="success">Ein neues Passwort wurde erfolgreich an Ihre E-Mail-Adresse versendet.</div>';
			echo '<div class="success">'.t("Das Passwort wurde erfolgreich an die hinterlegte E-Mail-Adresse versendet.").'</div>';
			$_POST["username"]="";
			$_POST["usermail"]="";
		}
		else echo '<div class="failure">'.t("Die E-Mail-Adresse konnte nicht gefunden werden.").'</div>';
	}
	elseif ($_POST["username"]!="")
	{
		$results=q("SELECT * FROM cms_users WHERE username='".mysqli_real_escape_string($dbweb, $_POST["username"])."' LIMIT 1;", $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows($results)>0)
		{
			$row=mysqli_fetch_array($results);
			$password = $row["password"];
//			$password = createPassword(8);
//			q("UPDATE cms_users SET password='".$password."' WHERE id_user=".$row["id_user"].";", $dbweb, __FILE__, __LINE__);
//			$text  = '<p>Sie haben auf MAPCO.de ein neues Kennwort für Ihr Benutzerkonto angefordert.</p>';
//			$text .= '<p>Ihr neues Kennwort lautet: '.$password.'</p>';
//			$text .= '<p>Wenn Sie sich angemeldet haben, können Sie Ihr Passwort im Benutzermenü jederzeit ändern.</p>';
			$text  = '<p>Sie haben auf MAPCO.de das Kennwort fuer Ihr Benutzerkonto angefordert.</p>';
			$text .= '<p>Ihr Kennwort lautet: '.$password.'</p>';
			$text .= '<p>Wenn Sie sich angemeldet haben, koennen Sie Ihr Passwort im Benutzermenue jederzeit ändern.</p>';
//			send_html_mail("developer@mapco.de", "MAPCO Online-Shop Passwort", "Benutzername: ".$row["username"].$text);
			send_html_mail($row["usermail"], "NMAPCO Online-Shop Passwort", $text);
//			echo '<div class="success">Ein neues Passwort wurde erfolgreich an Ihre E-Mail-Adresse versendet.</div>';
			echo '<div class="success">'.t("Das Passwort wurde erfolgreich an die hinterlegte E-Mail-Adresse versendet.").'</div>';
			$_POST["username"]="";
			$_POST["usermail"]="";
		}
		else echo '<div class="failure">'.t("Der Benutzername konnte nicht gefunden werden.").'</div>';
	}

	//form
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	echo '<div id="mid_column">';
	echo '<h1>'.t("Passwort vergessen").'</h1>';
	echo '<p>'.t("Geben Sie Ihren Benutzernamen oder Ihre E-Mail-Adresse ein, um sich Ihr Passwort per E-Mail zusenden zu lassen.").'</p>';
	echo '<form method="post" enctype="multipart/form-data">';
	echo '<table class="hover">';
	echo '	<tr>';
	echo '		<td>'.t("Benutzername").'</td><td><input type="text" name="username" value="'.$_POST["username"].'" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>'.t("oder").'</td><td></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>'.t("E-Mail").'</td><td><input type="text" name="usermail" value="'.$_POST["usermail"].'" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2"><input type="submit" value="'.t("Passwort versenden").'" /></td>';
	echo '	</tr>';
	echo '</table>';
	echo '</form>';
	echo '</div>';

	include("templates/".TEMPLATE."/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>