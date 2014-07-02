<?php

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
		if (mysql_num_rows($res_check)==1)
		{
			//UPDATE VERKNÜPFUNG
			q("UPDATE shop_orders_items SET customer_vehicle_id = ".$_POST["customerVehicleID"]." WHERE id = ".$_POST["OrderItemID"].";", $dbshop, __FILE__, __LINE__);
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
		if (mysql_num_rows($res_check)>0)
		{
			//UPDATE VERKNÜPFUNG
			q("UPDATE shop_orders_items SET customer_vehicle_id = ".$_POST["customerVehicleID"]." WHERE order_id = ".$_POST["OrderID"].";", $dbshop, __FILE__, __LINE__);
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