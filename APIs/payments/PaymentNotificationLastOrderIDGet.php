<?php

	//SOA2 Service
	
	//INCLUDE CONSTANTS
	include_once("constants.php");
	
	$required=array("orderid" => "numericNN");
	
	check_man_params($required);

	$res_id_PN = q("SELECT * FROM ".PN_Table." WHERE notification_type = 2 AND order_id = ".$_POST["orderid"]." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_id_PN)!=0)
	{
		$row_id_PN = mysqli_fetch_assoc($res_id_PN);
		$id_PN=$row_id_PN["id_PN"];
	}
	else
	{
		$id_PN=0;
	}

	//SERVICE OUTPUT
	echo '<íd_PN>'.$íd_PN.'</íd_PN>'."\n";

?>