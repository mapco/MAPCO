<?php
	require_once("../../mapco_shop_de/functions/mapco_baujahr.php");

	//get name value pairs
	$nvp=array();
	$i=0;
	if( isset($_POST["id_list"]) )
	{
		$results=q("SELECT * FROM shop_lists_fields WHERE list_id=".$_POST["id_list"]." ORDER BY ordering;", $dbshop, __FILE__,__LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			$nvp[$i]["id_field"]=$row["field_id"];
			$nvp[$i]["id_value"]=$row["value_id"];
			$i++;
		}
		$_POST["id_item"]="";
		$results=q("SELECT * FROM shop_lists_items WHERE list_id=".$_POST["id_list"].";", $dbshop, __FILE__,__LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			if( $_POST["id_item"]!="" ) $_POST["id_item"].=", ";
			$_POST["id_item"].=$row["item_id"];
		}
	}
	elseif( isset($_POST["id_listprofile"]) )
	{
		$results=q("SELECT * FROM shop_lists_profiles_fields WHERE listprofile_id=".$_POST["id_listprofile"]." ORDER BY ordering;", $dbshop, __FILE__,__LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			$nvp[$i]["id_field"]=$row["field_id"];
			$nvp[$i]["id_value"]=$row["value_id"];
			$i++;
		}
	}
	elseif ( isset($_POST["id_field"]) and isset($_POST["id_value"]))
	{
		$_POST["id_field"]=explode(", ", $_POST["id_field"]);
		$_POST["id_value"]=explode(", ", $_POST["id_value"]);
		$_POST["title"]=explode(", ", $_POST["title"]);
		$nvp=array();
		for($i=0; $i<sizeof($_POST["id_field"]); $i++)
		{
			$nvp[$i]["id_field"]=$_POST["id_field"][$i];
			$nvp[$i]["id_value"]=$_POST["id_value"][$i];
			$nvp[$i]["title"]=$_POST["title"][$i];
		}
	}
	elseif ( isset($_POST["values"]) )
	{
		$results=q("SELECT * FROM shop_fields_values WHERE value IN (".$_POST["values"].");", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_assoc($results) )
		{
			$nvp[$i]["id_field"]=$row["field_id"];
			$nvp[$i]["id_value"]=$row["id_value"];
			$i++;
		}
	}
	else show_error(9847, 7, __FILE__, __LINE__);
	
	//cache shop_fields
	$fieldname=array();
	$fieldtitle=array();
	$results=q("SELECT * FROM shop_fields;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$fieldname[$row["id_field"]]=$row["name"];
		$fieldtitle[$row["id_field"]]=$row["title"];
	}
	$valuename=array();
	$results=q("SELECT * FROM shop_fields_values;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$valuename[$row["id_value"]]=$row["value"];
	}

	//return data
	echo '<ListGetResponse>'."\n";
	$items=explode(", ", $_POST["id_item"]);
	if( sizeof($items)==1 and $items[0]=="" ) unset($items[0]); //empty list fix
	$TotalItems=sizeof($items);
	echo '	<TotalItems>'.$TotalItems.'</TotalItems>'."\n";
	
	//jump to next item
	$count=1;
	if ( !isset($_POST["NextItem"]) ) $_POST["NextItem"]=1;
	while( $count<$_POST["NextItem"] )
	{
		$count++;
		$row=mysqli_fetch_array($results);
	}
	
	//header
	echo '	<Header>'."\n";
	for($i=0; $i<sizeof($nvp); $i++)
	{
		if( $nvp[$i]["title"]=="" ) $title=$fieldtitle[$nvp[$i]["id_field"]]; else $title=$nvp[$i]["title"];
		if( $nvp[$i]["id_value"]>0 )
		{
			echo '		<ColumnName name="'.$valuename[$nvp[$i]["id_value"]].'">'.$title.'</ColumnName>'."\n";
		}
		else echo '		<ColumnName name="'.$fieldname[$nvp[$i]["id_field"]].'">'.$title.'</ColumnName>'."\n";
	}
	echo '	</Header>'."\n";
	
	
	//data
	$starttime=time()+microtime();
	for($i=0; $i<sizeof($items); $i++)
	{
		$count++;
		unset($shop_items);
		unset($shop_items_lang);
		unset($shop_items_de);
		unset($shop_items_en);
		unset($shop_items_it);
		unset($shop_items_es);
		unset($shop_items_pl);
		unset($shop_items_fr);
		unset($shop_items_ru);
		unset($shop_items_zh);
		unset($shop_items_nvp);
		unset($lager);
		$results3=q("SELECT * FROM shop_items WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
		$shop_items=mysqli_fetch_array($results3);
		echo '<Item id="'.$row["id"].'" id_item="'.$items[$i].'" id_article="'.$shop_items["article_id"].'">'."\n";
/*
		$results2=q("SELECT * FROM shop_lists_fields WHERE list_id=".$_POST["id_list"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows($results)==0 )
		{
			echo '<ListGetResponse>'."\n";
			echo '	<Ack>Error</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Keine Listenfelder gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Für diese Liste wurden keine Felder definiert, sodass auch keine Werte ausgegeben werden können.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</ListGetResponse>'."\n";
			exit;
		}
*/
		for($j=0; $j<sizeof($nvp); $j++)
		{
			$found=false;

			//Title
			if ( $nvp[$j]["id_field"]==2 )
			{
				$found=true;
				$Title="";

				//TitleGerman
				if ( $nvp[$j]["id_value"]==78 )
				{
					if ( !isset($shop_items_de) )
					{
						$results3=q("SELECT * FROM shop_items_de WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
						$shop_items_de=mysqli_fetch_array($results3);
					}
					$Title=$shop_items_de["title"];
				}
				//TitleEnglish
				if ( $nvp[$j]["id_value"]==79 )
				{
					if ( !isset($shop_items_en) )
					{
						$results3=q("SELECT * FROM shop_items_en WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
						$shop_items_en=mysqli_fetch_array($results3);
					}
					$Title=$shop_items_en["title"];
				}
				//TitleItalian
				if ( $nvp[$j]["id_value"]==80 )
				{
					if ( !isset($shop_items_it) )
					{
						$results3=q("SELECT * FROM shop_items_it WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
						$shop_items_it=mysqli_fetch_array($results3);
					}
					$Title=$shop_items_it["title"];
				}
				//TitleSpanish
				if ( $nvp[$j]["id_value"]==88 )
				{
					if ( !isset($shop_items_es) )
					{
						$results3=q("SELECT * FROM shop_items_es WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
						$shop_items_es=mysqli_fetch_array($results3);
					}
					$Title=$shop_items_es["title"];
				}
				//TitlePolish
				if ( $nvp[$j]["id_value"]==89 )
				{
					if ( !isset($shop_items_pl) )
					{
						$results3=q("SELECT * FROM shop_items_pl WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
						$shop_items_pl=mysqli_fetch_array($results3);
					}
					$Title=$shop_items_pl["title"];
				}
				//TitleFrench
				if ( $nvp[$j]["id_value"]==90 )
				{
					if ( !isset($shop_items_fr) )
					{
						$results3=q("SELECT * FROM shop_items_fr WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
						$shop_items_fr=mysqli_fetch_array($results3);
					}
					$Title=$shop_items_fr["title"];
				}
				//TitleRussian
				if ( $nvp[$j]["id_value"]==91 )
				{
					if ( !isset($shop_items_ru) )
					{
						$results3=q("SELECT * FROM shop_items_ru WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
						$shop_items_ru=mysqli_fetch_array($results3);
					}
					$Title=$shop_items_ru["title"];
				}
				//TitleChinese
				if ( $nvp[$j]["id_value"]==92 )
				{
					if ( !isset($shop_items_zh) )
					{
						$results3=q("SELECT * FROM shop_items_zh WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
						$shop_items_zh=mysqli_fetch_array($results3);
					}
					$Title=$shop_items_zh["title"];
				}

				if( $nvp[$j]["id_value"]>0 ) echo '<'.$valuename[$nvp[$j]["id_value"]].'>'.$Title.'</'.$valuename[$nvp[$j]["id_value"]].'>'."\n";
				else echo '<'.$fieldname[$nvp[$j]["id_field"]].'>'.$Title.'</'.$fieldname[$nvp[$j]["id_field"]].'>'."\n";
			}
			
			//Criteria
			if ( $nvp[$j]["id_field"]==32 )
			{
				$found=true;
				$Criteria="";

				//Criterion1
				if ( $nvp[$j]["id_value"]==98 ) $nr=0;
				if ( $nvp[$j]["id_value"]==99 ) $nr=1;
				if ( $nvp[$j]["id_value"]==100 ) $nr=2;
				if ( $nvp[$j]["id_value"]==101 ) $nr=3;
				if ( $nvp[$j]["id_value"]==102 ) $nr=4;
				if( !isset($shop_items_nvp[1][3]) )
				{
					$results3=q("SELECT * FROM shop_items_nvp WHERE item_id=".$items[$i]." AND category_id=3 AND language_id=1 ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
					while( $row=mysqli_fetch_array($results3) )
					{
						$shop_items_nvp[1][3][]=$row;
					}
				}
				if( isset($shop_items_nvp[1][3][$nr]) )
				{
					if( $nr==4 )
					{
						for($k=4; $k<sizeof($shop_items_nvp[1][3]); $k++)
						{
							if( $Criteria!="" ) $Criteria.=", ";
							$Criteria.=$shop_items_nvp[1][3][$k]["name"].':'.$shop_items_nvp[1][3][$k]["value"];
						}
					}
					else
					{
						$Criteria=$shop_items_nvp[1][3][$nr]["name"].':'.$shop_items_nvp[1][3][$nr]["value"];
					}
				}

				if( $nvp[$j]["id_value"]>0 ) echo '<'.$valuename[$nvp[$j]["id_value"]].'>'.$Criteria.'</'.$valuename[$nvp[$j]["id_value"]].'>'."\n";
				else echo '<'.$fieldname[$nvp[$j]["id_field"]].'>'.$Criteria.'</'.$fieldname[$nvp[$j]["id_field"]].'>'."\n";
			}

			
			//Quantity
			if ( $nvp[$j]["id_field"]==3 )
			{
				$found=true;
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}
				if ( !isset($lager) )
				{
					$results3=q("SELECT * FROM lager WHERE ArtNr='".$shop_items["MPN"]."';", $dbshop, __FILE__, __LINE__);
					$lager=mysqli_fetch_array($results3);
				}

				//QuantityCentral
				if ( $nvp[$j]["id_value"]==93 ) { $Quantity=$lager["ISTBESTAND"]; }
				//QuantityMOCOM
				if ( $nvp[$j]["id_value"]==94 ) { $Quantity=$lager["MOCOMBESTAND"]; }
				//QuantityOnline
				if ( $nvp[$j]["id_value"]==95 ) { $Quantity=$lager["ONLINEBESTAND"]; }
				//QuantityAmazon
				if ( $nvp[$j]["id_value"]==96 ) { $Quantity=$lager["AMAZONBESTAND"]; }

				if( $nvp[$j]["id_value"]>0 ) echo '<'.$valuename[$nvp[$j]["id_value"]].'>'.$Quantity.'</'.$valuename[$nvp[$j]["id_value"]].'>'."\n";
				else echo '<'.$fieldname[$nvp[$j]["id_field"]].'>'.$Quantity.'</'.$fieldname[$nvp[$j]["id_field"]].'>'."\n";
			}
			
			//ImageCount
			if ( $nvp[$j]["id_field"]==5 )
			{
				$found=true;
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}
				$results3=q("SELECT * FROM cms_articles_images WHERE article_id='".$shop_items["article_id"]."';", $dbweb, __FILE__, __LINE__);
				$ImageCount=mysqli_num_rows($results3);

				if( $nvp[$j]["id_value"]>0 ) echo '<'.$valuename[$nvp[$j]["id_value"]].'>'.$ImageCount.'</'.$valuename[$nvp[$j]["id_value"]].'>'."\n";
				else echo '<'.$fieldname[$nvp[$j]["id_field"]].'>'.$ImageCount.'</'.$fieldname[$nvp[$j]["id_field"]].'>'."\n";
			}
			
			//Registrations
			if ( $nvp[$j]["id_field"]==6 )
			{
				$found=true;
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}

				if( $nvp[$j]["id_value"]>0 ) echo '<'.$valuename[$nvp[$j]["id_value"]].'>'.$shop_items["KFZ_BESTAND_TECDOC"].'</'.$valuename[$nvp[$j]["id_value"]].'>'."\n";
				else echo '<'.$fieldname[$nvp[$j]["id_field"]].'>'.$shop_items["KFZ_BESTAND_TECDOC"].'</'.$fieldname[$nvp[$j]["id_field"]].'>'."\n";
			}
			
			//Collateral
			if ( $nvp[$j]["id_field"]==20 )
			{
				$found=true;
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}

				if( $nvp[$j]["id_value"]>0 ) echo '<'.$valuename[$nvp[$j]["id_value"]].'>'.$shop_items["collateral"].'</'.$valuename[$nvp[$j]["id_value"]].'>'."\n";
				else echo '<'.$fieldname[$nvp[$j]["id_field"]].'>'.$shop_items["collateral"].'</'.$fieldname[$nvp[$j]["id_field"]].'>'."\n";
			}
			
			//TectDocCriterion
			if ( $nvp[$j]["id_field"]==9 )
			{
				$found=true;
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}
				if ( !isset($shop_items_lang) )
				{
					$results3=q("SELECT * FROM shop_items_de WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
					$shop_items_lang=mysqli_fetch_array($results3);
				}
				//100
				if ( $nvp[$j]["id_value"]==1 )
				{ 
					$Criterion="";
					$criteria=explode(";", $shop_items_lang["short_description"]);
					for($k=0; $k<sizeof($criteria); $k++)
					{
						$crit=explode(":", $criteria[$k]);
						if( trim($crit[0]=="Einbauseite") )
						{
							if ($Criterion!="") $Criterion.=", ";
							$Criterion=trim($crit[1]);
						}
					}
				}

				if( $nvp[$j]["id_value"]>0 ) echo '<'.$valuename[$nvp[$j]["id_value"]].'>'.$Criterion.'</'.$valuename[$nvp[$j]["id_value"]].'>'."\n";
				else echo '<'.$fieldname[$nvp[$j]["id_field"]].'>'.$Criterion.'</'.$fieldname[$nvp[$j]["id_field"]].'>'."\n";
			}
			
			//PriceResearch
			if ( $nvp[$j]["id_field"]==12 )
			{
				$found=true;
				$results3=q("SELECT id FROM shop_price_research WHERE item_id=".$items[$i].";", $dbshop, __FILE__, __LINE__);
				$PriceResearch=mysqli_num_rows($results3);

				if( $nvp[$j]["id_value"]>0 ) echo '<'.$valuename[$nvp[$j]["id_value"]].'>'.$PriceResearch.'</'.$valuename[$nvp[$j]["id_value"]].'>'."\n";
				else echo '<'.$fieldname[$nvp[$j]["id_field"]].'>'.$PriceResearch.'</'.$fieldname[$nvp[$j]["id_field"]].'>'."\n";
			}
			
			//PriceRelevant
			if ( $nvp[$j]["id_field"]==13 )
			{
				$found=true;
/*
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}
				$results3=q("SELECT id_pricesuggestion FROM shop_price_suggestions WHERE item_id=".$items[$i]." AND NOT status=3;", $dbshop, __FILE__, __LINE__);
				$PriceRelevant=mysqli_num_rows($results3);
				echo '<'.$fieldname[$nvp[$j]["id_field"]].'>'.$PriceRelevant.'</'.$fieldname[$nvp[$j]["id_field"]].'>'."\n";
*/
				$results3=q("SELECT * FROM shop_price_suggestions WHERE item_id=".$items[$i]." AND firstmod>".(time()-90*24*3600).";", $dbshop, __FILE__, __LINE__);
				if( mysqli_num_rows($results3)>0 ) $PriceRelevant="ja"; else $PriceRelevant="nein";

				if( $nvp[$j]["id_value"]>0 ) echo '<'.$valuename[$nvp[$j]["id_value"]].'>'.$PriceRelevant.'</'.$valuename[$nvp[$j]["id_value"]].'>'."\n";
				else echo '<'.$fieldname[$nvp[$j]["id_field"]].'>'.$PriceRelevant.'</'.$fieldname[$nvp[$j]["id_field"]].'>'."\n";
			}
			
			//ImagePreview
			if ( $nvp[$j]["id_field"]==14 )
			{
				$found=true;
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}
				$results3=q("SELECT * FROM cms_articles_images WHERE article_id=".$shop_items["article_id"]." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
				if ( mysqli_num_rows($results3)>0 )
				{
					$row3=mysqli_fetch_array($results3);
					$results3=q("SELECT * FROM cms_files WHERE original_id=".$row3["file_id"]." AND imageformat_id=10;", $dbweb, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
				}
				else $url=PATH.'files_thumbnail/0.jpg';
				$url=PATH.'files/'.bcdiv($row3["id_file"], 1000).'/'.$row3["id_file"].'.'.$row3["extension"];

				if( $nvp[$j]["id_value"]>0 ) echo '<'.$valuename[$nvp[$j]["id_value"]].'>'.$url.'</'.$valuename[$nvp[$j]["id_value"]].'>'."\n";
				else echo '<'.$fieldname[$nvp[$j]["id_field"]].'>'.$url.'</'.$fieldname[$nvp[$j]["id_field"]].'>'."\n";
			}
			
			//AuctionCount
			if ( $nvp[$j]["id_field"]==15 )
			{
				$found=true;
				//AuctionsAll
				if ( $nvp[$j]["id_value"]==86 )
				{ 
					$results3=q("SELECT COUNT(id_auction) FROM ebay_auctions WHERE shopitem_id=".$items[$i].";", $dbshop, __FILE__, __LINE__);
					if( mysqli_num_rows($results3)>0 )
					{
						$row3=mysqli_fetch_array($results3);
						$AuctionCount=$row3[0];
					}
				}
				//AuctionsMapcoEuDE
				if ( $nvp[$j]["id_value"]==81 )
				{ 
					$results3=q("SELECT COUNT(id_auction) FROM ebay_auctions WHERE shopitem_id=".$items[$i]." AND accountsite_id=1;", $dbshop, __FILE__, __LINE__);
					if( mysqli_num_rows($results3)>0 )
					{
						$row3=mysqli_fetch_array($results3);
						$AuctionCount=$row3[0];
					}
				}
				//AuctionsMapcoEuIT
				if ( $nvp[$j]["id_value"]==82 )
				{ 
					$results3=q("SELECT COUNT(id_auction) FROM ebay_auctions WHERE shopitem_id=".$items[$i]." AND accountsite_id=9;", $dbshop, __FILE__, __LINE__);
					if( mysqli_num_rows($results3)>0 )
					{
						$row3=mysqli_fetch_array($results3);
						$AuctionCount=$row3[0];
					}
				}
				//AuctionsMapcoEuES
				if ( $nvp[$j]["id_value"]==83 )
				{ 
					$results3=q("SELECT COUNT(id_auction) FROM ebay_auctions WHERE shopitem_id=".$items[$i]." AND accountsite_id=10;", $dbshop, __FILE__, __LINE__);
					if( mysqli_num_rows($results3)>0 )
					{
						$row3=mysqli_fetch_array($results3);
						$AuctionCount=$row3[0];
					}
				}
				//AuctionsIhrAutopartnerDE
				if ( $nvp[$j]["id_value"]==84 )
				{ 
					$results3=q("SELECT COUNT(id_auction) FROM ebay_auctions WHERE shopitem_id=".$items[$i]." AND accountsite_id=2;", $dbshop, __FILE__, __LINE__);
					if( mysqli_num_rows($results3)>0 )
					{
						$row3=mysqli_fetch_array($results3);
						$AuctionCount=$row3[0];
					}
				}
				//AuctionsMapcoUK
				if ( $nvp[$j]["id_value"]==85 )
				{ 
					$results3=q("SELECT COUNT(id_auction) FROM ebay_auctions WHERE shopitem_id=".$items[$i]." AND accountsite_id=8;", $dbshop, __FILE__, __LINE__);
					if( mysqli_num_rows($results3)>0 )
					{
						$row3=mysqli_fetch_array($results3);
						$AuctionCount=$row3[0];
					}
				}

				if( $nvp[$j]["id_value"]>0 ) echo '<'.$valuename[$nvp[$j]["id_value"]].'>'.$AuctionCount.'</'.$valuename[$nvp[$j]["id_value"]].'>'."\n";
				else echo '<'.$fieldname[$nvp[$j]["id_field"]].'>'.$AuctionCount.'</'.$fieldname[$nvp[$j]["id_field"]].'>'."\n";
			}


			//Price
			if ( $nvp[$j]["id_field"]==16 )
			{
				$found=true;
				$Price="";
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}
				//PriceGross
				if ( $nvp[$j]["id_value"]==11 )
				{ 
					$results3=q("SELECT * FROM prpos WHERE ArtNr='".$shop_items["MPN"]."' AND LST_NR=0;", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$Price=$row3["POS_0_WERT"];
				}
				//PriceBlue
				if ( $nvp[$j]["id_value"]==5 )
				{ 
					$results3=q("SELECT * FROM prpos WHERE ArtNr='".$shop_items["MPN"]."' AND LST_NR=3;", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$Price=$row3["POS_0_WERT"];
				}
				//PriceGreen
				if ( $nvp[$j]["id_value"]==6 )
				{ 
					$results3=q("SELECT * FROM prpos WHERE ArtNr='".$shop_items["MPN"]."' AND LST_NR=4;", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$Price=$row3["POS_0_WERT"];
				}
				//PriceYellow
				if ( $nvp[$j]["id_value"]==7 )
				{ 
					$results3=q("SELECT * FROM prpos WHERE ArtNr='".$shop_items["MPN"]."' AND LST_NR=5;", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$Price=$row3["POS_0_WERT"];
				}
				//PriceOrange
				if ( $nvp[$j]["id_value"]==8 )
				{ 
					$results3=q("SELECT * FROM prpos WHERE ArtNr='".$shop_items["MPN"]."' AND LST_NR=6;", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$Price=$row3["POS_0_WERT"];
				}
				//PriceRed
				if ( $nvp[$j]["id_value"]==9 )
				{ 
					$results3=q("SELECT * FROM prpos WHERE ArtNr='".$shop_items["MPN"]."' AND LST_NR=7;", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$Price=$row3["POS_0_WERT"];
				}
				//PriceEbay
				if ( $nvp[$j]["id_value"]==10 )
				{ 
					$results3=q("SELECT * FROM prpos WHERE ArtNr='".$shop_items["MPN"]."' AND LST_NR=16815;", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$Price=$row3["POS_0_WERT"];
				}
				//PriceCustomerNet
				if ( $nvp[$j]["id_value"]==12 )
				{ 
					if( !isset($_POST["id_user"]) ) $_POST["id_user"]=$_SESSION["id_user"];
					require_once("../../mapco_shop_de/functions/shop_get_prices.php");
					$Price=get_prices($shop_items["id_item"], $_POST["id_user"]);
					$Price=number_format($Price["net"], 2, ".", ",");
				}
				//PriceCustomerGross
				if ( $nvp[$j]["id_value"]==13 )
				{ 
					if( !isset($_POST["id_user"]) ) $_POST["id_user"]=$_SESSION["id_user"];
					require_once("../../mapco_shop_de/functions/shop_get_prices.php");
					$Price=get_prices($shop_items["id_item"], $_POST["id_user"]);
					$Price=number_format($Price["gross"], 2, ".", ",");
				}
				//AmazonUKPrice
				if ( $nvp[$j]["id_value"]==33 )
				{ 
					$results3=q("SELECT * FROM prpos WHERE ARTNR='".$shop_items["MPN"]."' AND LST_NR='5';", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$Price=$row3["POS_0_WERT"];
					$Price=round($Price*0.861253113, 2);
					$Price=number_format($Price, 2, ",", "");
				}
				//AmazonUKRRT
				if ( $nvp[$j]["id_value"]==34 )
				{ 
					$results3=q("SELECT * FROM prpos WHERE ARTNR='".$shop_items["MPN"]."' AND LST_NR='5';", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$AmazonUKPrice=$row3["POS_0_WERT"];
					$AmazonUKPrice=round($AmazonUKPrice*0.861253113, 2);

					$results3=q("SELECT * FROM prpos WHERE ARTNR='".$shop_items["MPN"]."' AND LST_NR='3';", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$Price=$row3["POS_0_WERT"];
					$Price=round($Price*1.2*0.861253113, 2);
					if ( $Price < ($AmazonUKPrice*1.2*1.2) )
					{
						$Price=round($AmazonUKPrice*1.2*1.25, 2);
					}
					$Price=number_format($Price, 2, ",", "");
				}
				//PriceDealer
				if ( $nvp[$j]["id_value"]==76 )
				{ 
					$results3=q("SELECT * FROM prpos WHERE ArtNr='".$shop_items["MPN"]."' AND LST_NR=20214;", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$Price=$row3["POS_0_WERT"];
				}
				//PriceGarage
				if ( $nvp[$j]["id_value"]==77 )
				{ 
					$results3=q("SELECT * FROM prpos WHERE ArtNr='".$shop_items["MPN"]."' AND LST_NR=20215;", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$Price=$row3["POS_0_WERT"];
				}

				if( $nvp[$j]["id_value"]>0 ) echo '<'.$valuename[$nvp[$j]["id_value"]].'>'.$Price.'</'.$valuename[$nvp[$j]["id_value"]].'>'."\n";
				else echo '<'.$fieldname[$nvp[$j]["id_field"]].'>'.$Price.'</'.$fieldname[$nvp[$j]["id_field"]].'>'."\n";
			}
			
			//ProductManager
			if ( $nvp[$j]["id_field"]==17 )
			{
				$found=true;
				$ProductManager="";
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}
				switch($shop_items["menuitem_id"])
				{
					case 100:$ProductManager="Marcel Wassel"; break; //ABS-Ringe
					case  79:$ProductManager="Marcel Wassel"; break; //Achsmanschetten
					case  77:$ProductManager="Marcel Wassel"; break; //Gelenksätze
					case 101:$ProductManager="Marcel Wassel"; break; //Gelenkwellen
					case  78:$ProductManager="Marcel Wassel"; break; //Lenkmanschetten
					case  85:$ProductManager="Marcel Wassel"; break; //Radlagersätze / Radnaben
					case 104:$ProductManager="Shi Wang-Siebert"; break; //ABS-Sensoren
					case  74:$ProductManager="Florian Lindner"; break; //Anbau- und Federsätze
					case  71:$ProductManager="Florian Lindner"; break; //Brems- / Kupplungsseile
					case  72:$ProductManager="Florian Lindner"; break; //Bremsbeläge
					case  95:$ProductManager="Florian Lindner"; break; //Bremsensätze
					case  70:$ProductManager="Florian Lindner"; break; //Bremssättel
					case  76:$ProductManager="Florian Lindner"; break; //Bremsscheiben
					case  69:$ProductManager="Florian Lindner"; break; //Bremsschläuche
					case  92:$ProductManager="Florian Lindner"; break; //Bremstrommeln
					case  67:$ProductManager="Florian Lindner"; break; //Hauptbrems- / Kupplungsgeberzylinder
					case  68:$ProductManager="Florian Lindner"; break; //Radbrems- / Kupplungsnehmerzylinder
					case  73:$ProductManager="Florian Lindner"; break; //Trommelbremsbacken
					case  96:$ProductManager="Florian Lindner"; break; //Warnkontakte für Scheibenbremsbeläge
					case  90:$ProductManager="Thomas Neue"; break; //Buchsen
					case  98:$ProductManager="Gaby Driemert"; break; //Fahrwerksfedern
					case  91:$ProductManager="Gaby Driemert"; break; //Federbeinlager und -kits
					case  87:$ProductManager="Stefan Habermann"; break; //Lenkgetriebe
					case  80:$ProductManager="Thomas Neue"; break; //Lenkungs- und Chassisteile
					case  108:$ProductManager="Thomas Neue"; break; //Montagesätze Lenkerarme
					case  86:$ProductManager="Stefan Habermann"; break; //Servopumpen
					case  81:$ProductManager="Gaby Driemert"; break; //Stoßdämpfer
					case  184:$ProductManager="Gaby Driemert"; break; //Anlasser / Lichtmaschinen
					case  106:$ProductManager="Gaby Driemert"; break; //Gasfedern
					case  89:$ProductManager="Detlev Seeliger"; break; //Katalysatoren
					case  83:$ProductManager="Shi Wang-Siebert"; break; //Kraftstoffpumpen
					case  75:$ProductManager="Stefan Habermann"; break; //Kupplungssätze und Automaten
					case  103:$ProductManager="Shi Wang-Siebert"; break; //Kurbelwellensensoren
					case  97:$ProductManager="Florian Lindner"; break; //Filter
					case  94:$ProductManager="Shi Wang-Siebert"; break; //Luftmassenmesser
					case  93:$ProductManager="Thomas Neue"; break; //Motor- und Getriebelager
					case  84:$ProductManager="Marcel Wassel"; break; //Riementriebskomponenten
					case  88:$ProductManager="Detlev Seeliger"; break; //Schalldämpfer, Halter Abgasanlagen
					case  105:$ProductManager="Shi Wang-Siebert"; break; //Waschwasserpumpen
					case  82:$ProductManager="Marcel Wassel"; break; //Wasserpumpen
					case  99:$ProductManager="Marcel Wassel"; break; //Zahn- und Keilrippenriemensätze
					case  107:$ProductManager="Marcel Wassel"; break; //Keilriemen
					case  102:$ProductManager="Shi Wang-Siebert"; break; //Zündspulen & Zündmodule
					case  109:$ProductManager="Thomas Neue"; break; //Glühlampen
					case  110:$ProductManager="Marcel Wassel"; break; //Wischerblätter
				}

				if( $nvp[$j]["id_value"]>0 ) echo '<'.$valuename[$nvp[$j]["id_value"]].'>'.$ProductManager.'</'.$valuename[$nvp[$j]["id_value"]].'>'."\n";
				else echo '<'.$fieldname[$nvp[$j]["id_field"]].'>'.$ProductManager.'</'.$fieldname[$nvp[$j]["id_field"]].'>'."\n";
			}
			
			//FitmentDetails
			if ( $nvp[$j]["id_field"]==18 )
			{
				$found=true;
				$bez1=array();
				$bez2=array();
				//FitmentDetailsOptimized
				if ( $nvp[$j]["id_value"]==14 )
				{
					//get vehicles
					$k=0;
					$results3=q("SELECT * FROM shop_items_vehicles WHERE item_id=".$shop_items["id_item"]." AND language_id=1;", $dbshop, __FILE__, __LINE__);
					while( $row3=mysqli_fetch_array($results3) )
					{
						$results4=q("SELECT * FROM vehicles_de WHERE id_vehicle=".$row3["vehicle_id"].";", $dbshop, __FILE__, __LINE__);
						$row4=mysqli_fetch_array($results4);
						$bez1[$k]=$row4["BEZ1"];
						$bez2[$k]=$row4["BEZ2"];
						if ( strpos($bez2[$k], "(") >0 )
							$bez2[$k]=substr($bez2[$k], 0, strpos($bez2[$k], "(")-1);
		//				$bez3[$k]=utf8_decode($row4["BEZ3"]);
						$k++;
					}
					array_multisort($bez1, $bez2);
		
					//remove sub models
					$make=array();
					$model=array();
					$testbez2="___";
					for($k=0; $k<sizeof($bez2); $k++)
					{
						$state=strpos($bez2[$k], $testbez2." ");
						if ( ($state === false or $state > 0) and $bez2[$k]!=$testbez2 )
						{
							$make[]=$bez1[$k];
							$model[]=$bez2[$k];
							$testbez2=$bez2[$k];
						}
					}
					$bez1=$make;
					$bez2=$model;
					array_multisort($bez1, $bez2);
		
					//remove repeated brands
					$vehicles="";
					$testbez1="";
					$testbez2="";
					for($k=0; $k<sizeof($bez1); $k++)
					{
						if ( $testbez1!=$bez1[$k] )
						{
							$vehicles.=$bez1[$k];
							$testbez1=$bez1[$k];
						}
						if ( $testbez2!=$bez2[$k] )
						{
							$vehicles.=" ".$bez2[$k];
							$testbez2=$bez2[$k];
							if ( ($k+1)<sizeof($bez1) ) $vehicles.=", ";
						}
					}
					for($k=0; $k<sizeof($bez1); $k++)
					{
						if ( $testbez3!=$bez3[$k] )
						{
							$testbez3=$bez3[$k];
							if ( $testbez1!=$bez1[$k] )
							{
								$vehicles.=$bez1[$k];
								$testbez1=$bez1[$k];
							}
							if ( $testbez2!=$bez2[$k] )
							{
								$vehicles.=" ".$bez2[$k];
								$testbez2=$bez2[$k];
							}
							$vehicles.=" ".$bez3[$k];
							if ( ($k+1)<sizeof($bez1) ) $vehicles.=", ";
						}
					}

					if( $nvp[$j]["id_value"]>0 ) echo '<'.$valuename[$nvp[$j]["id_value"]].'><![CDATA['.$vehicles.']]></'.$valuename[$nvp[$j]["id_value"]].'>'."\n";
					else echo '<'.$fieldname[$nvp[$j]["id_field"]].'><![CDATA['.$vehicles.']]></'.$fieldname[$nvp[$j]["id_field"]].'>'."\n";
				}
			}


			//ImageOverview
			if ( $nvp[$j]["id_field"]==19 )
			{
				$found=true;
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}
				$results3=q("SELECT * FROM cms_articles_images WHERE article_id=".$shop_items["article_id"]." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
				if ( mysqli_num_rows($results3)>0 )
				{
					$k=0;
					$url="";
					while( $row3=mysqli_fetch_array($results3) )
					{
						$results4=q("SELECT * FROM cms_files WHERE original_id=".$row3["file_id"]." AND imageformat_id=9;", $dbweb, __FILE__, __LINE__);
						$row4=mysqli_fetch_array($results4);
						if( $k>0 ) $url.=", ";
						$url.=PATH.'files/'.bcdiv($row4["id_file"], 1000).'/'.$row4["id_file"].'.'.$row4["extension"];
						$k++;
					}
				}
				else $url=PATH.'files_thumbnail/0.jpg';

				if( $nvp[$j]["id_value"]>0 ) echo '<'.$valuename[$nvp[$j]["id_value"]].'>'.$url.'</'.$valuename[$nvp[$j]["id_value"]].'>'."\n";
				else echo '<'.$fieldname[$nvp[$j]["id_field"]].'>'.$url.'</'.$fieldname[$nvp[$j]["id_field"]].'>'."\n";
			}

			//Amazon
			if ( $nvp[$j]["id_field"]==21 )
			{
				$found=true;
				$Amazon="";
				//VendorCodeUK
				if ( $nvp[$j]["id_value"]==15 )
				{
					$Amazon='MAPCO Autotechnik GmbH, uk automotive, MAPD1';
				}
				//DoNotTouch
				if ( $nvp[$j]["id_value"]==19 )
				{
					$Amazon='';
				}
				//ExternalID
				if ( $nvp[$j]["id_value"]==20 )
				{
					$Amazon='EAN';
				}
				//Manufacturer
				if ( $nvp[$j]["id_value"]==21 )
				{
					$Amazon='Manufacturer';
				}
				//ManufacturerName
				if ( $nvp[$j]["id_value"]==22 )
				{
					$Amazon='MAPCO Autotechnik GmbH';
				}
				//BrandName
				if ( $nvp[$j]["id_value"]==23 )
				{
					$Amazon='Mapco (MBIE9)';
				}
				//NewBrandName
				if ( $nvp[$j]["id_value"]==24 )
				{
					$Amazon='';
				}
				//Feature1
				if ( $nvp[$j]["id_value"]==27 )
				{
					$Amazon='High Quality Product';
				}
				//Feature2
				if ( $nvp[$j]["id_value"]==28 )
				{
					$Amazon='directly from the manufacturer';
				}
				//Feature3
				if ( $nvp[$j]["id_value"]==29 )
				{
					$Amazon='OE quality or higher';
				}
				//Feature4
				if ( $nvp[$j]["id_value"]==30 )
				{
					$Amazon='Condition: New Item';
				}
				//Feature5
				if ( $nvp[$j]["id_value"]==31 )
				{
					$Amazon='Manufacturer: MAPCO Autotechnik GmbH';
				}
				//Description
				if ( $nvp[$j]["id_value"]==32 )
				{
					$Amazon='Fitments:<br />';
					$vehicle=array();
					$results3=q("SELECT * FROM shop_items_vehicles WHERE item_id=".$items[$i].";", $dbshop, __FILE__, __LINE__);
					while( $row3=mysqli_fetch_array($results3) )
					{
						$vehicle[$row3["vehicle_id"]]=$row3["vehicle_id"];
					}
			
					$vehicle2=array();
					$results3=q("SELECT * FROM vehicles_de ORDER BY registrations DESC;", $dbshop, __FILE__, __LINE__);
					while( $row3=mysqli_fetch_array($results3) )
					{
						if( isset($vehicle[$row3["id_vehicle"]]) ) $vehicle2[]=$row3["id_vehicle"];
					}
			
					for($k=0; $k<sizeof($vehicle2); $k++)
					{
						$results3=q("SELECT * FROM vehicles_en WHERE id_vehicle=".$vehicle2[$k].";", $dbshop, __FILE__, __LINE__);
						$row3=mysqli_fetch_array($results3);
						$newline=$row3["BEZ1"].' '.$row3["BEZ2"].' '.$row3["BEZ3"].', '.$row3["PS"].'HP, ('.baujahr($row3["BJvon"]).'-'.baujahr($row3["BJbis"]).')<br />';
						if( strlen($Amazon.$newline) <=2000 )
						{
							$Amazon .= $newline;
							if( strlen($Amazon)>=2000 ) break;
						}
						else break;
					}
					if( $Amazon=="Fitments:<br />" )
					{
						$Amazon="MAPCO products have been sold with enormous success in Germany since 1977. Over the last 30 years millions of MAPCO products have been fitted to a multitude of different vehicle applications. Customer satisfaction still commands the highest priority. Originally founded as a PLC in France, the company now coordinates its entire activities as MAPCO Autotechnik GmbH from its headquarters in Borkheide, near Berlin. MAPCO in Brueck MAPCO has made itself a name in the last three decades all over Europe as a brake specialist. Although the total sales programme for other automotive replacement parts has been dramatically extended during this period, MAPCO has not neglected its original specialism and has continually developed and enhanced its range of brake parts. MAPCO steering and suspension parts have been available to the German market since 1985. However the programme entered its most dynamic growth phase, leading up to the impressive dimensions which have now been reached, from 1995 onwards. The new technologies for front and rear axle constructions introduced by the vehicle manufacturers in the Nineties, has lead led to an explosive growth in the market potential for these replacement parts. Far in excess of 3500 individual items are carried in this product group. The corresponding catalogue with original photos and illustrations is clearly presented and practice-oriented. The quality, price and availability of these parts set new standards in the marketplace.";

					}
				}
				//ProductCategoryUK
				if ( $nvp[$j]["id_value"]==35 )
				{
					$Amazon='Car_Parts';
				}
				//ProductSubcategoryUK
				if ( $nvp[$j]["id_value"]==36 )
				{
					$Amazon='Steering_and_Suspension';
				}
				//Package Dimension Units
				if ( $nvp[$j]["id_value"]==37 )
				{
					$Amazon='Centimeters';
				}
				//PackageWeightUnits
				if ( $nvp[$j]["id_value"]==38 )
				{
					$Amazon='Grams';
				}
				//BatteriesIncluded
				if ( $nvp[$j]["id_value"]==43 )
				{
					$Amazon='No';
				}
				//LithiumIonBatteries
				if ( $nvp[$j]["id_value"]==44 )
				{
					$Amazon='';
				}
				//LithiumMetalBatteries
				if ( $nvp[$j]["id_value"]==45 )
				{
					$Amazon='';
				}
				//ASIN
				if ( $nvp[$j]["id_value"]==50 )
				{
					$Amazon='';
					$results3=q("SELECT * FROM amazon_products WHERE item_id=".$items[$i].";", $dbshop, __FILE__, __LINE__);
					if( mysqli_num_rows($results3)>0 )
					{
						$row3=mysqli_fetch_array($results3);
						$Amazon=$row3["ASIN"];
					}
				}
				//Title
				if ( $nvp[$j]["id_value"]==51 )
				{
					$Amazon='';
					$results3=q("SELECT * FROM shop_items_en WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
					if( mysqli_num_rows($results3)>0 )
					{
						$row3=mysqli_fetch_array($results3);
						$Amazon='MAPCO '.$row3["title"];
					}
				}
				//AllFeatures
				//Feature1
				if ( $nvp[$j]["id_value"]==52 )
				{
					if ( !isset($shop_items_lang) )
					{
						$results3=q("SELECT * FROM shop_items_en WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
						$shop_items_lang=mysqli_fetch_array($results3);
					}
					$Amazon=$shop_items_lang["short_description"];
				}

				if( $nvp[$j]["id_value"]>0 ) echo '<'.$valuename[$nvp[$j]["id_value"]].'>'.$Amazon.'</'.$valuename[$nvp[$j]["id_value"]].'>'."\n";
				else echo '<'.$fieldname[$nvp[$j]["id_field"]].'>'.$Amazon.'</'.$fieldname[$nvp[$j]["id_field"]].'>'."\n";
			}

			//Barcode
			if ( $nvp[$j]["id_field"]==22 )
			{
				$found=true;
				$Barcode="";
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}
				//EAN
				if ( $nvp[$j]["id_value"]==16 )
				{
					$Barcode=$shop_items["EAN"];
				}

				if( $nvp[$j]["id_value"]>0 ) echo '<'.$valuename[$nvp[$j]["id_value"]].'>'.$Barcode.'</'.$valuename[$nvp[$j]["id_value"]].'>'."\n";
				else echo '<'.$fieldname[$nvp[$j]["id_field"]].'>'.$Barcode.'</'.$fieldname[$nvp[$j]["id_field"]].'>'."\n";
			}

			//OENumbers
			if ( $nvp[$j]["id_field"]==23 )
			{
				$found=true;
				$OENumbers="";
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}
				$results3=q("SELECT LBezNr, OENr FROM t_203 AS a, t_100 AS b WHERE ArtNr='".$shop_items["MPN"]."' AND a.KHerNr=b.KherNr AND VGL=0;", $dbshop, __FILE__, __LINE__);
				//FirstOENumber
				if ( $nvp[$j]["id_value"]==17 )
				{
					if( mysqli_num_rows($results3)>0 )
					{
						$row3=mysqli_fetch_array($results3);
						$OENumbers=$row3["OENr"];
					}
				}
				//AllOENumbers
				if ( $nvp[$j]["id_value"]==18 )
				{
					$k=0;
					while($row3=mysqli_fetch_array($results3))
					{	
						if($k>0) $OENumbers.=', ';
						$OENumbers.=$row3["OENr"];
						$k++;
					}
				}
				if( $nvp[$j]["id_value"]>0 ) echo '<'.$valuename[$nvp[$j]["id_value"]].'>'.$OENumbers.'</'.$valuename[$nvp[$j]["id_value"]].'>'."\n";
				else echo '<'.$fieldname[$nvp[$j]["id_field"]].'>'.$OENumbers.'</'.$fieldname[$nvp[$j]["id_field"]].'>'."\n";
			}

			//PackageDimensions
			if ( $nvp[$j]["id_field"]==25 )
			{
				$found=true;
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}
				//PackageLength
				if ( $nvp[$j]["id_value"]==39 )
				{
					$PackageDimensions=$shop_items["PackageLength"];
				}
				//PackageWidth
				if ( $nvp[$j]["id_value"]==40 )
				{
					$PackageDimensions=$shop_items["PackageWidth"];
				}
				//PackageHeight
				if ( $nvp[$j]["id_value"]==41 )
				{
					$PackageDimensions=$shop_items["PackageHeight"];
				}
				//PackageWeight
				if ( $nvp[$j]["id_value"]==42 )
				{
					$PackageDimensions=$shop_items["GrossWeight"];
				}

				echo '<'.$fieldname[$nvp[$j]["id_field"]].'>'.$PackageDimensions.'</'.$fieldname[$nvp[$j]["id_field"]].'>'."\n";
			}
			
			//FreeText
			if ( $nvp[$j]["id_field"]==26 )
			{
				$found=true;
				//Empty
				if ( $nvp[$j]["id_value"]==46 ) { $FreeText=''; }
				//Yes
				if ( $nvp[$j]["id_value"]==47 ) { $FreeText='Yes'; }
				//No
				if ( $nvp[$j]["id_value"]==48 ) { $FreeText='No'; }
				//Germany
				if ( $nvp[$j]["id_value"]==49 ) { $FreeText='Germany'; }

				if( $nvp[$j]["id_value"]>0 ) echo '<'.$valuename[$nvp[$j]["id_value"]].'>'.$ProductManager.'</'.$valuename[$nvp[$j]["id_value"]].'>'."\n";
				else echo '<'.$fieldname[$nvp[$j]["id_field"]].'>'.$ProductManager.'</'.$fieldname[$nvp[$j]["id_field"]].'>'."\n";
			}
			
			//Sales
			if ( $nvp[$j]["id_field"]==27 )
			{
				$found=true;
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}
				$Sales=0;
				$from=0;
				$to=0;
				//B2CLastMonth
				if ( $nvp[$j]["id_value"]==53 )
				{
					//from
					$month=date("m", time());
					$from=mktime(0, 0, 0, $month, 1, 2013);
					//to
					$day=date("t", time());
					$to=mktime(23, 59, 59, $month, $day, 2013);
				}
				//B2CLastSixMonths
				if ( $nvp[$j]["id_value"]==54 )
				{
					//from
					$month=date("m", time());
					$year=date("Y", time());
					$month-=5;
					if( $month<0 )
					{
						$year--;
						$month+=12;
					}
					$from=mktime(0, 0, 0, $month, 1, $year);
					//to
					$day=date("t", time());
					$month=date("m", time());
					$year=date("Y", time());
					$to=mktime(23, 59, 59, $month, $day, $year);
				}
				//B2CLastTwelveMonths
				if ( $nvp[$j]["id_value"]==55 )
				{
					//from
					$month=date("m", time());
					$year=date("Y", time());
					$month-=11;
					if( $month<0 )
					{
						$year--;
						$month+=12;
					}
					$from=mktime(0, 0, 0, $month, 1, $year);
					//to
					$day=date("t", time());
					$month=date("m", time());
					$year=date("Y", time());
					$to=mktime(23, 59, 59, $month, $day, $year);
//					echo date("d-m-Y", $from);
//					echo ' '.date("d-m-Y", $to);
//					exit;
				}
				$orders=array();
				$results3=q("SELECT id_order FROM shop_orders WHERE (shop_id=3 OR shop_id=4 or shop_id=5) AND firstmod>".$from." AND firstmod<".$to.";", $dbshop, __FILE__, __LINE__);
				while( $row3=mysqli_fetch_array($results3) )
				{
					$orders[$row3["id_order"]]=true;
				}
				$results4=q("SELECT * FROM shop_orders_items WHERE item_id=".$shop_items["id_item"].";", $dbshop, __FILE__, __LINE__);
				while( $row4=mysqli_fetch_array($results4) )
				{
					if( isset($orders[$row4["order_id"]]) ) $Sales+=$row4["amount"];
				}

				if( $nvp[$j]["id_value"]>0 ) echo '<'.$valuename[$nvp[$j]["id_value"]].'>'.$Sales.'</'.$valuename[$nvp[$j]["id_value"]].'>'."\n";
				else echo '<'.$fieldname[$nvp[$j]["id_field"]].'>'.$Sales.'</'.$fieldname[$nvp[$j]["id_field"]].'>'."\n";
			}
			

			//ItemData
			if ( $nvp[$j]["id_field"]==29 )
			{
				$found=true;
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}
				//ItemActive
				if ( $nvp[$j]["id_value"]==56 )
				{
					$ItemData=$shop_items["active"];
				}
				//ItemActive
				if ( $nvp[$j]["id_value"]==57 )
				{
					$ItemData=$shop_items["note"];
				}

				//WeightNet
				if ( $nvp[$j]["id_value"]==58 )
				{
					$ItemData=$shop_items["ItemWeight"];
				}

				//WeightGross
				if ( $nvp[$j]["id_value"]==59 )
				{
					$ItemData=$shop_items["GrossWeight"];
				}

				//DimensionsLength
				if ( $nvp[$j]["id_value"]==60 )
				{
					$ItemData=$shop_items["PackageLength"];
				}

				//DimensionsHeight
				if ( $nvp[$j]["id_value"]==61 )
				{
					$ItemData=$shop_items["PackageHeight"];
				}

				//DimensionsWidth
				if ( $nvp[$j]["id_value"]==62 )
				{
					$ItemData=$shop_items["PackageWidth"];
				}

				//DescriptionShort
				if ( $nvp[$j]["id_value"]==63 )
				{
					if ( !isset($shop_items_it) )
					{
						$results3=q("SELECT * FROM shop_items_it WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
						$shop_items_it=mysqli_fetch_array($results3);
					}
					$ItemData=$shop_items_it["short_description"];
				}

				//SKU
				if ( $nvp[$j]["id_value"]==87 )
				{
					$found=true;
					if ( !isset($shop_items) )
					{
						$results3=q("SELECT * FROM shop_items WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
						$shop_items=mysqli_fetch_array($results3);
					}
					$ItemData=$shop_items["MPN"];
				}

				if( $nvp[$j]["id_value"]>0 ) echo '<'.$valuename[$nvp[$j]["id_value"]].'>'.$ItemData.'</'.$valuename[$nvp[$j]["id_value"]].'>'."\n";
				else echo '<'.$fieldname[$nvp[$j]["id_field"]].'>'.$ItemData.'</'.$fieldname[$nvp[$j]["id_field"]].'>'."\n";
			}
			

			//Daparto
			if ( $nvp[$j]["id_field"]==30 )
			{
				$found=true;
				//DapartoManufacturerSKU
				if ( $nvp[$j]["id_value"]==65 )
				{
					if ( !isset($shop_items) )
					{
						$results3=q("SELECT * FROM shop_items WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
						$shop_items=mysqli_fetch_array($results3);
					}
					$Daparto="MAPCO, ".$shop_items["MPN"];
				}
				
				//DapartoOEMNumbers
				if ( $nvp[$j]["id_value"]==66 )
				{
					$Daparto="";
					if ( !isset($shop_items) )
					{
						$results3=q("SELECT * FROM shop_items WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
						$shop_items=mysqli_fetch_array($results3);
					}
					$results3=q("SELECT LBezNr, OENr FROM t_203 AS a, t_100 AS b WHERE ArtNr='".$shop_items["MPN"]."' AND a.KHerNr=b.KherNr AND VGL=1;", $dbshop, __FILE__, __LINE__);
					while( $row3=mysqli_fetch_array($results3) )
					{
						$results4=q("SELECT * FROM t_012 WHERE LBezNr=".$row3["LBezNr"]." AND SprachNr=1;", $dbshop, __FILE__, __LINE__);
						$row4=mysqli_fetch_array($results4);
						if( $Daparto!="" ) $Daparto .= ',';
						$Daparto .= $row4["Bez"].','.$row3["OENr"];
					}
				}
				
				//DapartoKBANumbers
				if ( $nvp[$j]["id_value"]==67 )
				{
					$Daparto="";
					$vehicles=array();
					$results3=q("SELECT * FROM shop_items_vehicles WHERE item_id=".$items[$i]." AND language_id=1;", $dbshop, __FILE__, __LINE__);
					while( $row3=mysqli_fetch_array($results3) ) $vehicles[]=$row3["vehicle_id"];
					print_r($vehicles);
					if( sizeof($vehicles)>0 )
					{
						$kbas=array();
						$results3=q("SELECT * FROM vehicles_de WHERE id_vehicle IN (".implode(", ", $vehicles).");", $dbshop, __FILE__, __LINE__);
						while( $row3=mysqli_fetch_array($results3) )
						{
							if( $row3["KBA"]!="" )
							{
								$kbas[]=$row3["KBA"];
							}
						}
						$Daparto=implode(",", $kbas);
						unset($kbas);
					}
					unset($vehicles);
				}

				//DapartoCategory
				if ( $nvp[$j]["id_value"]==68 )
				{
					$Daparto="";
				}

				//DapartoDescription
				if ( $nvp[$j]["id_value"]==69 )
				{
					$Daparto="";
					if ( !isset($shop_items_lang) )
					{
						$results3=q("SELECT * FROM shop_items_de WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
						$shop_items_lang=mysqli_fetch_array($results3);
					}
					$Daparto=$shop_items_lang["short_description"];
				}
				
				//DapartoLink
				if ( $nvp[$j]["id_value"]==70 )
				{
					$Daparto='http://www.ihr-autopartner.de/online-shop/autoteile/'.$items[$i].'/';
				}

				//DapartoCondition
				if ( $nvp[$j]["id_value"]==71 )
				{
					$Daparto='1';
				}

				//DapartoUnused
				if ( $nvp[$j]["id_value"]==72 )
				{
					$Daparto='';
				}

				//DapartoShippingCosts
				if ( $nvp[$j]["id_value"]==73 )
				{
					$Daparto='4,90';
				}

				//DapartoItemWeight
				if ( $nvp[$j]["id_value"]==74 )
				{
					if ( !isset($shop_items) )
					{
						$results3=q("SELECT * FROM shop_items WHERE id_item=".$items[$i].";", $dbshop, __FILE__, __LINE__);
						$shop_items=mysqli_fetch_array($results3);
					}
					$Daparto=round($shop_items["WeightNet"]/1000);
				}

				//DapartoDeliveryTime
				if ( $nvp[$j]["id_value"]==75 )
				{
					$Daparto="1";
				}

				if( $nvp[$j]["id_value"]>0 ) echo '<'.$valuename[$nvp[$j]["id_value"]].'>'.$Daparto.'</'.$valuename[$nvp[$j]["id_value"]].'>'."\n";
				else echo '<'.$fieldname[$nvp[$j]["id_field"]].'>'.$Daparto.'</'.$fieldname[$nvp[$j]["id_field"]].'>'."\n";
			}

			if( $found==false )
			{
				if( $nvp[$j]["id_value"]>0 ) echo '<'.$valuename[$nvp[$j]["id_value"]].'></'.$valuename[$nvp[$j]["id_value"]].'>'."\n";
				else echo '<'.$fieldname[$nvp[$j]["id_field"]].'></'.$fieldname[$nvp[$j]["id_field"]].'>'."\n";
			}
		}
		echo '</Item>'."\n";
		
		$stoptime=time()+microtime();
		if( $stoptime-$starttime > 10 )
		{
			echo '<NextItem>'.($count+1).'</NextItem>';
			break;
		}
	}
	echo '	<Ack>Success</Ack>'."\n";
	echo '</ListGetResponse>'."\n";

?>