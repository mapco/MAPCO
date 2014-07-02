<?php

	/*************************
	********** SOA 2 *********
	*************************/

	$required=array("category_id" => "numericNN", "title" => "text", "name_label" => "text", "value_label" => "text");
	check_man_params($required);
	
	$data['title'] = $_POST['title'];
	$data['name_label'] = $_POST['name_label'];
	$data['value_label'] = $_POST['value_label'];

	$where = "WHERE id=".$_POST['category_id'];

	q_update('shop_items_nvp_categories', $data, $where, $dbshop , __FILE__, __LINE__);
	
	echo $xml;

?>