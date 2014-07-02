<?php

	$results=q("SELECT * FROM ebay_orders WHERE BuyerUserID = '".$_POST["user_id"]."' and account_id = '".$_POST["account_id"]."' order by CreatedTimeTimestamp;", $dbshop, __FILE__, __LINE__);
	
	if (mysqli_num_rows($results)>0) 
	{
		echo '<div id="transaction_select">';
		echo '<table>';
		echo '<th></th><th>Kaufsdatum</th><th>VPN</th><th>Käufer ID</th><th>Käufername</th><th>Artikelnummer</th><th>Artikelbezeichnung</th><th>Stückzahl</th></tr>';
		while ($row=mysqli_fetch_array($results)) 
		{
			
			$results2=q("SELECT * FROM ebay_orders_items WHERE OrderID = '".$row["OrderID"]."' and account_id = '".$_POST["account_id"]."';", $dbshop, __FILE__, __LINE__);
			
			while ($row2=mysqli_fetch_array($results2))
			{
				//ARTIKELGRUPPE
				$results3=q("SELECT * FROM shop_items WHERE MPN = '".$row2["ItemSKU"]."';", $dbshop, __FILE__, __LINE__);
				$row3=mysqli_fetch_array($results3);
				echo '<tr>';
				echo '	<td><input type="checkbox" value="'.$row2["TransactionID"].'"></td>';
				echo '	<td id="transaction_select_OrderDate_'.$row2["TransactionID"].'">'.date("d.m.Y", $row2["CreatedDateTimestamp"]).'</td>';
				echo '	<td id="transaction_select_OrderVPN_'.$row2["TransactionID"].'">'.$row2["ShippingDetailsSellingManagerSalesRecordNumber"].'</td>';
				echo '	<td id="transaction_select_OrderBuyerID_'.$row2["TransactionID"].'">'.$row["BuyerUserID"].'</td>';
				echo '	<td id="transaction_select_OrderBuyerName_'.$row2["TransactionID"].'">'.$row["ShippingAddressName"].'</td>';
				echo '	<td id="transaction_select_OrderMPN_'.$row2["TransactionID"].'">'.$row2["ItemSKU"].'</td>';
				echo '	<td id="transaction_select_OrderArtBez_'.$row2["TransactionID"].'">'.$row2["ItemTitle"].'</td>';
				echo '	<td id="transaction_select_OrderQty_'.$row2["TransactionID"].'">'.$row2["QuantityPurchased"].'</td>';
		//		echo '	<input type="hidden" name="ArtGroup" id="transaction_select_OrderArtGroup_'.$row2["TransactionID"].'" value="'.$row3["category_id"].'">';
				echo '</tr>';
			}
		}
		echo '</table>';
		echo '</div>';

	}
	else
	{
		echo '<b>Es wurden keine Verkäufe für dase Ebay-Mitglied '.$_POST["user_id"].' gefunden.</b>';
	}



?>