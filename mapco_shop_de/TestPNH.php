<?php
	include("config.php");
//	include("templates/".TEMPLATE_BACKEND."/header.php");
/*
	$res = q("SELECT * FROM payment_notification_messages3 WHERE id = 22566", $dbshop, __FILE__, __LINE__);
	
	$row = mysqli_fetch_assoc($res);
	
	$msg = $row["message"];
	
	$text = array();
	
	$text = explode("&", $msg);
	
	for ($i=1; $i<sizeof($text); $i++)
	{
		$zeile = explode("=", $text[$i]);
		if ($zeile[0]!="")
		{
			$_POST[$zeile[0]]=$zeile[1];	
		}
	}
*/	
	///print_r($_POST);
	$res=q("SELECT * FROM payment_notification_messages3 WHERE id >10000 AND id <12000", $dbshop, __FILE__, __LINE__);
	while ($row=mysqli_fetch_assoc($res))
	{
		echo	$response=post(PATH."soa/", array("API" => "crm", "Action" => "PaymentNotificationHandler3", "id" => $row["id"]))."<br />";
	}

//	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>