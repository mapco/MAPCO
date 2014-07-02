<?php

	function save_order_event($eventtype_id, $order_id, $data)
	{
		global $dbshop;
		//CREATE XML FROM DATA
		$xml='<data>';
		foreach ($data as $key => $val)
		{
			$xml.='<'.$key.'>';
			if (!is_numeric($val)) $xml.='<![CDATA['.$val.']]>'; else $xml.=$val;
			$xml.='</'.$key.'>';
			
		}
		$xml.='</data>';
		
		//SAVE EVENT
		q("INSERT INTO shop_orders_events (
			order_id, 
			eventtype_id, 
			data, 
			firstmod, 
			firstmod_user
		) VALUES (
			".$order_id.",
			".$eventtype_id.",
			'".mysqli_real_escape_string($dbshop, $xml)."',
			".time().",
			".$_SESSION["id_user"]."
		);", $dbshop, __FILE__, __LINE__);
		
		return mysqli_insert_id($dbshop);
	}


	$required=array("SELECTOR_id" => "numericNN");
	check_man_params($required);					

	//CHECK IF ENTRY EXISTS
	$res_check=q("
		SELECT * 
		FROM shop_orders_items 
		WHERE id = " . $_POST["SELECTOR_id"] . ";", $dbshop, __FILE__, __LINE__);
		if (mysqli_fetch_array($res_check) == 0)
		{
			//>>>>>>>>>>> F E H L E R
					exit;
			//>>>>>>>>>>> F E H L E R
		}

	//get TableStructure
	//build SQL-Statement
	$data=array();
	$nvp="";
	$selector="";
	$res_struct=q("SHOW COLUMNS FROM shop_orders_items;", $dbshop, __FILE__, __LINE__);
	while($struct=mysqli_fetch_assoc($res_struct))
	{
		if ($struct["Extra"]!="auto_increment")
		{
			if (isset($_POST[$struct["Field"]]))
			{
				//DATA FOR ORDEREVENT
				$data[$struct["Field"]]=$_POST[$struct["Field"]];

				if ($nvp!="") $nvp.=", ";
				$nvp.=$struct["Field"]." = '".mysqli_real_escape_string($dbshop, $_POST[$struct["Field"]])."'";
				
			}
			elseif(isset($_POST["SELECTOR_".$struct["Field"]]))
			{
				//DATA FOR ORDEREVENT
				$data["SELECTOR_".$struct["Field"]]=$_POST["SELECTOR_".$struct["Field"]];

				if ($selector!="") $selector.= " AND ";
				$selector.=$struct["Field"]." = '".$_POST["SELECTOR_".$struct["Field"]]."'";
			}
		}
		elseif(isset($_POST["SELECTOR_".$struct["Field"]]))
		{
			//DATA FOR ORDEREVENT
			$data["SELECTOR_".$struct["Field"]]=$_POST["SELECTOR_".$struct["Field"]];

			if ($selector!="") $selector.= " AND ";
			$selector.=$struct["Field"]." = '".$_POST["SELECTOR_".$struct["Field"]]."'";
		}
	
	}
	
	//CHECK IF THERE IS ANYTHING TO UPDATE
	if ($nvp!="" && $selector!="")
	{
		//UPDATE
		$sql="UPDATE shop_orders_items SET ".$nvp." WHERE ".$selector.";";

		$res_update=q($sql,$dbshop, __FILE__, __LINE__);
		$affected_rows=mysqli_affected_rows();
	
		//GET order_id
		$orderid=0;
		$res_check=q("SELECT * FROM shop_orders_items WHERE id = ".$_POST["SELECTOR_id"].";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_check)!=0)
		{
			$row_order=mysqli_fetch_array($res_check);
			$orderid=$row_order["order_id"];
		}

		//SET ORDEREVENT
		$id_event=save_order_event(4, $orderid, $data);

		//SERVICE RESPONSE		
		echo '	<affected_rows>'.$affected_rows.'</affected_rows>'."\n";
		echo '	<id_event>'.$id_event.'</id_event>'."\n";
		
		
		if(!isset($_POST["mode"]) || (isset($_POST["mode"]) && $_POST["mode"]!="ebay"))
		{
/*
			//GET "MOTHER" ORDER
			$res_m_order=q("SELECT * FROM shop_orders WHERE id_order = ".$orderid, $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_m_order)==0)
			{
				//show_error();
			}
			$row_m_order = mysqli_fetch_assoc($res_m_order);
			if ($row_m_order["combined_with"]>0) 
			{
				$order_id = $row_m_order["combined_with"];
			}
			else
			{
				$order_id = $orderid;
			}
	*/		
			
			//CALL PAYMENTNOTIFICATIONHANDLER
			$postfields["API"]="payments";
			$postfields["APIRequest"]="PaymentNotificationHandler";
			$postfields["mode"]="OrderAdjustment";
			$postfields["orderid"]=$orderid;
			$postfields["order_event_id"]=$id_event;
			
			$responseXML=post(PATH."soa2/", $postfields);
			
			$use_errors = libxml_use_internal_errors(true);
			try
			{
				$response = new SimpleXMLElement($responseXML);
			}
			catch(Exception $e)
			{
				//XML FEHLERHAFT
			//	echo "XMLERROR".$responseXML;
				//show_error(9756, 7, __FILE__, __LINE__, $responseXML);
				//exit;
			}
			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);
			if ($response->Ack[0]=="Success")
			{
			}
			else
			{
				//show_error();
			}
		}

	}
	else
	{	
		//SERVICE RESPONSE		
		echo '	<affected_rows>0</affected_rows>'."\n";
		echo '	<id_event>0</id_event>'."\n";
	}


?>