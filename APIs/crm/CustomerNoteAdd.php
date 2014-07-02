<?php

	/*************************
	********** SOA 2 *********
	*************************/
	
	check_man_params(array("customer_id" => "numericNN", "note" => "text"));
	
	q("INSERT INTO crm_customer_notes (customer_id, note, firstmod, firstmod_user, lastmod, lastmod_user) VALUES (".$_POST["customer_id"].", '".mysqli_real_escape_string($dbweb,$_POST["note"])."', ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbweb, __FILE__, __LINE__);
	$insert_id = mysqli_insert_id($dbweb);
	
	print '<insert_id>'.$insert_id.'</insert_id>'."\n";

?>