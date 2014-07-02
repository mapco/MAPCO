<?php

//include("../../mapco_shop_de/config.php");
include("../functions/cms_send_html_mail.php");


$zeit=time();
$datum_heute=mktime(0,0,1,date('m', $zeit), date('d', $zeit), date('Y', $zeit));
$tage_seit_montag=date('N')-1;
$sekunden_seit_montag=$tage_seit_montag*86400;
$zeit_montag=$zeit-$sekunden_seit_montag;
$montag=mktime(0,0,1,date('m', $zeit_montag), date('d', $zeit_montag), date('Y', $zeit_montag));

//USERARRAY
//Andy Braun, Tobias Buls, Kai Fröhlich, Adrian Hoppe, Andre Mischke, Nikolas Müller, A.Reinke
$user = array (0 => 29115, 1 => 28623, 2 => 22733, 3 => 28624, 4 => 23617, 5 => 23916, 6=> 30719);


//QUERY-STRINGS
$sql1="select * from shop_price_research where firstmod_user in (";
$sql2="select * from shop_price_suggestions where firstmod_user in (";
for ($i=0; $i<sizeof($user); $i++)
	{
		if ($i==0) $sql=$user[$i]; else $sql.=", ".$user[$i];
	}
$sql1.=$sql.") and firstmod > ".$montag.";";
$sql2.=$sql.") and firstmod > ".$montag.";";

//$res=q("select * from shop_price_research where firstmod_user in (29115, 28623, 22733, 28624, 23617, 23916) and firstmod => ".$montag.";", $dbshop, __FILE__, __LINE__);
//PRICE RESEARCH
$res=q($sql1, $dbshop, __FILE__, __LINE__);
while ($row=mysqli_fetch_array($res)) 
{
	if (!isset($user_pr[$row["firstmod_user"]][date('N', $row["firstmod"])])) $user_pr[$row["firstmod_user"]][date('N', $row["firstmod"])]=0;
	
	$user_pr[$row["firstmod_user"]][date('N', $row["firstmod"])]++;
}
//PRICE SUGGESTIONS
$res=q($sql2, $dbshop, __FILE__, __LINE__);
while ($row=mysqli_fetch_array($res)) 
{
	if (!isset($user_ps[$row["firstmod_user"]][date('N', $row["firstmod"])])) $user_ps[$row["firstmod_user"]][date('N', $row["firstmod"])]=0;
	
	$user_ps[$row["firstmod_user"]][date('N', $row["firstmod"])]++;
}
/*
for ($i=0; $i<sizeof($user); $i++) {
	for ($j=1; $j<=5; $j++) {
		if (isset($user_pr[$user[$i]][$j])) echo 'User: '.$user[$i].' Tag: '.$j.' Anzahl: '.$user_pr[$user[$i]][$j].'<br />';
	}
}
*/
//USER MAIL
for ($i=0; $i<sizeof($user); $i++) {
	$sum_ps=$sum_pr=0;
	$msg ='<p>&Uuml;bersicht der recherchierten Referenzen / gegebenen Preisvorschl&auml;ge in dieser Woche.<br />';
	$msg.='<small><i>Vorgabe: 10 recherchierte Referenzen / Preisvorschl&auml;ge pro Tag (je 50 pro Woche)</i></small></p>';
	$msg.='<p><table style="border:2px solid">';
	$msg.='<colgroup><col width="200"><col width="150"><col width="150"></colgroup>';
	$msg.='<tr><th style="text-align:left"><b>Wochentag</b></th><th style="text-align:left"><b>rech. Referenzen</b></th><th style="text-align:left"><b>Preisvorschl&auml;ge</b></th></tr>';
	for ($j=1; $j<=5; $j++) {
		$wochentag=$montag+(86400*($j-1));
		$msg.='<tr style="border:1px solid"><td>'.date("d.m.Y, l", $wochentag).'</td>';
		if (isset($user_pr[$user[$i]][date("N", $wochentag)]))
			{
				$msg.='<td style="text-align:right; padding-right:80px">'.$user_pr[$user[$i]][date("N", $wochentag)].'</td>'; 
				$sum_pr+=$user_pr[$user[$i]][date("N", $wochentag)];
			}
		else { $msg.='<td style="text-align:right; padding-right:80px">0</td>'; }
		if (isset($user_ps[$user[$i]][date("N", $wochentag)]))
			{
				$msg.='<td style="text-align:right; padding-right:80px">'.$user_ps[$user[$i]][date("N", $wochentag)].'</td>'; 
				$sum_ps+=$user_ps[$user[$i]][date("N", $wochentag)];
			}
		else { $msg.='<td style="text-align:right; padding-right:80px">0</td>'; }
		$msg.='</tr>';
		//$msg.='<td>'.$user_ps[$user[$i]][date("N", $wochentag)].'</td></tr>';
	}
	$msg.='<tr><td><b>Woche gesamt</b></td><td style="text-align:right; padding-right:80px"><b>'.$sum_pr.'</b></td><td style="text-align:right; padding-right:80px"><b>'.$sum_ps.'</b></td></tr>';
	
	$msg.='</table></p>';

//get MAIL from USER
$res=q("select firstname, lastname, mail from cms_contacts where idCmsUser = '".$user[$i]."';", $dbweb, __FILE__, __LINE__);
if (mysqli_num_rows($res)==1) 
	{
		$row=mysqli_fetch_array($res);
		send_remind_mail($row["mail"], "Übersicht der Recherchen für die KW ".date("W"), $msg);
	//	send_remind_mail('nputzing@mapco.de','@'.$row["mail"].'-Übersicht der Recherchen für die KW '.date("W"), $msg);
		echo 'Recherche&uuml;bersicht an '.$row["firstname"].' '.$row["lastname"].' ('.$user[$i].') gesendet! <br />';
	}
else {
		echo 'KEINE Recherche&uuml;bersicht an '.$row["firstname"].' '.$row["lastname"].' ('.$user[$i].') gesendet! <br />';

	}

}
?>