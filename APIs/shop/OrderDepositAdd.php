<?php

	include("../functions/mapco_gewerblich.php");

	check_man_params(array("order_id" => "numericNN"));
	
	$gross_total=0;
	$results=q("SELECT * FROM shop_orders WHERE id_order=".$_POST["order_id"]." AND discount_event=0 AND firstmod>=1382306400 AND firstmod<=1385333999;", $dbshop, __FILE__, __LINE__);
	if(mysqli_num_rows($results)>0)
	{
		$row=mysqli_fetch_array($results);
		$results6=q("SELECT * FROM cms_users WHERE id_user=".$row["customer_id"].";", $dbweb, __FILE__, __LINE__);
		$row6=mysqli_fetch_array($results6);
		if(!gewerblich($row["customer_id"]) && $row6["shop_id"]==8 && $row6["origin"]=="DE")
		{
			$results2=q("SELECT * FROM shop_orders_items WHERE order_id=".$_POST["order_id"].";", $dbshop, __FILE__, __LINE__);
			while($row2=mysqli_fetch_array($results2))
			{
				$gross_total+=$row2["amount"]*$row2["price"];
			}
			if($gross_total>=79)
			{
				$results3=q("SELECT * FROM shop_user_deposit WHERE user_id=".$row["customer_id"].";", $dbshop, __FILE__, __LINE__);
				if(mysqli_num_rows($results3)>0)
				{
					$row3=mysqli_fetch_array($results3);
					$results4=q("UPDATE shop_user_deposit SET deposit=".round(($row3["deposit"]+($gross_total/10)), 2)." WHERE user_id=".$row["customer_id"].";", $dbshop, __FILE__, __LINE__);
				}
				else
				{
					$results4=q("INSERT INTO shop_user_deposit (user_id, deposit) VALUES (".$row["customer_id"].", ".round(($gross_total/10), 2).");", $dbshop, __FILE__, __LINE__);
				}
				$results5=q("UPDATE shop_orders SET discount_event=1 WHERE id_order=".$_POST["order_id"].";", $dbshop, __FILE__, __LINE__);
			}
		}
	}

?>