<?php

	if ( !isset($_POST["mode"]) )
	{
		echo '<crm_update_orderItem_vehicleResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Bearbeitungsmodus nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Bearbeitungsmodus zum Kundenfahrzeug angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_update_orderItem_vehicleResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["vehicleID"]))
	{
		echo '<crm_update_orderItem_vehicleResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>VehicleID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine ID des zu bearbeitenden Fahrzeuges angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_update_orderItem_vehicleResponse>'."\n";
		exit;
	}


	if ( !isset($_POST["customerVehicleID"]) && $_POST["mode"]=="update")
	{
		echo '<crm_update_orderItem_vehicleResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>CustomerVehicleID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine ID des zu bearbeitenden Kundenfahzeuges angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_update_orderItem_vehicleResponse>'."\n";
		exit;
	}

	if ($_POST["mode"]=="update")
	{
		$res_vehicle=q("SELECT * FROM crm_vehicles WHERE id_customer_vehicle = ".$_POST["customerVehicleID"].";", $dbweb, __FILE__, __LINE__);
		
		if (mysql_num_rows($res_vehicle)==0)
		{
			echo '<crm_update_orderItem_vehicleResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Vehicle nicht gefunden.</shortMsg>'."\n";
			echo '		<longMsg>Es konnte kein Fahrzeug zur angegebenen ID gefunden werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</crm_update_orderItem_vehicleResponse>'."\n";
			exit;
		}
	
		q("UPDATE crm_vehicles SET vehicle_id = ".$_POST["vehicleID"].", HSN = '".$_POST["HSN"]."', TSN = '".$_POST["TSN"]."', DateBuilt = ".$_POST["dateBuilt"].", FIN  = '".$_POST["FIN"]."', additional = '".$_POST["additional"]."', lastmod = ".time().", lastmod_user = ".$_SESSION["id_user"]." WHERE id_customer_vehicle = ".$_POST["customerVehicleID"].";" , $dbweb, __FILE__, __LINE__);

	$customerVehicleID=$_POST["customerVehicleID"];
	}

	if ($_POST["mode"]=="add")
	{
		q("INSERT INTO crm_vehicles (vehicle_id, crm_customer_id, HSN, TSN, DateBuilt, FIN, additional, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$_POST["vehicleID"].", ".$_POST["crm_customer_id"].", '".$_POST["HSN"]."', '".$_POST["TSN"]."', DateBuilt = ".$_POST["dateBuilt"].", '".$_POST["FIN"]."', '".$_POST["additional"]."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
		$customerVehicleID=mysql_insert_id($dbweb);
		
	}
	
	echo "<crm_update_orderItem_vehicleResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "<customerVehicleID>".$customerVehicleID."</customerVehicleID>";
	echo "</crm_update_orderItem_vehicleResponse>";

?>