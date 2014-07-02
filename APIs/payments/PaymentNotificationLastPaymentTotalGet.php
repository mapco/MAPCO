<?php

	//SOA2 Service
	
	//INCLUDE CONSTANTS
	//include_once("constants.php");
	
	$required=array("transactionID" => "textNN");
	
	check_man_params($required);

	define("PN_Table", "payment_notifications4");

	$payment_total = 0;
	$res_paymentdeposit = q("SELECT * FROM ".PN_Table." WHERE paymentTransactionID ='".$transactionID."' AND (notification_type = 1 OR notification_type = 4) ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_paymentdeposit)==0)
	{
		$payment_total = 0;
		$has_total = "false";
	}
	else
	{
		$row_paymentdeposit = mysqli_fetch_assoc($res_paymentdeposit);
		$payment_total = $row_paymentdeposit["total"];
		$has_total = "true";
	}
	

	//SERVICE OUTPUT
	echo '<has_total>'.$has_total.'</has_total>'."\n";
	echo '<payment_total>'.$payment_total.'</payment_total>'."\n";

?>