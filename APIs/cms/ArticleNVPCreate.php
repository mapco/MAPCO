<?php

	check_man_params(array("item_id"	=> "numeric",
						   "language_id"	=> "numeric",
						   "category_id"	=> "numeric",
						   "name"	=> "text",
						   "value"	=> "text",
						   "comment"	=> "text"));
						   				   
	$data=array(); 
	$data['item_id'] = $_POST['item_id'];
	$data['category_id'] = $_POST['category_id'];
	$data['language_id'] = $_POST['language_id'];
	$data['name'] = $_POST['name'];
	$data['value'] = $_POST['value'];
	$data['comment'] = $_POST['comment'];
	$data["ordering"]=1;
	$data["active"]=0;

	q("UPDATE shop_items_nvp SET ordering=ordering+1 WHERE item_id=".$data['item_id']." AND category_id=".$data['category_id']." AND language_id=".$data['language_id'].";", $dbshop, __FILE__, __LINE__);

	$result=q_insert("shop_items_nvp", $data, $dbshop, __FILE__, __LINE__);
	$xml = '<insert_id>'.mysqli_insert_id($dbshop).'</insert_id>'."\n";

	print $xml;
?>