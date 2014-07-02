<?php

	/*************************
	********** SOA 2 *********
	*************************/
	
	check_man_params(array("customer_id" => "numeric", "name" => "text", "company" => "text", "phone" => "text", "mobile" => "text", "fax" => "text", "street1" => "text", "street2" => "text", "zip" => "text", "city" => "text"));
	
	$mod_time = time();
	$mod_user = $_SESSION["id_user"];

	$customer_data['name'] = $_POST['name'];
	$customer_data['company'] = $_POST['company'];
	$customer_data['street1'] = $_POST['street1'];
	$customer_data['street2'] = $_POST['street2'];
	$customer_data['zip'] = $_POST['zip'];
	$customer_data['city'] = $_POST['city'];
	$customer_data['phone'] = $_POST['phone'];
	$customer_data['mobile'] = $_POST['mobile'];
	$customer_data['fax'] = $_POST['fax'];
	$customer_data['mail'] = $_POST['mail'];
	$customer_data['lastmod'] = $mod_time;
	$customer_data['lastmod_user'] = $mod_user;
	
	if ($_POST['customer_id'] == 0)
	{
		$res = q("SELECT c.country FROM shop_countries AS c, shop_shops AS s WHERE s.id_shop=".$_SESSION['id_shop']." AND c.country_code=s.country_code;", $dbshop, __FILE__, __LINE__);
		$row = mysqli_fetch_assoc($res);
		
		$customer_data['country'] = $row['country'];
		$customer_data['firstmod'] = $mod_time;
		$customer_data['firstmod_user'] = $mod_user;
		q_insert('crm_customers', $customer_data, $dbweb, __FILE__, __LINE__);
		
		$data['list_id'] = $_POST['id_list'];
		$data['customer_id'] = mysqli_insert_id($dbweb);
		$data['firstmod'] = $mod_time;
		$data['firstmod_user'] = $mod_user;
		$data['lastmod'] = $mod_time;
		$data['lastmod_user'] = $mod_user;
		q_insert('crm_costumer_lists_customers', $data, $dbweb, __FILE__, __LINE__);
	}
	else
	{
		$where = 'WHERE `id_crm_customer`= '.$_POST["customer_id"];
		q_update('crm_customers', $customer_data, $where, $dbweb, __FILE__, __LINE__);
	}
?>