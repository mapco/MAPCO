<?php
	echo '<style> .highlight_word { background-color: pink; } </style>';
	function highlightWords($string, $words)
	 {
		foreach ( $words as $word )
		{
			$string = str_ireplace($word, '<span class="highlight_word">'.$word.'</span>', $string);
		}
		/*** return the highlighted string ***/
		return $string;
	 }
 
	//determine search words
 	$words=explode(" ", $_POST["needle"]);
	for($i=0; $i<sizeof($words); $i++) $words[$i]=trim($words[$i]);
 
	echo '<table>';
	echo '	<tr>';
	echo '		<th>Nr.</th>';
	echo '		<th>Datum</th>';
	echo '		<th>Empfänger</th>';
	echo '		<th>Ware</th>';
	echo '		<th>Versandart</th>';
	echo '		<th>Sendungsnummer</th>';
	echo '		<th>Optionen</th>';
	echo '	</tr>';
	if( isset($_POST["filter"]) and $_POST["filter"]==1 )
	{
		$results=q("SELECT * FROM shop_orders WHERE shipping_type_id=1 OR shipping_type_id=2 OR shipping_type_id=5 OR shipping_type_id=7;", $dbshop, __FILE__, __LINE__);
	}
	else
	{
		$results=q("SELECT * FROM shop_orders WHERE (shipping_type_id=1 OR shipping_type_id=2 OR shipping_type_id=5 OR shipping_type_id=7) AND shipping_number='';", $dbshop, __FILE__, __LINE__);
	}
	$i=0;
	while( $row=mysqli_fetch_array($results) )
	{
		//get receiver
		$receiver="";
		if( $row["bill_company"]!="" ) $receiver.=$row["bill_company"].'<br />';
		$receiver.=$row["bill_firstname"].' '.$row["bill_lastname"].'<br />';
		$receiver.=$row["bill_street"].' '.$row["bill_number"].'<br />';
		$receiver.=$row["bill_zip"].' '.$row["bill_city"].'<br />';
		$receiver.=$row["bill_country"];
		$receiver=highlightWords($receiver, $words);
		//get order items
		/*
		$orderitems="";
		$results2=q("SELECT * FROM shop_orders_items WHERE order_id=".$row["id_order"].";", $dbshop, __FILE__, __LINE__);
		while( $row2=mysqli_fetch_array($results2) )
		{
			$results3=q("SELECT * FROM shop_items_de WHERE id_item=".$row2["item_id"].";", $dbshop, __FILE__, __LINE__);
			$row3=mysqli_fetch_array($results3);
			$orderitems.=$row2["amount"].'x '.$row3["title"].'<br />';
		}
		$orderitems=highlightWords($orderitems, $words);
		*/
		//get shipping_number
		$shipping_number=highlightWords($row["shipping_number"], $words);
		
		if( $_POST["needle"]=="" or strpos($receiver, "highlight_word") !== false or strpos($orderitems, "highlight_word") !== false or strpos($shipping_number, "highlight_word") !== false )
		{
			$i++;
			$needle_id_order=$row["id_order"];
			echo '<tr>';
			//number
			echo '<td>'.$i.'</td>';
			//date
			echo '<td>'.date("d.m.Y H:i", $row["firstmod"]).'</td>';
			//Receiver
			echo '	<td>';
			echo '		<a href="javascript:show_order('.$row["id_order"].');">';
			echo $receiver;
			echo '		</a>';
			echo '</td>';
			//Order Items
			echo '	<td>';
			echo $orderitems;
			echo '	</td>';
			echo '	<td>'.substr($row["shipping_details"], 0, strpos($row["shipping_details"], ",")).'</td>';
			echo '	<td>'.$shipping_number.'</td>';
			echo '	<td>';
			if( $row["shipping_number"]!="" )
			{
				echo '		<img alt="Sendungsverfolgungsnummer an eBay schicken" src="'.PATH.'images/icons/24x24/up.png" style="cursor:pointer;" title="Sendungsverfolgungsnummer an eBay schicken" onclick="set_shipment_tracking_info('.$row["id_order"].');" />';
			}
			echo '		<img alt="Bestellung bearbeiten" src="'.PATH.'images/icons/24x24/edit.png" style="cursor:pointer;" title="Bestellung bearbeiten" onclick="show_order('.$row["id_order"].', 5);" />';
			echo '	</td>';
			echo '</tr>';
		}
	}
	echo '</table>';
	if($i!=1) $needle_id_order="";
	echo '<input id="needle_id_order" type="hidden" value="'.$needle_id_order.'" />';
?>