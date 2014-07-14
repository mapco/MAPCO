<?php

	if (!function_exists("checkOnlinePayment"))
	{
		function checkOnlinePayment ($id_payment)
		{
			global $dbshop;
			$onlinepayment["selected"]=false;
			$i=0;
			$res=q("SELECT * FROM shop_payment WHERE shop_id=".$_SESSION["id_shop"]." AND paymenttype_id IN (4,5,6)",$dbshop, __FILE__, __LINE__);
			while ($row=mysqli_fetch_array($res)) 
			{
				$payment_IDs[$i]=$row["id_payment"]; 
				$payment_methods[$row["id_payment"]]=$row["payment"]; 
				$payment_type_id[$row["id_payment"]]=$row["paymenttype_id"];
				$i++;
			}	
			if (in_array($id_payment, $payment_IDs))
			{
				$onlinepayment["selected"]=true;
				$onlinepayment["method"]=$payment_methods[$id_payment];
				$onlinepayment["paymenttype_id"]=$payment_type_id[$id_payment];
			}
			else
			{
				$onlinepayment["selected"]=false;
				$onlinepayment["method"]=$payment_methods[$id_payment];
				$onlinepayment["paymenttype_id"]=$payment_type_id[$id_payment];
			}
			return $onlinepayment;
		}
	}

?>