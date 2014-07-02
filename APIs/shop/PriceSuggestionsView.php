<?php
	require_once("../functions/shop_get_prices.php");

	if ( !isset($_POST["artnr"]) )
	{
		$results=q("SELECT a.* FROM shop_price_suggestions AS a, shop_items AS b WHERE status=0 AND a.item_id=b.id_item ORDER BY b.MPN;", $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows($results)>0 )
		{
			$row=mysqli_fetch_array($results);
			$pricesuggestion=$row;
		}
		else
		{
			echo '<p>Momentan gibt es keine offenen Preisvorschläge.</p>';
			exit;
		}
	}
	else
	{
		$results=q("SELECT * FROM shop_items WHERE MPN='".$_POST["artnr"]."';", $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows($results)>0 )
		{
			$row=mysqli_fetch_array($results);
			$row["item_id"]=$row["id_item"];
			$row["price"]=$_POST["price"];
			$pricesuggestion=$row;
		}
		else
		{
			echo 'Die Artikelnummer konnte nicht gefunden werden.';
			exit;
		}
	}


		if ( $_POST["price"]>0 ) $price=$_POST["price"]; else $price=$row["price"];
		$price=(float)str_replace(",", ".", $price);
		$price=round($price, 2);

		$results2=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
		$shop_items=mysqli_fetch_array($results2);
		$results2=q("SELECT * FROM shop_items_de WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
		$shop_items_de=mysqli_fetch_array($results2);
		$results2=q("SELECT * FROM lager WHERE ArtNr = '".$shop_items["MPN"]."';",  $dbshop, __FILE__, __LINE__);
		$shop_items_lager=mysqli_fetch_array($results2);
		
		echo '<input id="id_pricesuggestion" type="hidden" value="'.$row["id_pricesuggestion"].'" />';
		echo '<table style="float:left;">';
		echo '	<tr>';
		echo '		<th colspan="2">Preisvorschlag</th>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<th colspan="2">'.$shop_items_de["title"].'</th>';
		echo '	</tr>';
		echo '	<tr style="font-size:16px; font-weight:bold;">';
		echo '		<td>Preisvorschlag</td>';
		echo '		<td><input id="price" style="width:70px; font-size:16px; font-weight:bold;" type="text" value="'.number_format($price, 2, ",", "").'" /> Euro</td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>Preisvorschlag Netto</td>';
		$price_net=$price/1.19;
		echo '		<td>'.number_format($price_net, 2, ",", ".").' Euro</td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>COS</td>';
		echo '		<td>'.number_format($shop_items["COS"], 2, ",", ".").' Euro</td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>COS Marge</td>';
		echo '		<td>'.number_format(100-($shop_items["COS"]/$price_net*100), 2, ",", ".").' %</td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>Brutto</td>';
		echo '		<td>'.number_format($shop_items["BRUTTO"], 2, ",", ".").' Euro</td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>Mindest-VK</td>';
		echo '		<td>'.number_format($shop_items["MINDEST_VK"], 2, ",", ".").' Euro</td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>Menge 360 Tage</td>';
		echo '		<td>'.$shop_items["MENGE_360_TAGE"].'</td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>KfZ-Bestand</td>';
		echo '		<td>'.$shop_items["KFZ_BESTAND_TECDOC"].'</td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>Bestand Zentrale</td>';
		echo '		<td>'.$shop_items_lager["ISTBESTAND"].'</td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>Bestand MOCOM</td>';
		echo '		<td>'.$shop_items_lager["MOCOMBESTAND"].'</td>';
		echo '	</tr>';
		echo '	<tr>';
		echo '		<td>Bestellt</td>';
		echo '		<td>'.$shop_items["BESTELLT"].'</td>';
		echo '	</tr>';

		echo '	<tr>';
		echo '		<td>Verkäufe eBay letzte 6 Monate</td>';
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
		$orders=array();
		$results3=q("SELECT id_order FROM shop_orders WHERE (shop_id=3 OR shop_id=4 or shop_id=5) AND firstmod>".$from." AND firstmod<".$to.";", $dbshop, __FILE__, __LINE__);
		while( $row3=mysqli_fetch_array($results3) )
		{
			$orders[$row3["id_order"]]=true;
		}
		$results4=q("SELECT * FROM shop_orders_items WHERE item_id=".$shop_items["id_item"].";", $dbshop, __FILE__, __LINE__);
		$Sales=0;
		while( $row4=mysqli_fetch_array($results4) )
		{
			if( isset($orders[$row4["order_id"]]) ) $Sales+=$row4["amount"];
		}
		if( $row2["value_id"]>0 ) echo '<'.$valuename[$row2["value_id"]].'>'.$Sales.'</'.$valuename[$row2["value_id"]].'>'."\n";
		echo '		<td>'.$Sales.'</td>';
		echo '	</tr>';

		echo '</table>';


		//price overview
		$prices=array();
		$i=0;
		//price suggestion
		$prices["price"][$i]=$price;
		$prices["shipping"][$i]=4.9;
		$prices["total"][$i]=$prices["price"][$i]+$prices["shipping"][$i];
		$prices["seller"][$i]="Preisvorschlag";
		$prices["EbayID"][$i]=0;
		$prices["id"][$i]=0;
		$prices["comment"][$i]="";
		$prices["class"][$i]="good";
		//price MAPCO (eBay)
		$i++;
		$price=get_prices($shop_items["id_item"], 1, 27991);
		$prices["price"][$i]=round($price["gross"], 2);
		$prices["shipping"][$i]=4.9;
		$prices["total"][$i]=$prices["price"][$i]+$prices["shipping"][$i];
		$prices["seller"][$i]="Onlinepreis DE";
		$prices["EbayID"][$i]=0;
		$prices["id"][$i]=0;
		$prices["comment"][$i]="";
		$prices["class"][$i]="neutral";
		//price AUTOPARTNER
/*
		$i++;
		$price=get_prices($shop_items["id_item"], 1, 27992);
		$prices["price"][$i]=round($price["gross"], 2);
		$prices["shipping"][$i]=4.9;
		$prices["total"][$i]=$prices["price"][$i]+$prices["shipping"][$i];
		$prices["seller"][$i]="AP (eBay)";
		$prices["EbayID"][$i]=0;
		$prices["id"][$i]=0;
		$prices["comment"][$i]="";
		$prices["class"][$i]="neutral";
*/
		//price rote Liste
		$i++;
		$results2=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$results2=q("SELECT * FROM prpos WHERE ArtNr='".$row2["MPN"]."' AND LST_NR=7;", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$prices["price"][$i]=round($row2["POS_0_WERT"]*1.19, 2);
		$prices["shipping"][$i]=4.9;
		$prices["total"][$i]=$prices["price"][$i]+$prices["shipping"][$i];
		$prices["seller"][$i]="rote Liste";
		$prices["EbayID"][$i]=0;
		$prices["id"][$i]=0;
		$prices["comment"][$i]="";
		$prices["class"][$i]="neutral";
		//price orange Liste
		$i++;
		$results2=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$results2=q("SELECT * FROM prpos WHERE ArtNr='".$row2["MPN"]."' AND LST_NR=6;", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$prices["price"][$i]=round($row2["POS_0_WERT"]*1.19, 2);
		$prices["shipping"][$i]=4.9;
		$prices["total"][$i]=$prices["price"][$i]+$prices["shipping"][$i];
		$prices["seller"][$i]="orange Liste";
		$prices["EbayID"][$i]=0;
		$prices["id"][$i]=0;
		$prices["comment"][$i]="";
		$prices["class"][$i]="neutral";
		//price gelbe Liste
		$i++;
		$results2=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$results2=q("SELECT * FROM prpos WHERE ArtNr='".$row2["MPN"]."' AND LST_NR=5;", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$prices["price"][$i]=round($row2["POS_0_WERT"]*1.19, 2);
		$prices["shipping"][$i]=4.9;
		$prices["total"][$i]=$prices["price"][$i]+$prices["shipping"][$i];
		$prices["seller"][$i]="gelbe Liste";
		$prices["EbayID"][$i]=0;
		$prices["id"][$i]=0;
		$prices["comment"][$i]="";
		$prices["class"][$i]="neutral";
		//price grüne Liste
		$i++;
		$results2=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$results2=q("SELECT * FROM prpos WHERE ArtNr='".$row2["MPN"]."' AND LST_NR=4;", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$prices["price"][$i]=round($row2["POS_0_WERT"]*1.19, 2);
		$prices["shipping"][$i]=4.9;
		$prices["total"][$i]=$prices["price"][$i]+$prices["shipping"][$i];
		$prices["seller"][$i]="grüne Liste";
		$prices["EbayID"][$i]=0;
		$prices["id"][$i]=0;
		$prices["comment"][$i]="";
		$prices["class"][$i]="neutral";
		//price blaue Liste
		$i++;
		$results2=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$results2=q("SELECT * FROM prpos WHERE ArtNr='".$row2["MPN"]."' AND LST_NR=3;", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		$prices["price"][$i]=round($row2["POS_0_WERT"]*1.19, 2);
		$prices["shipping"][$i]=4.9;
		$prices["total"][$i]=$prices["price"][$i]+$prices["shipping"][$i];
		$prices["seller"][$i]="blaue Liste";
		$prices["EbayID"][$i]=0;
		$prices["id"][$i]=0;
		$prices["comment"][$i]="";
		$prices["class"][$i]="neutral";

		$results2=q("SELECT * FROM shop_price_research WHERE item_id=".$shop_items["id_item"].";", $dbshop, __FILE__, __LINE__);
		while( $row2=mysqli_fetch_array($results2) )
		{
			$i++;
			$prices["price"][$i]=$row2["price"];
			$prices["shipping"][$i]=$row2["shipping"];
			$prices["total"][$i]=$prices["price"][$i]+$prices["shipping"][$i];
			$prices["seller"][$i]=$row2["seller"];
			$prices["EbayID"][$i]=$row2["EbayID"];
			$prices["id"][$i]=$row2["id"];
			$prices["comment"][$i]=$row2["comment"];
			$prices["class"][$i]="bad";
		}
//		array_multisort($prices["total"], $prices["price"], $prices["shipping"], $prices["seller"], $prices["EbayID"], $prices["id"]);
		array_multisort($prices["price"],
						$prices["shipping"],
						$prices["seller"],
						$prices["EbayID"],
						$prices["id"],
						$prices["comment"],
						$prices["class"]);
		
		echo '	<table style="float:left;">';
		echo '	<tr>';
		echo '		<th colspan="5">Situation auf eBay</th>';
		echo '	</tr>';
		echo '		<tr>';
//		echo '			<th>Gesamt</th>';
		echo '			<th>Preis</th>';
		echo '			<th>Versandkosten</th>';
		echo '			<th>Händler</th>';
		echo '			<th>Auktionsnummer</th>';
		echo '			<th>Optionen</th>';
		echo '		</tr>';
		$suggested_price=0;
		for($i=0; $i<sizeof($prices["price"]); $i++)
		{
			if ($suggested_price==0 and $prices["EbayID"][$i]>0) $suggested_price=$prices["price"][$i]*1.1;
			$class=' class="'.$prices["class"][$i].'";';
			echo '		<tr'.$class.'>';
//			echo '			<td>'.number_format($prices["total"][$i], 2).' €</td>';
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
			//set this price
			echo '	<img src="'.PATH.'images/icons/24x24/left.png" title="Preis als Preisvorschlag übernehmen" onclick="price_set(\''.number_format($prices["price"][$i], 2, ",", ".").'\');" style="cursor:pointer;" />';
			//comment
			if ( $prices["comment"][$i]!="" )
			{
				echo '<img src="images/icons/24x24/info.png" alt="'.$prices["comment"][$i].'" title="'.$prices["comment"][$i].'" style="cursor:help;" onclick="alert(\''.htmlentities($prices["comment"][$i]).'\');" />';
			}
			echo '			</td>';
			echo '		</tr>';
		}
		echo '</table>';

		//HISTORIE
		$status=array();
		$status[0]="Preisbestätigung steht aus";
		$status[1]="Preis automatisch bestätigt";
		$status[2]="Preis durch DS bestätigt";
		$status[3]="Preis durch DS abgelehnt";
		$status[4]="Preis durch DS geändert";
		echo '<table style="float:left;">';
		echo '	<tr><th colspan="6">Preisvorschlags-Historie</th></tr>';
		echo '	<tr>';
		echo '		<th>Datum</th>';
		echo '		<th>Preis</th>';
		echo '		<th>Vorschlag</th>';
		echo '		<th>Status</th>';
		echo '		<th>Benutzer</th>';
		echo '		<th>Optionen</th>';
		echo '	</tr>';
		$results3=q("SELECT * FROM shop_price_suggestions WHERE item_id=".$pricesuggestion["item_id"].";", $dbshop, __FILE__, __LINE__);
		while( $row3=mysqli_fetch_array($results3) )
		{
			echo '<tr>';
			echo '	<td>'.date("d.m.Y H:i", $row3["firstmod"]).'</td>';
			echo '	<td>'.$row3["price"].' Euro</td>';
			if($row3["suggestion"]>0) $suggestion=$row3["suggestion"]." Euro";
			else $suggestion="";
			echo '	<td>'.$suggestion.'</td>';
			echo '	<td>'.$status[$row3["status"]].'</td>';
			$results4=q("SELECT * FROM cms_users WHERE id_user=".$row3["firstmod_user"].";", $dbweb, __FILE__, __LINE__);
			$row4=mysqli_fetch_array($results4);
			echo '	<td>'.$row4["name"].'</td>';
			echo '			<td>';
			//set this price
			echo '	<img src="'.PATH.'images/icons/24x24/left.png" title="Preis als Preisvorschlag übernehmen" onclick="price_set(\''.number_format($row3["price"], 2, ",", ".").'\');" style="cursor:pointer;" />';
			echo '			</td>';
			echo '</tr>';
		}
		echo '</table>';



?>
	<style>
		.bigbuttons
		{
			margin:5px;
			padding:5px;
			
			font-size:16px;
			font-weight:bold;
			
			cursor:pointer;
		}
    </style>
<?php
		//accept or reject price suggestion
		echo '<br style="clear:both;" />';
		echo '<br style="clear:both;" />';
		echo '<div style="border:2px solid #ccc;">';
		echo '	<input class="bigbuttons" onclick="price_accept();" type="button" value="Preis akzeptieren" />';
		echo '	<input class="bigbuttons" onclick="price_reject();" type="button" value="Preis ablehnen" />';
		echo '	<input class="bigbuttons" onclick="view();" type="button" value="Preis prüfen" />';
		
		//own price
/*
		echo '	<input class="bigbuttons" onclick="price_own();" style="float:right;" type="button" value="Eigenen Preis freigeben" />';
		echo '	<span style="float:right;">';
		echo '	<input class="bigbuttons" id="price_own" style="width:70px;" type="value" id="price_sugesstion_own_price" />';
		echo 'Euro </span>';
*/
		echo '</div>';
		

	if ( mysqli_num_rows($results)>0 )
	{
		echo '<br style="clear:both;" />';
		echo '<br style="clear:both;" />';
		echo '<br style="clear:both;" />';
		echo '<table>';
		echo '<tr>';
		echo '	<th colspan="5">Weitere '.mysqli_num_rows($results).' Preisvorschläge vorhanden</th>';
		echo '</tr>';
		echo '<tr>';
		echo '	<th>Nr.</th>';
		echo '	<th>Artikelbezeichnung</th>';
		echo '	<th>Preis</th>';
		echo '	<th>Vorschlag vom</th>';
		echo '	<th>Vorschlag von</th>';
		echo '</tr>';
		$i=0;
		while( $row=mysqli_fetch_array($results) )
		{
			echo '<tr>';
			//number
			$i++;
			echo '	<td>'.$i.'</td>';
			//title
			$results2=q("SELECT * FROM shop_items_de WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			echo '	<td>'.$row2["title"].'</td>';
			echo '	<td>'.$row["price"].'</td>';
			echo '	<td>'.date("d.m.Y H:i", $row["firstmod"]).'</td>';
			$results2=q("SELECT * FROM cms_users WHERE id_user=".$row["firstmod_user"].";", $dbweb, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			echo '	<td>'.$row2["username"].'</td>';
			echo '</tr>';
			if( $i==10 ) break;
		}
		echo '</table>';
	}
?>