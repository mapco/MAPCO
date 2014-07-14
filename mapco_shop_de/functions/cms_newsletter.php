<?php
	///äöüÄÖÜ UTF-8
	if (!function_exists("newsletter"))
	{
		function newsletter($usermail, $id_article, $title, $article, $introduction, $firstmod, $lang)
		{
			global $dbweb;
			global $dbshop;

			include_once("cms_url_encode.php");
			include_once("cms_tl.php");
			
			$mail = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
			$mail .= '<html xmlns="http://www.w3.org/1999/xhtml">';
			$mail .= '<head>';
			$mail .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			$mail .= '<title>Newsletter</title>';
			$mail .= '</head>';
			$mail .= '<body>';
			$mail .= '<!-- Link Start -->';
			$mail .= '<p align="center" style="font-size:9px; font-family:Arial, Helvetica, sans-serif; color:#333333";>Wird der Newsletter nicht korrekt angezeigt? <a target="_blank" href="http://www.mapco.de/'.$lang.'/news/'.$id_article.'/'.url_encode($title).'">Klicken Sie hier!</a></p>';
			$mail .= '<!-- Link Stop -->';
			$mail .= '<table style="width:600px;" width="600px" align="center">';
			$mail .= '<tr>';
			$mail .= '<td style="font-size:12px; width:70%; font-family:Arial, Helvetica, sans-serif; color:#333333">Newsletter Kalenderwoche #'.date("W", $firstmod).'</td>';
			$mail .= '<td align="right" style="font-size:12px; width:30%; font-family:Arial, Helvetica, sans-serif;"><a href="http://www.mapco.de/'.$lang.'/" target="_blank">Startseite</a> | <a href="http://www.mapco.de/'.$lang.'/login/" target="_blank">Login</a></td>';
			$mail .= '</tr>';
			if (strpos($article, "NO HEADER -->")==0)
			{
			$mail .= '<tr>';
			$mail .= '<td colspan="2"><img  border="0" src="http://www.mapco.de/images/newsletter/newsletter_kopf.png" alt="MAPCO Autotechnik GmbH" title="MAPCO Autotechnik GmbH" /><br /><br /></td>';
			$mail .= ' </tr>';	
			}
			$mail .= '<tr>';
			if($introduction!="")
			{
				$mail .= '<td style="font-size:12px; font-family:Arial, Helvetica, sans-serif;" width="400px" valign="top">';
				$mail .= '<h3>';
				$mail .= $title;
				$mail .= '</h3>';
				$mail .= $article.'<br /><br />';
				$mail .= '</td>';
				$mail .= '<td style="font-size:12px; font-family:Arial, Helvetica, sans-serif;" width="200px" align="right" valign="top">';
				$mail .= '<br />';
				$mail .= $introduction;
				$mail .= '</td>';
			}
			else
			{
				$mail .= '<td  colspan="2" style="font-size:12px; font-family:Arial, Helvetica, sans-serif;" width="600px" valign="top">';
				$mail .= '<h3>';
				$mail .= $title;
				$mail .= '</h3>';
				$mail .= $article.'<br /><br />';
				$mail .= '</td>';
			}
			$mail .= '</tr>';        
			if(isset($usermail) and $usermail!="-")
			{
				$mail .= '<tr>';
				$mail .= '<td colspan="2" align="center">';
				$mail .= '<p style="font-size:9px; font-family:Arial, Helvetica, sans-serif; color:#333333";>Wenn Sie diesen Newsletter nicht mehr beziehen möchten, können Sie hier den <a href="'.PATHLANG.tl(833, "alias").$usermail.'/" target="_blank" title="'.tl(833, "description").'">'.tl(833, "title").'</a>.</p><br />';
				$mail .='</td>';
				$mail .= '</tr>';
			}
			$mail .= '<tr>';
			$mail .= '<td colspan="2" align="center" style="font-size:9px; font-family:Arial, Helvetica, sans-serif; color:#333333";>';
			$mail .= '<p>Impressum / Rechtliche Verantwortung <br />';
			$mail .= 'MAPCO Autotechnik GmbH, Moosweg 1, DE-14822 Borkheide <br />';
			$mail .= 'Telefon: +49 33845 6 00 30 / Telefax: +49 33845 4 10 32 <br />';
			$mail .= 'E-Mail: info@mapco.eu / URL: www.mapco.com <br />';
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