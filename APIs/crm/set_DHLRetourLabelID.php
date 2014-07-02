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
			'".mysqli_real_escape_string($dbshop, $xml)."',
			".time().",
			".$_SESSION["id_user"]."
		);", $dbshop, __FILE__, __LINE__);
		
		return mysqli_insert_id($dbshop);
		
	}


	if ( !isset($_POST["OrderID"]))
	{
		echo '<crm_set_DHLRetourLabelResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>OrderID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine OrderID (shop_orders.id_order) angegeben werden, zu der die Retourlabel ID gespeichert werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_set_DHLRetourLabelResponse>'."\n";
		exit;
	}
	
	$res_order=q("SELECT * FROM shop_orders WHERE id_order = ".$_POST["OrderID"]."; ", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_order)==0)
	{
		echo '<crm_set_DHLRetourLabelResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Shop Order nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Zur angegebenen OrderID konnte keine Bestellung gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_set_DHLRetourLabelResponse>'."\n";
		exit;
	}


	if ( !isset($_POST["LabelID"]))
	{
		echo '<crm_set_DHLRetourLabelResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>RetourlabelID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine RetourlabelID angegeben werden, die gespeichert werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_set_DHLRetourLabelResponse>'."\n";
		exit;
	}

	q("UPDATE shop_orders SET RetourLabelID = '".$_POST["LabelID"]."', RetourLabelTimestamp = ".time()."  WHERE id_order = ".$_POST["OrderID"]."; ", $dbshop, __FILE__, __LINE__);

	$data=array();
	$data["RetourLabelID"]=$_POST["LabelID"];
	$data["RetourLabelTimestamp"]=time();
	$data["SELECTOR_id_order"]=$_POST["OrderID"];

	//SAVE ORDEREVENT
	$id_event=save_order_event(14, $_POST["OrderID"], $data);

	echo "<crm_set_DHLRetourLabelResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "</crm_set_DHLRetourLabelResponse>";


?>