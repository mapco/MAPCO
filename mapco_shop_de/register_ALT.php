<?php
	include("config.php");
	include("templates/".TEMPLATE."/header.php");
	include("functions/cms_send_html_mail.php");
	include("functions/cms_t.php");


	if ($reg_success!="") echo '<div class="success">'.$reg_success.'</div>';
	
	if (!($reg_success!="") and $_SESSION["id_user"]>0)
	{
		echo '<div class="failure">Sie sind bereits mit einem Konto angemeldet. Wenn Sie ein neues Konto registrieren möchten, loggen Sie sich bitte zunächst aus!</div>';
		include("templates/".TEMPLATE."/cms_leftcolumn.php");
	}
	else
	{
		//show errors
		if ($reg_error!="") echo '<div class="failure">'.$reg_error.'</div>';
		include("templates/".TEMPLATE."/cms_leftcolumn.php");
		echo '<div id="mid_column">';
		echo '<form method="post">';
		echo '<table class="hover">';
		echo '	<tr>';
		echo '		<th colspan="2">'.t("Registrierung als Endkunde").'</th>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td colspan="2">'.t("Als Gewerbekunde können Sie sich").' <a href="'.PATHLANG.'online-shop/registrieren/">'.t("hier registrieren!").'</a></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Anrede").'</td>';
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
		echo '		<td>'.t("Vorname").'</td><td><input class="customer" type="text" name="reg_firstname" value="'.$_POST["reg_firstname"].'" /></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Nachname").'</td><td><input class="customer" type="text" name="reg_lastname" value="'.$_POST["reg_lastname"].'" /> <span class="customer_span">*</span></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Benutzername").'</td><td><input type="text" name="reg_username" value="'.$_POST["reg_username"].'" /><span class="customer_span">*</span></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Passwort").'</td><td><input type="password" name="reg_password" value="'.$_POST["reg_password"].'" /><span class="customer_span">*</span></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("Passwort wiederholen").'</td><td><input type="password" name="confirm_password" value="'.$_POST["confirm_password"].'" /><span class="customer_span">*</span></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>'.t("E-Mail").'</td><td><input type="text" name="reg_mail" value="'.$_POST["reg_mail"].'" /><span class="customer_span">*</span></td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td colspan="2"><input type="checkbox" name="reg_newsletter" value="1"/> '.t("Ja ich möchte den MAPCO-Newsletter empfangen", __FILE__, __LINE__).'!</td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td colspan="2"><input type="submit" name="reg_button" value="'.t("Registrieren").'" /></td>';
		echo '	</tr>';
		echo '  <tr><td colspan="2"><span class="customer_span">*</span> = '.t("Pflichtfelder bei Endkunden").'</td></tr>';
		echo '</table>';
		echo '</form>';
		echo '</div>';
	}

	if ($reg_success!="")
	{
		$text='<p>'.t("Willkommen auf mapco.de!").'</p>';
		$text.='<p>'.t("Vielen Dank für Ihre Registrierung. Ihr Konto wurde erfolgreich angelegt. Ab sofort können Sie").'</p>';
		$text.='<ul>';
		$text.='<li>'.t("bequem online bestellen").'</li>';
		$text.='<li>'.t("den Bestellstatus Ihrer Bestellungen einsehen").'</li>';
//		$text.='<li>ihre bisherigen Bestellungen inkl. Rechnung und Lieferschein einsehen</li>';
		$text.='<li>'.t("ihre Kundendaten bearbeiten").'</li>';
		$text.='</ul>';
		$text.='<p>'.t("Wir wünschen Ihnen weiterhin Viel Spaß auf mapco.de!").'</p>';
		send_html_mail($_POST["reg_mail"], "Registrierung erfolgreich", $text);
	}

	include("templates/".TEMPLATE."/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>