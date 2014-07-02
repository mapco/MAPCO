<?php

	//SOA2 Service
	
	//INCLUDE CONSTANTS
	//include_once("constants.php");
	
	$required=array("orderid" => "numericNN");
	
	check_man_params($required);

	$res_orderdeposit = q("SELECT * FROM payment_notifications4 WHERE (notification_type = 2 OR notification_type = 5) AND order_id = ".$_POST["orderid"]." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_orderdeposit)!=0)
	{
		$row_orderdeposit = mysqli_fetch_assoc($res_orderdeposit);
		$orderdeposit=$row_orderdeposit["deposit_EUR"];
	}
	else
	{
		$orderdeposit=0;
	}

	//SERVICE OUTPUT
	echo '<orderdeposit>'.$orderdeposit.'</orderdeposit>'."\n";

?>