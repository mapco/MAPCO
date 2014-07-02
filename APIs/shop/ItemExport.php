 <?php
	require_once("../../mapco_shop_de/functions/mapco_baujahr.php");
	
	if ( !isset($_POST["id_item"]) )
	{
		echo '<ItemExportResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Es konnte keine Shopartikel-ID gefunden werden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte keine Shopartikel-ID gefunden werden. Die ID ist notwendig, da der Service sonst nicht weiß, welchen Shopartikel er exportieren soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemExportResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["id_exportformat"]) )
	{
		echo '<ItemExportResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Es konnte Exportformat gefunden werden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte kein Exportformat gefunden werden. Das Exportformat ist notwendig, da der Service sonst nicht weiß, welche Artikelinformationen exportiert werden sollen.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemExportResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["file"]) )
	{
		echo '<ItemExportResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Es konnte keine Dateiangabe gefunden werden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Dateiname übergeben werden, damit die Daten an einen bestimmten ort exportiert werden können.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemExportResponse>'."\n";
		exit;
	}

	$results=q("SELECT * FROM shop_items WHERE id_item=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<ItemExportResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Es konnte kein gültiger Shopartikel gefunden werden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte kein gültiger Shopartikel gefunden werden. Die ID ist ungültig.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemExportResponse>'."\n";
		exit;
	}
	$shop_items=mysqli_fetch_array($results);
	
	//get export fields
	$fields=array();
	$results=q("SELECT * FROM shop_export_fields WHERE exportformat_id=".$_POST["id_exportformat"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$fields[$row["value"]]=true;
	}
/*
	$results=q("SELECT * FROM shop_items_de WHERE id_item=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
	$shop_items_de=mysqli_fetch_array($results);
	
*/
	switch ($_POST["id_exportformat"])
	{
		case "5": 	
			$results=q("SELECT * FROM shop_items_en WHERE id_item=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
			$account_id="5";
			$langID="2";
			break;
		case "6": 	
			$results=q("SELECT * FROM shop_items_en WHERE id_item=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
			$account_id="5";
			$langID="2";
			break;
		default:
			$results=q("SELECT * FROM shop_items_de WHERE id_item=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
			$account_id="3";
			$langID="1";
			break;
	}
	$shop_items_lang=mysqli_fetch_array($results);

	$col=-1;
	
	//empty by default
	$col++;
	$name[$col]="";
	$value[$col]="";
	
	//Vendor Name And Code
	$col++;
	$name[$col]="Vendor Name And Code";
	$value[$col]="MAPD1";
	
	//Brand name
	$col++;
	$name[$col]="BrandName";
	$value[$col]="MAPCO";
	$field["Brand"]="MAPCO Autotechnik GmbH";

	//Manufacturer
/*
	$col++;
	$name[$col]="Manufacturer";
	$value[$col]="MAPCO Autotechnik GmbH";
	$field["Manufacturer"]="MAPCO Autotechnik GmbH";
*/

	//Vendor Product ID
	$col++;
	$name[$col]="Vendor Product ID";
	$value[$col]=$shop_items["MPN"];
	$field["SKU"]=$shop_items["MPN"];

	//Product Title
	$col++;
	$name[$col]="Product Title";
//	$value[$col]="MAPCO ".$shop_items_de["title"];
//	$field["Title"]="MAPCO ".$shop_items_de["title"];
	$value[$col]="MAPCO ".$shop_items_lang["title"];
	$field["Title"]="MAPCO ".$shop_items_lang["title"];


	//Model Number
	$col++;
	$name[$col]="Model Number";
	$value[$col]=$shop_items["MPN"];


//BROWSENODES
	$res=q("select * from t_200 where ArtNr = '".$shop_items["MPN"]."';", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($res);

	//Führende Nullen beseitigen
	$GART=$row["GART"]*1;

	// fix the accountsite id value
	$res=q("select * from amazon_categories where GART = '".$GART."' and accountsite_id = '3';", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($res);
	$browsenode=$row["BrowseNodeId1"];
	$res=q("select * from amazon_browsenodes where BrowseNodeId = '".$browsenode."' and Lang_Id = '".$langID."';", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($res);
	$NodePath=$row["Path"];
	
	$Nodes=explode("/", $NodePath);
	for($i=0; $i<sizeof($Nodes); $i++)
	{
		$Nodes[$i]=str_replace(" ", "_", $Nodes[$i]);
		$Nodes[$i]=str_replace("&", "and", $Nodes[$i]);
	}
	//$Nodes[0] auslassen => "AUTOMOTIV ist autom. TopLevelNode
	//Top Level Browse Node
	$col++;
	$name[$col]="Top Level Browse Node";
	$value[$col]=248877031;
	if (isset($Nodes[1])) $field["AmazonBrowseLevel1"]=$Nodes[1]; else $field["AmazonBrowseLevel1"]="";

	//Browse Level 2
	$col++;
	$name[$col]="Browse Level 2";
	$value[$col]="";
	if (isset($Nodes[2])) $field["AmazonBrowseLevel2"]=$Nodes[2]; else $field["AmazonBrowseLevel2"]="";

	//Browse Level 3
	$col++;
	$name[$col]="Browse Level 3";
	$value[$col]="";
	if (isset($Nodes[3])) $field["AmazonBrowseLevel3"]=$Nodes[3]; else $field["AmazonBrowseLevel3"]="";

	//Browse Level 4
	$col++;
	$name[$col]="Browse Level 4";
	$value[$col]="";
	if (isset($Nodes[4])) $field["AmazonBrowseLevel4"]=$Nodes[4]; else $field["AmazonBrowseLevel4"]="";

	//Keywords
	$col++;
	$name[$col]="Keywords";
	$value[$col]="MAPCO;car;part;spare part";
	$field["KeyWords"]="MAPCO;car;part;spare part";
	
	$kWords=explode(",", $shop_items_lang["title"]);
	for ($i=0; $i<sizeof($kWords); $i++) 
	{
		$field["KeyWords"].=";".$kWords[$i];
	}
		
	//Category
	$col++;
	$name[$col]="Category";
	$value[$col]="Undercar_Replacement_Parts_Passenger_Car_and_Light_Truck";
	$field["Category"]="Undercar_Replacement_Parts_Passenger_Car_and_Light_Truck";
	
	//Subcategory
	$col++;
	$name[$col]="Subcategory";
	$value[$col]="Brakes";
	
	//HasMainsPlug
	$col++;
	$name[$col]="HasMainsPlug";
	$value[$col]="No";
	
	//ExternalIDType
	/*
	$col++;
	$name[$col]="ExternalIDType";
	$value[$col]="UPC";
	*/
	
	//ExternalID
	$col++;
	$name[$col]="ExternalID";
	$value[$col]=$shop_items["EAN"];
	$field["Barcode"]=$shop_items["EAN"];

	//OENumbers
	$col++;
	$name[$col]="OENumbers";
	$results=q("SELECT LBezNr, OENr FROM t_203 AS a, t_100 AS b WHERE ArtNr='".$shop_items["MPN"]."' AND a.KHerNr=b.KherNr AND VGL=0;", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)>0 )
	{
		$row=mysqli_fetch_array($results);
		$oenr=$row["OENr"];
	}
	else $oenr='';
	$value[$col]=$oenr;
	$field["OENumbers"]=$oenr;
	
	//PackageLength
	$col++;
	$name[$col]="PackageLength";
	$value[$col]=$shop_items["PackageLength"];
	$field["PackageLength"]=number_format(number_format($shop_items["PackageLength"])/10, 2,",","");
	
	//PackageHeight
	$col++;
	$name[$col]="PackageHeight";
	$value[$col]=$shop_items["PackageHeight"];
	$field["PackageHeight"]=number_format(number_format($shop_items["PackageHeight"])/10, 2,",","");
	//PackageWidth
	$col++;
	$name[$col]="PackageWidth";
	$value[$col]=$shop_items["PackageWidth"];
	$field["PackageWidth"]=number_format(number_format($shop_items["PackageWidth"])/10, 2,",","");

	//HeavierThan40
	$col++;
	$name[$col]="HeavierThan40";
	$value[$col]="";
	if ($shop_items["GrossWeight"]>40000) $field["HeavierThan40"]="Yes"; else $field["HeavierThan40"]="No";
	
	//UnitOfMeasurement
	$col++;
	$name[$col]="UnitOfMeasurement";
	$value[$col]="Gram";
	
	//PackageWeight
	$col++;
	$name[$col]="PackageWeight";
	$value[$col]=1.50;
	$field["GrossWeight"]=$shop_items["GrossWeight"];

	//MinimumAgeLimit
	$col++;
	$name[$col]="MinimumAgeLimit";
	$value[$col]="No";
	
	//Fragile
	$col++;
	$name[$col]="Fragile";
	$value[$col]="No";
	
	//HazardousMaterial
	$col++;
	$name[$col]="HazardousMaterial";
	$value[$col]="No";
	
	//DropShip
	$col++;
	$name[$col]="DropShip";
	$value[$col]="No";
	
	//MinimumOrderQuantity
	$col++;
	$name[$col]="MinimumOrderQuantity";
	$value[$col]=1;
	$field["MinimumOrderQuantity"]=1;
	
	//LaunchDate
	$col++;
	$name[$col]="LaunchDate";
	$value[$col]=date("d/m/Y", time());
	$field["LaunchDate"]=date("Ymd", time());
	
	//ReleaseDate
	$col++;
	$name[$col]="ReleaseDate";
	$value[$col]="";
	
	//WEEE regulated
	$col++;
	$name[$col]="WEEE regulated";
	$value[$col]="No";
	
	//LithiumBattery
	$col++;
	$name[$col]="LithiumBattery";
	$value[$col]="No";
	
	//ConstructionType
	$col++;
	$name[$col]="ConstructionType";
	$value[$col]="No";
	
	//LoadType
	$col++;
	$name[$col]="LoadType";
	$value[$col]="";
	
	//RimDiameter
	$col++;
	$name[$col]="RimDiameter";
	$value[$col]="";
	
	//SpeedRating
	$col++;
	$name[$col]="SpeedRating";
	$value[$col]="";
	
	//TyreAspectRatio
	$col++;
	$name[$col]="TyreAspectRatio";
	$value[$col]="";
	
	//TyreSectionWidth
	$col++;
	$name[$col]="TyreSectionWidth";
	$value[$col]="";
	
	//TyreType
	$col++;
	$name[$col]="TyreType";
	$value[$col]="";
	
	//BoreDiameter
	$col++;
	$name[$col]="BoreDiameter";
	$value[$col]="";
	
	//HoleCount
	$col++;
	$name[$col]="HoleCount";
	$value[$col]="";
	
	//PitchCircleDiameter
	$col++;
	$name[$col]="PitchCircleDiameter";
	$value[$col]="";
	
	//RimMaterial
	$col++;
	$name[$col]="RimMaterial";
	$value[$col]="";
	
	//RimOffset
	$col++;
	$name[$col]="RimOffset";
	$value[$col]="";
	
	//RimWidth
	$col++;
	$name[$col]="RimWidth";
	$value[$col]="";
	
	//Bullet Points
//	$crits=explode("; ", $shop_items_de["short_description"]);
	$crits=explode("; ", $shop_items_lang["short_description"]);
	for($i; $i<5; $i++)
	{
		$col++;
		$name[$col]="Bullet Point ".($i+1);
		$value[$col]="";
		//$field["BulletPoint".$i+1]="";
	}
		$field["BulletPoint5"]="Manufacturer: MAPCO Autotechnik GmbH";
		$field["BulletPoint4"]="Condition: New Item";
		$field["BulletPoint3"]="OE quality or higher";
		$field["BulletPoint2"]="directly from the manufacturer";
		$field["BulletPoint1"]="High Quality Product";
		
	
	for($i=0; $i<sizeof($crits); $i++)
	{
		if ($i<5)
		{
			$col++;
			$name[$col]="Bullet Point ".($i+1);
			$value[$col]=$crits[$i];
			$k=$i;
			$k++;
			$field["BulletPoint".$k]=utf8_decode($crits[$i]);
		}
		else
		{
			$value[$col].="; ".$crits[$i];
			$field["BulletPoint5"].="; ".utf8_decode($crits[$i]);
		}
	}

	//TechnicalDetails
	$col++;
	$name[$col]="TechnicalDetails";
//	$value[$col]=str_replace("; ", ";", $shop_items_de["short_description"]);
	$value[$col]=str_replace("; ", ";", $shop_items_lang["short_description"]);
	$field["TechnicalDetails"]=str_replace("; ", ";", $shop_items_lang["short_description"]);


	//Keyword1
	if ($fields["Keyword1"])
	{
		echo "SELECT * FROM shop_items_keywords WHERE language_id='en' AND GART=".($shop_items["GART"]*1)." ORDER BY ordering;";
		exit;
		$results=q("SELECT * FROM shop_items_keywords WHERE language_id='en' AND GART=".($shop_items["GART"]*1)." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results) )
		{
			$row=mysqli_fetch_array($results);
			$field["Keyword1"]=$row["keyword"];
		}
		else $field["Keyword1"]='Car Part';
		echo $field["Keyword1"];
		exit;
	}


	//Introduction
	$col++;
	$name[$col]="Description";
	$value[$col]="";
//	$field["Introduction"]=$shop_items_de["short_description"];
	$field["Introduction"]=$shop_items_lang["short_description"];

	//Description
	$col++;
	$name[$col]="Description";
	$value[$col]="MAPCO products have been sold with enormous success in Germany since 1977. Over the last 30 years millions of MAPCO products have been fitted to a multitude of different vehicle applications. Customer satisfaction still commands the highest priority. Originally founded as a PLC in France, the company now coordinates its entire activities as MAPCO Autotechnik GmbH from its headquarters in Borkheide, near Berlin. MAPCO in Brueck MAPCO has made itself a name in the last three decades all over Europe as a brake specialist. Although the total sales programme for other automotive replacement parts has been dramatically extended during this period, MAPCO has not neglected its original specialism and has continually developed and enhanced its range of brake parts. MAPCO steering and suspension parts have been available to the German market since 1985. However the programme entered its most dynamic growth phase, leading up to the impressive dimensions which have now been reached, from 1995 onwards. The new technologies for front and rear axle constructions introduced by the vehicle manufacturers in the Nineties, has lead led to an explosive growth in the market potential for these replacement parts. Far in excess of 3500 individual items are carried in this product group. The corresponding catalogue with original photos and illustrations is clearly presented and practice-oriented. The quality, price and availability of these parts set new standards in the marketplace.";


	//AmazonUKASIN
	if ($fields["AmazonUK_ASIN"])
	{
		$results=q("SELECT * FROM amazon_products WHERE account_id=5 AND item_id='".$_POST["id_item"]."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$field["AmazonUK_ASIN"]=$row["ASIN"];
	}

	//AmazonUKDescription
	if ($fields["AmazonUKDescription"])
	{
		//FAHREZUGVERWENDUNGSLISTE EXTRAHIEREN
		$vehicle=array();
		$results=q("SELECT * FROM shop_items_vehicles WHERE item_id=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			$vehicle[$row["vehicle_id"]]=$row["vehicle_id"];
		}

		$vehicle2=array();
		$results=q("SELECT * FROM vehicles_de ORDER BY registrations DESC;", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			if( isset($vehicle[$row["id_vehicle"]]) ) $vehicle2[]=$row["id_vehicle"];
		}

		$field["AmazonUKDescription"] = 'Fitments:<br />';
		for($i=0; $i<sizeof($vehicle2); $i++)
		{
			$results=q("SELECT * FROM vehicles_en WHERE id_vehicle=".$vehicle2[$i].";", $dbshop, __FILE__, __LINE__);
			$row=mysqli_fetch_array($results);
			$newline=$row["BEZ1"].' '.$row["BEZ2"].' '.$row["BEZ3"].', '.$row["PS"].'HP, ('.baujahr($row["BJvon"]).'-'.baujahr($row["BJbis"]).')<br />';
			if( strlen($field["AmazonUKDescription"].$newline) <=2000 )
			{
				$field["AmazonUKDescription"] .= $newline;
				if( strlen($field["AmazonUKDescription"])>=2000 ) break;
			}
			else break;
		}

//		$field["AmazonUKDescription"]="MAPCO products have been sold with enormous success in Germany since 1977. Over the last 30 years millions of MAPCO products have been fitted to a multitude of different vehicle applications. Customer satisfaction still commands the highest priority. Originally founded as a PLC in France, the company now coordinates its entire activities as MAPCO Autotechnik GmbH from its headquarters in Borkheide, near Berlin. MAPCO in Brueck MAPCO has made itself a name in the last three decades all over Europe as a brake specialist. Although the total sales programme for other automotive replacement parts has been dramatically extended during this period, MAPCO has not neglected its original specialism and has continually developed and enhanced its range of brake parts. MAPCO steering and suspension parts have been available to the German market since 1985. However the programme entered its most dynamic growth phase, leading up to the impressive dimensions which have now been reached, from 1995 onwards. The new technologies for front and rear axle constructions introduced by the vehicle manufacturers in the Nineties, has lead led to an explosive growth in the market potential for these replacement parts. Far in excess of 3500 individual items are carried in this product group. The corresponding catalogue with original photos and illustrations is clearly presented and practice-oriented. The quality, price and availability of these parts set new standards in the marketplace.";

	}


	//PictureURL
	$i=0;
	$results3=q("SELECT * FROM cms_articles_images WHERE article_id=".$shop_items["article_id"]." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
	if ( mysqli_num_rows($results3)>1 )
	{
	//	echo '<div style="width:500px; margin:2px; border:1px solid #cccccc; padding:0px; float:left;">';
		while($row3=mysqli_fetch_array($results3))
		{
			$i++;
			$results2=q("SELECT * FROM cms_files WHERE original_id='".$row3["file_id"]."' AND imageformat_id=19 LIMIT 1;", $dbweb, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			$filename='files/'.floor(bcdiv($row2["id_file"], 1000)).'/'.$row2["id_file"].'.'.$row2["extension"];
			$field["PictureURL".$i]=PATH.$filename;
		}
	//	echo '</div>';
	}
	for($i; $i<10; $i++)
	{
		$field["PictureURL".$i]='';
	}


	//PriceGross
	$results=q("SELECT * FROM prpos WHERE ARTNR='".$shop_items["MPN"]."' AND LST_NR='0';", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$field["PriceGross"]=$row["POS_0_WERT"];
	$field["PriceGross"]=round($field["PriceGross"], 2);

	//PriceYellow
	$results=q("SELECT * FROM prpos WHERE ARTNR='".$shop_items["MPN"]."' AND LST_NR='5';", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$field["PriceYellow"]=$row["POS_0_WERT"];

	//AmazonUKPrices
	$field["AmazonUKCostPrice"]=round($field["PriceYellow"]*0.805968293, 2);
	$results=q("SELECT * FROM prpos WHERE ARTNR='".$shop_items["MPN"]."' AND LST_NR='3';", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$field["PriceBlue"]=$row["POS_0_WERT"];
	$field["AmazonUKRecommendedRetailPrice"]=round($field["PriceBlue"]*1.2*0.805968293, 2);
	if ( $field["AmazonUKRecommendedRetailPrice"] < ($field["AmazonUKCostPrice"]*1.2*1.2) )
	{
		$field["AmazonUKRecommendedRetailPrice"]=round($field["AmazonUKCostPrice"]*1.2*1.25, 2);
	}
	$field["AmazonUKCostPrice"]=number_format($field["AmazonUKCostPrice"], 2, ",", "");
	$field["AmazonUKRecommendedRetailPrice"]=number_format($field["AmazonUKRecommendedRetailPrice"], 2, ",", "");


	//ItemID
	$field["ItemID"]=$shop_items["id_item"];

	//Quantitiy
	$field["Quantity"]=10;


	//create file and header
	$name=array();
	$value=array();
	$results=q("SELECT * FROM shop_export_fields WHERE exportformat_id=".$_POST["id_exportformat"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$name[]=$row["name"];
		$value[]=$row["value"];
	}
	$separator=";";
	if ( $_POST["header"] == 1 )
	{
		$handle=fopen($_POST["file"], "w");
	
		$line="";
		for($i=0; $i<sizeof($name); $i++)
		{
			$line .= '"'.$name[$i].'"';
			if ( ($i+1)!=sizeof($name) ) $line .= $separator;
		}
		fwrite($handle, $line."\n");
	}
	//or open existing file
	else
	{
		$handle=fopen($_POST["file"], "a");
	}

	//build line
	$line="";
	for($i=0; $i<sizeof($value); $i++)
	{
		if (isset($field[$value[$i]])) $val=$field[$value[$i]]; else $val=$value[$i];
		$line.='"'.$val.'"';
		if ( ($i+1)!=sizeof($name) ) $line .= $separator;
	}

	fwrite($handle, $line."\n");
	fclose($handle);

	//return success
	echo '<ItemExportResponse>'."\n";
	echo '	<Ack>Success</Ack>'."\n";
	echo '</ItemExportResponse>'."\n";

?>