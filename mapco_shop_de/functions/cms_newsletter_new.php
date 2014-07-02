<?php
	///äöüÄÖÜ UTF-8
	if (!function_exists("newsletter_new"))
	{
		function newsletter_new($usermail, $id=0)
		{
			global $dbweb;
			global $dbshop;
			
			$mail = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
			$mail .= '<html xmlns="http://www.w3.org/1999/xhtml">';
			$mail .= '<head>';
			$mail .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			$mail .= '<title>Newsletter</title>';
			$mail .= '</head>';
			$mail .= '<body>';
			$mail .= '<table style="width:600px;" width="600" align="center">';
			$mail .= '<tr>';
			$mail .= '<td style="font-size:12px; font-family:Arial, Helvetica, sans-serif; color:#333333"></td>';
			$mail .= '<td align="right" style="font-size:12px; font-family:Arial, Helvetica, sans-serif;"><a href="http://www.mapco.de?lang=de" target="_blank">Startseite</a> | <a href="http://www.mapco.de/user_index.php?lang=de" target="_blank">Login</a></td>';
			$mail .= '</tr>';
			$mail .= '<tr>';
			$mail .= '<td colspan="2"><img  border="0" src="http://www.mapco.de/images/newsletter/newsletter_kopf.png" alt="MAPCO Autotechnik GmbH" title="MAPCO Autotechnik GmbH" /></td>';
			$mail .= ' </tr>';	
			$mail .= '<tr>';
			$mail .= '<td style="font-size:12px; font-family:Arial, Helvetica, sans-serif;" width="600" valign="top">';
		
			$mail .= '<h3>';
			$mail .= 'Sie wollen weiterhin neueste Informationen von MAPCO?<br />Dann aktualisieren Sie jetzt Ihre Newsletter-Anmeldung!';
			$mail .= '</h3>';
			$mail .= '<p>Sehr geehrte Damen und Herren, sehr geehrte Geschäftspartner!</p>';
			$mail .= '<p align="justify">';
			$mail .= 'MAPCO Autotechnik versendet in regelmäßigen Abständen Newsletter mit aktuellen Informationen zu neuen Produkten und interessanten Verkaufsaktionen.';
			$mail .= 'Wir haben das Anmeldeverfahren und die Datenspeicherung den ab 1. September geltenden Bestimmungen des Bundesdatenschutzgesetzes angepasst.';
			$mail .= 'Damit Sie auch in Zukunft das Neueste rund um MAPCO Autotechnik erfahren können, bitten wir Sie, Ihr Einverständnis für den kostenlosen Empfang des Newsletters erneut zu bestätigen. Sie erhalten diese E-Mail, weil Sie bereits bei uns registriert sind. Möchten Sie den Newsletter weiterhin erhalten, folgen Sie bitte diesem Link:';
			$mail .= '</p>';			
			$mail .= '<p><b><a href="http://www.mapco.de/index.php?id='.$id.'" target="_blank">Newsletter Anmeldung bestätigen</a></b></p>';

			$mail .= '<br /><br />';
			$mail .= '<h3>';
			$mail .= 'Tanken Sie doch mal mit MAPCO auf!';
			$mail .= '</h3>';
			$mail .= '<p align="justify">';
			$mail .= 'Ob Diesel oder Benzin, die Kraftstoffpreise erreichen in diesem Sommer immer wieder ein neues Jahres-Hoch. MAPCO Autotechnik startet für seine gewerblichen Kunden gemeinsam mit Aral nur im September eine Bonusaktion. Weitere Informationen erhalten Sie von Ihrem zuständigen Außendienstmitarbeiter oder in einem unserer RegionalCENTER!';
			$mail .= '</p>';			
			$mail .= '<br />';
			$mail .= '<p>';
			$mail .= 'Mit freundlichen Grüßen';
			$mail .= '</p>';
			$mail .= '<p>';
			$mail .= 'Frank Langrock<br />';
			$mail .= '- Kommunikation -<br />';
			$mail .= 'MAPCO Autotechnik GmbH';
			$mail .= '</p>';
			$mail .= '<br /><br />';
			$mail .= '</td>';
			$mail .= '</tr>';        
			$mail .= '<tr>';
			$mail .= '<td colspan="2" align="center">';
			$mail .= '<p style="font-size:9px; font-family:Arial, Helvetica, sans-serif; color:#333333";>Wenn Sie diesen Newsletter nicht mehr beziehen möchten, können Sie sich <a href="http://www.mapco.de/index.php?lang=de&unsubscribe=1&email='.$usermail.'" target="_blank">hier abmelden</a>.</p><br />';
			$mail .='</td>';
			$mail .= '</tr>';
			$mail .= '<tr>';
			$mail .= '<td colspan="2" align="center" style="font-size:9px; font-family:Arial, Helvetica, sans-serif; color:#333333";>';
			$mail .= '<p>Impressum / Rechtliche Verantwortung <br />';
			$mail .= 'MAPCO Autotechnik GmbH, Moosweg 1, DE-14822 Borkheide <br />';
			$mail .= 'Telefon: +49 33845 6 00 30 / Telefax: +49 33845 4 10 32 <br />';
			$mail .= 'E-Mail: info@mapco.eu / URL: www.mapco.eu <br />';
			$mail .= 'Geschäftsführer: Detlev Seeliger / Registergericht: Amtsgericht Potsdam <br />';
			$mail .= 'Registernummer: HRB 3965 / Steuernr.: 048/114/01965 / Ust.-ID-Nr.: DE 138456821 (gemäß § 27a UStG)</p>';
			$mail .= '<p></p>';
			$mail .= '<p></p>';
			$mail .= '</table>';
			$mail .= '</body>';
			$mail .= '</html>';
			return($mail);
		}
	}
?>