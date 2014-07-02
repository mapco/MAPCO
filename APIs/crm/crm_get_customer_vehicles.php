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


	if ( !isset($_POST["customer_id"]) && $_POST["mode"]=="customer")
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
		$res_vehicles=q("SELECT * FROM shop_carfleet WHERE id = ".$_POST["vehicle_customer_id"].";", $dbshop, __FILE__, __LINE__);
	}
	if ($_POST["mode"]=="customer")
	{
		$res_vehicles=q("SELECT * FROM shop_carfleet WHERE user_id = ".$_POST["customer_id"].";", $dbshop, __FILE__, __LINE__);
	}

	while ($row_vehicles=mysqli_fetch_array($res_vehicles))
	{
		$xmldata.='<vehicle>'."\n";
		$xmldata.='<vehicleID>'.$row_vehicles["vehicle_id"].'</vehicleID>'."\n";
		$xmldata.='<vehicleCustomerID>'.$row_vehicles["id"].'</vehicleCustomerID>'."\n";
		$xmldata.='<vehicleKBA>'.$row_vehicles["kbanr"].'</vehicleKBA>'."\n";
		$xmldata.='<vehicleDateBuilt>'.$row_vehicles["date_built"].'</vehicleDateBuilt>'."\n";
		$xmldata.='<vehicleFIN>'.$row_vehicles["FIN"].'</vehicleFIN>'."\n";
		$xmldata.='<vehiclec0003>'.$row_vehicles["c0003"].'</vehiclec0003>'."\n";
		$xmldata.='<vehiclec0004>'.$row_vehicles["c0004"].'</vehiclec0004>'."\n";
		$xmldata.='<vehiclec0005>'.$row_vehicles["c0005"].'</vehiclec0005>'."\n";
		$xmldata.='<vehiclec0006>'.$row_vehicles["c0006"].'</vehiclec0006>'."\n";
		$xmldata.='<vehicles0033>'.$row_vehicles["s0033"].'</vehicles0033>'."\n";
		$xmldata.='<vehicles0038>'.$row_vehicles["s0038"].'</vehicles0038>'."\n";
		$xmldata.='<vehicles0040>'.$row_vehicles["s0040"].'</vehicles0040>'."\n";
		$xmldata.='<vehicles0067>'.$row_vehicles["s0067"].'</vehicles0067>'."\n";
		$xmldata.='<vehicles0072>'.$row_vehicles["s0072"].'</vehicles0072>'."\n";
		$xmldata.='<vehicles0112>'.$row_vehicles["s0112"].'</vehicles0112>'."\n";
		$xmldata.='<vehicles0139>'.$row_vehicles["s0139"].'</vehicles0139>'."\n";
		$xmldata.='<vehicles0233>'.$row_vehicles["s0233"].'</vehicles0233>'."\n";
		$xmldata.='<vehicles0514>'.$row_vehicles["s0514"].'</vehicles0514>'."\n";
		$xmldata.='<vehicles0564>'.$row_vehicles["s0564"].'</vehicles0564>'."\n";
		$xmldata.='<vehicles0567>'.$row_vehicles["s0567"].'</vehicles0567>'."\n";
		$xmldata.='<vehicles0608>'.$row_vehicles["s0608"].'</vehicles0608>'."\n";
		$xmldata.='<vehicles0649>'.$row_vehicles["s0649"].'</vehicles0649>'."\n";
		$xmldata.='<vehicles1197>'.$row_vehicles["s1197"].'</vehicles1197>'."\n";
		$xmldata.='<vehicleAdditional>'.$row_vehicles["additional"].'</vehicleAdditional>'."\n";
		$xmldata.='<vehicleActive>'.$row_vehicles["active"].'</vehicleActive>'."\n";
		$xmldata.='<vehicleFirstmod><![CDATA['.$row_vehicles["firstmod"].']]></vehicleFirstmod>'."\n";
		
		$res_vehicle_data=q("SELECT * FROM vehicles_de WHERE id_vehicle = ".$row_vehicles["vehicle_id"].";",  $dbshop, __FILE__, __LINE__);
		while ($row_vehicle_data=mysqli_fetch_array($res_vehicle_data))
		{
			$xmldata.='<vehicleBrand><![CDATA['.$row_vehicle_data["BEZ1"].']]></vehicleBrand>'."\n";
			$xmldata.='<vehicleModel><![CDATA['.$row_vehicle_data["BEZ2"].']]></vehicleModel>'."\n";
			$xmldata.='<vehicleModelType><![CDATA['.$row_vehicle_data["BEZ3"].']]></vehicleModelType>'."\n";
			$xmldata.='<vehicleBuiltFrom>'.$row_vehicle_data["BJvon"].'</vehicleBuiltFrom>'."\n";
			$xmldata.='<vehicleBuiltTo>'.$row_vehicle_data["BJbis"].'</vehicleBuiltTo>'."\n";
			$xmldata.='<vehicleCcmTech>'.$row_vehicle_data["ccmTech"].'</vehicleCcmTech>'."\n";
			$xmldata.='<vehicleKW>'.$row_vehicle_data["kW"].'</vehicleKW>'."\n";
			$xmldata.='<vehiclePS>'.$row_vehicle_data["PS"].'</vehiclePS>'."\n";
		}
		
		
		$xmldata.='</vehicle>'."\n";
		
	}
	
	echo "<crm_get_customer_vehiclesResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "<vehicles>".$xmldata."</vehicles>\n";
	echo "</crm_get_customer_vehiclesResponse>";

?>