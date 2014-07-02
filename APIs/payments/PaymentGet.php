<?php

	//SOA2 SERVICE
	
	define("PN_Table", "payment_notifications4");
	
	$required=array("TransactionID" => "textNN");
	check_man_params($required);
	
	
	$payment = array();
	$deposit = false;
	$total = false;
	$user_id = false;
	$totalEUR = 0;
	$orderids = array();

	if (isset($_POST["payment_type_id"]) && $_POST["payment_type_id"]!="" && $_POST["payment_type_id"]!=0)
	{
		$res=q("SELECT * FROM ".PN_Table." WHERE paymentTransactionID = '".$_POST["TransactionID"]."' AND payment_type_id = ".$_POST["payment_type_id"]." ORDER BY id_PN DESC", $dbshop, __FILE__, __LINE__);	
	}
	else
	{
		$res=q("SELECT * FROM ".PN_Table." WHERE paymentTransactionID = '".$_POST["TransactionID"]."' ORDER BY id_PN DESC", $dbshop, __FILE__, __LINE__);
	}
	

	while ($row=mysqli_fetch_assoc($res))
	{
		//define Payment_deposit
		if ($deposit===false)
		{
			$deposit = $row["deposit_EUR"];
		}
		//define Payment_total
		if ($total===false && $row["notification_type"]==1)
		{
			$total = $row["total"];	
			$totalEUR = round($row["total"]*(1/$row["exchange_rate_from_EUR"]),2);	
		}
		//define Payment User
		if ($user_id === false)
		{
			$user_id = $row["user_id"];
		}
		
		$payment[$row["id_PN"]] = $row;
		if ($row["notification_type"]==4 && $row["order_id"]==0 && $row["f_id"]!=0)
		{
			$res2 = q("SELECT * FROM ".PN_Table." WHERE f_id = ".$row["id_PN"], $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res2)>0)
			{
				$row2 = mysqli_fetch_assoc($res2);
				$payment[$row["id_PN"]]["order_id"] = $row2["order_id"];
			}
			if ($payment[$row["id_PN"]]["order_id"] == 0)
			{
				$res3=q("SELECT * FROM ".PN_Table." WHERE id_PN = ".$row["f_id"], $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res3)>0)
				{
					$row3 = mysqli_fetch_assoc($res3);
					$payment[$row["id_PN"]]["order_id"] = $row3["order_id"];
				}
			}
		}
		if ($payment[$row["id_PN"]]["order_id"]!=0) $orderids[$payment[$row["id_PN"]]["order_id"]]=1;
	}

	//define Payment_total
	if ($total===false)
	{
	//	show_error(9802, 9, __FILE__, __LINE__, "PaymentTransactionID ".$_POST["TransactionID"] );
		
	}
	else
	{
		//SERVICE RESPONSE
		echo '<PaymentTotal>'.$total.'</PaymentTotal>'."\n";
		echo '<PaymentTotalEUR>'.$totalEUR.'</PaymentTotalEUR>'."\n";
		echo '<Last_PaymentDeposit>'.$deposit.'</Last_PaymentDeposit>'."\n";
		echo '<PaymentUserID>'.$user_id.'</PaymentUserID>'."\n";
		echo '<accountings>'."\n'";
		foreach ($payment as $id_PN => $paymentdata)
		{
			echo '	 <accounting>'."\n";
			while (list ($key, $val) = each ($paymentdata))	
			{
				echo '		<'.$key.'><![CDATA['.$val.']]></'.$key.'>'."\n";
			}
			echo '	</accounting>'."\n";
		}
		echo '</accountings>'."\n";
		echo '<orders>'."\n";
		foreach($orderids as $orderid => $data)
		{
			echo '<order_id>'.$orderid.'</order_id>'."\n";
		}
		echo '</orders>'."\n";
	}
?>