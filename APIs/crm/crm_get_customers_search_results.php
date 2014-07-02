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
		$crm_customer_account[$row["crm_customer_id"]][$row["account"]]=$row["account_user_id"];
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
	$res_comm=q("SELECT customer_id, reminder FROM crm_communications;", $dbweb, __FILE__, __LINE__);
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
		if ($row_comm["reminder"]!=0)
		{
			if (isset($reminder[$row_comm["customer_id"]]))
			{
				if ($reminder[$row_comm["customer_id"]]<=$row_comm["reminder"]) $reminder[$row_comm["customer_id"]]=$row_comm["reminder"];
			}
			else
			{
				$reminder[$row_comm["customer_id"]]=$row_comm["reminder"];
			}
		}

	}

	$lists=array();
	$res_lists=q("SELECT * FROM crm_costumer_lists;", $dbweb, __FILE__, __LINE__);
	while ($row_lists=mysqli_fetch_array($res_lists))
	{
		$lists[$row_lists["id_list"]]=$row_lists["title"];
	}
	
	$list_customers=array();
	$res_lists_customer=q("SELECT * FROM crm_costumer_lists_customers WHERE firstmod_user=".$_SESSION["id_user"].";",$dbweb, __FILE__, __LINE__);
	while($row_lists_customer=mysqli_fetch_array($res_lists_customer))
	{
		if (isset($list_customers[$row_lists_customer["customer_id"]]))
		{
			$list_customers[$row_lists_customer["customer_id"]][sizeof($list_customers[$row_lists_customer["customer_id"]])]=$row_lists_customer["list_id"];
		}
		else
		{
			$list_customers[$row_lists_customer["customer_id"]][0]=$row_lists_customer["list_id"];
		}

	}


	$counter=0;
	// ANZEIGE SUCHERGEBNISSE
	if (sizeof($crm_customer)>0)
	{
		$xmldata="";
	
		while( list($customer, $val) = each ($crm_customer))
		{
			$counter++;
			$xmldata.="<customer>";
			$xmldata.="<customer_id>".$customer."</customer_id>";
			
			if (isset($reminder[$customer]))
			{
				$xmldata.="<reminder>".date("d.m.Y H:i", $reminder[$customer])."</reminder>";
			}
			else 
			{
				$xmldata.="<reminder></reminder>";
			}
			$xmldata.="<name><![CDATA[".$crm_customer_name[$customer]."]]></name>";
			$xmldata.="<street1><![CDATA[".$crm_customer_street1[$customer]."]]></street1>";
			$xmldata.="<street2><![CDATA[".$crm_customer_street2[$customer]."]]></street2>";
			$xmldata.="<zip><![CDATA[".$crm_customer_zip[$customer]."]]></zip>";
			$xmldata.="<city><![CDATA[".$crm_customer_city[$customer]."]]></city>";
			$xmldata.="<country><![CDATA[".$crm_customer_country[$customer]."]]></country>";
			if (isset($notes[$customer]))
			{
				$xmldata.="<notes>".$notes[$customer]."</notes>";
			}
			else
			{
				$xmldata.="<notes>0</notes>";
			}
			if (isset($communications[$customer]))
			{
				$xmldata.="<communications>".$communications[$customer]."</communications>";
			}
			else 
			{
				$xmldata.="<communications>0</communications>";
			}
			$xmldata.="<inlists>\n";
			if (isset($list_customers[$row["customer_id"]]))
			{
				for($i=0; $i<sizeof($list_customers[$row["customer_id"]]); $i++)
				{
					$xmldata.="<inlist>".$lists[$list_customers[$row["customer_id"]][$i]]."</inlist>\n";
				}
			}
			$xmldata.="</inlists>\n";
			$xmldata.="</customer>";
		}
	}
		
echo "<crm_get_customers_search_resultsResponse>\n";
echo "<Ack>Success</Ack>\n";
echo "<count>".$counter."</count>\n";
echo "<listowner>0</listowner>\n";
	echo $xmldata;
echo "</crm_get_customers_search_resultsResponse>";

?>