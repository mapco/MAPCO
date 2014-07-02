<?php

	/*************************
	********** SOA 2 *********
	*************************/
	
	check_man_params(array("customer_id" => "numericNN", "name" => "text", "company" => "text", "id_address" => "numeric", "street1" => "text", "street2" => "text", "zip" => "text", "city" => "text"));
	
	$mod_time = time();
	$mod_user = $_SESSION["id_user"];	
	
	$address_data['company'] = $_POST['company'];
	$address_data['name'] = $_POST['name'];
	$address_data['street1'] = $_POST['street1'];
	$address_data['street2'] = $_POST['street2'];
	$address_data['zip'] = $_POST['zip'];
	$address_data['city'] = $_POST['city'];
	$address_data['lastmod'] = $mod_time;
	$address_data['lastmod_user'] = $mod_user;
	
	if ($_POST['id_address'] == 0)	{
		$address_data['crm_customer_id'] = $_POST['customer_id'];
		$address_data['firstmod'] = $mod_time;
		$address_data['firstmod_user'] = $mod_user;
		q_insert('crm_address', $address_data, $dbweb, __FILE__, __LINE__);
	}
	else
	{
		$where = 'WHERE `id_address`= '.$_POST["id_address"];
		q_update('crm_address', $address_data, $where, $dbweb, __FILE__, __LINE__);
	}
?>