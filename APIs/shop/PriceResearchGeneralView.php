<?php
//	require_once("../../mapco_shop_de/functions/shop_get_prices.php");
	
		if ( !isset($_POST["id_item"]) ) { echo 'Artikel-ID ungültig.'; exit; }
		
		$results=q("SELECT * FROM shop_items WHERE id_item=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
		$shop_items=mysqli_fetch_array($results);

		$prices=array();
		$i=0;
		//price selected pricelist
		$results=q("SELECT * FROM prpos WHERE ArtNr='".$shop_items["MPN"]."' AND LST_NR=".$_POST["pricelist"].";", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$prices["price"][$i]=round($row["POS_0_WERT"]*1.19, 2);
		if( $_POST["pricelist"]==20413 ) $prices["shipping"][$i]=10.90; //Italy
		elseif( $_POST["pricelist"]==20412 ) $prices["shipping"][$i]=14.90; //Spain
		else $prices["shipping"][$i]=4.90;
		$prices["total"][$i]=$prices["price"][$i]+$prices["shipping"][$i];
		$results=q("SELECT * FROM idims_price_update_groups WHERE PREISLISTE=".$_POST["pricelist"].";", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$prices["seller"][$i]=$row["comment"];
		$prices["EbayID"][$i]=0;
		$prices["id"][$i]=0;
		$prices["comment"][$i]="";
		$prices["class"][$i]="good";
/*
		//price MAPCO (eBay)
		$price=get_prices($_POST["id_item"], 1, 27991);
		$prices["price"][$i]=round($price["gross"], 2);
		$prices["shipping"][$i]=4.90;
		$prices["total"][$i]=$prices["price"][$i]+$prices["shipping"][$i];
		$prices["seller"][$i]="MAPCO (eBay)";
		$prices["EbayID"][$i]=0;
		$prices["id"][$i]=0;
		$prices["comment"][$i]="";
		//price AUTOPARTNER
		$i++;
		$price=get_prices($_POST["id_item"], 1, 27992);
		$prices["price"][$i]=round($price["gross"], 2);
		$prices["shipping"][$i]=4.90;
		$prices["total"][$i]=$prices["price"][$i]+$prices["shipping"][$i];
		$prices["seller"][$i]="AP (eBay)";
		$prices["EbayID"][$i]=0;
		$prices["id"][$i]=0;
		$prices["comment"][$i]="";
*/	
		//price rote Liste
		$i++;
		$results=q("SELECT * FROM prpos WHERE ArtNr='".$shop_items["MPN"]."' AND LST_NR=7;", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$prices["price"][$i]=round($row["POS_0_WERT"]*1.19, 2);
		if( $_POST["pricelist"]==20413 ) $prices["shipping"][$i]=10.90; //Italy
		elseif( $_POST["pricelist"]==20412 ) $prices["shipping"][$i]=14.90; //Spain
		else $prices["shipping"][$i]=4.90;
		$prices["total"][$i]=$prices["price"][$i]+$prices["shipping"][$i];
		$prices["seller"][$i]="rote Liste";
		$prices["EbayID"][$i]=0;
		$prices["id"][$i]=0;
		$prices["comment"][$i]="";
		$prices["class"][$i]="neutral";
		//price orange Liste
		$i++;
		$results=q("SELECT * FROM prpos WHERE ArtNr='".$shop_items["MPN"]."' AND LST_NR=6;", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$prices["price"][$i]=round($row["POS_0_WERT"]*1.19, 2);
		if( $_POST["pricelist"]==20413 ) $prices["shipping"][$i]=10.90; //Italy
		elseif( $_POST["pricelist"]==20412 ) $prices["shipping"][$i]=14.90; //Spain
		else $prices["shipping"][$i]=4.90;
		$prices["total"][$i]=$prices["price"][$i]+$prices["shipping"][$i];
		$prices["seller"][$i]="orange Liste";
		$prices["EbayID"][$i]=0;
		$prices["id"][$i]=0;
		$prices["comment"][$i]="";
		$prices["class"][$i]="neutral";
		//price gelbe Liste
		$i++;
		$results=q("SELECT * FROM prpos WHERE ArtNr='".$shop_items["MPN"]."' AND LST_NR=5;", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$prices["price"][$i]=round($row["POS_0_WERT"]*1.19, 2);
		$yellow=round($row["POS_0_WERT"]*1.19, 2);
		if( $_POST["pricelist"]==20413 ) $prices["shipping"][$i]=10.90; //Italy
		elseif( $_POST["pricelist"]==20412 ) $prices["shipping"][$i]=14.90; //Spain
		else $prices["shipping"][$i]=4.90;
		$prices["total"][$i]=$prices["price"][$i]+$prices["shipping"][$i];
		$prices["seller"][$i]="gelbe Liste";
		$prices["EbayID"][$i]=0;
		$prices["id"][$i]=0;
		$prices["comment"][$i]="";
		$prices["class"][$i]="neutral";
		//price grün Liste
		$i++;
		$results=q("SELECT * FROM prpos WHERE ArtNr='".$shop_items["MPN"]."' AND LST_NR=4;", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$prices["price"][$i]=round($row["POS_0_WERT"]*1.19, 2);
		if( $_POST["pricelist"]==20413 ) $prices["shipping"][$i]=10.90; //Italy
		elseif( $_POST["pricelist"]==20412 ) $prices["shipping"][$i]=14.90; //Spain
		else $prices["shipping"][$i]=4.90;
		$prices["total"][$i]=$prices["price"][$i]+$prices["shipping"][$i];
		$prices["seller"][$i]="grüne Liste";
		$prices["EbayID"][$i]=0;
		$prices["id"][$i]=0;
		$prices["comment"][$i]="";
		$prices["class"][$i]="neutral";
		//price blau Liste
		$i++;
		$results=q("SELECT * FROM prpos WHERE ArtNr='".$shop_items["MPN"]."' AND LST_NR=3;", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$prices["price"][$i]=round($row["POS_0_WERT"]*1.19, 2);
		if( $_POST["pricelist"]==20413 ) $prices["shipping"][$i]=10.90; //Italy
		elseif( $_POST["pricelist"]==20412 ) $prices["shipping"][$i]=14.90; //Spain
		else $prices["shipping"][$i]=4.90;
		$prices["total"][$i]=$prices["price"][$i]+$prices["shipping"][$i];
		$prices["seller"][$i]="blaue Liste";
		$prices["EbayID"][$i]=0;
		$prices["id"][$i]=0;
		$prices["comment"][$i]="";
		$prices["class"][$i]="neutral";

		
		//GET PRICE-SUGGESTIONS-STATUS
		$results=q("SELECT * FROM shop_price_suggestions WHERE item_id=".$_POST["id_item"]." ORDER BY lastmod DESC LIMIT 1;", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($results)>0) 
		{
			$row=mysqli_fetch_array($results);
			switch ($row["status"]) {
				case 0: $price_suggestion_status='Preisvorschlag wurde noch nicht bearbeitet'; break;
				case 1: $price_suggestion_status='Preisvorschlag wurde automatisch akzeptiert ('.date("d.m.Y", $row["lastmod"]).')'; break;
				case 2: $price_suggestion_status='Preisvorschlag wurde akzeptiert ('.date("d.m.Y", $row["lastmod"]).')'; break;
				case 3: $price_suggestion_status='Preisvorschlag wurde abgelehnt ('.date("d.m.Y", $row["lastmod"]).')'; break;
				case 4: $price_suggestion_status='Preisvorschlag wurde (geändert) akzeptiert ('.date("d.m.Y", $row["lastmod"]).')'; break;
				default: $price_suggestion_status='';
			}
		} else $price_suggestion_status='';

		if($_POST["showall"]=="true")
		{
			$results=q("SELECT * FROM shop_price_research WHERE item_id=".$_POST["id_item"]." AND pricelist=".$_POST["pricelist"].";", $dbshop, __FILE__, __LINE__);
		}
		else
		{
			$results=q("SELECT * FROM shop_price_research WHERE item_id=".$_POST["id_item"]." AND pricelist=".$_POST["pricelist"]." AND expires>".time().";", $dbshop, __FILE__, __LINE__);
		}
		while( $row=mysqli_fetch_array($results) )
		{
			$i++;
			$prices["price"][$i]=$row["price"];
			$prices["shipping"][$i]=$row["shipping"];
			$prices["total"][$i]=$prices["price"][$i]+$prices["shipping"][$i];
			$prices["seller"][$i]=$row["seller"];
			$prices["EbayID"][$i]=$row["EbayID"];
			$prices["id"][$i]=$row["id"];
			$prices["comment"][$i]=$row["comment"];
			if( $row["expires"]<time() ) $prices["class"][$i]="standard";
			else $prices["class"][$i]="bad";
		}
		array_multisort($prices["total"], $prices["price"], $prices["shipping"], $prices["seller"], $prices["EbayID"], $prices["id"], $prices["comment"], $prices["class"]);
		
		echo '	<table style="width:700px; float:none;">';
		echo '		<tr>';
		echo '			<th>Gesamt</th>';
		echo '			<th>Preis</th>';
		echo '			<th>Versandkosten</th>';
		echo '			<th>Händler</th>';
		echo '			<th>Auktionsnummer</th>';
		echo '			<th>';
		echo '				<img alt="Preisrecherche hinzufügen" src="'.PATH.'images/icons/24x24/search_add.png" onclick="price_research_add('.$_POST["id_item"].');" title="Preisrecherche hinzufügen" style="cursor:pointer;" />';
		echo '				<img alt="Preisvorschlag hinzufügen" src="'.PATH.'images/icons/24x24/add.png" onclick="price_suggestion_add('.$_POST["id_item"].');" title="Preisvorschlag hinzufügen" style="cursor:pointer;" />';
		echo '			</th>';
		echo '		</tr>';
//		$suggested_price=0;
		for($i=0; $i<sizeof($prices["price"]); $i++)
		{
//			if ($suggested_price==0 and $prices["EbayID"][$i]>0) $suggested_price=$prices["price"][$i]*1.1;
			$class=' class="'.$prices["class"][$i].'";';
			echo '		<tr'.$class.'>';
			echo '			<td>'.number_format($prices["total"][$i], 2).' €</td>';
			echo '			<td>'.number_format($prices["price"][$i], 2).' €</td>';
			echo '			<td>'.$prices["shipping"][$i].' €</td>';
			echo '			<td>'.$prices["seller"][$i].'</td>';
			echo '			<td>';
			if ( $prices["EbayID"][$i]!=0 )
			{
				echo '<a target="_blank" href="http://www.ebay.de/itm/'.$prices["EbayID"][$i].'">'.$prices["EbayID"][$i].'</a>';
			}
			echo '			</td>';
			echo '			<td>';
			//remove price research
			if ( $prices["id"][$i]!=0 )
			{
				echo '				<img src="'.PATH.'images/icons/24x24/remove.png" alt="Preisrecherche löschen" title="Preisrecherche löschen" style="cursor:pointer;" onclick="price_research_remove('.$prices["id"][$i].');" />';
			}
			elseif ($price_suggestion_status!="" && $prices["seller"][$i]=="AP (eBay)")
			{
				echo '<img src="'.PATH.'images/icons/24x24/info.png" alt="'.$price_suggestion_status.'" title="'.$price_suggestion_status.'" style="cursor:help;" />';
			}
			//comment
			if ( $prices["comment"][$i]!="" )
			{
				echo '<img src="'.PATH.'images/icons/24x24/info.png" alt="'.$prices["comment"][$i].'" title="'.$prices["comment"][$i].'" style="cursor:help;" onclick="alert(\''.htmlentities($prices["comment"][$i]).'\');" />';
			}

			echo '		</td>';
			//INFOBOX FÜR PRICERESEARCH
			echo '		</tr>';
		}
		echo '	</table>';
		if ( $_SESSION["userrole_id"]==1 or $_SESSION["userrole_id"]==3 or $_SESSION["userrole_id"]==4 or $_SESSION["userrole_id"]==7 or $_SESSION["userrole_id"]==15 )
		{

			$results=q("SELECT * FROM shop_price_suggestions WHERE item_id=".$_POST["id_item"]." AND imported=0;", $dbshop, __FILE__, __LINE__);
			if ( mysqli_num_rows($results)>0 )
			{
				$row=mysqli_fetch_array($results);
				switch ($row["status"]) {
					case 0: $price_suggestion_status='Preisvorschlag (€ '.number_format($row["price"], 2).') wurde noch nicht bearbeitet'; break;
					case 1: $price_suggestion_status='Preisvorschlag (€ '.number_format($row["price"], 2).') wurde automatisch akzeptiert ('.date("d.m.Y", $row["lastmod"]).')'; break;
					case 2: $price_suggestion_status='Preisvorschlag (€ '.number_format($row["price"], 2).') wurde akzeptiert ('.date("d.m.Y", $row["lastmod"]).')'; break;
					case 3: $price_suggestion_status='Preisvorschlag (€ '.number_format($row["price"], 2).') wurde abgelehnt ('.date("d.m.Y", $row["lastmod"]).')'; break;
					case 4: $price_suggestion_status='Preisvorschlag (€ '.number_format($row["price"], 2).') wurde (geändert) akzeptiert ('.date("d.m.Y", $row["lastmod"]).')'; break;
					default: $price_suggestion_status='';
				}
				if( $row["status"]==0 ) echo $price_suggestion_status.' und noch nicht eingepflegt.';
				else echo $price_suggestion_status.', aber noch nicht eingepflegt.';
				echo '	</table>';
			}
		}

?>