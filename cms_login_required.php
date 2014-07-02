<?php

 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
	//LOGIN
//	echo '<form method="post" action="'.PATHLANG.$_GET["url"].'">';
	echo '<form method="post" action="'.$pageURL.'">';
	echo '<table class="hover">';
	echo '	<tr>';
	echo '		<th colspan="2">'.t("Anmeldung").'</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>'.t("E-Mail").' / '.t("Benutzername").'</td>';
	echo '		<td><input type="text" name="login_user" value="'.$_POST["form_username"].'" /></td>';
	echo '	</tr>';
	echo '		<td>'.t("Passwort").'</td>';
	echo '		<td><input type="password" name="login_pw" value="'.$_POST["form_password"].'" /></td>';
	echo '	</tr>';
	echo '	</tr>';
	echo '		<td colspan="2"><input type="submit" value="'.t("Anmelden").'" /></td>';
	echo '	</tr>';
	echo '	</tr>';
	echo '		<td colspan="2">';
	echo '			<ul>';
	echo '				<li><a href="'.PATHLANG.'online-shop/passwort/">'.t("Passwort vergessen? Lassen Sie sich ein neues zuschicken!").'</a></li>';
	echo '			</ul>';
	echo '		</td>';
	echo '	</tr>';
	echo '</table>';
	echo '</form>';


	//REGISTER
	echo '<form action="'.PATHLANG.'registrieren/" method="post">';
	echo '<table class="hover">';
	echo '	<tr>';
	echo '		<th colspan="2">'.t("Registrierung als Endkunde").'</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">'.t("Als Gewerbekunde können Sie sich").' <a href="'.PATHLANG.'online-shop/registrieren/">'.t("hier registrieren!").'</a></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td colspan="2">'.t("Sie haben noch kein Benutzerkonto? Registrieren Sie sich jetzt!").'</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>'.t("Benutzername").'</td>';
	echo '		<td><input type="text" name="reg_username" value="'.$_POST["reg_username"].'" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>'.t("Passwort").'</td>';
	echo '		<td><input type="password" name="reg_password" value="'.$_POST["reg_passwort"].'" /></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>'.t("Passwort wiederholen").'</td>';
	echo '		<td><input type="password" name="confirm_password" value="'.$_POST["confirm_passwort"].'" /></td>';
	echo '	</tr>';
	echo '	</tr>';
	echo '		<td>'.t("E-Mail").'</td>';
	echo '		<td><input type="text" name="reg_mail" value="'.$_POST["reg_mail"].'" /></td>';
	echo '	</tr>';
	echo '	</tr>';
	echo '		<td colspan="2"><input type="submit" name="reg_button" value="'.t("Registrieren").'" /></td>';
	echo '	</tr>';
	echo '</table>';
	echo '</form>';
?>