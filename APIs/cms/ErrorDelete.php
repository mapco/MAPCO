<?php
	/*************************
	********** SOA 2 *********
	*************************/

	$required=array("id_errorcode"	=> "numeric");
	
	check_man_params($required);

	$xml ='';
	
	$id_errorcode = $_POST["id_errorcode"];

	$sql = 'DELETE FROM cms_errors WHERE error_id='.$id_errorcode;
	$result = q($sql,$dbweb, __FILE__, __LINE__);
	//$xml .= '	<geloescht>'.$sql.'</geloescht>'."\n";
	
	echo $xml;

?>