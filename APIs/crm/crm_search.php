<?php

		$crm_customer = array();
		//DATEN aus Adressen laden
		$res=q("SELECT * FROM crm_address;", $dbweb, __LINE__, __FILE__);
		while ($row=mysqli_fetch_array($res))
		{
			$crm_customer[$row["crm_customer_id"]]="";
			$name="";
			$name=$row["company"];
			if ($name=="") $name=$row["name"]; else $name.=' '.$row["name"];
			
			if (isset($data[$row["crm_customer_id"]]["name"]))
			{
				if (!in_array($name, $data[$row["crm_customer_id"]]["name"])) $data[$row["crm_customer_id"]]["name"][sizeof($data[$row["crm_customer_id"]]["name"])]='#'.$row["crm_customer_id"].'#'.$name;
			}
			else
			{
				$data[$row["crm_customer_id"]]["name"][0]='#'.$row["crm_customer_id"].'#'.$name;
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
			
			if (isset($data[$row["crm_customer_id"]]["address"]))
			{
				if (!in_array($address, $data[$row["crm_customer_id"]]["address"])) $data[$row["crm_customer_id"]]["address"][sizeof($data[$row["crm_customer_id"]]["address"])]='#'.$row["crm_customer_id"].'#'.$address;
			}
			else
			{
				$data[$row["crm_customer_id"]]["address"][0]='#'.$row["crm_customer_id"].'#'.$address;
			}

		}
		
		//DATEN aus Numbers laden
		$res=q("SELECT * FROM crm_numbers;", $dbweb, __LINE__, __FILE__);
		while ($row=mysqli_fetch_array($res))
		{
			if (!isset($crm_customer[$row["crm_customer_id"]]))  $crm_customer[$row["crm_customer_id"]]=="";

			$number=$row["number"];
			if (isset($data[$row["crm_customer_id"]]["number"]))
			{
				if (!in_array($number, $data[$row["crm_customer_id"]]["number"])) $data[$row["crm_customer_id"]]["number"][sizeof($data[$row["crm_customer_id"]]["number"])]='#'.$row["crm_customer_id"].'#'.$number;
			}
			else
			{
				$data[$row["crm_customer_id"]]["number"][0]='#'.$row["crm_customer_id"].'#'.$number;
			}
		
		}

		$datafield = array();

		$qry_string=explode(' ', $_POST["qry_string"]);
		$qry_string_size=sizeof($qry_string);
		for ($i=0; $i<$qry_string_size; $i++)
		{
			$qry_string[$i]=trim($qry_string[$i]);
		}
		
		while( list($customer, $val) = each ($crm_customer))
		{
			$matches=true;
			$match_name = array();
				$match_name[0]="";
			$match_address = array();
				$match_address[0]="";
			$match_number = array();
				$match_number[0]="";
			foreach($qry_string as $qrystr)
			{
				if (isset($data[$customer]["name"]))
				{
					foreach ($data[$customer]["name"] as $dataval)
					{
						$singel_match=false;
						if (strpos(strtolower(' '.$dataval), strtolower($qrystr))>0) {
							$singel_match=true;
							if (!in_array($dataval, $match_name)) 
							{
								if ($match_name=="")
								{
									$match_name[0]=$dataval;
								}
								else
								{
									$match_name[sizeof($match_name)]=$dataval;
								}
							}
						}
					}
				}
				if (isset($data[$customer]["address"]))
				{
					foreach ($data[$customer]["address"] as $dataval)
					{
						if (strpos(strtolower(' '.$dataval), strtolower($qrystr))>0) {
							$singel_match=true;
							if (!in_array($dataval, $match_address)) 
							{
								if ($match_name=="")
								{
									$match_address[0]=$dataval;
								}
								else
								{
									$match_address[sizeof($match_address)]=$dataval;
								}
							}
						}
					}
				}
				if (isset($data[$customer]["number"]))
				{
					foreach ($data[$customer]["number"] as $dataval)
					{
						if (strpos(strtolower(' '.$dataval), strtolower($qrystr))>0) {
							$singel_match=true;
							if (!in_array($dataval, $match_number)) 
							{
								if ($match_name=="")
								{
									$match_number[0]=$dataval;
								}
								else
								{
									$match_number[sizeof($match_number)]=$dataval;
								}
							}							
						}
					}
				}
				
				if (!$singel_match) $matches=false;
			}
			
			if ($matches)
			{
				for ($a=0; $a<sizeof($match_name); $a++)
				{
					for ($b=0; $b<sizeof($match_address); $b++)
					{
						for ($c=0; $c<sizeof($match_number); $c++)
						{
							$tmp="";
							while ($a+1<sizeof($match_name) && $match_name[$a]=="") $a++;
							$tmp.=$match_name[$a];
							if ($tmp=="")
							{
								while ($b+1<sizeof($match_address) && $match_address[$b]=="") $b++;
								if ($match_address[$b]!="") $tmp.=$match_address[$b];
								//$tmp.=$match_address[$b];
							}
							else 
							{
								while ($b+1<sizeof($match_address) && $match_address[$b]=="") $b++;
								if ($match_address[$b]!="") $tmp.='+'.$match_address[$b];
								//$tmp.='+'.$match_address[$b];
							}
							if ($tmp=="")
							{
								while ($c+1<sizeof($match_number) && $match_number[$c]=="") $c++;
								if ($match_number[$c]!="") $tmp.=$match_number[$c];
								//$tmp.=$match_number[$c];
							}
							else 
							{
								while ($c+1<sizeof($match_number) && $match_number[$c]=="") $c++;
								if ($match_number[$c]!="") $tmp.='+'.$match_number[$c];
								//$tmp.='+'.$match_number[$c];
							}
							
							if ($tmp!="") $datafield[sizeof($datafield)]=$tmp;
							
						}
					}
				}
			}

			if ($matches)
			{
				$tmp="";
				foreach($match as $val)
				{
					if ($tmp=="")
					{
						$tmp.=$val;
					}
					else
					{
						$tmp.=', '.$val;
					}
				}
				$datafield[sizeof($datafield)]=$tmp;
			}
		}
		$xmldata="";
		$count=0;
		foreach($datafield as $val)
		{
			$xmldata.= '<data_'.$count.'_string><![CDATA['.$val.']]></data_'.$count.'_string>'."\n";
			$count++;
		}
		echo '<crm_searchResponse>'."\n";
		echo '	<Ack>Success</Ack>'."\n";
		echo '	<count>'.$count.'</count>'."\n";
		echo '	<Response>'."\n";
		echo $xmldata;
		echo '	</Response>'."\n";
		echo '</crm_searchResponse>'."\n";

/*
	$qry_string = array();
	if (!$_POST["qry_string"]=="") {
		$qry_string=explode(' ', $_POST["qry_string"]);
		$qry_string_size=sizeof($qry_string);
		for ($i=0; $i<$qry_string_size; $i++)
		{
			$qry_string[$i]=strtolower(trim($qry_string[$i]));
		}
	}


	$res=q("SELECT * FROM crm_customers;", $dbweb, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($res))
	{

		$crm_customer[$row["id_crm_customer"]]=true;

		// CUSTOMER-Bezeichnung
		$name=$row["company"];
		if ($name=="") $name=$row["name"]; else $name.=' '.$row["name"];
		$crm_customer_name[$row["id_crm_customer"]]=$name;
//			$data[$row["id_crm_customer"]]=$crm_customer_name[$row["id_crm_customer"]];
		for ($i=0; $i<$qry_string_size; $i++)
		{
			if (strpos(' '.$name, $qry_string[$i])>0)
			{
				if (isset($match[$row["id_crm_customer"]][$i]))
				{
					$match[$row["id_crm_customer"]][$i][sizeof($match[$row["id_crm_customer"]][$i])]=$name;
				}
				else
				{
					$match[$row["id_crm_customer"]][$i][0]=$name;
				}
			}
		}
echo sizeof($match[$row["id_crm_customer"]][$i]).'#';

		$crm_customer_street1[$row["id_crm_customer"]]=$row["street1"];
//			$data[$row["id_crm_customer"]].=' '.$row["street1"];
		for ($i=0; $i<$qry_string_size; $i++)
		{
			if (strpos(' '.$row["street1"], $qry_string[$i])>0)
			$match[$row["id_crm_customer"]][$i][sizeof($match[$row["id_crm_customer"]][$i])]=$row["street1"];
		}

		$crm_customer_street2[$row["id_crm_customer"]]=$row["street2"];
//			$data[$row["id_crm_customer"]].=' '.$row["street2"];
		for ($i=0; $i<$qry_string_size; $i++)
		{
			if (strpos(' '.$row["street2"], $qry_string[$i])>0)
			$match[$row["id_crm_customer"]][$i][sizeof($match[$row["id_crm_customer"]][$i])]=$row["street2"];
		}

		// für ZIP-UMKREISSUCHE
		$crm_customer_zip[$row["id_crm_customer"]]=$row["zip"];
//			$data[$row["id_crm_customer"]].=' '.$row["zip"];
		for ($i=0; $i<$qry_string_size; $i++)
		{
			if (strpos(' '.$row["zip"], $qry_string[$i])>0)
			$match[$row["id_crm_customer"]][$i][sizeof($match[$row["id_crm_customer"]][$i])]=$row["zip"];
		}

			
		$crm_customer_city[$row["id_crm_customer"]]=$row["city"];
//			$data[$row["id_crm_customer"]].=' '.$row["city"];
		for ($i=0; $i<$qry_string_size; $i++)
		{
			if (strpos(' '.$row["city"], $qry_string[$i])>0)
			$match[$row["id_crm_customer"]][$i][sizeof($match[$row["id_crm_customer"]][$i])]=$row["city"];
		}

		$crm_customer_country[$row["id_crm_customer"]]=$row["country"];
//			$data[$row["id_crm_customer"]].=' '.$row["country"];
		for ($i=0; $i<$qry_string_size; $i++)
		{
			if (strpos(' '.$row["country"], $qry_string[$i])>0)
			$match[$row["id_crm_customer"]][$i][sizeof($match[$row["id_crm_customer"]][$i])]=$row["country"];
		}

		$crm_customer_phone[$row["id_crm_customer"]]=$row["phone"];
		for ($i=0; $i<$qry_string_size; $i++)
		{
			if (strpos(' '.$row["phone"], $qry_string[$i])>0)
			$match[$row["id_crm_customer"]][$i][sizeof($match[$row["id_crm_customer"]][$i])]=$row["phone"];
		}

//			$data[$row["id_crm_customer"]].=' '.$row["phone"];
/*
			// Störzeichen entfernen
			$number=str_replace("/", "", $row["phone"]);			
			$number=str_replace(" ", "", $number);
			$number=str_replace("-", "", $number);
			$number=str_replace(".", "", $number);
			$number=str_replace("+", "", $number);
			$data[$row["id_crm_customer"]].=' '.$number;
*/	
/*		
		$crm_customer_mobile[$row["id_crm_customer"]]=$row["mobile"];
		for ($i=0; $i<$qry_string_size; $i++)
		{
			if (strpos(' '.$row["mobile"], $qry_string[$i])>0)
			$match[$row["id_crm_customer"]][$i][sizeof($match[$row["id_crm_customer"]][$i])]=$row["mobile"];
		}

//			$data[$row["id_crm_customer"]].=' '.$row["mobile"];
/*
			// Störzeichen entfernen
			$number=str_replace("/", "", $row["mobile"]);			
			$number=str_replace(" ", "", $number);
			$number=str_replace("-", "", $number);
			$number=str_replace(".", "", $number);
			$number=str_replace("+", "", $number);
			$data[$row["id_crm_customer"]].=' '.$number;
*/
/*			
		$crm_customer_fax[$row["id_crm_customer"]]=$row["fax"];
		for ($i=0; $i<$qry_string_size; $i++)
		{
			if (strpos(' '.$row["fax"], $qry_string[$i])>0)
			$match[$row["id_crm_customer"]][$i][sizeof($match[$row["id_crm_customer"]][$i])]=$row["fax"];
		}

//			$data[$row["id_crm_customer"]].=' '.$row["fax"];
		/*
			// Störzeichen entfernen
			$number=str_replace("/", "", $row["fax"]);			
			$number=str_replace(" ", "", $number);
			$number=str_replace("-", "", $number);
			$number=str_replace(".", "", $number);
			$number=str_replace("+", "", $number);
			$data[$row["id_crm_customer"]].=' '.$number;
*/
/*
		$crm_customer_mail[$row["id_crm_customer"]]=$row["mail"];
		for ($i=0; $i<$qry_string_size; $i++)
		{
			if (strpos(' '.$row["mail"], $qry_string[$i])>0)
			$match[$row["id_crm_customer"]][$i][sizeof($match[$row["id_crm_customer"]][$i])]=$row["mail"];
		}

//			$data[$row["id_crm_customer"]].=' '.$row["mail"];
			

	}
	
	$datafield=array();
	while (list($customer, $val) = each ($crm_customer))
	{
		for ($i=0; $i<$qry_string_size; $i++)
		{
			$matches=true;
			if (sizeof($match[$costumer][$i])==0) $matches=false;
			echo sizeof($match[$costumer][$i]).'+';
		}
		
		if ($matches)
		{
			$tmp="";
			for ($i=0; $i<$qry_string_size; $i++)
			{
				if ($tmp=="")
					{
						$tmp.=$match[$costumer][$i][0];
					}
					else
					{
						$tmp.=', '.$match[$costumer][$i][0];
					}
			}
			$datafield[sizeof($datafield)]=$tmp;
		}
	
	}
		$xmldata="";
		$count=0;
		foreach($datafield as $val)
		{
			$xmldata.= '<data_'.$count.'_string><![CDATA['.$val.']]></data_'.$count.'_string>'."\n";
			$count++;
		}
		echo '<crm_searchResponse>'."\n";
		echo '	<Ack>Success</Ack>'."\n";
		echo '	<count>'.$count.'</count>'."\n";
		echo '	<Response>'."\n";
		echo $xmldata;
		echo '	</Response>'."\n";
		echo '</crm_searchResponse>'."\n";

	*/

?>