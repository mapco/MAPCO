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
	$results=q("SELECT * FROM shop_items WHERE active=1 AND NOT google;", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($results)==0 or !file_exists("google.dat"))
	{
		//rename csv
		if (file_exists("google.dat"))
		{
			echo 'Erstelle komplette google.csv...';
			rename("google.dat", "google.csv");
			echo 'OK.<br />';
		
			//Kopiere Datei auf den Google Server
			echo 'Kopiere Datei auf Google-FTP...';
			$quellserver = 'google.csv';
			$zielserver  = 'ftp://admapco_1:b42EqrJQ@uploads.google.com/google.csv';
			$bytes=file_put_contents($zielserver,file_get_contents($quellserver));		
			echo ($bytes/1024).'kB kopiert.<br />';
		}

		//create new file and fileheader
		echo 'Schreibe neuen Header...';
		$file=fopen("google.dat", "w");
		fwrite($file, "id\ttitle\tdescription\tgoogle_product_category\tproduct_type\tlink\timage_link\tadditional_image_link\tcondition\tavailability\tprice\tsale_price\tsale_price_effective_date\tbrand\tgtin\tmpn");
		fclose($file);
		$query="UPDATE shop_items SET google=0;";
		q($query, $dbshop, __FILE__, __LINE__);
		echo 'OK.<br />';
	}
	
	//write lines
	echo 'Erstelle Artikeleinträge...';
	$file=fopen("google.dat", "a");
	$results=q("SELECT * FROM shop_items WHERE active=1 AND NOT google;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		//Artikelnummer im Shop
		$id=$row["MPN"];
		
		//Produktname
		$titles=get_titles($id, 70);
		$title=substr(utf8_decode($titles[0]), 0, 69);
		
		//Beschreibung
		$results2=q("SELECT * FROM shop_items_de WHERE id_item=".$row["id_item"].";", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$description=str_replace(";", ",", utf8_decode($row2["short_description"]));
		
		//Google Produktkategorie
		$google_product_category='Kraftfahrzeuge > Auto-Teile';
		
		//Google Produkttyp
		$product_type='Autoteil';
		
		//Links
		$link='http://www.mapco.de/shop_item.php?lang=de&id_item='.$row["id_item"].'&pid=1298856';
		
		//Bildlink und zusätzliche Bildlinks
		$bild_urls=array();
		$results2=q("SELECT * FROM shop_items_files WHERE item_id=".$row["id_item"]." LIMIT 3;", $dbshop, __FILE__, __LINE__);
		while($row2=mysqli_fetch_array($results2))
		{
			$bild_urls[]='http://www.mapco.de/files/'.floor(bcdiv($row2["file_id"], 1000)).'/'.$row2["file_id"].'.jpg';
		}
		$image_link=$bild_urls[0];
		for($j=1; $j<sizeof($bild_urls); $j++)
		{
			if ($j==1) $additional_image_link=$bild_urls[$j];
			else $additional_image_link.=','.$bild_urls[$j];
		}
		
		//Zustand
		$condition='new';

		//Verfügbarkeit
		$availability='in stock';
		
		//Preis
		$query="SELECT * FROM prpos WHERE ARTNR='".$id."' AND LST_NR=3;";
		$results2=q($query, $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$price=number_format($row2["POS_0_WERT"]*((100+UST)/100), 2).' EUR'; //mandatory

		//Sonderangebotspreis
		$sale_price='';
		
		//Sonderangebotszeitraum
		$sale_price_effective_date='';
		
		//Marke
		$brand='MAPCO Autotechnik GmbH';
		
		//GTIN
		$gtin='';
		
		//MPN
		$mpn='';
		

		fwrite($file, "\n".$id."\t".$title."\t".$description."\t".$google_product_category."\t".$product_type."\t".$link."\t".$image_link."\t".$additional_image_link."\t".$condition."\t".$availability."\t".$price."\t".$sale_price."\t".$sale_price_effective_date."\t".$brand."\t".$gtin."\t".$mpn);
/*
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
*/
		

		$query="UPDATE shop_items SET google=1 WHERE id_item=".$row["id_item"].";";
		q($query, $dbshop, __FILE__, __LINE__);
	}
	echo 'OK.<br />';
	fclose($file);
?>