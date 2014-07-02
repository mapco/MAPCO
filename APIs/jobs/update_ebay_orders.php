<?php

	$res_ebay_orders=q("SELECT * FROM ebay_orders_import WHERE updated = 0 ORDER BY lastmod LIMIT 100;", $dbshop, __FILE__, __LINE__);
	while ($ebay_orders=mysqli_fetch_array($res_ebay_orders))
	{
		$xml = new SimpleXMLElement($ebay_orders["order_xml"]);	
		
		//check for known TransactionIDs
		foreach ( $xml->OrderItemDetails[0]->OrderLineItem as $orderLineItem)
		{
			echo $transactionID = substr($orderLineItem->OrderLineItemID[0], strpos($orderLineItem->OrderLineItemID[0], "-")+1)."<br />";
		}
		
	}

?>