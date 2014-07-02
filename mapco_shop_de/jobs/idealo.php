<?php
	include("../config.php");
	include("../functions/cms_remove_element.php");
	include("../functions/mapco_get_titles.php");

	//header
	/*
	$application="text/csv";
	$filename='idealo.csv';
	header( "Content-Type: $application" ); 
	header( "Content-Disposition: attachment; filename=$filename"); 
	header( "Content-Description: csv File" ); 
	header( "Pragma: no-cache" ); 
	header( "Expires: 0" );  
	*/
	
	//restart process
	$results=q("SELECT * FROM shop_items WHERE active=1 AND NOT idealo;", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($results)==0)
	{
		//rename csv
		echo 'Erstelle komplette idealo.csv...';
		rename("idealo.dat", "idealo.csv");
		echo 'OK.';

		//create new file and fileheader
		echo 'Schreibe neuen Header...';
		$file=fopen("idealo.dat", "w");
		fwrite($file, "Artikelnummer im Shop;EAN / Barcodenummer;Original Herstellerartikelnummer;Herstellername;Produktname;Produktgruppe im Shop (möglichst als Pfad ausgehend von der Wurzelkategorie);Preis (Brutto);Lieferzeit;ProduktURL;BildURL_1 (großes Bild);BildURL_2 (großes Bild);BildURL_3 (großes Bild) - bei Bedarf weitere Bildspalten einfügen;Vorkasse;Nachnahme;Versandkosten Kommentar (max. 100 Zeichen);Produktbeschreibung (max. 1000 Zeichen);Grundpreis (Produktabhängig)");
		fclose($file);
		$query="UPDATE shop_items SET idealo=0;";
		q($query, $dbshop, __FILE__, __LINE__);
		echo 'OK.<br />';
	}
	
	//write lines
	echo 'Erstelle Artikeleinträge...';
	$file=fopen("idealo.dat", "a");
	$results=q("SELECT * FROM shop_items WHERE active=1 AND NOT idealo;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		//Artikelnummer im Shop
		$artnr=$row["MPN"];
//		echo $artnr.'<br />';
		
		$barcode='';
		$herstellerartikelnummer=$artnr;
		$herstellername='MAPCO Autotechnik GmbH';
		
		//Produktname
		$produktname=get_titles($artnr, 16384);

		//Produktgruppe
		$query="SELECT GART FROM t_200 WHERE ArtNr='".$artnr."';";
		$results2=q($query, $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$query="SELECT * FROM mapco_gart_export WHERE GART='".$row2["GART"]."';";
		$results2=q($query, $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($results2)=='')
		{
			$query="SELECT * FROM mapco_gart_export WHERE GART=0;";
			$results2=q($query, $dbshop, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			$produktgruppe=utf8_decode($row2["idealo"]);
		}
		else
		{
			$row2=mysqli_fetch_array($results2);
			if ($row2["idealo"]=='')
			{
				$query="SELECT * FROM mapco_gart_export WHERE GART=0;";
				$results2=q($query, $dbshop, __FILE__, __LINE__);
				$row2=mysqli_fetch_array($results2);
				$produktgruppe=utf8_decode($row2["idealo"]);
			}
			else
			{
				$produktgruppe=utf8_decode($row2["idealo"]);
			}
		}
		
		//Preis Brutto
		$query="SELECT * FROM prpos WHERE ARTNR='".$artnr."' AND LST_NR=3;";
		$results2=q($query, $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$preis_brutto=number_format($row2["POS_0_WERT"]*((100+UST)/100), 2); //mandatory
		
		$lieferzeit='1 Werktag';
		
		$produkt_url='http://www.mapco.de/shop_item.php?lang=de&id_item='.$row["id_item"].'&pid=1298855';
		
		//Bild URLs
		$bild_urls=array();
		$results2=q("SELECT * FROM shop_items_files WHERE item_id=".$row["id_item"]." LIMIT 3;", $dbshop, __FILE__, __LINE__);
		while($row2=mysqli_fetch_array($results2))
		{
			$bild_urls[]='http://www.mapco.de/files/'.floor(bcdiv($row2["file_id"], 1000)).'/'.$row2["file_id"].'.jpg';
		}
		
		$vorkasse=4.76;
		$nachname=13.69;
		$versandkosten_kommentar='';
		
		//Produktbeschreibung
		$query="SELECT * FROM shop_items_de WHERE id_item=".$row["id_item"].";";
		$results2=q($query, $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$produktbeschreibung=str_replace(";", ",", $row2["short_description"]);
		
		fwrite($file, "\n".$artnr.";".$barcode.";".$herstellerartikelnummer.";".$herstellername.";".utf8_decode($produktname[0]).";".$produktgruppe.";".$preis_brutto.";".$lieferzeit.";".$produkt_url.";".$bild_urls[0].";".$bild_urls[1].";".$bild_urls[2].";".$vorkasse.";".$nachname.";".utf8_decode($versandkosten_kommentar).";".utf8_decode($produktbeschreibung));

		$query="UPDATE shop_items SET idealo=1 WHERE id_item=".$row["id_item"].";";
		q($query, $dbshop, __FILE__, __LINE__);
	}
	echo 'OK.<br />';
	fclose($file);
?>