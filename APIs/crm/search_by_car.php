<?php

	include("../functions/mapco_baujahr.php");
	
	$xmldata="";
	
	if ( !isset($_POST["mode"]) )
	{
		echo '<search_by_carResponse>'."\n";
		echo '	<Ack>Failure</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Datensuchmodus nicht gefunden</shortMsg>'."\n";
		echo '		<longMsg>Es muss ein Modus angegeben werden, nach dem Daten zu Fahrzeugen gesucht und ausgegeben werden sollen.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</search_by_carResponse>'."\n";
		exit;
	}

	if ($_POST["mode"]=="manufacturer")
	{
		$res=q("SELECT * FROM vehicles_de WHERE Exclude=0 GROUP BY KHerNr ORDER BY BEZ1;", $dbshop, __FILE__, __LINE__);
		while($row=mysqli_fetch_array($res))
		{
			$xmldata.='<manufacturer>'."\n";
			$xmldata.='	<KHerNr><![CDATA['.$row["KHerNr"].']]></KHerNr>'."\n";
			$xmldata.='	<Name><![CDATA['.$row["BEZ1"].']]></Name>'."\n";
			$xmldata.='</manufacturer>'."\n";
		}
			
	}
	
	if ($_POST["mode"]=="modell")
	{
		
		if ( !isset($_POST["KHerNr"]) )
		{
			echo '<search_by_carResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>KHerNr nicht gefunden</shortMsg>'."\n";
			echo '		<longMsg>Es muss eine Herstellernummer angegeben werden, nach dem Daten zu Fahrzeugen gesucht und ausgegeben werden sollen.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</search_by_carResponse>'."\n";
			exit;
		}
		
		$res=q("SELECT KModNr, BEZ2 FROM vehicles_de WHERE Exclude=0 AND KHerNr='".$_POST["KHerNr"]."' GROUP BY KModNr ORDER BY BEZ2;", $dbshop, __FILE__, __LINE__);
		
		if (mysqli_num_rows($res)==0)
		{
			echo '<search_by_carResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Keine Modelle zum Hersteller gefunden</shortMsg>'."\n";
			echo '		<longMsg>Zur angegebenen Herstellernummer konnten keine Fahrzeugmodelle gefunden werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</search_by_carResponse>'."\n";
			exit;
		}
		
		while($row=mysqli_fetch_array($res))
		{
			$xmldata.='<modell>'."\n";
			$xmldata.='	<KModNr><![CDATA['.$row["KModNr"].']]></KModNr>'."\n";
			$xmldata.='	<Name><![CDATA['.$row["BEZ2"].']]></Name>'."\n";
			$xmldata.='</modell>'."\n";
		}
			
	}

	if ($_POST["mode"]=="type")
	{
		
		if ( !isset($_POST["KModNr"]) )
		{
			echo '<search_by_carResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>KModNr nicht gefunden</shortMsg>'."\n";
			echo '		<longMsg>Es muss eine Modellnummer angegeben werden, nach dem Daten zu Fahrzeugen gesucht und ausgegeben werden sollen.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</search_by_carResponse>'."\n";
			exit;
		}
		
		$res=q("SELECT * FROM vehicles_de WHERE Exclude=0 AND KModNr=".$_POST["KModNr"]." ORDER BY BEZ3;", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res)==0)
		{
			echo '<search_by_carResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Keine Fahrzeugtypen zum Modell gefunden</shortMsg>'."\n";
			echo '		<longMsg>Zur angegebenen Modellnummer konnten keine Modelltypen gefunden werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</search_by_carResponse>'."\n";
			exit;
		}
		$max=0;
		while($row=mysqli_fetch_array($res))
		{
			$fid[]=$row["id_vehicle"];
			$bez=str_replace("  ", " ", trim($row["BEZ3"]));
			$bez3[]=$bez;
			$bjvon[]=$row["BJvon"];
			$bjbis[]=$row["BJbis"];
			$leistungkw[]=number_format($row["kW"]).'KW';
			$leistungps[]=number_format($row["PS"]).'PS';
			if ($max<strlen($bez)) $max=strlen($bez);
			
		}
		$max+=3;
		for($i=0; $i<sizeof($bez3); $i++)
		{
			//if ($_POST["id_vehicle"]==$fid[$i]) $selected=' selected="selected"'; else $selected='';
			//echo '<option'.$selected.' value="'.$fid[$i].'">';
			$val='';
			$val=$bez3[$i];
			for($j=0; $j<($max-strlen($bez3[$i])); $j++) $val.= '&nbsp;';
			$val.= baujahr($bjvon[$i]).' - '.baujahr($bjbis[$i]).'&nbsp;&nbsp;&nbsp;';
			$val.=$leistungkw[$i].' ('.$leistungps[$i].')';
			
			$xmldata.='<type>'."\n";
			$xmldata.='	<id_vehicle><![CDATA['.$fid[$i].']]></id_vehicle>'."\n";
			$xmldata.='	<Name><![CDATA['.$val.']]></Name>'."\n";
			$xmldata.='</type>'."\n";
		}			
	}

	if ($_POST["mode"]=="car")
	{
		if ( !isset($_POST["id_vehicle"]) )
		{
			echo '<search_by_carResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Vehicle_ID nicht gefunden</shortMsg>'."\n";
			echo '		<longMsg>Es muss eine FahrzeugID (id_vehicle.vehicles_de) angegeben werden, nach dem Daten zu Fahrzeugen gesucht und ausgegeben werden sollen.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</search_by_carResponse>'."\n";
			exit;
		}
		
		$res_vehicle_data=q("SELECT * FROM vehicles_de WHERE id_vehicle = ".$_POST["id_vehicle"].";",  $dbshop, __FILE__, __LINE__);

		if (mysqli_num_rows($res_vehicle_data)==0)
		{
			echo '<search_by_carResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Kein Fahrzeug zur VehicleID gefunden</shortMsg>'."\n";
			echo '		<longMsg>Zur angegebenen VehicleID (id_vehicle.vehicles_de) konnte kein Fahrzeug gefunden werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</search_by_carResponse>'."\n";
			exit;
		}

		while ($row_vehicle_data=mysqli_fetch_array($res_vehicle_data))
		{
			$xmldata.='<vehicle>';
			$xmldata.='<vehicleID><![CDATA['.$row_vehicle_data["id_vehicle"].']]></vehicleID>';
			$xmldata.='<vehicleKBA><![CDATA['.$row_vehicle_data["KBA"].']]></vehicleKBA>';
			$xmldata.='<vehicleBrand><![CDATA['.$row_vehicle_data["BEZ1"].']]></vehicleBrand>';
			$xmldata.='<vehicleModel><![CDATA['.$row_vehicle_data["BEZ2"].']]></vehicleModel>';
			$xmldata.='<vehicleModelType><![CDATA['.$row_vehicle_data["BEZ3"].']]></vehicleModelType>';
			$xmldata.='<vehicleBuiltFrom><![CDATA['.$row_vehicle_data["BJvon"].']]></vehicleBuiltFrom>';
			$xmldata.='<vehicleBuiltTo><![CDATA['.$row_vehicle_data["BJbis"].']]></vehicleBuiltTo>';
			$xmldata.='<vehicleCcmTech><![CDATA['.$row_vehicle_data["ccmTech"].']]></vehicleCcmTech>';
			$xmldata.='<vehicleKW><![CDATA['.$row_vehicle_data["kW"].']]></vehicleKW>';
			$xmldata.='<vehiclePS><![CDATA['.$row_vehicle_data["PS"].']]></vehiclePS>';
			$xmldata.='</vehicle>';
		}
	}

	
	echo "<search_by_carResponse>\n";
	echo "<Ack>Success</Ack>\n";
	echo "<ResultList>\n";
	echo $xmldata;
	echo "</ResultList>\n";
	echo "</search_by_carResponse>";

?>