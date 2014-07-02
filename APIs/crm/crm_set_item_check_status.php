<?php

	function save_order_event($eventtype_id, $order_id, $data)
	{
		global $dbshop;
		//CREATE XML FROM DATA
		$xml='<data>';
		foreach ($data as $key => $val)
		{
			$xml.='<'.$key.'>';
			if (!is_numeric($val)) $xml.='<![CDATA['.$val.']]>'; else $xml.=$val;
			$xml.='</'.$key.'>';
			
		}
		$xml.='</data>';
		
		//SAVE EVENT
		q("INSERT INTO shop_orders_events (
			order_id, 
			eventtype_id, 
			data, 
			firstmod, 
			firstmod_user
		) VALUES (
			".$order_id.",
			".$eventtype_id.",
			'".mysqli_real_escape_string($dbshop,$xml)."',
			".time().",
			".$_SESSION["id_user"]."
		);", $dbshop, __FILE__, __LINE__);
		
		return mysqli_insert_id($dbshop);
		
	}


	if ( !isset($_POST["OrderItemID"]) )
	{
		echo '<crm_set_item_check_status>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>OrderItemID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine OrderItemID (id.shop_orders_items) angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_set_item_check_status>'."\n";
		exit;
	}

	//GET ORDERdata
	$res_order_item=q("SELECT * FROM shop_orders_items WHERE id = ".$_POST["OrderItemID"].";", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_order_item)==0)
	{
		echo '<crm_set_item_check_status>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>OrderItem nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zur OrderItemID konnte keine Bestellposition gefunden werden</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_set_item_check_status>'."\n";
		exit;
	}
	$order_item=mysqli_fetch_array($res_order_item);
	
	if ( !isset($_POST["status"]) )
	{
		echo '<crm_get_order_detailResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Status nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Status zur Bestellposition angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_get_order_detailResponse>'."\n";
		exit;
	}
	
	q("UPDATE shop_orders_items SET checked = ".$_POST["status"].", ckecked_by_user = ".$_SESSION["id_user"]." WHERE id = ".$_POST["OrderItemID"].";", $dbshop, __FILE__, __LINE__);
	
	$data=array();
	$data["checked"]=$_POST["status"];
	$data["ckecked_by_user"]=$_SESSION["id_user"];
	$data["SELECTOR_id"]=$_POST["OrderItemID"];
	

	//SET ORDEREVENT
	$id_event=save_order_event(15, $order_item["order_id"], $data);

	

echo "<crm_set_item_check_status>\n";
echo "<Ack>Success</Ack>\n";
echo "<OrderID>".$order_item["order_id"]."</OrderID>\n";
echo "</crm_set_item_check_status>";



?>