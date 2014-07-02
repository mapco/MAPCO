<?php

	check_man_params(array("id" => "numericNN"));
	
	$res = q("SELECT * FROM payment_notification_messages WHERE id = ".$_POST["id"], $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows( $res ) == 0)
	{
		exit;
	}
	
	$row = mysqli_fetch_assoc($res);
	
	$datafield = array();
	$datafield["message"] 			= str_replace("payment_status=Pending", "payment_status=Completed", $row["message"]);
	$datafield["date_received"] 	= $row["date_received"];
	$datafield["processed"] 		= 0;
	$datafield["checked"] 			= $row["checked"];
	$datafield["payment_type_id"] 	= $row["payment_type_id"];
	$datafield["ipn_track_id"]	 	= $row["ipn_track_id"];
	$datafield["processed2"] 		= $row["processed2"];
	
	q_insert("payment_notification_messages", $datafield, $dbshop, __FILE__, __LINE__);