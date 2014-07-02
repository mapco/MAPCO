<?php

	if ( !isset($_POST["vehicle_ID"]))
	{
		echo '<crm_get_vehicle_byIDResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Fahrzeug ID nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine Fahrzeug ID uebergeben werden.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</crm_get_vehicle_byIDResponse>'."\n";
		exit;
	}

	$xmldata='';
	$res_vehicle_data=q("SELECT * FROM vehicles_de WHERE id_vehicle LIKE '".$_POST["vehicle_ID"]."';",  $dbshop, __FILE__, __LINE__);
	while ($row_vehicle_data=mysqli_fetch_array($res_vehicle_data))
	{
		$xmldata.='<vehicle>';
		$xmldata.='<vehicleKBA><![CDATA['.$row_vehicle_data["KBA"].']]></vehicleKBA>';
		$xmldata.='<vehicleKHerNr>'.$row_vehicle_data["KHerNr"].'</vehicleKHerNr>';
		$xmldata.='<vehicleBrand><![CDATA['.$row_vehicle_data["BEZ1"].']]></vehicleBrand>';
		$xmldata.='<vehicleModel><![CDATA['.$row_vehicle_data["BEZ2"].']]></vehicleModel>';
		$xmldata.='<vehicleModelType><![CDATA['.$row_vehicle_data["BEZ3"].']]></vehicleModelType>';
		$xmldata.='<vehicleKModNr>'.$row_vehicle_data["KModNr"].'</vehicleKModNr>';
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