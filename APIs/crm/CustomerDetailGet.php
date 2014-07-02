<?php

	/*************************
	********** SOA 2 *********
	*************************/
	
	check_man_params(array("customer_id" => "numericNN"));

	$res=q("SELECT * FROM crm_customers WHERE id_crm_customer = ".$_POST["customer_id"].";", $dbweb, __FILE__, __LINE__);
	if (mysqli_num_rows($res)>0)
	{
		$row=mysqli_fetch_assoc($res);
		$data = $row;
		
		if($data['user_id']>0)
		{
			$res=q("SELECT username, usermail FROM cms_users WHERE id_user = ".$data['user_id'].";", $dbweb, __FILE__, __LINE__);
			if (mysqli_num_rows($res)>0)
			{
				$data = array_merge($data, mysqli_fetch_assoc($res));	
			}
		}

		/*$res=q("SELECT `id_address`, `address_type`, `foreign_address_id`, `company`, `gender`, `name`, `street1`, `street2`, `zip`, `city`, `country` FROM `crm_address` WHERE `crm_customer_id` = ".$_POST["customer_id"]." order By firstmod ASC;", $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows($res)>0)
		{
			$row = mysqli_fetch_assoc($res);
			$data['id_address'] = $row['id_address'];
			$data['address_type'] = $row['address_type'];
			$data['foreign_address_id'] = $row['foreign_address_id'];
			$data['company'] = $row['company'];
			$data['gender'] = $row['gender'];
			$data['name'] = $row['name'];
			$data['street1'] = $row['street1'];
			$data['street2'] = $row['street2'];
			$data['zip'] = $row['zip'];
			$data['city'] = $row['city'];
			$data['country'] = $row['country'];
		}
		else
		{
			$data['id_address'] = 0;
		}
*/
		$xml = '';
 
		foreach($data as $key => $value)
		{
			if (is_numeric($value)) {
				$xml.= '	<' . $key . '><![CDATA[' . $value . ']]></' . $key . '>' . "\n";
			} else {
				if (strpos($value, '[CDATA[') === false) {
					$xml.= '	<' . $key . '><![CDATA[' . $value . ']]></' . $key . '>' . "\n";
				} else {
					$xml.=  $value . "\n";
				}	
			}
		}
		
		print $xml;
	}
?>