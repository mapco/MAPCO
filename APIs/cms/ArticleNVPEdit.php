<?php

	/*************************
	********** SOA 2 *********
	*************************/

	check_man_params(array("item_id"	=> "numeric",
						   "language_id"	=> "numeric",
						   "category_id"	=> "numeric",
						   "name"	=> "text",
						   "value"	=> "text",
						   "comment"	=> "text"));
						   				   
	$data=array(); 
	//$data['id'] = $_POST['nvp_id'];
	$data['item_id'] = $_POST['item_id'];
	$data['category_id'] = $_POST['category_id'];
	$data['language_id'] = $_POST['language_id'];
	$data['name'] = $_POST['name'];
	$data['value'] = $_POST['value'];
	$data['comment'] = $_POST['comment'];

	$xml = '';
	
	$where = 'WHERE id='.$_POST['nvp_id'];
	
	$result=q_update("shop_items_nvp", $data, $where, $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($result) != 1)
	{
		$xml .=	'<Error>Beim Speichern der Änderungen ist ein Fehler aufgetreten.</Error>';
	}

	print $xml;
?>