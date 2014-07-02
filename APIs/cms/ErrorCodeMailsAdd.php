<?php

	/*************************
	********** SOA 2 *********
	*************************/
	
	$required=array("errorcode" => "numeric", "errortype" => "numeric", "mail" => "text");
	check_man_params($required);
	
	$data['errorcode_id'] = $_POST['errorcode'];
	$data['errortype_id'] = $_POST['errortype'];
	$data['mail'] = $_POST['mail'];

	q_insert('cms_errorcodes_mails', $data, $dbweb , __FILE__, __LINE__);
	$xml .= '<insert_id>'.mysqli_insert_id($dbweb).'</insert_id>';
	
	echo $xml;

?>