<?php

	$KBA=$_POST["HSN"].$_POST["TSN"];


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
		$res_vehicle=q("SELECT * FROM shop_carfleet WHERE id = ".$_POST["customerVehicleID"].";", $dbshop, __FILE__, __LINE__);
		
		if (mysqli_num_rows($res_vehicle)==0)
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
	
		$customerVehicleID=$_POST["customerVehicleID"];
	
		q("UPDATE shop_carfleet SET vehicle_id = ".$_POST["vehicleID"].",
								    kbanr = '".$KBA."',
								    date_built = ".$_POST["dateBuilt"].",
								    FIN  = '".$_POST["FIN"]."',
									c0003  = '".$_POST["c0003"]."',
									c0004  = '".$_POST["c0004"]."',
									c0005  = '".$_POST["c0005"]."',
									c0006  = '".$_POST["c0006"]."',
									s0033  = '".$_POST["s0033"]."',
									s0038  = '".$_POST["s0038"]."',
									s0040  = '".$_POST["s0040"]."',
									s0067  = '".$_POST["s0067"]."',
									s0072  = '".$_POST["s0072"]."',
									s0112  = '".$_POST["s0112"]."',
									s0139  = '".$_POST["s0139"]."',
									s0233  = '".$_POST["s0233"]."',
									s0514  = '".$_POST["s0514"]."',
									s0564  = '".$_POST["s0564"]."',
									s0567  = '".$_POST["s0567"]."',
									s0608  = '".$_POST["s0608"]."',
									s0649  = '".$_POST["s0649"]."',
									s1197  = '".$_POST["s1197"]."',
								    additional = '".$_POST["additional"]."',
								    lastmod = ".time().",
								    lastmod_user = ".$_SESSION["id_user"]." WHERE id = ".$_POST["customerVehicleID"].";" , $dbshop, __FILE__, __LINE__);
		
	}

	if ($_POST["mode"]=="add")
	{
		q("INSERT INTO shop_carfleet (vehicle_id,
									  shop_id,
									  user_id,
									  kbanr,
									  date_built,
									  FIN,
									  c0003,
									  c0004,
									  c0005,
									  c0006,
									  s0033,
									  s0038,
									  s0040,
									  s0067,
									  s0072,
									  s0112,
									  s0139,
									  s0233,
									  s0514,
									  s0564,
									  s0567,
									  s0608,
									  s0649,
									  s1197,
									  additional,
									  active,
									  firstmod,
									  firstmod_user,
									  lastmod,
									  lastmod_user) VALUES (".$_POST["vehicleID"].",
									  						".$_SESSION["id_shop"].",
									  					    ".$_POST["customer_id"].",
														    '".$KBA."',
														    ".$_POST["dateBuilt"].",
														    '".$_POST["FIN"]."',
															'".$_POST["c0003"]."',
															'".$_POST["c0004"]."',
															'".$_POST["c0005"]."',
															'".$_POST["c0006"]."',
															'".$_POST["s0033"]."',
															'".$_POST["s0038"]."',
															'".$_POST["s0040"]."',
															'".$_POST["s0067"]."',
															'".$_POST["s0072"]."',
															'".$_POST["s0112"]."',
															'".$_POST["s0139"]."',
															'".$_POST["s0233"]."',
															'".$_POST["s0514"]."',
															'".$_POST["s0564"]."',
															'".$_POST["s0567"]."',
															'".$_POST["s0608"]."',
															'".$_POST["s0649"]."',
															'".$_POST["s1197"]."',
														    '".$_POST["additional"]."',
														    1,
														    ".time().",
														    ".$_SESSION["id_user"].",
														    ".time().",
														    ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
		
		$customerVehicleID=mysqli_insert_id($dbshop);
	}
	
	echo "<crm_update_orderItem_vehicleResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "<customerVehicleID>".$customerVehicleID."</customerVehicleID>\n";
	echo "</crm_update_orderItem_vehicleResponse>";

?>