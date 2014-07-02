<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");
	
//	$res=q("SELECT * FROM payment_notification_messages order by id desc limit 3500, 3500;", $dbshop, __FILE__, __LINE__);

	$res=q("SELECT * FROM payment_notification_messages WHERE id = 36554", $dbshop, __FILE__, __LINE__);
	while ($row=mysqli_fetch_array($res))
	{
		$field=array();
		$Varfield=array();
		$field=explode("&", $row["message"] );
		echo $row["id"]." ".sizeof($field)."<br />";
		$tmp=array();
		for ($i=0; $i<sizeof($field); $i++)
		{
			$tmp=array();
			$tmp=explode("=", $field[$i]);
			//echo $i.":      ".$tmp[0]."-".$tmp[1]."<br />";
			if (isset($tmp[0]) && isset($tmp[1]))
			{
				$Varfield[$tmp[0]]=$tmp[1];
			}
			$Varfield["API"]="payments";
			$Varfield["Action"]="PaymentNotificationListener_PayPal2";

		}
		
		//print_r($Varfield);
		echo $responseXML=post(PATH."/soa/", $Varfield)."<br />";


	}
	
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>