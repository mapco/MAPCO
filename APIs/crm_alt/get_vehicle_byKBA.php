<?php

	if ( !isset($_POST["HSN"]))
	{
		echo '<crm_get_vehicle_byKBAResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>VehicleID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine VehicleID angegeben werden, die ausgegeben werden soll.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_get_vehicle_byKBAResponse>'."\n";
		exit;
	}


	if ( !isset($_POST["TSN"]))
	{
		echo '<crm_get_vehicle_byKBAResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>CRM Customer ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein CRM Customer angegeben werden, dessen Fahrzeuge ausgegeben werden sollen.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_get_vehicle_byKBAResponse>'."\n";
		exit;
	}

	$xmldata='';
	$res_vehicle_data=q("SELECT * FROM vehicles_de WHERE KBA LIKE '%".$_POST["HSN"].$_POST["TSN"]."%';",  $dbshop, __FILE__, __LINE__);
	while ($row_vehicle_data=mysql_fetch_array($res_vehicle_data))
	{
		$xmldata.='<vehicle>';
		$xmldata.='<vehicleID><![CDATA['.$row_vehicle_data["id_vehicle"].']]></vehicleID>';
		$xmldata.='<vehicleBrand><![CDATA['.$row_vehicle_data["BEZ1"].']]></vehicleBrand>';
		$xmldata.='<vehicleModel><![CDATA['.$row_vehicle_data["BEZ2"].']]></vehicleModel>';
		$xmldata.='<vehicleModelType><![CDATA['.$row_vehicle_data["BEZ3"].']]></vehicleModelType>';
		$xmldata.='<vehicleBuiltFrom>'.$row_vehicle_data["BJvon"].'</vehicleBuiltFrom>';
		$xmldata.='<vehicleBuiltTo>'.$row_vehicle_data["BJbis"].'</vehicleBuiltTo>';
		$xmldata.='<vehicleCcmTech>'.$row_vehicle_data["ccmTech"].'</vehicleCcmTech>';
		$xmldata.='<vehicleKW>'.$row_vehicle_data["kW"].'</vehicleKW>';
		$xmldata.='<vehiclePS>'.$row_vehicle_data["PS"].'</vehiclePS>';
		$xmldata.='</vehicle>';
	}
	
	

	echo "<crm_get_vehicle_byKBAResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "<vehicles>".$xmldata."</vehicles>\n";
	echo "</crm_get_vehicle_byKBAResponse>";

?>