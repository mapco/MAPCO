<?php

	//SOA2 Service
	
	//INCLUDE CONSTANTS
	//include_once("constants.php");

	$required=array("orderid" => "numericNN");
	
	check_man_params($required);

	$ordertotal = 0;
	$res_ordertotal = q("SELECT * FROM payment_notifications4 WHERE notification_type = 2 AND order_id = ".$_POST["orderid"]." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_ordertotal)!=0)
	{
		$row_ordertotal = mysqli_fetch_assoc($res_ordertotal);
		$ordertotal = $row_ordertotal["total"];
	}

	//SERVICE OUTPUT
	echo '<ordertotal>'.$ordertotal.'</ordertotal>'."\n";

?>