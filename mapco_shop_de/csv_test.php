<?php

	include("config.php");
	
	echo '<form action="csv_test.php" method="post" enctype="multipart/form-data">';
	echo '<b>Bitte eine OP-Liste ausw&auml;hlen....</b><br />';
	echo '<input type="File" name="csv_import" value="" />';

	echo '<input type="submit" name="import" value="CSV-Daten jetzt einlesen" />';
	echo '</form>';

if (isset($_POST["import"]))
{
	
	$res=q("SELECT * FROM payment_notifications WHERE NOT IDIMS_AuftragsNR = 0;", $dbshop, __FILE__, __LINE__);
	while($row=mysql_fetch_array($res))
	{
		if (!isset($payNotes[$row["IDIMS_AuftragsNR"]]["Total"]))
		{
			$payNotes[$row["IDIMS_AuftragsNR"]]["Total"]=$row["total"];
			$payNotes[$row["IDIMS_AuftragsNR"]]["Fee"]=$row["fee"];
		}
		else
		{
			$payNotes[$row["IDIMS_AuftragsNR"]]["Total"]+=$row["total"];
			$payNotes[$row["IDIMS_AuftragsNR"]]["Fee"]+=$row["fee"];
		}
	}
	
	$summe_ges=0;
	$summe_ueberw=0;
	$counter=0;
	$summe_fee=0;
	//$filetext.="";
	$data=array();
	if (!isset($_FILES["csv_import"]["tmp_name"]) || $_FILES["csv_import"]["tmp_name"]=="" )
	{
		echo '<b>ES WURDE KEINE LISTE EINGEGEBEN</b>';
		exit;
	}
	$fp = fopen($_FILES["csv_import"]["tmp_name"], "r"); 
	if (!$fp) exit;
	while( !feof($fp) ) 
	{ 
			
		$zeile = fgetcsv  ( $fp  , 4096 , ";"  ); 
		if (!isset($header))
		{
			$header=array();
			for ($i=0; $i<sizeof($zeile); $i++)
			{
				if ($zeile[$i]=="VOR_AUF_ID") define("AUF_ID", $i);
				if ($zeile[$i]=="KONTO") define("KONTO", $i);
				if ($zeile[$i]=="KUNDE") define("KUNDE", $i);
				if ($zeile[$i]=="FIBU_BELEGNR") define("FIBU_BELEGNR", $i);
				if ($zeile[$i]=="BELEG_VOM") define("BELEG_VOM",$i);
				if ($zeile[$i]=="REST_SUMME") define("REST_SUMME", $i);
			}
		}
		else 
		{
			if (isset($payNotes[$zeile[AUF_ID]]))
			{
				$div=0;
				if(!strpos(" ".$zeile[REST_SUMME],","))
				{
					$tmp_n="00";
				}
				else
				{
					//nachkommastelle
					$tmp_n=substr($zeile[REST_SUMME], strpos($zeile[REST_SUMME],",")+1);
					if (strlen($tmp_n)<2) $tmp_n.="0";
				}
				$tmp_v=substr($zeile[REST_SUMME],0,strpos($zeile[REST_SUMME],","));
				$idims_summe=number_format($tmp_v.$tmp_n,0,"","");
				
				$ipn_summe=str_replace(".","", $payNotes[$zeile[AUF_ID]]["Total"]);
				$div=$idims_summe-$ipn_summe;
				if ($div<0) $div=$div*(-1);
				if ($div<5)
				{
					//echo "MATCH!!!: (".$ipn_summe."|".$idims_summe.") ";
					//echo $zeile[AUF_ID]." ".$zeile[KUNDE]." ".$zeile[FIBU_BELEGNR]." ".$zeile[BELEG_VOM]." ".$zeile[REST_SUMME]." Differenz: ".$div."<br />";
					//$filetext.=$zeile[FIBU_BELEGNR].";".$zeile[KUNDE].";".$zeile[BELEG_VOM].";".$zeile[REST_SUMME]."\n\r";
					$data[$counter][0]=$zeile[FIBU_BELEGNR];
					$data[$counter][1]=$zeile[KUNDE];
					$data[$counter][2]=$zeile[BELEG_VOM];
					$data[$counter][3]=$zeile[REST_SUMME];
					//GESAMTSUMME:
					$counter++;
					$summe_ges+=$payNotes[$zeile[AUF_ID]]["Total"];
					$summe_ueberw+=$payNotes[$zeile[AUF_ID]]["Total"]-$payNotes[$zeile[AUF_ID]]["Fee"];
					$summe_fee+=$payNotes[$zeile[AUF_ID]]["Fee"];
				}
			}
		}
	}
	fclose($fp);
	
	if (sizeof($data)>0)
	{
		$head = array('FIBU_BELEGNR', 'KUNDE', 'BELEG_VOM', 'REST_SUMME');
		$filename='soa/op_listenabgleich_'.date("dmY").'.csv';
		$fp = fopen($filename, 'a');
		fputcsv($fp, $head, ';');
		for ($i=0; $i<sizeof($data); $i++)
		{
			fputcsv($fp, $data[$i], ';');
		}
		fclose($fp);

		echo '<a href="http://www.mapco.de/'.$filename.'">CSV herunterladen</a><br />';
		//print_r($data);
	}
	
	echo 'Rechnungssumme: '.$summe_ges.'<br />';
	echo 'Summe zur &Uuml;berweisung: '.$summe_ueberw.'<br />';
	echo 'PayPal Geb&uuml;hren: '.$summe_fee.'<br />';
	echo 'Anzahl der zugeordneten Zahlungen: '.$counter;
	
	
}

?>
