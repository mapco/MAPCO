<?php

	/*************************
	********** SOA 2 *********
	*************************/

	$required=array("error_type_id"	=> "numeric",
					"type"	=> "text",
					"shortMsg"	=> "text",
					"longMsg"	=> "text"
					);
	
	check_man_params($required);

	$dummy_data['errorcode'] = 0;
	
	$data['errortype_id'] = $_POST["error_type_id"];
	$data['type'] = $_POST["type"];
	$data['shortMsg'] = $_POST["shortMsg"];
	$data['longMsg'] = $_POST["longMsg"];

	$result = q_insert('cms_errorcodes', $dummy_data, $dbweb, __FILE__, __LINE__);
	$data['errorcode'] = mysqli_insert_id($dbweb);
	
	$where = 'WHERE id='.$data['errorcode'];
	
	$result = q_update('cms_errorcodes', $data, $where, $dbweb, __FILE__, __LINE__);
	
	$xml =  '<errorcode_id>'.$data['errorcode'].'</errorcode_id>'."\n";

	echo $xml;

?>