<?php

	/*************************
	********** SOA 2 *********
	*************************/
	
	$required=array("code_mail_id" => "numeric");
	check_man_params($required);
	
	q('DELETE FROM `cms_errorcodes_mails` WHERE id='.$_POST['code_mail_id'], $dbweb , __FILE__, __LINE__);

	$xml .= '<Message>'.mysqli_num_rows($dbweb).'</Message>';
	echo $xml;

?>