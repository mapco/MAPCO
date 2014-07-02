<?php

	define("PN_Table", "payment_notifications4");
	define("PNM_Table", "payment_notification_messages4");

	include ("config.php");

	//ERHALTENE DATEN SPEICHERN
	$message="";
	foreach ($_POST as $key => $value) 
	{
		//$value = urlencode(stripslashes($value));
		$value=urldecode($value);
		
		if ($message!="") $message.='&';
		
		$message.= $key.'='.$value;
	}
	
		//PAYMENTTYPE
	switch ($_POST["PaymentType"]*1)
	{
		/*
		case 1: $PAYMENTTYPE="Kreditkarte"; $payment_type_id=5; break;	
		case 2: $PAYMENTTYPE="Lastschrift"; $payment_type_id=0; break;
		case 3: $PAYMENTTYPE="Sofortüberweisung"; $payment_type_id=6; break;				
		default: $PAYMENTTYPE=$PaymentType
		*/
		case 1: $payment_type_id=5; break;	
		case 2: $payment_type_id=0; break;
		case 3: $payment_type_id=6; break;		
		
		default: echo "Unbekannte Zahlart von PayGenic übermittelt"; exit;		
		
	}

	
	//SCHREIBE NACHRICHT IN PNMESSAGES
	$insert_data=array();
	$insert_data["message"]=$message;
	$insert_data["date_received"]=time();
	$insert_data["processed"]=0;
	$insert_data["checked"]="";
	$insert_data["payment_type_id"]=$payment_type_id;
	$insert_data["ipn_track_id"]="";

	$res_insert = q_insert(PNM_Table, $insert_data, $dbshop, __FILE__, __LINE__);

	$id = mysqli_insert_id($dbshop);


	
	$postfields["API"]="payments";
	$postfields["APIRequest"]="PaymentsNotificationSet_Paygenic";
	$postfields["usertoken"]="merci2664";
	//$postfields["ipn_track_id"]=$ipn_track_id;
	$postfields["id"]=$id;
	$responseXML=post(PATH."soa2/", $postfields);
	
	try
	{
		$response = new SimpleXMLElement($responseXML);
	}
	catch(Exception $e)
	{
		//XML FEHLERHAFT
		echo "XMLERROR".$responseXML;
		//show_error(9756, 7, __FILE__, __LINE__, $responseXML);
		//exit;
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);
	if ($response->Ack[0]=="Success")
	{
		$update_data=array();
		$update_data["processed"]=1;
		//q_update("payment_notification_messages4", $update_data, "ipn_track_id = '".$ipn_track_id."'", $dbshop, __FILE__, __LINE__);
		q_update(PNM_TABLE, $update_data, "id = ".$id, $dbshop, __FILE__, __LINE__);
	}




?>