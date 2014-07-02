<?php

	/*************************
	********** SOA 2 *********
	*************************/

	$required=array("title"	=> "text");
	
	check_man_params($required);

	$data['title'] = $_POST["title"];

	$result = q_insert('cms_errortypes', $data, $dbweb, __FILE__, __LINE__);
	$id_errortype = mysqli_insert_id($dbweb);
	
	$xml =  '<errortype_id>'.$id_errortype.'</errortype_id>'."\n";
	//$xml =  '<errortype_id>'.$data['title'].'</errortype_id>'."\n";

	echo $xml;

?>