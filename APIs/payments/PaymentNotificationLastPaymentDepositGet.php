<?php

	//SOA2 Service
	
	//INCLUDE CONSTANTS
	//include_once("constants.php");
	
	$required=array("transactionID" => "textNN");
	
	check_man_params($required);

	define("PN_Table", "payment_notifications4");

	$payment_deposit = 0;
	$res_paymentdeposit = q("SELECT * FROM ".PN_Table." WHERE paymentTransactionID ='".$transactionID."' AND (notification_type = 1 OR notification_type = 4) ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_paymentdeposit)==0)
	{
		$payment_deposit = 0;
		$has_deposit = "false";
	}
	else
	{
		$row_paymentdeposit = mysqli_fetch_assoc($res_paymentdeposit);
		$payment_deposit = $row_paymentdeposit["deposit_EUR"];
		$has_deposit = "true";
	}
	

	//SERVICE OUTPUT
	echo '<has_deposit>'.$has_deposit.'</has_deposit>'."\n";
	echo '<payment_deposit>'.$payment_deposit.'</payment_deposit>'."\n";

?>