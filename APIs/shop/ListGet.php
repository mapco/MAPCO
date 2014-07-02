<?php
	require_once("../../mapco_shop_de/functions/mapco_baujahr.php");

	if ( !isset($_POST["id_list"]) )
	{
		echo '<ListGetResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Listen-ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Die Listen-ID muss angegeben werden, damit der Service weiß, welche Liste bearbeitet werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ListGetResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["NextItem"]) ) $_POST["NextItem"]=1;
	
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
	$results=q("SELECT * FROM shop_lists_items WHERE list_id=".$_POST["id_list"].";", $dbshop, __FILE__, __LINE__);
	echo '<ListGetResponse>'."\n";
	$TotalItems=mysqli_num_rows($results);
	echo '	<TotalItems>'.$TotalItems.'</TotalItems>';
	
	$count=1;
	while( $count<$_POST["NextItem"] )
	{
		$count++;
		$row=mysqli_fetch_array($results);
	}
	
	//header
	$results2=q("SELECT * FROM shop_lists_fields WHERE list_id=".$_POST["id_list"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	echo '<Header>';
	while( $row2=mysqli_fetch_array($results2) )
	{
		if( $row2["title"]=="" ) $title=$fieldtitle[$row2["field_id"]]; else $title=$row2["title"];
		if( $row2["value_id"]>0 )
		{
			echo '	<ColumnName name="'.$valuename[$row2["value_id"]].'">'.$title.'</ColumnName>';
		}
		else echo '	<ColumnName name="'.$fieldname[$row2["field_id"]].'">'.$title.'</ColumnName>';
	}
	echo '</Header>';
	
	
	//data
	$starttime=time()+microtime();
	while ( $row=mysqli_fetch_array($results) )
	{
		$count++;
		unset($shop_items);
		unset($shop_items_lang);
		unset($shop_items_it);
		unset($lager);
		$results3=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
		$shop_items=mysqli_fetch_array($results3);
		echo '<Item id="'.$row["id"].'" id_item="'.$row["item_id"].'" id_article="'.$shop_items["article_id"].'">'."\n";
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
		while ( $row2=mysqli_fetch_array($results2) )
		{
			$found=false;

			//SKU
			if ( $row2["field_id"]==1 )
			{
				$found=true;
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}

				if( $row2["value_id"]>0 ) echo '<'.$valuename[$row2["value_id"]].'>'.$shop_items["MPN"].'</'.$valuename[$row2["value_id"]].'>'."\n";
				else echo '<'.$fieldname[$row2["field_id"]].'>'.$shop_items["MPN"].'</'.$fieldname[$row2["field_id"]].'>'."\n";
			}
			
			//Title
			if ( $row2["field_id"]==2 )
			{
				$found=true;
				if ( !isset($shop_items_lang) )
				{
					$results3=q("SELECT * FROM shop_items_de WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
					$shop_items_lang=mysqli_fetch_array($results3);
				}

				if( $row2["value_id"]>0 ) echo '<'.$valuename[$row2["value_id"]].'>'.$shop_items_lang["title"].'</'.$valuename[$row2["value_id"]].'>'."\n";
				else echo '<'.$fieldname[$row2["field_id"]].'>'.$shop_items_lang["title"].'</'.$fieldname[$row2["field_id"]].'>'."\n";
			}
			
			//QuantityCentral
			if ( $row2["field_id"]==3 )
			{
				$found=true;
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}
				if ( !isset($lager) )
				{
					$results3=q("SELECT * FROM lager WHERE ArtNr='".$shop_items["MPN"]."';", $dbshop, __FILE__, __LINE__);
					$lager=mysqli_fetch_array($results3);
				}

				if( $row2["value_id"]>0 ) echo '<'.$valuename[$row2["value_id"]].'>'.$lager["ISTBESTAND"].'</'.$valuename[$row2["value_id"]].'>'."\n";
				else echo '<'.$fieldname[$row2["field_id"]].'>'.$lager["ISTBESTAND"].'</'.$fieldname[$row2["field_id"]].'>'."\n";
			}
			
			//QuantityMOCOM
			if ( $row2["field_id"]==4 )
			{
				$found=true;
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}
				if ( !isset($lager) )
				{
					$results3=q("SELECT * FROM lager WHERE ArtNr='".$shop_items["MPN"]."';", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}

				if( $row2["value_id"]>0 ) echo '<'.$valuename[$row2["value_id"]].'>'.$lager["MOCOMBESTAND"].'</'.$valuename[$row2["value_id"]].'>'."\n";
				else echo '<'.$fieldname[$row2["field_id"]].'>'.$lager["MOCOMBESTAND"].'</'.$fieldname[$row2["field_id"]].'>'."\n";
			}
			
			//ImageCount
			if ( $row2["field_id"]==5 )
			{
				$found=true;
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}
				$results3=q("SELECT * FROM cms_articles_images WHERE article_id='".$shop_items["article_id"]."';", $dbweb, __FILE__, __LINE__);
				$ImageCount=mysqli_num_rows($results3);

				if( $row2["value_id"]>0 ) echo '<'.$valuename[$row2["value_id"]].'>'.$ImageCount.'</'.$valuename[$row2["value_id"]].'>'."\n";
				else echo '<'.$fieldname[$row2["field_id"]].'>'.$ImageCount.'</'.$fieldname[$row2["field_id"]].'>'."\n";
			}
			
			//Registrations
			if ( $row2["field_id"]==6 )
			{
				$found=true;
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}

				if( $row2["value_id"]>0 ) echo '<'.$valuename[$row2["value_id"]].'>'.$shop_items["KFZ_BESTAND_TECDOC"].'</'.$valuename[$row2["value_id"]].'>'."\n";
				else echo '<'.$fieldname[$row2["field_id"]].'>'.$shop_items["KFZ_BESTAND_TECDOC"].'</'.$fieldname[$row2["field_id"]].'>'."\n";
			}
			
			//Collateral
			if ( $row2["field_id"]==20 )
			{
				$found=true;
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}

				if( $row2["value_id"]>0 ) echo '<'.$valuename[$row2["value_id"]].'>'.$shop_items["collateral"].'</'.$valuename[$row2["value_id"]].'>'."\n";
				else echo '<'.$fieldname[$row2["field_id"]].'>'.$shop_items["collateral"].'</'.$fieldname[$row2["field_id"]].'>'."\n";
			}
			
			//TectDocCriterion
			if ( $row2["field_id"]==9 )
			{
				$found=true;
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}
				if ( !isset($shop_items_lang) )
				{
					$results3=q("SELECT * FROM shop_items_de WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
					$shop_items_lang=mysqli_fetch_array($results3);
				}
				//100
				if ( $row2["value_id"]==1 )
				{ 
					$Criterion="";
					$criteria=explode(";", $shop_items_lang["short_description"]);
					for($i=0; $i<sizeof($criteria); $i++)
					{
						$crit=explode(":", $criteria[$i]);
						if( trim($crit[0]=="Einbauseite") )
						{
							if ($Criterion!="") $Criterion.=", ";
							$Criterion=trim($crit[1]);
						}
					}
				}

				if( $row2["value_id"]>0 ) echo '<'.$valuename[$row2["value_id"]].'>'.$Criterion.'</'.$valuename[$row2["value_id"]].'>'."\n";
				else echo '<'.$fieldname[$row2["field_id"]].'>'.$Criterion.'</'.$fieldname[$row2["field_id"]].'>'."\n";
			}
			
			//PriceResearch
			if ( $row2["field_id"]==12 )
			{
				$found=true;
				$results3=q("SELECT id FROM shop_price_research WHERE item_id=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
				$PriceResearch=mysqli_num_rows($results3);

				if( $row2["value_id"]>0 ) echo '<'.$valuename[$row2["value_id"]].'>'.$PriceResearch.'</'.$valuename[$row2["value_id"]].'>'."\n";
				else echo '<'.$fieldname[$row2["field_id"]].'>'.$PriceResearch.'</'.$fieldname[$row2["field_id"]].'>'."\n";
			}
			
			//PriceRelevant
			if ( $row2["field_id"]==13 )
			{
				$found=true;
/*
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}
				$results3=q("SELECT id_pricesuggestion FROM shop_price_suggestions WHERE item_id=".$row["item_id"]." AND NOT status=3;", $dbshop, __FILE__, __LINE__);
				$PriceRelevant=mysqli_num_rows($results3);
				echo '<'.$fieldname[$row2["field_id"]].'>'.$PriceRelevant.'</'.$fieldname[$row2["field_id"]].'>'."\n";
*/
				$results3=q("SELECT * FROM shop_price_suggestions WHERE item_id=".$row["item_id"]." AND firstmod>".(time()-90*24*3600).";", $dbshop, __FILE__, __LINE__);
				if( mysqli_num_rows($results3)>0 ) $PriceRelevant="ja"; else $PriceRelevant="nein";

				if( $row2["value_id"]>0 ) echo '<'.$valuename[$row2["value_id"]].'>'.$PriceRelevant.'</'.$valuename[$row2["value_id"]].'>'."\n";
				else echo '<'.$fieldname[$row2["field_id"]].'>'.$PriceRelevant.'</'.$fieldname[$row2["field_id"]].'>'."\n";
			}
			
			//ImagePreview
			if ( $row2["field_id"]==14 )
			{
				$found=true;
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
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

				if( $row2["value_id"]>0 ) echo '<'.$valuename[$row2["value_id"]].'>'.$url.'</'.$valuename[$row2["value_id"]].'>'."\n";
				else echo '<'.$fieldname[$row2["field_id"]].'>'.$url.'</'.$fieldname[$row2["field_id"]].'>'."\n";
			}
			
			//AuctionCount
			if ( $row2["field_id"]==15 )
			{
				$found=true;
				$count=0;
				$results3=q("SELECT COUNT(id_auction) FROM ebay_auctions WHERE shopitem_id=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
				if( mysqli_num_rows($results3)>0 )
				{
					$row3=mysqli_fetch_array($results3);
					$count=$row3[0];
				}

				if( $row2["value_id"]>0 ) echo '<'.$valuename[$row2["value_id"]].'>'.$count.'</'.$valuename[$row2["value_id"]].'>'."\n";
				else echo '<'.$fieldname[$row2["field_id"]].'>'.$count.'</'.$fieldname[$row2["field_id"]].'>'."\n";
			}
			
			//Price
			if ( $row2["field_id"]==16 )
			{
				$found=true;
				$Price="";
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}
				//PriceGross
				if ( $row2["value_id"]==11 )
				{ 
					$results3=q("SELECT * FROM prpos WHERE ArtNr='".$shop_items["MPN"]."' AND LST_NR=0;", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$Price=$row3["POS_0_WERT"];
				}
				//PriceBlue
				if ( $row2["value_id"]==5 )
				{ 
					$results3=q("SELECT * FROM prpos WHERE ArtNr='".$shop_items["MPN"]."' AND LST_NR=3;", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$Price=$row3["POS_0_WERT"];
				}
				//PriceGreen
				if ( $row2["value_id"]==6 )
				{ 
					$results3=q("SELECT * FROM prpos WHERE ArtNr='".$shop_items["MPN"]."' AND LST_NR=4;", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$Price=$row3["POS_0_WERT"];
				}
				//PriceYellow
				if ( $row2["value_id"]==7 )
				{ 
					$results3=q("SELECT * FROM prpos WHERE ArtNr='".$shop_items["MPN"]."' AND LST_NR=5;", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$Price=$row3["POS_0_WERT"];
				}
				//PriceOrange
				if ( $row2["value_id"]==8 )
				{ 
					$results3=q("SELECT * FROM prpos WHERE ArtNr='".$shop_items["MPN"]."' AND LST_NR=6;", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$Price=$row3["POS_0_WERT"];
				}
				//PriceRed
				if ( $row2["value_id"]==9 )
				{ 
					$results3=q("SELECT * FROM prpos WHERE ArtNr='".$shop_items["MPN"]."' AND LST_NR=7;", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$Price=$row3["POS_0_WERT"];
				}
				//PriceEbay
				if ( $row2["value_id"]==10 )
				{ 
					$results3=q("SELECT * FROM prpos WHERE ArtNr='".$shop_items["MPN"]."' AND LST_NR=16815;", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$Price=$row3["POS_0_WERT"];
				}
				//PriceCustomerNet
				if ( $row2["value_id"]==12 )
				{ 
					if( !isset($_POST["id_user"]) ) $_POST["id_user"]=$_SESSION["id_user"];
					require_once("../../mapco_shop_de/functions/shop_get_prices.php");
					$Price=get_prices($shop_items["id_item"], $_POST["id_user"]);
					$Price=number_format($Price["net"], 2, ".", ",");
				}
				//PriceCustomerGross
				if ( $row2["value_id"]==13 )
				{ 
					if( !isset($_POST["id_user"]) ) $_POST["id_user"]=$_SESSION["id_user"];
					require_once("../../mapco_shop_de/functions/shop_get_prices.php");
					$Price=get_prices($shop_items["id_item"], $_POST["id_user"]);
					$Price=number_format($Price["gross"], 2, ".", ",");
				}
				//AmazonUKPrice
				if ( $row2["value_id"]==33 )
				{ 
					$results3=q("SELECT * FROM prpos WHERE ARTNR='".$shop_items["MPN"]."' AND LST_NR='5';", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$Price=$row3["POS_0_WERT"];
					$Price=round($Price*0.861253113, 2);
					$Price=number_format($Price, 2, ",", "");
				}
				//AmazonUKRRT
				if ( $row2["value_id"]==34 )
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
				if ( $row2["value_id"]==76 )
				{ 
					$results3=q("SELECT * FROM prpos WHERE ArtNr='".$shop_items["MPN"]."' AND LST_NR=20214;", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$Price=$row3["POS_0_WERT"];
				}
				//PriceGarage
				if ( $row2["value_id"]==77 )
				{ 
					$results3=q("SELECT * FROM prpos WHERE ArtNr='".$shop_items["MPN"]."' AND LST_NR=20215;", $dbshop, __FILE__, __LINE__);
					$row3=mysqli_fetch_array($results3);
					$Price=$row3["POS_0_WERT"];
				}

				if( $row2["value_id"]>0 ) echo '<'.$valuename[$row2["value_id"]].'>'.$Price.'</'.$valuename[$row2["value_id"]].'>'."\n";
				else echo '<'.$fieldname[$row2["field_id"]].'>'.$Price.'</'.$fieldname[$row2["field_id"]].'>'."\n";
			}
			
			//ProductManager
			if ( $row2["field_id"]==17 )
			{
				$found=true;
				$ProductManager="";
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
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

				if( $row2["value_id"]>0 ) echo '<'.$valuename[$row2["value_id"]].'>'.$ProductManager.'</'.$valuename[$row2["value_id"]].'>'."\n";
				else echo '<'.$fieldname[$row2["field_id"]].'>'.$ProductManager.'</'.$fieldname[$row2["field_id"]].'>'."\n";
			}
			
			//FitmentDetails
			if ( $row2["field_id"]==18 )
			{
				$found=true;
				//FitmentDetailsOptimized
				if ( $row2["value_id"]==14 )
				{
					//get vehicles
					$j=0;
					$results3=q("SELECT * FROM shop_items_vehicles WHERE item_id=".$shop_items["id_item"]." AND language_id=1;", $dbshop, __FILE__, __LINE__);
					while( $row3=mysqli_fetch_array($results3) )
					{
						$results4=q("SELECT * FROM vehicles_de WHERE id_vehicle=".$row3["vehicle_id"].";", $dbshop, __FILE__, __LINE__);
						$row4=mysqli_fetch_array($results4);
						$bez1[$j]=$row4["BEZ1"];
						$bez2[$j]=$row4["BEZ2"];
						if ( strpos($bez2[$j], "(") >0 )
							$bez2[$j]=substr($bez2[$j], 0, strpos($bez2[$j], "(")-1);
		//				$bez3[$j]=utf8_decode($row4["BEZ3"]);
						$j++;
					}
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

					if( $row2["value_id"]>0 ) echo '<'.$valuename[$row2["value_id"]].'>'.$vehicles.'</'.$valuename[$row2["value_id"]].'>'."\n";
					else echo '<'.$fieldname[$row2["field_id"]].'>'.$vehicles.'</'.$fieldname[$row2["field_id"]].'>'."\n";
				}
			}


			//ImageOverview
			if ( $row2["field_id"]==19 )
			{
				$found=true;
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}
				$results3=q("SELECT * FROM cms_articles_images WHERE article_id=".$shop_items["article_id"]." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
				if ( mysqli_num_rows($results3)>0 )
				{
					$i=0;
					$url="";
					while( $row3=mysqli_fetch_array($results3) )
					{
						$results4=q("SELECT * FROM cms_files WHERE original_id=".$row3["file_id"]." AND imageformat_id=9;", $dbweb, __FILE__, __LINE__);
						$row4=mysqli_fetch_array($results4);
						if( $i>0 ) $url.=", ";
						$url.=PATH.'files/'.bcdiv($row4["id_file"], 1000).'/'.$row4["id_file"].'.'.$row4["extension"];
						$i++;
					}
				}
				else $url=PATH.'files_thumbnail/0.jpg';

				if( $row2["value_id"]>0 ) echo '<'.$valuename[$row2["value_id"]].'>'.$url.'</'.$valuename[$row2["value_id"]].'>'."\n";
				else echo '<'.$fieldname[$row2["field_id"]].'>'.$url.'</'.$fieldname[$row2["field_id"]].'>'."\n";
			}

			//Amazon
			if ( $row2["field_id"]==21 )
			{
				$found=true;
				$Amazon="";
				//VendorCodeUK
				if ( $row2["value_id"]==15 )
				{
					$Amazon='MAPCO Autotechnik GmbH, uk automotive, MAPD1';
				}
				//DoNotTouch
				if ( $row2["value_id"]==19 )
				{
					$Amazon='';
				}
				//ExternalID
				if ( $row2["value_id"]==20 )
				{
					$Amazon='EAN';
				}
				//Manufacturer
				if ( $row2["value_id"]==21 )
				{
					$Amazon='Manufacturer';
				}
				//ManufacturerName
				if ( $row2["value_id"]==22 )
				{
					$Amazon='MAPCO Autotechnik GmbH';
				}
				//BrandName
				if ( $row2["value_id"]==23 )
				{
					$Amazon='Mapco (MBIE9)';
				}
				//NewBrandName
				if ( $row2["value_id"]==24 )
				{
					$Amazon='';
				}
				//Feature1
				if ( $row2["value_id"]==27 )
				{
					$Amazon='High Quality Product';
				}
				//Feature2
				if ( $row2["value_id"]==28 )
				{
					$Amazon='directly from the manufacturer';
				}
				//Feature3
				if ( $row2["value_id"]==29 )
				{
					$Amazon='OE quality or higher';
				}
				//Feature4
				if ( $row2["value_id"]==30 )
				{
					$Amazon='Condition: New Item';
				}
				//Feature5
				if ( $row2["value_id"]==31 )
				{
					$Amazon='Manufacturer: MAPCO Autotechnik GmbH';
				}
				//Description
				if ( $row2["value_id"]==32 )
				{
					$Amazon='Fitments:<br />';
					$vehicle=array();
					$results3=q("SELECT * FROM shop_items_vehicles WHERE item_id=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
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
			
					for($i=0; $i<sizeof($vehicle2); $i++)
					{
						$results3=q("SELECT * FROM vehicles_en WHERE id_vehicle=".$vehicle2[$i].";", $dbshop, __FILE__, __LINE__);
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
				if ( $row2["value_id"]==35 )
				{
					$Amazon='Car_Parts';
				}
				//ProductSubcategoryUK
				if ( $row2["value_id"]==36 )
				{
					$Amazon='Steering_and_Suspension';
				}
				//Package Dimension Units
				if ( $row2["value_id"]==37 )
				{
					$Amazon='Centimeters';
				}
				//PackageWeightUnits
				if ( $row2["value_id"]==38 )
				{
					$Amazon='Grams';
				}
				//BatteriesIncluded
				if ( $row2["value_id"]==43 )
				{
					$Amazon='No';
				}
				//LithiumIonBatteries
				if ( $row2["value_id"]==44 )
				{
					$Amazon='';
				}
				//LithiumMetalBatteries
				if ( $row2["value_id"]==45 )
				{
					$Amazon='';
				}
				//ASIN
				if ( $row2["value_id"]==50 )
				{
					$Amazon='';
					$results3=q("SELECT * FROM amazon_products WHERE account_id=5 AND item_id=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
					if( mysqli_num_rows($results3)>0 )
					{
						$row3=mysqli_fetch_array($results3);
						$Amazon=$row3["ASIN"];
					}
				}
				//Title
				if ( $row2["value_id"]==51 )
				{
					$Amazon='';
					$results3=q("SELECT * FROM shop_items_en WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
					if( mysqli_num_rows($results3)>0 )
					{
						$row3=mysqli_fetch_array($results3);
						$Amazon='MAPCO '.$row3["title"];
					}
				}
				//AllFeatures
				//Feature1
				if ( $row2["value_id"]==52 )
				{
					if ( !isset($shop_items_lang) )
					{
						$results3=q("SELECT * FROM shop_items_en WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
						$shop_items_lang=mysqli_fetch_array($results3);
					}
					$Amazon=$shop_items_lang["short_description"];
				}

				if( $row2["value_id"]>0 ) echo '<'.$valuename[$row2["value_id"]].'>'.$Amazon.'</'.$valuename[$row2["value_id"]].'>'."\n";
				else echo '<'.$fieldname[$row2["field_id"]].'>'.$Amazon.'</'.$fieldname[$row2["field_id"]].'>'."\n";
			}

			//Barcode
			if ( $row2["field_id"]==22 )
			{
				$found=true;
				$Barcode="";
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}
				//EAN
				if ( $row2["value_id"]==16 )
				{
					$Barcode=$shop_items["EAN"];
				}

				if( $row2["value_id"]>0 ) echo '<'.$valuename[$row2["value_id"]].'>'.$Barcode.'</'.$valuename[$row2["value_id"]].'>'."\n";
				else echo '<'.$fieldname[$row2["field_id"]].'>'.$Barcode.'</'.$fieldname[$row2["field_id"]].'>'."\n";
			}

			//OENumbers
			if ( $row2["field_id"]==23 )
			{
				$found=true;
				$OENumbers="";
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}
				$results3=q("SELECT LBezNr, OENr FROM t_203 AS a, t_100 AS b WHERE ArtNr='".$shop_items["MPN"]."' AND a.KHerNr=b.KherNr AND VGL=0;", $dbshop, __FILE__, __LINE__);
				//FirstOENumber
				if ( $row2["value_id"]==17 )
				{
					if( mysqli_num_rows($results3)>0 )
					{
						$row3=mysqli_fetch_array($results3);
						$OENumbers=$row3["OENr"];
					}
				}
				//AllOENumbers
				if ( $row2["value_id"]==18 )
				{
					$k=0;
					while($row3=mysqli_fetch_array($results3))
					{	
						if($k>0) $OENumbers.=', ';
						$OENumbers.=$row3["OENr"];
						$k++;
					}
				}
				if( $row2["value_id"]>0 ) echo '<'.$valuename[$row2["value_id"]].'>'.$OENumbers.'</'.$valuename[$row2["value_id"]].'>'."\n";
				else echo '<'.$fieldname[$row2["field_id"]].'>'.$OENumbers.'</'.$fieldname[$row2["field_id"]].'>'."\n";
			}

			//PackageDimensions
			if ( $row2["field_id"]==25 )
			{
				$found=true;
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}
				//PackageLength
				if ( $row2["value_id"]==39 )
				{
					$PackageDimensions=$shop_items["PackageLength"];
				}
				//PackageWidth
				if ( $row2["value_id"]==40 )
				{
					$PackageDimensions=$shop_items["PackageWidth"];
				}
				//PackageHeight
				if ( $row2["value_id"]==41 )
				{
					$PackageDimensions=$shop_items["PackageHeight"];
				}
				//PackageWeight
				if ( $row2["value_id"]==42 )
				{
					$PackageDimensions=$shop_items["GrossWeight"];
				}

				echo '<'.$fieldname[$row2["field_id"]].'>'.$PackageDimensions.'</'.$fieldname[$row2["field_id"]].'>'."\n";
			}
			
			//FreeText
			if ( $row2["field_id"]==26 )
			{
				$found=true;
				//Empty
				if ( $row2["value_id"]==46 ) { $FreeText=''; }
				//Yes
				if ( $row2["value_id"]==47 ) { $FreeText='Yes'; }
				//No
				if ( $row2["value_id"]==48 ) { $FreeText='No'; }
				//Germany
				if ( $row2["value_id"]==49 ) { $FreeText='Germany'; }

				if( $row2["value_id"]>0 ) echo '<'.$valuename[$row2["value_id"]].'>'.$ProductManager.'</'.$valuename[$row2["value_id"]].'>'."\n";
				else echo '<'.$fieldname[$row2["field_id"]].'>'.$ProductManager.'</'.$fieldname[$row2["field_id"]].'>'."\n";
			}
			
			//Sales
			if ( $row2["field_id"]==27 )
			{
				$found=true;
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}
				$Sales=0;
				$from=0;
				$to=0;
				//B2CLastMonth
				if ( $row2["value_id"]==53 )
				{
					//from
					$month=date("m", time());
					$from=mktime(0, 0, 0, $month, 1, 2013);
					//to
					$day=date("t", time());
					$to=mktime(23, 59, 59, $month, $day, 2013);
				}
				//B2CLastSixMonths
				if ( $row2["value_id"]==54 )
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
				if ( $row2["value_id"]==55 )
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

				if( $row2["value_id"]>0 ) echo '<'.$valuename[$row2["value_id"]].'>'.$Sales.'</'.$valuename[$row2["value_id"]].'>'."\n";
				else echo '<'.$fieldname[$row2["field_id"]].'>'.$Sales.'</'.$fieldname[$row2["field_id"]].'>'."\n";
			}
			

			//ItemData
			if ( $row2["field_id"]==29 )
			{
				$found=true;
				if ( !isset($shop_items) )
				{
					$results3=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
					$shop_items=mysqli_fetch_array($results3);
				}
				//ItemActive
				if ( $row2["value_id"]==56 )
				{
					$ItemData=$shop_items["active"];
				}
				//ItemActive
				if ( $row2["value_id"]==57 )
				{
					$ItemData=$shop_items["note"];
				}

				//WeightNet
				if ( $row2["value_id"]==58 )
				{
					$ItemData=$shop_items["ItemWeight"];
				}

				//WeightGross
				if ( $row2["value_id"]==59 )
				{
					$ItemData=$shop_items["GrossWeight"];
				}

				//DimensionsLength
				if ( $row2["value_id"]==60 )
				{
					$ItemData=$shop_items["PackageLength"];
				}

				//DimensionsHeight
				if ( $row2["value_id"]==61 )
				{
					$ItemData=$shop_items["PackageHeight"];
				}

				//DimensionsWidth
				if ( $row2["value_id"]==62 )
				{
					$ItemData=$shop_items["PackageWidth"];
				}

				//DescriptionShort
				if ( $row2["value_id"]==63 )
				{
					if ( !isset($shop_items_it) )
					{
						$results3=q("SELECT * FROM shop_items_it WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
						$shop_items_it=mysqli_fetch_array($results3);
					}
					$ItemData=$shop_items_it["short_description"];
				}

				if( $row2["value_id"]>0 ) echo '<'.$valuename[$row2["value_id"]].'>'.$ItemData.'</'.$valuename[$row2["value_id"]].'>'."\n";
				else echo '<'.$fieldname[$row2["field_id"]].'>'.$ItemData.'</'.$fieldname[$row2["field_id"]].'>'."\n";
			}
			

			//Daparto
			if ( $row2["field_id"]==30 )
			{
				$found=true;
				//DapartoManufacturerSKU
				if ( $row2["value_id"]==65 )
				{
					if ( !isset($shop_items) )
					{
						$results3=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
						$shop_items=mysqli_fetch_array($results3);
					}
					$Daparto="MAPCO, ".$shop_items["MPN"];
				}
				
				//DapartoOEMNumbers
				if ( $row2["value_id"]==66 )
				{
					$Daparto="";
					if ( !isset($shop_items) )
					{
						$results3=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
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
				if ( $row2["value_id"]==67 )
				{
					$Daparto="";
					$vehicles=array();
					$results3=q("SELECT * FROM shop_items_vehicles WHERE item_id=".$row["item_id"]." AND language_id=1;", $dbshop, __FILE__, __LINE__);
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
				if ( $row2["value_id"]==68 )
				{
					$Daparto="";
				}

				//DapartoDescription
				if ( $row2["value_id"]==69 )
				{
					$Daparto="";
					if ( !isset($shop_items_lang) )
					{
						$results3=q("SELECT * FROM shop_items_de WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
						$shop_items_lang=mysqli_fetch_array($results3);
					}
					$Daparto=$shop_items_lang["short_description"];
				}
				
				//DapartoLink
				if ( $row2["value_id"]==70 )
				{
					$Daparto='http://www.ihr-autopartner.de/online-shop/autoteile/'.$row["item_id"].'/';
				}

				//DapartoCondition
				if ( $row2["value_id"]==71 )
				{
					$Daparto='1';
				}

				//DapartoUnused
				if ( $row2["value_id"]==72 )
				{
					$Daparto='';
				}

				//DapartoShippingCosts
				if ( $row2["value_id"]==73 )
				{
					$Daparto='4,90';
				}

				//DapartoItemWeight
				if ( $row2["value_id"]==74 )
				{
					if ( !isset($shop_items) )
					{
						$results3=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
						$shop_items=mysqli_fetch_array($results3);
					}
					$Daparto=round($shop_items["WeightNet"]/1000);
				}

				//DapartoDeliveryTime
				if ( $row2["value_id"]==75 )
				{
					$Daparto="1";
				}

				if( $row2["value_id"]>0 ) echo '<'.$valuename[$row2["value_id"]].'>'.$Daparto.'</'.$valuename[$row2["value_id"]].'>'."\n";
				else echo '<'.$fieldname[$row2["field_id"]].'>'.$Daparto.'</'.$fieldname[$row2["field_id"]].'>'."\n";
			}

			if( $found==false )
			{
				if( $row2["value_id"]>0 ) echo '<'.$valuename[$row2["value_id"]].'></'.$valuename[$row2["value_id"]].'>'."\n";
				else echo '<'.$fieldname[$row2["field_id"]].'></'.$fieldname[$row2["field_id"]].'>'."\n";
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