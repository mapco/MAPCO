<?php

	//SOA2 Service
	
	//INCLUDE CONSTANTS
	//include_once("constants.php");
	
	$required=array("TransactionID" => "textNN");
	
	check_man_params($required);

	define("PN_Table", "payment_notifications4");

	$res_payment = q("SELECT * FROM ".PN_Table." WHERE paymentTransactionID ='".$_POST["TransactionID"]."' AND (notification_type = 1 OR notification_type = 4) ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_payment)==0)
	{
		$has_payment = false;
	}
	else
	{
		$row_payment = mysqli_fetch_assoc($res_payment);
		$has_payment = true;
	}
	
	//SERVICE OUTPUT
	if ($has_payment)
	{
		echo '	 <payment>'."\n";
		while (list ($key, $val) = each ($row_payment))	
		{
			echo '		<'.$key.'><![CDATA['.$val.']]></'.$key.'>'."\n";
		}
		echo '	</payment>'."\n";
	}
?>