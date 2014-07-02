<?php

	/*************************
	********** SOA 2 *********
	*************************/

	$required=array("title" => "text", "name_label" => "text", "value_label" => "text", "ordering" => "numeric");
	check_man_params($required);
	
	$data['title'] = $_POST['title'];
	$data['name_label'] = $_POST['name_label'];
	$data['value_label'] = $_POST['value_label'];
	$data['ordering'] = $_POST['ordering'];

	q("UPDATE shop_items_nvp_categories SET ordering=ordering+1 WHERE ordering>=".$data['ordering'].";",$dbshop,__FILE__,__LINE__);

	q_insert('shop_items_nvp_categories', $data, $dbshop , __FILE__, __LINE__);
	$xml .= '<insert_id>'.mysqli_insert_id($dbweb).'</insert_id>';
	
	echo $xml;

?>