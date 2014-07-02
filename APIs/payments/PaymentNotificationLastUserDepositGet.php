<?php

	//SOA2 Service
	
	//INCLUDE CONSTANTS
//	include_once("constants.php");
	
	$required=array("userid" => "numeric");
	
	//check_man_params($required);


	$res_userdeposit = q("SELECT * FROM payment_notifications4 WHERE user_id = ".$_POST["userid"]." ORDER BY id_PN DESC LIMIT 1", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_userdeposit)==0)
	{
		//KEIN EINTRAG GEFUNDEN - > OLD DEPOSIT = 0
		$user_deposit=0;
	}
	else
	{
		$row_userdeposit = mysqli_fetch_assoc($res_userdeposit);
		$user_deposit = $row_userdeposit["user_deposit_EUR"];
	}


	//SERVICE OUTPUT
	echo '<user_deposit><![CDATA['.$user_deposit.']]></user_deposit>'."\n";

?>