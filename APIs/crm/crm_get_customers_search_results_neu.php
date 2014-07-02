<?php

	$qury_where = '';
	$qury_where2 = '';
	
	if ( isset($_POST['id_list']) && $_POST['id_list'] != '' )
	{
		$res=q("SELECT customer_id FROM crm_costumer_lists_customers WHERE list_id='".$_POST['id_list']."';", $dbweb, __FILE__, __LINE__);
		while($row=mysqli_fetch_assoc($res))
		{
			$in_list[] = $row['customer_id'];
		}
		if ( sizeof($in_list) > 1 )
		{
			$qury_where .= 'id_crm_customer NOT IN ('.implode(',',$in_list).') AND ';
		}
		elseif( sizeof($in_list) == 1 )
		{
			$qury_where .= 'id_crm_customer!='.$in_list[0].' AND ';
		}
		else
		{
			$qury_where = '';
		}
	}
	else
	{
		$qury_where = '';
	}
	
	//CHECK, OB FREITEXTSUCHE
	$qry_string = array();
	if (!$_POST["qry_string"]=="") {
		$qry_string=explode(' ', $_POST["qry_string"]);
		$qry_string2 = $qury_string;
		$qry_string_size=sizeof($qry_string);
		for ($i=0; $i<$qry_string_size; $i++)
		{
			$qry_string[$i] = strtolower(trim($qry_string[$i]));
		}
		$freitextsuche=true;

		if ( sizeof($qry_string) > 1 )
		{
			$search_field = 'IN (';
			foreach($qury_string as $qury)
			{
				if ( !is_numeric($qury) )
				{
					$search_field .= "'".$qury."'";
				}
			}
			
			$search_field .= ')';
		}
		else
		{
			$search_field = "LIKE '%".$qry_string[0]."%'";
		}
		
		$qury_where2 = " AND company ".$search_field." OR name ".$search_field." OR street1 ".$search_field." OR street2 ".$search_field." OR city ".$search_field." OR zip ".$search_field." OR phone ".$search_field." OR mobile ".$search_field." OR fax ".$search_field." OR mail ".$search_field;
	}
	else {$freitextsuche=false;}
	
	$res=q("SELECT country FROM cms_countries WHERE country_code='".$_SESSION['origin']."';", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_assoc($res);
	$country = $row['country'];

	if ( isset($_POST["zip_search"]) && $_POST['zip_search'] == 0 )
	{
		$qury_where = $qury_Where. 'country="'.$country.'"'.$qury_where2;
		$sql = "SELECT id_crm_customer FROM crm_customers WHERE ".$qury_where.";";
var_dump($sql); die();
		$res=q($sql, $dbweb, __FILE__, __LINE__);
		while ($row=mysqli_fetch_assoc($res))
		{
			$customer_ids[]= $row["id_crm_customer"];
		}
	}
	else
	{
		$qury_where = $qury_Where. 'country="'.$country.'"'.$qury_where2;
		// ZIP-SUCHE
		if ( isset($_POST["rc_zip"]) && $_POST["rc_zip"]!="" && isset($_POST["distance"]) && $_POST["distance"] !== '' )
		{
			$qury_where = $qury_Where. 'country="'.$country.' AND zip!=""'.$qury_where2;
		
			//LISTE ALLER CRM_CUSTOMERS LADEN
			$sql = "SELECT id_crm_customer, zip FROM crm_customers WHERE ".$qury_where.";";

			$res=q($sql, $dbweb, __FILE__, __LINE__);
			while ($row=mysqli_fetch_assoc($res))
			{	
				$data[$row["id_crm_customer"]]['zip'] = $row["zip"]+0;
				$zipcodes[] = $row["zip"]+0;
			}
			
			if ( sizeof($zipcodes) > 1 )
			{
				$zipcodes = implode(',', $zipcodes);
			}
			else
			{
				$zipcodes = $zipcodes[0];	
			}
			
			if ( sizeof($zipcodes) > 0 )
			{
				$res=q("SELECT zipcode, latitude, longitude FROM cms_zipcodes WHERE `zipcode` IN (".$zipcodes.");", $dbweb, __FILE__, __LINE__);
				while ($row=mysqli_fetch_assoc($res))
				{
					$coords[$row["zipcode"]]['latitude'] = $row["latitude"];
					$coords[$row["zipcode"]]['longitude'] = $row["longitude"];
				}
				
				$results=q("SELECT latitude, longitude FROM cms_zipcodes WHERE zipcode=".$_POST["rc_zip"].";", $dbweb, __FILE__, __LINE__);
				$row=mysqli_fetch_array($results);
				$lat1=$row["latitude"]/180*pi();
				$long1=$row["longitude"]/180*pi();
	
				foreach( $data as $customer_id => $customer )
				{
					$key = $customer['zip'];
					//get latitude and longitude for each zipcode
					$lat2=$coords[$key]["latitude"];
					$long2=$coords[$key]["longitude"];
					
					//GRAD to RAD
					$lat2=$lat2/180*pi();
					$long2=$long2/180*pi();
					
					//lat and long in GRAD
					$e = acos( sin($lat1)*sin($lat2) + cos($lat1)*cos($lat2)*cos($long2-$long1) );
					
					//get distance
					$distance = $e * 6378.137;
					
					if ( $distance <= $_POST['distance'] || $customer['zip'] == $_POST['rc_zip'] )
					{
						$customer_ids[] = $customer_id;
					}
				}
			}
		}	
	}

	if ( sizeof($customer_ids) > 1 )
	{
		$customer_ids = ' WHERE id_crm_customer IN ('.implode(',',$customer_ids).')';
	}
	elseif( sizeof($customer_ids) == 1 )
	{
		$customer_ids = ' WHERE id_crm_customer='.$customer_ids[0];
	}
	else
	{
		$customer_ids = '';
	}
	
	$counter=0;
	if ( $customer_ids !== '' )
	{
		$res=q("SELECT id_crm_customer, company, name, street1, street2, zip, city, phone, mobile, fax, mail, gewerblich FROM crm_customers".$customer_ids.";", $dbweb, __FILE__, __LINE__);
		while ($row=mysqli_fetch_assoc($res))
		{
			$crm_customer[$row['id_crm_customer']] = $row;
		}
				
		// ANZEIGE SUCHERGEBNISSE
		if (sizeof($crm_customer)>0)
		{
			$xmldata="";
		
			foreach( $crm_customer as $customer )
			{		
				$counter++;
				$xmldata.="<customer>\n";
				foreach( $customer as $key => $value )
				{
					if ( gettype($value) )
					{
						$value = '<![CDATA['.$value.']]>';	
					}
					$xmldata.="	<".$key.">".$value."</".$key.">\n";
				}
				$xmldata.="</customer>\n";
			}
		}
	}
echo $xmldata;
echo "<num_rows>".$counter."</num_rows>\n";

?>