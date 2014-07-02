<?php
	include("../config.php");
	include("../functions/mapco_cutout.php");
	require_once("../functions/shop_get_price.php");

	//Einbaubezeichnungen
	$einbaubez=array(1 => "zum Zylinder 1", 2 => "zum Zylinder 2", 3 => "zum Zylinder 3", 4 => "zum Zylinder 4", 5 => "zum Zylinder 5", 52 => "Ausrückgabel an Kupplungsgehäuse", 6 => "zum Zylinder 6", 7 => "zum Zylinder 7", 8 => "zum Zylinder 8", "AB" => "am Bremssattel", "BS" => "beifahrerseitig", "F" => "Fahrzeugfront", "FB" => "beidseitig", "FE" => "Fronteinbau", "FS" => "fahrerseitig", "GS" => "getriebeseitig", "H" => "hinten", "HA" => "Hinterachse", "HD" => "hinter der Achse", "HG" => "Hinterachse beidseitig", "HL" => "Hinterachse links", "HO" => "Hinterachse oben", "HP" => "Fahrzeugheckklappe", "HR" => "Hinterachse rechts", "HS" => "Fahrzeugheckscheibe", "HU" => "Hinterachse unten", "I" => "innen", "L" => "links", "LH" => "hinten links", "LV" => "vorne links", "M" => "mitte", "O" => "oben", "R" => "rechts", "RH" => "hinten rechts", "RS" => "radseitig", "RV" => "vorne rechts", "SE" => "seitlicher Einbau", "U" => "unten", "V" => "vorne", "VA" => "Vorderachse", "VD" => "vor der Achse", "VG" => "Vorderachse beidseitig", "VH" => "vorne und hinten", "VL" => "Vorderachse links", "VR" => "Vorderachse rechts");			

	//categories
	$results=q("SELECT * FROM  ebay_categories;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$StoreCategoryID[$row["account_id"]][$row["GART"]]=$row["StoreCategory"];
		$StoreCategory2ID[$row["account_id"]][$row["GART"]]=$row["StoreCategory2"];
	}
	$results=q("SELECT * FROM  mapco_gart_export;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$CategoryID[$row["GART"]]=$row["Category"];
		$CategoryID2[$row["GART"]]=$row["Category2"];
/*		
		$StoreCategoryID[1][$row["GART"]]=$row["StoreCategory"];
		$StoreCategory2ID[1][$row["GART"]]=$row["StoreCategory2"];
		$StoreCategoryID[2][$row["GART"]]=$row["StoreCategoryAP"];
		$StoreCategory2ID[2][$row["GART"]]=$row["StoreCategoryAP2"];
*/
	}
	if(isset($_GET["item_id"]))
	{
		$results=q("SELECT * FROM ebay_accounts_items WHERE active>0 and item_id=".$_GET["item_id"]." ORDER BY lastupdate;", $dbshop, __FILE__, __LINE__);
	}
	elseif(isset($_GET["account_id"]))
	{
		$results=q("SELECT * FROM ebay_accounts_items WHERE active>0 and account_id=".$_GET["account_id"]." ORDER BY lastupdate;", $dbshop, __FILE__, __LINE__);
	}
	else
	{
		$results=q("SELECT * FROM ebay_accounts_items WHERE active>0 ORDER BY lastupdate;", $dbshop, __FILE__, __LINE__);
	}
	while($row=mysqli_fetch_array($results))
	{		
		$id=$row["id"];
		$item_id=$row["item_id"];
		$account_id=$row["account_id"];
		$pricelist_id=$row["pricelist_id"];
		$results2=q("SELECT * FROM shop_items_artnr WHERE item_id=".$item_id.";", $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows($results2)>0 )
		{
			//ArtNr
			$row2=mysqli_fetch_array($results2);
			$artnr=$row2["ArtNr"];
			
			//GART
			$results3=q("SELECT * FROM t_200 WHERE ArtNr='".$artnr."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
			$row3=mysqli_fetch_array($results3);
			$GART=$row3["GART"]+0;

			$results3=q("SELECT * FROM shop_items_de WHERE id_item=".$item_id.";", $dbshop, __FILE__, __LINE__);
			$row3=mysqli_fetch_array($results3);
			
			$itemtitle='MAPCO '.$row3["title"];
			$itemtitle=substr($itemtitle, 0, strpos($itemtitle, "(")-1);
			
/*			
			$Description=$row3["description"];
			$Description =cutout($Description, '<!-- Reverse Start -->', '<!-- Reverse Stop -->');
			$Description=str_replace('<h1>', '<div class="box"><h1>', $Description);
			$Description=str_replace('</table>', '</table></div>', $Description);
			$Description=cutout($Description, '<a href="', '">');
			$Description=str_replace('</a>', '', $Description);
			$Description=addslashes(stripslashes($Description));
*/			
			$results3=q("SELECT * FROM t_400 WHERE ArtNr='".$artnr."' AND KritNr=2;", $dbshop, __FILE__, __LINE__);
			while($row3=mysqli_fetch_array($results3))
			{
				$KTypNr=$row3["KritWert"];
				$results4=q("SELECT * FROM vehicles_de WHERE Exclude=0 AND KTypNr=".$row3["KritWert"].";", $dbshop, __FILE__, __LINE__);
				while($row4=mysqli_fetch_array($results4))
				{
					$ConditionID=1000;
					$Currency="EUR";
					$DispatchTimeMax=1;
					$ListingDuration="GTC";
					$ListingType="FixedPriceItem";
					$Title=$itemtitle;
					
					//Einbauseite zum Artikel
					$results_t210=q("SELECT * FROM t_210 WHERE ArtNr='".$artnr."' AND KritNr=100;", $dbshop, __FILE__, __LINE__);
					while($row_t210=mysqli_fetch_array($results_t210))
					{
						$Title.=' '.$einbaubez[$row_t210["KritVal"]];
					}

					//Einbauseite zum Fahrzeug
					$results_t400=q("SELECT * FROM t_400 WHERE ArtNr='".$artnr."' AND KritNr=2 AND KritWert='".$KTypNr."';", $dbshop, __FILE__, __LINE__);
					$row_t400=mysqli_fetch_array($results_t400);
					$results_t400=q("SELECT * FROM t_400 WHERE ArtNr='".$artnr."' AND LfdNr=".$row_t400["LfdNr"]." AND KritNr=100;", $dbshop, __FILE__, __LINE__);
					if( mysqli_num_rows($results_t400)>0 )
					{
						$row_t400=mysqli_fetch_array($results_t400);
						$Title.=' '.$einbaubez[$row_t400["KritWert"]];
					}
					
					$Title.=' '.$row4["BEZ1"].' '.$row4["BEZ2"].' '.$row4["BEZ3"];


					//get vehicles
/*
					$ktypnr=array();
					$eb=array();
					$i=0;
					$results=q("SELECT * FROM t_400 WHERE ArtNr='".$artnr."' ORDER BY LfdNr, SortNr;", $dbshop, __FILE__, __LINE__);
					while($row=mysqli_fetch_array($results))
					{
		//				echo $row["KritNr"].'<br />';
						if ($row["KritNr"]==2)
						{
							$ktypnr[$i]=$row["KritWert"];
							$i++;
						}
						elseif($row["KritNr"]==100)
						{
							if ($eb[$i-1]!="") $eb[$i-1].=' ';
							$eb[$i-1].=$einbaubez[$row["KritWert"]];
						}
					}
		//			print_r($eb);
*/
				
					//mapco replacements
					$query="";
					$results7=q("SELECT * FROM mapco_replacements;", $dbweb, __FILE__, __LINE__);
					while ($row7=mysqli_fetch_array($results7))
					{
						$Title=str_replace($row7["search"], $row7["replace"], $Title);
					}
					

					//StartPrice
					if ($pricelist_id==16815)
					{
						$price=get_price($item_id, 1, 27991);
						$StartPrice=round($price*((100+UST)/100), 2); //mandatory
					}
					elseif ($pricelist_id==18209)
					{
						$price=get_price($item_id, 1, 27992);
						$StartPrice=round($price*((100+UST)/100), 2); //mandatory
					}
					else
					{
						$query="SELECT * FROM prpos WHERE ARTNR='".$artnr."' AND LST_NR=".$pricelist_id.";";
						$results6=q($query, $dbshop, __FILE__, __LINE__);
						$row6=mysqli_fetch_array($results6);
						$StartPrice=number_format($row6["POS_0_WERT"]*((100+UST)/100), 2); //mandatory
					}

					//update auction
//					echo "SELECT * FROM ebay_auctions WHERE shopitem_id=".$item_id." AND KTypNr=".$row3["KritWert"]." AND account_id=".$account_id.";<br />";
					$results5=q("SELECT * FROM ebay_auctions WHERE shopitem_id=".$item_id." AND KTypNr=".$row3["KritWert"]." AND account_id=".$account_id.";", $dbshop, __FILE__, __LINE__);
					if ( mysqli_num_rows($results5)>0 )
					{
						$row5=mysqli_fetch_array($results5);
//						if ( $row2["lastmod"]>$row5["lastmod"] )
						{
							echo 'update '.$item_id.'<br />';

							q("UPDATE ebay_auctions
							   SET KTypNr='".$KTypNr."',
								   CategoryID='".$CategoryID[$GART]."',
								   CategoryID2='".$CategoryID2[$GART]."',
								   ConditionID='".$ConditionID."',
								   Currency='".$Currency."',
								   DispatchTimeMax='".$DispatchTimeMax."',
								   ListingDuration='".$ListingDuration."',
								   ListingType='".$ListingType."',
								   StartPrice='".$StartPrice."',
								   StoreCategoryID='".$StoreCategoryID[$account_id][$GART]."',
								   StoreCategory2ID='".$StoreCategory2ID[$account_id][$GART]."',
								   Title='".$Title."',
								   lastmod=".time()."
							   WHERE id_auction=".$row5["EbayID"].";", $dbshop, __FILE__, __LINE__);

						}
//						else echo 'update skipped '.$item_id.'<br />';
					}
					//or create auction
					else
					{
						echo 'create '.$item_id.'<br />';
						q("INSERT INTO ebay_auctions (shopitem_id, account_id, KTypNr, CategoryID, CategoryID2, ConditionID, Currency, DispatchTimeMax, ListingDuration, ListingType, StartPrice, StoreCategoryID, StoreCategory2ID, Title, lastmod) VALUES(".$item_id.", ".$account_id.", ".$KTypNr.", ".$CategoryID[$GART].", ".$CategoryID2[$GART].", ".$ConditionID.", '".$Currency."', ".$DispatchTimeMax.", '".$ListingDuration."', '".$ListingType."', '".$StartPrice."', ".$StoreCategoryID[$account_id][$GART].", ".$StoreCategory2ID[$account_id][$GART].", '".$Title."', ".time().");", $dbshop, __FILE__, __LINE__);
					}
					
				}
			}
		$item_id="";
		$account_id="";
		$pricelist_id="";
		}
		q("UPDATE ebay_accounts_items SET lastupdate=".time()." WHERE id=".$id.";", $dbshop, __FILE__, __LINE__);
	}
	
?>