<?php
	
	/*************************
	********** SOA 2 *********
	*************************/
	
	check_man_params(array("id_list" => "numericNN"));
	
	$id_list = $_POST['id_list'];
	
	$xml = '';
	
	$customers_in_list = '';
	$res = q("SELECT firstmod_user FROM crm_costumer_lists WHERE id_list =".$id_list.";", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_assoc($res);
	if ( $row['firstmod_user'] === $_SESSION['id_user'] )
	{
		$xml .= "<listowner>1</listowner>\n";
	}
	else
	{
		$xml .= "<listowner>0</listowner>\n";
	}
	
	$customers_in_list = '';
	$res = q("SELECT cos_lis.customer_id, cos . * FROM crm_costumer_lists_customers AS cos_lis, crm_customers AS cos WHERE cos_lis.list_id =".$id_list." AND cos.id_crm_customer = cos_lis.customer_id;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_assoc($res))
	{
		$customer_ids[] = $row['customer_id'];
		$customers[$row['id_crm_customer']] = $row;
		$customers[$row["id_crm_customer"]]['notes']=0;
		$customers[$row["id_crm_customer"]]['communications']=0;
	}
	
	if($customer_ids[0] != '')
	{
		$customer_ids = implode(",", $customer_ids);

		//GET NOTES
		$res_notes=q("SELECT user_id FROM crm_conversations WHERE user_id IN (".$customer_ids.") AND type_id=4;", $dbweb, __FILE__, __LINE__);
		while ($row_notes=mysqli_fetch_array($res_notes))
		{
			$customers[$row_notes["user_id"]]['notes']++;
		}
		
		//GET COMMUNICATIONS
		$res_comm=q("SELECT user_id, reminder FROM crm_conversations WHERE user_id IN (".$customer_ids.") AND type_id!=4;", $dbweb, __FILE__, __LINE__);
		while ($row_comm=mysqli_fetch_assoc($res_comm))
		{ 
			$customers[$row_comm["user_id"]]['communications']++;
	
			if ($row_comm["reminder"]!=0)
			{
				if ($customers[$row_comm["customer_id"]]["reminder"]<=$row_comm["reminder"]) $customers[$row_comm["user_id"]]["reminder"]=$row_comm["reminder"];
			}
		}	
		foreach($customers as $customer_id => $customer)
		{
			$xml.= "<customer>";
			foreach($customer as $key => $value)
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
			$xml.= "</customer>";
		}
	}

	print $xml;
?>