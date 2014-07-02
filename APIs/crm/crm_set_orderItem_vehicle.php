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


	if ( !isset($_POST["mode"]) )
	{
		echo '<crm_set_orderItem_vehicleResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bearbeitungsmodus nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Bearbeitungsmodus zum Kundenfahrzeugverknüpfung angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_set_orderItem_vehicleResponse>'."\n";
		exit;
	}

	if ( !isset($_POST["customerVehicleID"]))
	{
		echo '<crm_set_orderItem_vehicleResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>CustomerVehicleID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine ID des zu verknüpfenden Kundenfahzeuges angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_set_orderItem_vehicleResponse>'."\n";
		exit;
	}

	if ($_POST["mode"]=="all" &&  !isset($_POST["OrderID"]))
	{
		echo '<crm_set_orderItem_vehicleResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Order nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine OrderID der zu verknüpfenden Artikel angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_set_orderItem_vehicleResponse>'."\n";
		exit;
	}
	

	if ( $_POST["mode"]=="selected" && !isset($_POST["OrderItemID"]) )
	{
		echo '<crm_set_orderItem_vehicleResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>OrderItem nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Bestellposition zum zu verknüpfenden Kundenfahzeug angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_set_orderItem_vehicleResponse>'."\n";
		exit;
	}

	if ($_POST["mode"]=="selected")
	{
		//Prüfe OrderItem
		$res_check=q("SELECT * FROM shop_orders_items WHERE id = ".$_POST["OrderItemID"].";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_check)==1)
		{
			$row_check=mysqli_fetch_array($res_check);
			//UPDATE VERKNÜPFUNG
			q("UPDATE shop_orders_items SET customer_vehicle_id = ".$_POST["customerVehicleID"]." WHERE id = ".$_POST["OrderItemID"].";", $dbshop, __FILE__, __LINE__);
			
			$data=array();
			$data["customer_vehicle_id"]=$_POST["customerVehicleID"];
			$data["SELECTOR_id"]=$_POST["OrderItemID"];
			
			$event_id=save_order_event(19, $row_check["order_id"], $data);
			
		}
		else
		{
			echo '<crm_set_orderItem_vehicleResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>OrderItem nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Es konnte keine Bestellposition zur OrderItemID gefunden werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</crm_set_orderItem_vehicleResponse>'."\n";
			exit;
		}
	}

	if ($_POST["mode"]=="all")
	{
		//prüfe auf Items zur Order
		$res_check=q("SELECT * FROM shop_orders_items WHERE order_id = ".$_POST["OrderID"].";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_check)>0)
		{
			//UPDATE VERKNÜPFUNG
			q("UPDATE shop_orders_items SET customer_vehicle_id = ".$_POST["customerVehicleID"]." WHERE order_id = ".$_POST["OrderID"].";", $dbshop, __FILE__, __LINE__);
			
			$data=array();
			$data["customer_vehicle_id"]=$_POST["customerVehicleID"];
			$data["SELECTOR_order_id"]=$_POST["OrderID"];
			
			$event_id=save_order_event(20, $_POST["OrderID"], $data);

		}
		else
		{
			echo '<crm_set_orderItem_vehicleResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>OrderItems nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Es konnte keine Bestellposition zur Order gefunden werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</crm_set_orderItem_vehicleResponse>'."\n";
			exit;
		}
	}


	echo "<crm_set_orderItem_vehicleResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "</crm_set_orderItem_vehicleResponse>";



?>