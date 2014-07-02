<?php
	if ( !isset($_POST["id_item"]) )
	{
		echo '<ItemAuctionsViewResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Es konnte keine Shopartikel-ID gefunden werden.</shortMsg>'."\n";
		echo '		<longMsg>Es konnte keine Shopartikel-ID gefunden werden. Die ID ist notwendig, da der Service sonst nicht weiß, welchen Shopartikel er exportieren soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemAuctionsViewResponse>'."\n";
		exit;
	}

	//title
	$results=q("SELECT * FROM shop_items WHERE id_item=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<ItemAuctionsViewResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Artikel unbekannt.</shortMsg>'."\n";
		echo '		<longMsg>Die Artikelnummer (id_item) ist ungültig.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</ItemAuctionsViewResponse>'."\n";
		exit;
	}
	$shop_items=mysqli_fetch_array($results);

	$results=q("SELECT * FROM shop_items_".$_SESSION["lang"]." WHERE id_item=".$_POST["id_item"].";", $dbshop, __FILE__, __LINE__);
	$shop_items_lang=mysqli_fetch_array($results);
	echo '<h1>';
	echo $shop_items_lang["title"];
	echo '</h1>';

	$results=q("SELECT * FROM ebay_accounts WHERE active>0 ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	while( $account=mysqli_fetch_array($results) )
	{
		echo '<h2>'.$account["title"].'</h2>';
		$results3=q("SELECT * FROM ebay_accounts_sites WHERE account_id=".$account["id_account"]." AND active>0 ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
		while( $accountsite=mysqli_fetch_array($results3) )
		{
			echo '<h3>'.$accountsite["title"].'</h3>';
			echo '<table class="hover">';
			echo '<tr>';
			echo '	<th>Nr.</th>';
			echo '	<th>Auktions-ID</th>';
			echo '	<th>Titel</th>';
			echo '	<th>Preis</th>';
			echo '	<th>Porto</th>';
			echo '	<th>Verkauft</th>';
			echo '	<th>eBay-Auktionsnummer</th>';
			echo '	<th>Erstellt am</th>';
			echo '	<th>Letzte Aktualisierung</th>';
			echo '	<th>Letzter Upload</th>';
			echo '	<th>Aufruf</th>';
			echo '	<th>Warteschlange</th>';
			echo '	<th>Optionen<br />';
			echo '	<img alt="Auktionen neu generieren" onclick="item_create_auctions('.$_POST["id_item"].', '.$accountsite["id_accountsite"].');" src="'.PATH.'images/icons/24x24/repeat.png" style="cursor:pointer;" title="eBay-Auktionen neu generieren" />';
			echo '	<img alt="eBay-Details abrufen" onclick="get_ebay_details('.$account["id_account"].');" src="'.PATH.'images/icons/24x24/info.png" style="cursor:pointer;" title="eBay-Details abrufen" />';
			echo '</th>';
			echo '</tr>';
			$results2=q("SELECT * FROM ebay_auctions WHERE shopitem_id=".$_POST["id_item"]." AND account_id=".$account["id_account"]." and accountsite_id=".$accountsite["id_accountsite"]." ORDER BY id_auction;", $dbshop, __FILE__, __LINE__);
			if ( mysqli_num_rows($results2)==0 )
			{
				echo '<tr><td colspan="13">Keine Auktionen gefunden.</td></tr>';
			}
			else
			{
				$i=0;
				while( $auction=mysqli_fetch_array($results2) )
				{
					$i++;
					echo '<tr>';
					echo '	<td>'.$i.'</td>';
					echo '	<td>'.$auction["id_auction"].'</td>';
					echo '	<td>';
					echo $auction["Title"];
					if( $auction["SubTitle"]!="" ) echo '<br /><span style="font-family: Arial, sans-serif, Verdana; font-size:11px; color:rgb(102, 102, 102);">'.$auction["SubTitle"].'</span>';
					echo '</td>';
					echo '	<td>'.number_format($auction["StartPrice"], 2).' '.$auction["Currency"].'</td>';
					echo '	<td>'.number_format($auction["ShippingServiceCost"], 2).' '.$auction["Currency"].'</td>';
					echo '	<td>'.$auction["QuantitySold"].'</td>';
					echo '	<td><a href="http://www.ebay.de/itm/'.$auction["ItemID"].'" target="_blank">'.$auction["ItemID"].'</a></td>';
					echo '	<td>'.date("d.m.Y H:i", $auction["firstmod"]).'<br /><i>'.$auction["firstmod_user"].'</i></td>';
					echo '	<td>'.date("d.m.Y H:i", $auction["lastmod"]).'<br /><i>'.$auction["lastmod_user"].'</i></td>';
					echo '	<td>'.date("d.m.Y H:i", $auction["lastupdate"]).'<br /><i>'.$auction["lastmod_user"].'</i></td>';
					echo '	<td>'.$auction["Call"].'</td>';
					echo '	<td>'.$auction["upload"].'</td>';
					echo '	<td>';
					$icon='accept.png';
					if( strpos($auction["responseXml"], "<SeverityCode>Error</SeverityCode>") !== false )  $icon='remove.png';
					elseif( strpos($auction["responseXml"], "<SeverityCode>Warning</SeverityCode>") !== false )  $icon='warning.png';
					echo '		<img alt="Zeige letzte Serverantwort an" onclick="get_last_response('.$auction["id_auction"].');" src="'.PATH.'images/icons/24x24/'.$icon.'" style="cursor:pointer;" title="Zeige letzte Serverantwort an" />';
					echo '		<img alt="Rufe Auktionsdaten ab" onclick="get_item('.$row["id_account"].', '.$auction["ItemID"].');" src="'.PATH.'images/icons/24x24/info.png" style="cursor:pointer;" title="Rufe Auktionsdaten ab" />';
					echo '		<img alt="Auktion an eBay senden" onclick="auction_submit(\''.$auction["Call"].'\', '.$auction["id_auction"].');" src="'.PATH.'images/icons/24x24/up.png" style="cursor:pointer;" title="Auktion an eBay senden" />';
					if( $auction["ItemID"]>0 )
					{
						echo '		<img alt="Auktion beenden" onclick="if (confirm(\'Wollen Sie die Auktion wirklich beenden?\')) { auction_submit(\'EndItem\', '.$auction["id_auction"].'); }" src="'.PATH.'images/icons/24x24/remove.png" style="cursor:pointer;" title="Auktion beenden" />';
					}
					echo '	</td>';
					echo '</tr>';
				}
			} //end auctions
			echo '</table>';
		} //end accountsites
	} //end accounts

?>