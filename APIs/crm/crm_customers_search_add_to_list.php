<?php

	foreach ( $_POST['customer_ids'] as $customer )
	{
		$data = array();
		$data['customer_id'] = $customer;
		$data['list_id'] = $_POST['id_list'];
		$data['firstmod'] = time();
		$data['firstmod_user'] = $_SESSION["id_user"];
		$data['lastmod'] = $data['firstmod'];
		$data['lastmod_user'] = $_SESSION["id_user"];
		
		q_insert("crm_costumer_lists_customers", $data, $dbweb, __FILE__, __LINE__);
	}

?>