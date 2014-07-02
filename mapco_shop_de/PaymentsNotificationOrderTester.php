<?php

	include("config.php");
	
	
	$postfields=array();
	$postfields["API"]="payments";
	$postfields["APIRequest"]="PaymentNotificationHandler";
	$postfields["mode"]="OrderAdd";
	
	//GET ORDEREVENTS
	$res_ev = q("SELECT id_event, order_id FROM shop_orders_events WHERE eventtype_id = 1", $dbshop, __FILE__, __LINE__);
	while ($rwo_ev=mysqli_fetch_assoc($res_ev))
	{
		$event[$row_vev["order_id"]] = $row_ev["id_event"];
	}
	
	//GET ORDERS FROM SHOP_ORDERS
	$res = q("SELECT id_order FROM shop_orders WHERE id_order > 1783510", $dbshop, __FILE__, __LINE__);
	while ($row = mysqli_fetch_assoc($res))
	{
		
		// CALL PAYMENTNOTIFICATIONHANDLER ORDERADD
		$postfields["orderid"]=$row["id_order"];
		if (isset($event[$row["id_order"]])) $ev_id = $event[$row["id_order"]]; else $ev_id = 1;
		$postfields["order_event_id"] = $ev_id;
		
		$response = post(PATH."soa2/", $postfields);
		
		echo $row["id_order"]." ".$response."<br />";
	}
?>