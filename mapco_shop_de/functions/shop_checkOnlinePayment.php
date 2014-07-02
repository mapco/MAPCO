<?php

	if (!function_exists("checkOnlinePayment"))
	{
		function checkOnlinePayment ($id_payment)
		{
			global $dbshop;
			$onlinepayment["selected"]=false;
			$i=0;
			$res=q("SELECT id_payment, payment FROM shop_payment WHERE shop_id=".$_SESSION["id_shop"]." AND payment = 'PayPal' OR payment = 'Kreditkarte' OR payment = 'Sofortüberweisung' OR payment = 'Lastschrift';",$dbshop, __FILE__, __LINE__);
			while ($row=mysqli_fetch_array($res)) {$payment_IDs[$i]=$row["id_payment"]; $payment_methods[$row["id_payment"]]=$row["payment"]; $i++;}	
			if (in_array($id_payment, $payment_IDs))
			{
				$onlinepayment["selected"]=true;
				$onlinepayment["method"]=$payment_methods[$id_payment];
			}
			return $onlinepayment;
		}
	}

?>