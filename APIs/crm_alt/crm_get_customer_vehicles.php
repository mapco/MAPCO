<?php

	if ( !isset($_POST["mode"]) )
	{
		echo '<crm_get_customer_vehiclesResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Ausgabemodus nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Ausgabemodus (customer/vehicle)angegeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_get_customer_vehiclesResponse>'."\n";
		exit;
	}
	
	if ( !isset($_POST["vehicle_customer_id"]) && $_POST["mode"]=="vehicle")
	{
		echo '<crm_get_customer_vehiclesResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>VehicleID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine VehicleID angegeben werden, die ausgegeben werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_get_customer_vehiclesResponse>'."\n";
		exit;
	}


	if ( !isset($_POST["crm_customer_id"]) && $_POST["mode"]=="customer")
	{
		echo '<crm_get_customer_vehiclesResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>CRM Customer ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein CRM Customer angegeben werden, dessen Fahrzeuge ausgegeben werden sollen.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_get_customer_vehiclesResponse>'."\n";
		exit;
	}

	$xmldata='';
	if ($_POST["mode"]=="vehicle")
	{
		$res_vehicles=q("SELECT * FROM crm_vehicles WHERE id_customer_vehicle = ".$_POST["vehicle_customer_id"].";", $dbweb, __FILE__, __LINE__);
	}
	if ($_POST["mode"]=="customer")
	{
		$res_vehicles=q("SELECT * FROM crm_vehicles WHERE crm_customer_id = ".$_POST["crm_customer_id"].";", $dbweb, __FILE__, __LINE__);
	}

	while ($row_vehicles=mysql_fetch_array($res_vehicles))
	{
		$xmldata.='<vehicle>';
		$xmldata.='<vehicleID>'.$row_vehicles["vehicle_id"].'</vehicleID>';
		$xmldata.='<vehicleCustomerID>'.$row_vehicles["id_customer_vehicle"].'</vehicleCustomerID>';
		$xmldata.='<vehicleHSN>'.$row_vehicles["HSN"].'</vehicleHSN>';
		$xmldata.='<vehicleTSN>'.$row_vehicles["TSN"].'</vehicleTSN>';
		$xmldata.='<vehicleDateBuilt>'.$row_vehicles["DateBuilt"].'</vehicleDateBuilt>';
		$xmldata.='<vehicleFIN>'.$row_vehicles["FIN"].'</vehicleFIN>';
		$xmldata.='<vehicleAdditional>'.$row_vehicles["additional"].'</vehicleAdditional>';
		
		$res_vehicle_data=q("SELECT * FROM vehicles_de WHERE id_vehicle = ".$row_vehicles["vehicle_id"].";",  $dbshop, __FILE__, __LINE__);
		while ($row_vehicle_data=mysql_fetch_array($res_vehicle_data))
		{
			$xmldata.='<vehicleBrand><![CDATA['.$row_vehicle_data["BEZ1"].']]></vehicleBrand>';
			$xmldata.='<vehicleModel><![CDATA['.$row_vehicle_data["BEZ2"].']]></vehicleModel>';
			$xmldata.='<vehicleModelType><![CDATA['.$row_vehicle_data["BEZ3"].']]></vehicleModelType>';
			$xmldata.='<vehicleBuiltFrom>'.$row_vehicle_data["BJvon"].'</vehicleBuiltFrom>';
			$xmldata.='<vehicleBuiltTo>'.$row_vehicle_data["BJbis"].'</vehicleBuiltTo>';
			$xmldata.='<vehicleCcmTech>'.$row_vehicle_data["ccmTech"].'</vehicleCcmTech>';
			$xmldata.='<vehicleKW>'.$row_vehicle_data["kW"].'</vehicleKW>';
			$xmldata.='<vehiclePS>'.$row_vehicle_data["PS"].'</vehiclePS>';
		}
		
		
		$xmldata.='</vehicle>';
		
	}
	
	echo "<crm_get_customer_vehiclesResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "<vehicles>".$xmldata."</vehicles>\n";
	echo "</crm_get_customer_vehiclesResponse>";

?>