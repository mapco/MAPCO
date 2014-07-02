<?php

	//CHECK, OB FREITEXTSUCHE
	$qry_string = array();
	if (!$_POST["qry_string"]=="") {
		$qry_string=explode(' ', $_POST["qry_string"]);
		$qry_string_size=sizeof($qry_string);
		for ($i=0; $i<$qry_string_size; $i++)
		{
			$qry_string[$i]=strtolower(trim($qry_string[$i]));
		}
		$freitextsuche=true;
	}
	else {$freitextsuche=false;}
		
	//LISTE ALLER CMR_CUSTOMERS LADEN
	$res=q("SELECT * FROM crm_customers limit 20000", $dbweb, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($res))
	{
		$crm_customer[$row["id_crm_customer"]]=true;

		// CUSTOMER-Bezeichnung
		$name=$row["company"];
		if ($name=="") $name=$row["name"]; else $name.=' '.$row["name"];
		$crm_customer_name[$row["id_crm_customer"]]=$name;
			$data[$row["id_crm_customer"]]=$crm_customer_name[$row["id_crm_customer"]];

		$crm_customer_street1[$row["id_crm_customer"]]=$row["street1"];
			$data[$row["id_crm_customer"]].=' '.$row["street1"];
		$crm_customer_street2[$row["id_crm_customer"]]=$row["street2"];
			$data[$row["id_crm_customer"]].=' '.$row["street2"];
		// für ZIP-UMKREISSUCHE
		$crm_customer_zip[$row["id_crm_customer"]]=$row["zip"];
			$data[$row["id_crm_customer"]].=' '.$row["zip"];
			
		$crm_customer_city[$row["id_crm_customer"]]=$row["city"];
			$data[$row["id_crm_customer"]].=' '.$row["city"];
		$crm_customer_country[$row["id_crm_customer"]]=$row["country"];
			$data[$row["id_crm_customer"]].=' '.$row["country"];
		$crm_customer_phone[$row["id_crm_customer"]]=$row["phone"];
			$data[$row["id_crm_customer"]].=' '.$row["phone"];
			// Störzeichen entfernen
			$number=str_replace("/", "", $row["phone"]);			
			$number=str_replace(" ", "", $number);
			$number=str_replace("-", "", $number);
			$number=str_replace(".", "", $number);
			$number=str_replace("+", "", $number);
			$data[$row["id_crm_customer"]].=' '.$number;
			
		$crm_customer_mobile[$row["id_crm_customer"]]=$row["mobile"];
			$data[$row["id_crm_customer"]].=' '.$row["mobile"];
			// Störzeichen entfernen
			$number=str_replace("/", "", $row["mobile"]);			
			$number=str_replace(" ", "", $number);
			$number=str_replace("-", "", $number);
			$number=str_replace(".", "", $number);
			$number=str_replace("+", "", $number);
			$data[$row["id_crm_customer"]].=' '.$number;
			
		$crm_customer_fax[$row["id_crm_customer"]]=$row["fax"];
			$data[$row["id_crm_customer"]].=' '.$row["fax"];
			// Störzeichen entfernen
			$number=str_replace("/", "", $row["fax"]);			
			$number=str_replace(" ", "", $number);
			$number=str_replace("-", "", $number);
			$number=str_replace(".", "", $number);
			$number=str_replace("+", "", $number);
			$data[$row["id_crm_customer"]].=' '.$number;

		$crm_customer_mail[$row["id_crm_customer"]]=$row["mail"];
			$data[$row["id_crm_customer"]].=' '.$row["mail"];
			
		$crm_customer_gewerblich[$row["id_crm_customer"]]=$row["gewerblich"];
	}
	
	$res=q("SELECT * FROM crm_customer_accounts;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($res))
	{
		$crm_customer_account[$row["id_crm_customer"]][$row["account"]]=$row["account_user_id"];
		$data[$row["crm_customer_id"]].=' '.$row["account_user_id"];
	}


		$suchealles=false;
		//FREITEXTSUCHE
		if ($freitextsuche && $suchealles) 
		{
			//DATEN aus Adressen laden
			$res=q("SELECT * FROM crm_address;", $dbweb, __LINE__, __FILE__);
			while ($row=mysqli_fetch_array($res))
			{
				$crm_customer[$row["crm_customer_id"]]=true;

				$name="";
				$name=$row["company"];
				if ($name=="") $name=$row["name"]; else $name.=' '.$row["name"];
				
				if (isset($data[$row["crm_customer_id"]]))
				{
					$data[$row["crm_customer_id"]].=' '.$name;
				}
				else
				{
					$data[$row["crm_customer_id"]]=$name;
				}
	
				$address="";
				if ($row["street1"]!=""){
					if ($address=="") {
						$address.=$row["street1"]; 
					}
					else {
						$address.=' '.$row["street1"];
					}
				}
				if ($row["street2"]!=""){
					if ($address=="") {
						$address.=$row["street2"]; 
					}
					else {
						$address.=' '.$row["street2"];
					}
				}
				if ($row["zip"]!=""){
					if ($address=="") {
						$address.=$row["zip"]; 
					}
					else {
						$address.=' '.$row["zip"];
					}
				}
				if ($row["city"]!=""){
					if ($address=="") {
						$address.=$row["city"]; 
					}
					else {
						$address.=' '.$row["city"];
					}
				}
				if ($row["country"]!=""){
					if ($address=="") {
						$address.=$row["country"]; 
					}
					else {
						$address.=' '.$row["country"];
					}
				}
				
				if (isset($data[$row["crm_customer_id"]]))
				{
					$data[$row["crm_customer_id"]].=' '.$address;
				}
				else
				{
					$data[$row["crm_customer_id"]]=$address;
				}
	
			}
		
			//DATEN aus Numbers laden
			$res=q("SELECT * FROM crm_numbers;", $dbweb, __LINE__, __FILE__);
			while ($row=mysqli_fetch_array($res))
			{
	
				$number=$row["number"];
				if (isset($data[$row["crm_customer_id"]]))
				{
					$data[$row["crm_customer_id"]].=' '.$number;
				}
				else
				{
					$data[$row["crm_customer_id"]]=$number;
				}
			
			}
			
		}

		if ($freitextsuche)
		{
			
			$datafield = array();
	
			//SUCHE, OB SUCHWÖRTER IN KUNDENDATEN VORKOMMEN
			while( list($customer, $val) = each ($crm_customer))
			{
	//			if (isset($data[$customer]))
	//			{
					$data[$customer]=' '.strtolower($data[$customer]);
					$match=true;
					foreach ($qry_string as $qrystring)
					{
						if (strpos($data[$customer], $qrystring) === false ) $match=false;
					}
				
					//if ($match) $crm_customer[$customer]=true; else unset($crm_customer[$customer]);
					if (!$match) unset($crm_customer[$customer]);
	//			}
				//else {unset($crm_customer[$customer]);}
			}

			$dump=reset($crm_customer);
		} // FREITEXTSUCHE
		
		// ZIP-SUCHE
		if (isset($_POST["zip_search"]) && $_POST["zip_search"]=="1")
		{
			if (isset($_POST["rc_zip"]) && $_POST["rc_zip"]!="" && isset($_POST["distance"]) && $_POST["distance"]*1>0)
			{
				while( list($customer, $val) = each ($crm_customer))
				{
					if (isset($crm_customer_zip[$customer]) && $crm_customer_zip[$customer]!="" && $crm_customer_country[$customer]=="Deutschland")
					{
						$xmlResponse=post(PATH."soa/", array("API" => "cms", "Action" => "ZipcodeDistance", "Zipcode1" => $crm_customer_zip[$customer], "Zipcode2" => $_POST["rc_zip"]));
						if (strpos($xmlResponse, '<Ack>Success</Ack>')>0)
						{
							$xml = new SimpleXMLElement($xmlResponse);
							$distance = $xml->Distance[0];
					//		$crm_customer_distance[$customer]=$distance;
							if ($_POST["distance"]*1 < $distance) unset($crm_customer[$customer]);
						}
						else 
						{
							unset($crm_customer[$customer]);
						}
					}
					else
					{
						unset($crm_customer[$customer]);
					}
				}
			$dump=reset($crm_customer);
			}
		}

		// KUNDE GESCHÄFTSTYP
		if (isset($_POST["custormertype_search"]) && isset($_POST["customer_type"]) && $_POST["customer_type"]!="all")
		{
			while( list($customer, $val) = each ($crm_customer))
			{
				if ($crm_customer_gewerblich[$customer] != $_POST["customer_type"]*1) 
				{
					unset($crm_customer[$customer]);
				}
			}
			$dump=reset($crm_customer);
		}

	//GET NOTES
	$res_notes=q("SELECT customer_id FROM crm_customer_notes;", $dbweb, __FILE__, __LINE__);
	while ($row_notes=mysqli_fetch_array($res_notes))
	{
		if (isset($notes[$row_notes["customer_id"]]))
		{
			$notes[$row_notes["customer_id"]]++;
		}
		else 
		{
			$notes[$row_notes["customer_id"]]=1;
		}
	}
	//GET COMMUNICATIONS
	$res_comm=q("SELECT customer_id FROM crm_communications;", $dbweb, __FILE__, __LINE__);
	while ($row_comm=mysqli_fetch_array($res_comm))
	{
		if (isset($communications[$row_comm["customer_id"]]))
		{
			$communications[$row_comm["customer_id"]]++;
		}
		else 
		{
			$communications[$row_comm["customer_id"]]=1;
		}
	}
	//GET customer Lists


		// ANZEIGE SUCHERGEBNISSE
		if (sizeof($crm_customer)>0)
		{
			$counter=0;
			echo '<form name="customer_list">';
			echo '<table>';
			echo '<tr>';
			echo '	<th><input type="checkbox" name="customer_select_all" id="customer_select_all" onclick="checkAll();"></th>';
			echo '	<th></th>';
			echo '	<th style="width:740px">Kunde';
			echo '		<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/mail_edit.png" alt="Mail/Newsletter an Auswahl versenden" title="Mail/Newsletter an Auswahl versenden" onclick="create_mail(\'all\');" />';
			echo '		<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/add.png" alt="Kunde(n) zu einer Liste hinzufügen" title="Kunde(n) zu einer Liste hinzufügen" onclick="add_customer_to_costumer_list();" />';
			echo '	</th>';
			echo '</tr>';
			while( list($customer, $val) = each ($crm_customer))
			{
				$counter++;
				echo '<tr>';
				echo '	<td><input type="checkbox" name="customer_select[]" id="customer_select_'.$customer.'" value="'.$customer.'" /></td>';
				echo '	<td style="text-align:right">'.$counter.'</td>';
				echo '	<td><b>'.$crm_customer_name[$customer].'</b><br />';
				//echo '	<small><b>Anschrift:</b> ';
				echo '	<small>';
				if (isset($crm_customer_street1[$customer]) && $crm_customer_street1[$customer]!="") echo $crm_customer_street1[$customer];
				if (isset($crm_customer_street2[$customer]) && $crm_customer_street2[$customer]!="") echo ', '.$crm_customer_street2[$customer];
				if (isset($crm_customer_zip[$customer]) && $crm_customer_zip[$customer]!="") echo ', '.$crm_customer_zip[$customer];
				if (isset($crm_customer_city[$customer]) && $crm_customer_city[$customer]!="") echo ' '.$crm_customer_city[$customer];
				if (isset($crm_customer_country[$customer]) && $crm_customer_country[$customer]!="") echo ', '.$crm_customer_country[$customer];
				echo '</small>';
				if (isset($notes[$customer]))
				{
					echo '<br /><span style="background-color:#cc0; width:740px; font-size:8pt">Es sind '.$notes[$customer].' Notizen vorhanden. <a href="javascript:show_notes('.$customer.');">[+]</a></span>';
					echo '<div id="notebox'.$customer.'" style="display:none; width:740px;"></div>';
				}
				if (isset($communications[$customer]))
				{
					echo '<br /><span style="background-color:#da5; width:740px; font-size:8pt">Es sind '.$communications[$customer].' Kontakte protokolliert. <a href="javascript:show_communication('.$customer.');">[+]</a></span>';
					echo '<div id="commbox'.$customer.'" style="display:none; width:740px;;"></div>';
				}
				echo '</td>';
				echo '</tr>';
			}
			echo '</table>';
			echo '</form>';
		}
		else
		{
			echo '<b>Keine Suchtreffer</b>';
		}
		
?>