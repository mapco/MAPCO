<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	$items=array();
	$res_items=q("SELECT * FROM shop_orders_items WHERE NOT customer_vehicle_id =0;", $dbshop, __FILE__, __LINE__);
	while ($row_items = mysqli_fetch_array($res_items))
	{
		if (isset($items[$row_items["order_id"]]))
		{
			$items[$row_items["order_id"]][sizeof($items[$row_items["order_id"]])]=$row_items["customer_vehicle_id"];
		}
		else
		{
			$items[$row_items["order_id"]][0]=$row_items["customer_vehicle_id"];
		}
	}

	$carfleet=array();
	$res_carfleet = q("SELECT * FROM shop_carfleet WHERE user_id = lastmod_user;", $dbshop, __FILE__, __LINE__);
	while ($row_carfleet=mysqli_fetch_array($res_carfleet))
	{
		if (isset($carfleet[$row_carfleet["user_id"]]))
		{
			$carfleet[$row_carfleet["user_id"]][sizeof($carfleet[$row_carfleet["user_id"]])]=$row_carfleet["id"];
		}
		else
		{
			$carfleet[$row_carfleet["user_id"]][0]=$row_carfleet["id"];
		}
	}
	
	$count_vehicles=0;
	$count_user=0;
	$count_mail=0;
	$count_ungesetzt=0;
	$count_OK=0;
	$res_orders=q("SELECT * FROM shop_orders WHERE shop_id = 3 AND NOT status_id=3 AND NOT status_id=2;", $dbshop, __FILE__, __LINE__);
	while ($row_orders=mysqli_fetch_array($res_orders))
	{
		if (isset($carfleet[$row_orders["customer_id"]]))
		{
			echo $row_orders["id_order"]."User: ".$row_orders["customer_id"]." Anzahl: ".sizeof($carfleet[$row_orders["customer_id"]]);
			if (isset($items[$row_orders["id_order"]]))
			{
				echo "OK";
				$count_OK++;
			}
			else
			{
				echo "UNGESETZT";
				$count_ungesetzt++;
			}
			echo "<br />";
				
			$count_vehicles+=sizeof($carfleet[$row_orders["customer_id"]]);
			$count_user++;
		}
		if ($row_orders["fz_fin_mail_lastsent"] > mktime(0,0,1,7,23,2013))	
		{
			if ($row_orders["fz_fin_mail_lastsent"] > mktime(0,0,1,7,24,2013) && $row_orders["fz_fin_mail_count"]>1)
			{
				$count_mail+=$row_orders["fz_fin_mail_count"]-1;
			}
			elseif ($row_orders["fz_fin_mail_lastsent"] > mktime(0,0,1,7,25,2013) && $row_orders["fz_fin_mail_count"]>2)
			{
				$count_mail+=$row_orders["fz_fin_mail_count"]-2;
			}
			else
			{
				$count_mail+=$row_orders["fz_fin_mail_count"];
			}
		}
	}

	echo "Fahrzeuge: ".$count_vehicles."<br />";
	echo "User: ".$count_user."<br />";
	echo "Mails: ".$count_mail."<br />";
	echo "OK: ".$count_OK."<br />";
	echo "Ungesetzt: ".$count_ungesetzt."<br />";

	include("templates/".TEMPLATE_BACKEND."/footer.php");

?>
