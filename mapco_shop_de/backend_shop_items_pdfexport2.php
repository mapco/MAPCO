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
	for($i=0; $i<sizeof($_POST["items"]); $i++)
	{
		$bez1=array();
		$bez2=array();

		//get ArtNr
		$results=q("SELECT * FROM shop_items WHERE id_item=".$_POST["items"][$i].";", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$ArtNr=$row["MPN"];
		$x[0]=5.9;
		$lines[0]=$pdf->getStringWidth($ArtNr);
		$text[0]=$ArtNr;

		//get vehicles
		$j=0;
		$results=q("SELECT * FROM t_400 WHERE ArtNr='".$ArtNr."' AND (KritNr=2 OR KritNr=16);", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			$results2=q("SELECT * FROM vehicles_".$_POST["lang"]." WHERE KTypNr='".$row["KritWert"]."';", $dbshop, __FILE__, __LINE__);
			if ( mysqli_num_rows($results2)>0 )
			{
				$row2=mysqli_fetch_array($results2);
				$bez1[$j]=utf8_decode($row2["BEZ1"]);
				$bez2[$j]=utf8_decode($row2["BEZ2"]);
				if ( strpos($bez2[$j], "(") >0 )
					$bez2[$j]=substr($bez2[$j], 0, strpos($bez2[$j], "(")-1);
//				$bez3[$j]=utf8_decode($row2["BEZ3"]);
				$j++;
			}
		}
//		$testbez3="";
//		array_multisort($bez1, $bez2, $bez3);
		array_multisort($bez1, $bez2);
		
		//remove sub models
		$make=array();
		$model=array();
		$testbez2="___";
		for($j=0; $j<sizeof($bez2); $j++)
		{
			$state=strpos($bez2[$j], $testbez2." ");
			if ( ($state === false or $state > 0) and $bez2[$j]!=$testbez2 )
			{
				$make[]=$bez1[$j];
				$model[]=$bez2[$j];
				$testbez2=$bez2[$j];
			}
		}
		$bez1=$make;
		$bez2=$model;
		array_multisort($bez1, $bez2);
		
		//remove repeated brands
		$vehicles="";
		$testbez1="";
		$testbez2="";
		for($j=0; $j<sizeof($bez1); $j++)
		{
			if ( $testbez1!=$bez1[$j] )
			{
				$vehicles.=$bez1[$j];
				$testbez1=$bez1[$j];
			}
			if ( $testbez2!=$bez2[$j] )
			{
				$vehicles.=" ".$bez2[$j];
				$testbez2=$bez2[$j];
				if ( ($j+1)<sizeof($bez1) ) $vehicles.=", ";
			}
		}
/*
		for($j=0; $j<sizeof($bez1); $j++)
		{
			if ( $testbez3!=$bez3[$j] )
			{
				$testbez3=$bez3[$j];
				if ( $testbez1!=$bez1[$j] )
				{
					$vehicles.=$bez1[$j];
					$testbez1=$bez1[$j];
				}
				if ( $testbez2!=$bez2[$j] )
				{
					$vehicles.=" ".$bez2[$j];
					$testbez2=$bez2[$j];
				}
				$vehicles.=" ".$bez3[$j];
				if ( ($j+1)<sizeof($bez1) ) $vehicles.=", ";
			}
		}
*/
		$x[1]=145.9;
		$lines[1]=$pdf->getStringWidth($vehicles);
		$lh=ceil($lines[1]/110)*6;
//		echo $y+$lh.'<br />';
		if ($y+$lh>250) $y=260;
		$text[1]=$vehicles;

		//get Condition
		$Condition="Generalüberholt";
		$results2=q("SELECT * FROM t_210 WHERE ArtNr='".$ArtNr."';", $dbshop, __FILE__, __LINE__);
		while( $row2=mysqli_fetch_array($results2) )
		{
			if ( $row2["KritNr"]==1075 ) $Condition="Neuteil";
		}
		$x[1]=35.9;
		$lines[2]=$pdf->getStringWidth($Condition);
		$text[1]=$vehicles;

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
			$pdf->SetXY(5.9, $y);
			$pdf->MultiCell(30, 6, utf8_decode(t("Artikelnummer", __FILE__, __LINE__, $_POST["lang"])), 1, 'C', 0);
			$pdf->SetXY(35.9, $y);
			$pdf->MultiCell(110, 6, utf8_decode(t("Passend für", __FILE__, __LINE__, $_POST["lang"])), 1, 'L', 0);
			$pdf->SetXY(145.9, $y);
			$pdf->MultiCell(57.5, 6, utf8_decode(t("Zustand", __FILE__, __LINE__, $_POST["lang"])), 1, 'C', 0);
		}
		$y=$pdf->GetY();

//		array_multisort($lines, );
		
		$pdf->SetXY(35.9, $y);
		$pdf->SetFont('helvetica', '', 8);
		$pdf->MultiCell(110, 6, $vehicles , 1, 'J', 0);
		$nextY=$pdf->GetY();
		$pdf->SetXY(5.9, $y);
		$pdf->SetFont('helvetica', '', 12);
		$pdf->MultiCell(30, $nextY-$y, $ArtNr , 1, 'C', 0);
		$pdf->SetXY(145.9, $y);
		$pdf->MultiCell(57.5, $nextY-$y, iconv("UTF-8", "cp1252", $Condition) , 1, 'C', 0);
		$pdf->SetXY(0, $nextY);
	}
	
	//write pdf
	$pdf->Output("../test.pdf", "D");
	
	//return success
/*
	echo '<ItemExportResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</ItemExportResponse>'."\n";
*/
?>