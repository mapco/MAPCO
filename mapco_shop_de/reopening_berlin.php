<?php
	include("config.php");
	include("templates/".TEMPLATE."/header.php");
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	include("functions/cms_send_html_mail.php");
	include("functions/cms_t.php");

	echo '<div id="mid_right_column">';
	
	if (isset($_POST["form_submit"]))
	{
		if($_POST["name"]=="") echo '<div class="failure">Bitte geben Sie Name und Vorname an!</div>';
		elseif (strpos($_POST["mail"], "@")==0 or strpos($_POST["mail"], ".")==0) echo '<div class="failure">Bitte geben Sie eine gültige E-Mail Adresse an!</div>';
		else
		{
			$mail = '<html>';
			$mail .= '<head>';
			$mail .= '<title>Anmeldung zur Neueröffnung</title>';
			$mail .= '</head>';
			$mail .= '<body>';
			$mail .= '<p><h2>Anmeldung für die Verlosung zur Neueröffnung</h2></p>';
			$mail .= '<table>';
			$mail .= '<tr><td>Name:</td><td>'.$_POST["name"].'</td></tr>';
			$mail .= '<tr><td>Firma:</td><td>'.$_POST["company"].'</td></tr>';
			$mail .= '<tr><td>E-Mail:</td><td>'.$_POST["mail"].'</td></tr>';
			$mail .= '<tr><td>Personen:</td><td>'.$_POST["count"].'</td></tr>';
			if($_POST["phone"]!="" or $_POST["comment"]!="")
			{ 
				$mail .= '<tr><td colspan="2"><br /><b>Bitte nehmen Sie umgehend Kontakt zum Kunden auf:</b></td></tr>';
				$mail .= '<tr><td><b>Telefon:</b></td><td>'.$_POST["phone"].'</td></tr>';
				$mail .= '<tr><td colspan="2"><br /><b>wegen folgender Fragen:</b></td></tr>';
				$mail .= '<tr><td colspan="2">'.nl2br($_POST["comment"]).'</td></tr>';
			}
		    $mail .= '</table>';
  		  	$mail .= '</body>';
  			$mail .= '</html>';
			SendMail('berlin@mapco.de', $_POST["mail"], 'Anmeldung zur Neueröffnung RC Berlin 2014', $mail);
			SendMail('pm@mapco.eu', $_POST["mail"], 'Anmeldung zur Neueröffnung RC Berlin 2014', $mail);

			unset ($_POST);
			echo '<div class="success">Vielen Dank für Ihre Anmeldung, wir freuen uns auf Ihren Besuch!</div>';
		}
	}
	
	echo '  <img src="'.PATH.'images/reopening_berlin/reopening_header.jpg" style="width:628px; padding:20px 64px 0px;" alt="Neueröffnung im RegionalCENTER Berlin" />';

	echo '	<div style="padding:15px 75px; width:606px;">';

	echo '<form method="post">';
	echo '	<br /><h2 style="font-size:18px;">Anmeldung</h2><hr style="margin:0;">';
	echo '	<p style="font-size:16px; text-align:justify;">';
	echo '	Melden Sie sich hier für die Verlosung unserer Preise bei der Eröffnung unseres neuen RegionalCENTER an, ';
	echo '	und sichern Sie sich mit Ihrem Kommen die Teilnahme an unserem Losverfahren.';
	echo '	</p><p style="font-size:16px; text-align:justify;">';
	echo '	Name, Vorname <span class="customer_span">*</span><br />';
	echo '	<input class="customer" type="text" name="name" value="'.$_POST["name"].'" />';
	echo '	</p>';
	echo '	</p><p style="font-size:16px; text-align:justify;">';
	echo '	Firma <span class="customer_span">*</span><br />';
	echo '	<input class="customer" type="text" name="company" value="'.$_POST["company"].'" />';
	echo '	</p>';
	echo '	</p><p style="font-size:16px; text-align:justify;">';
	echo '	E-Mail <span class="customer_span">*</span><br />';
	echo '	<input class="customer" type="text" name="mail" value="'.$_POST["mail"].'" />';
	echo '	</p>';
	echo '	</p><p style="font-size:16px; text-align:justify;">';
	echo '	Wieviele Personen bringen Sie mit?<br />';
	echo '	<input class="customer" type="text" name="count" value="'.$_POST["count"].'" />';
	echo '	</p><br />';
	echo '	</p><p style="font-size:16px; text-align:justify;">';
	echo '	Sie haben Fragen zur Eröffnung?<br />';
	echo '	Hinterlassen Sie uns eine Nachricht und Ihre Telefonnummer**, wir werden uns bei Ihnen schnellstmöglich melden.<br />';
	echo '	</p><p style="font-size:16px; text-align:justify;">';
	echo '	Telefon**<br />';
	echo '	<input class="customer" type="text" name="phone" value="'.$_POST["phone"].'" />';
	echo '	<textarea class="customer" style="width:100%; margin-top:2px;" rows="8" name="comment" value="'.$_POST["comment"].'"></textarea>';
	echo '	</p><p style="font-size:16px; text-align:justify;">';
	echo '  <span class="customer_span">*</span> = Pflichtfelder';
	echo '	</p><p style="font-size:16px; text-align:justify;">';
	echo '<input id="cart_submit_button" style="float:left;" type="submit" name="form_submit" value="anmelden" /><br />';
	echo '	</p>';
	echo '</form>';
	echo '<p style="font-size:16px; text-align:justify; margin-top:60px;">';
	echo '	Anfahrt<hr style="margin:0;">';
	echo '<table><tr><td>';
	echo 'MAPCO RegionalCENTER Berlin<br />';
	echo 'Richard-Tauber-Damm 23<br />';
	echo '<br />';
	echo '12277 Berlin';
	echo '</td><td>';
	echo 'Tel.: 030 / 70 76 41 59<br />';
	echo 'FAX: 030 / 70 76 96 14<br />';
	echo 'E-Mail: <a href="mailto:berlin@mapco.de">berlin@mapco.de</a><br />';
	echo 'URL: <a href="http://www.mapco-berlin.de" target="_blank" title="MAPCO Berlin">www.mapco-berlin.de</a>';
	echo '</td><td>';
	echo '<b>Ansprechpartner:</b><br />';
	echo 'Hans Rupp<br />';
	echo 'Sven Hübner<br />';
	echo 'Michael Geier<br />';
	echo 'Henry Schulz';
	echo '</td></tr></table>';
	echo '	</p><p style="font-size:16px; text-align:justify;">';
	echo '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2433.379009564667!2d13.393160000000009!3d52.41792999999993!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47a8451e941c4b4f%3A0xdebcba73c1fbb9ec!2sRichard-Tauber-Damm+23!5e0!3m2!1sde!2sde!4v1401449502163" width="606" height="400" frameborder="0" style="border:0"></iframe>';
	echo '</p>';

	echo '	</div>';	
	echo '</div>';

	include("templates/".TEMPLATE."/footer.php");
?>
