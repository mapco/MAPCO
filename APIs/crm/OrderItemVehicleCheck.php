<?php

	if ( !isset($_POST["id_orderitem"]) )
	{
		echo '<OrderItemVehicleCheck>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Shopartikel nicht angegeben.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Shopartikel (id_orderitem) angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</OrderItemVehicleCheck>'."\n";
		exit;
	}
	$results=q("SELECT * FROM shop_orders_items WHERE id=".$_POST["id_orderitem"].";", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<OrderItemVehicleCheck>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Kundenfahrzeug nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Das angegebenen Kundenfahrzeug (id_carfleet) konnte nicht gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</OrderItemVehicleCheck>'."\n";
		exit;
	}
	$shop_orders_items=mysqli_fetch_array($results);

	if ( !isset($_POST["id_carfleet"]) )
	{
		echo '<OrderItemVehicleCheck>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Kundenfahrzeug nicht angegeben.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Kundenfahrzeug (id_carfleet) angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</OrderItemVehicleCheck>'."\n";
		exit;
	}
	$results=q("SELECT * FROM shop_carfleet WHERE id=".$_POST["id_carfleet"].";", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo '<OrderItemVehicleCheck>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Kundenfahrzeug nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Das angegebenen Kundenfahrzeug (id_carfleet) konnte nicht gefunden werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</OrderItemVehicleCheck>'."\n";
		exit;
	}
	$shop_carfleet=mysqli_fetch_array($results);

	$results=q("SELECT * FROM shop_items_vehicles WHERE language_id=1 AND item_id=".$shop_orders_items["item_id"]." AND vehicle_id=".$shop_carfleet["vehicle_id"].";", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 )
	{
		echo "<OrderItemVehicleCheck>\n";
		echo "	<Ack>Failure</Ack>\n";
		echo "</OrderItemVehicleCheck>";
		exit;
	}
	$shop_items_vehicles=mysqli_fetch_array($results);
	
	echo "<OrderItemVehicleCheck>\n";
	echo "	<Ack>Success</Ack>\n";
	echo "	<Restrictions>".$shop_items_vehicles["criteria"]."</Restrictions>\n";
	echo "</OrderItemVehicleCheck>";

?>