<?php
	require_once("config.php");
	require_once("functions/cms_t2.php");
	require_once("modules/fpdf/fpdf.php");
@	require_once("modules/fpdi/1.3.2/fpdi.php");

	session_start();
	
	if ( !isset($_POST["items"]) )
	{
		echo '<ItemExportResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Es konnte keine Referenzenliste gefunden werden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte keine Shopartikel-ID gefunden werden. Die ID ist notwendig, da der Service sonst nicht weiß, welchen Shopartikel er exportieren soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemExportResponse>'."\n";
		exit;
	}
	$_POST["items"]=explode(",", $_POST["items"]);
	
	if ( !isset($_POST["id_location"]) )
	{
		echo '<ItemExportResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Standort-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte keine Standort-ID gefunden werden. Die ID ist notwendig, da der Service sonst nicht weiß, welchen Anschrift er exportieren soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemExportResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["lang"]) )
	{
		echo '<ItemExportResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Sprache nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte keine Sprache gefunden werden. Die Sprachangabe ist notwendig, da der Service sonst nicht weiß, in welcher Sprache er exportieren soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemExportResponse>'."\n";
		exit;
	}

	//get XML data
	$responseXml=post(PATH."soa/", array("API" => "shop", "Action" => "ListView", "id_list" => $_POST["id_list"]));
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
		echo '<ItemExportResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>XML nicht valide.</shortMsg>'."\n";
		echo '		<longMsg>Die zurückgelieferten XML-Daten sind nicht valide und können deshalb nicht ausgewertet werden. Service gestoppt.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '	<Response><![CDATA['.$responseXml.']]></Response>'."\n";
		echo '</ItemExportResponse>'."\n";
		exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);

	$colwidth=array();
	$colsize=array();
	$maxwidth=197.5;
	//set colwidth for image col
	for($i=0; $i<count($response->Header[0]); $i++)
	{
		$fieldname=$response->Header[0]->ColumnName[$i]["name"];
		if( $fieldname=="ImagePreview" )
		{
			$maxwidth-=65;
			$colwidth[$i]=65;
			$colsize[$i]= $colwidth[$i] / 197.5 * 100;
		}
	}
	//set colwidth for all other cols
	for($i=0; $i<count($response->Header[0]); $i++)
	{
		$fieldname=$response->Header[0]->ColumnName[$i]["name"];
		if( $fieldname!="ImagePreview" )
		{
			$colwidth[$i]=$maxwidth / (count($response->Header[0])-1);
			$colsize[$i]= $colwidth[$i] / 197.5 * 100;
		}
	}
//	print_r($colwidth);
//	print_r($colsize);


	$results=q("SELECT * FROM cms_contacts_locations WHERE id_location=".$_POST["id_location"].";", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$contact=$row["title"].", ".$row["street"].", ".$row["zipcode"]." ".$row["city"];
	$contact=utf8_decode($contact);
	$phone=t("Telefon", __FILE__, __LINE__, $_POST["lang"]).": ".$row["phone"].", ".t("Telefax", __FILE__, __LINE__, $_POST["lang"]).": ".$row["fax"];
	$phone=utf8_decode($phone);
	$mail=$row["website"].", ".t("E-Mail", __FILE__, __LINE__, $_POST["lang"]).": ".$row["mail"];
	$mail=utf8_decode($mail);
	$disclaimer=t("Die angezeigten OE- und OEM-Nummern dienen nur zur Zuordnung technischer Daten und der Verwendungszwecke.", __FILE__, __LINE__, $_POST["lang"]);
	$disclaimer=utf8_decode($disclaimer);
	

	//Master-PDF öffnen
	$pdf = new FPDI();
	$pdf->setSourceFile('images/PdfExport/master.pdf');
	$tplidx = $pdf->importPage(1);
	$f=$pdf->getTemplateSize(1);


	//Tabelle zeichnen
	$y=260;
	for($i=0; $i<count($response->Item); $i++)
	{
		//add new page
		if ($y>250)
		{
			$pdf->addPage("P", array($f["w"], $f["h"]));
			$pdf->useTemplate($tplidx);
			$pdf->setMargins(0, 0, 0);
			$pdf->SetAutoPageBreak(0, 0);
			$pdf->SetDrawColor(0, 0, 0);
			//write title1
			if ($_POST["title1"]!="")
			{
				//determine font size
				$fontsize=100;
				$stringwidth=101;
				while( $stringwidth>100 )
				{
					$fontsize--;
					$pdf->SetFont('helvetica', 'BI', $fontsize);
					$stringwidth=$pdf->getStringWidth(utf8_decode($_POST["title1"]));
				}

				$pdf->SetXY(35, 22);
				$pdf->SetTextColor(255, 255, 255);
				$pdf->MultiCell(180, 6, utf8_decode($_POST["title1"]) , 0, 'L', 0);
			}
			//write title2
			if ($_POST["title2"]!="")
			{
				//determine font size
				$fontsize=100;
				$stringwidth=91;
				while( $stringwidth>90 )
				{
					$fontsize--;
					$pdf->SetFont('helvetica', 'B', $fontsize);
					$stringwidth=$pdf->getStringWidth(utf8_decode($_POST["title2"]));
				}

				$pdf->SetXY(45, 45);
				$pdf->SetTextColor(255, 255, 255);
				$pdf->MultiCell(180, 6, utf8_decode($_POST["title2"]) , 0, 'L', 0);
			}
			//write contact information
			$pdf->SetXY(5.9, 56.5);
			$pdf->SetTextColor(255, 255, 255);
			$pdf->SetFont('helvetica', 'B', 10);
			$pdf->MultiCell(180, 6, $contact , 0, 'L', 0);
			//write phone numbers
			$pdf->SetXY(5.9, 270);
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetFont('helvetica', 'B', 18);
			$pdf->MultiCell(197.5, 22, $phone , 0, 'C', 0);
			//write mail address
			$pdf->SetXY(5.9, 276);
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetFont('helvetica', '', 12);
			$pdf->MultiCell(197.5, 22, $mail , 0, 'C', 0);
			//write disclaimer
			$pdf->SetXY(5.9, 282);
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetFont('helvetica', 'I', 10);
			$pdf->MultiCell(197.5, 22, $disclaimer , 0, 'C', 0);
			//write header
			$pdf->SetXY(0, 65);
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetFont('helvetica', '', 12);
			$y=$pdf->GetY();
			$xx=5.9;
			//determine height for all cols
			$maxlines=0;
			for($j=0; $j<count($response->Header[0]); $j++)
			{
				$text=utf8_decode($response->Header[0]->ColumnName[$j]);
				$lines[$j]=ceil(($pdf->getStringWidth($text)+8) / $colwidth[$j]);
				if( $maxlines<$lines[$j] ) $maxlines=$lines[$j];
			}
			for($j=0; $j<count($response->Header[0]); $j++)
			{
				$text=utf8_decode($response->Header[0]->ColumnName[$j]);
				for($k=0; $k<=($maxlines-$lines[$j]); $k++) $text.="\n";
				$pdf->SetXY($xx, $y);
				$pdf->MultiCell($colwidth[$j], 6, $text, 1, 'C', 0);
				$xx+=$colwidth[$j];
			}
		}
		$y=$pdf->GetY();

		//determine height for all cols
		$j=0;
		$maxlines=0;
		foreach($response->Item[$i] as $item)
		{
			$text=utf8_decode($item);
			//determine image height
			if( strpos($text, ".jpg") !== false )
			{
				$text=substr($text, strpos($text, "files/"), strlen($text));
				$size = Getimagesize($text);
				$lines[$j]=ceil(65/$size[0]*$size[1])/5;
				if( $maxlines<$lines[$j] ) $maxlines=$lines[$j];
			}
			else
			{
				$lines[$j]=ceil(($pdf->getStringWidth($text)+8) / $colwidth[$j]);
				if( $maxlines<$lines[$j] ) $maxlines=$lines[$j];
			}
			$j++;
		}
		$xx=5.9;
		$nextY=0;
		$j=0;
		foreach($response->Item[$i] as $item)
		{
			$text=utf8_decode($item);
			if( strpos($text, ".jpg") !== false )
			{
				$text=substr($text, strpos($text, "files/"), strlen($text));
				$size = Getimagesize($text);
				$pdf->Image($text, $xx, $y, 65);
				$xx+=65;
				$nextY=$pdf->GetY();
				$j++;
			}
			else
			{
				for($k=0; $k<=($maxlines-$lines[$j]); $k++) $text.="\n";
				$pdf->SetXY($xx, $y);
				$pdf->MultiCell($colwidth[$j], 6, $text, 1, 'C', 0);
				if( $nextY<$pdf->GetY() ) $nextY=$pdf->GetY();
				$xx+=$colwidth[$j];
				$j++;
			}
		}
		$pdf->SetXY(0, $nextY);
/*
//		array_multisort($lines, );
		if( strpos($text, ".jpg") !== false )
		{
			echo $text='/../../'.substr($text, strpos($text, "files/"), strlen($text));
			$pdf->Image($text, 35.9, $y, 75);
			$nextY=$pdf->GetY();
			$pdf->SetXY(35.9, $y);
			$pdf->SetFont('helvetica', '', 8);
			$pdf->MultiCell(110, 6, $vehicles , 1, 'J', 0);
			$nextY=$pdf->GetY();
	//		$nextY=$h;
			$pdf->SetXY(5.9, $y);
			$pdf->SetFont('helvetica', '', 12);
			$pdf->MultiCell(30, $nextY-$y, $ArtNr , 1, 'C', 0);
			$pdf->SetXY(145.9, $y);
			$pdf->MultiCell(57.5, $nextY-$y, iconv("UTF-8", "cp1252", $fitting_position) , 1, 'C', 0);
			$pdf->SetXY(0, $nextY);
		}
*/
	}
	
	//write pdf
	$pdf->Output("../test.pdf", "I");
	
	//return success
/*
	echo '<ItemExportResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</ItemExportResponse>'."\n";
*/
?>