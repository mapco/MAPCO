<?php

	function save_order_event($eventtype_id, $order_id, $data)
	{
		global $dbshop;
		//CREATE XML FROM DATA
		$xml = '<data>';
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

	check_man_params(array("mode" => "text"));

	
	if ($_POST["mode"]=="new" || $_POST["mode"]=="ebay")
	{
		$required=array("order_id" =>"numericNN", 
						"item_id" =>"numeric", 
						"amount" => "numeric", 
						"price" =>"numeric", 
						"netto" => "numeric", 
						"Currency_Code" => "currency", 
						"exchange_rate_to_EUR" => "numeric");

		check_man_params($required);					
	}
	
	if ($_POST["mode"] == "copy") {
		$required=array("id" => "numericNN"); 
		check_man_params($required);					
	}

	if ($_POST["mode"]=="new") {
		
		//INSERT DATA
		//get TableStructure
		$data=array();
		$fields="";
		$values="";
		$res_struct=q("SHOW COLUMNS FROM shop_orders_items;", $dbshop, __FILE__, __LINE__);
		while($struct=mysqli_fetch_assoc($res_struct))
		{
			if ($struct["Extra"]!="auto_increment")
			{
				if (isset($_POST[$struct["Field"]]))
				{
					//DATA FOR ORDEREVENT
					$data[$struct["Field"]]=$_POST[$struct["Field"]];

					if ($fields!="") $fields.=", ";
					$fields.=$struct["Field"];
					
					if ($values!="") $values.=", ";
					$values.="'".mysqli_real_escape_string($dbshop, $_POST[$struct["Field"]])."'";
				}
			}
		
		}
		
		$sql="INSERT INTO shop_orders_items (".$fields.") VALUES (".$values.");";
		
		$res_ins=q($sql,$dbshop, __FILE__, __LINE__);
		$id_orderitem=mysqli_insert_id($dbshop);
		
		//CHECK IF ARTICLES HAS COLLATERAL
			// GET ITEM 
			$res_item = q("SELECT * FROM shop_orders_items WHERE id = ".$id_orderitem, $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_item)==0)
			{
				// KEIN ITEM MIT ID GEFUNDEN
				show_error(9797,8,__FILE__, __LINE__, 'no Items found with id');
				exit;
			}
			else
			{
				$item = mysqli_fetch_assoc($res_item);
				//CHECK for collateral	
				$res_collateral = q("SELECT * FROM shop_items WHERE id_item = ".$item["item_id"], $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_collateral)==0)
				{
					//KEIN ITEM IN SHOP_ITEMS GEFUNDEN
					show_error(9797,8,__FILE__, __LINE__, 'no Items found in shop items');
					exit;
				}
				else
				{
					//GET COLLETERAL
					$collateral=mysqli_fetch_assoc($res_collateral);					
						
					if ($collateral["collateral"]>0)
					{
						//GET VAT
						$res_vat = q("SELECT * FROM shop_orders WHERE id_order = ".$_POST["order_id"], $dbshop, __FILE__, __LINE__);
						if (mysqli_num_rows($res_vat)==0)
						{
							// ORDER NICHT GEFUNDEN
							show_error(9797,8,__FILE__, __LINE__, 'Order not found');
							exit;
						} else {
							$row_vat = mysqli_fetch_assoc($res_vat);	
							if ($row_vat["VAT"] > 0) {
								$vat = ($row_vat["VAT"]/100)+1;
							} else {
								$vat = 0;
							}
						}

						$netto = $collateral["collateral"]*$item["exchange_rate_to_EUR"];
						if ($vat == 0)
						{
							$price = $collateral["collateral"]*$item["exchange_rate_to_EUR"];
						}
						else
						{
							$price = $collateral["collateral"]*$item["exchange_rate_to_EUR"]*$vat;
						}
						
						$netto*=$item["amount"];
						$price*=$item["amount"];
						
						//CHECK FOR ALREADY EXISTING ORDERITEM 29999/1 (COLLATERAL)
						$res_check_collateral = q("
							SELECT * 
							FROM shop_orders_items 
							WHERE item_id = 28093 
							AND order_id = " . $_POST["order_id"], $dbshop, __FILE__, __LINE__);
						while ($row_check_collateral = mysqli_fetch_assoc($res_check_collateral))
						{
							if ($row_check_collateral["exchange_rate_to_EUR"] == $item["exchange_rate_to_EUR"] && !isset($check_collateral))
							{
								$check_collateral = array();
								$check_collateral["id"] = $row_check_collateral["id"];
								$check_collateral["price"] = $row_check_collateral["price"];
								$check_collateral["netto"] = $row_check_collateral["netto"];
							}
						}
						
						if (isset($check_collateral))
						{
							$price+=$check_collateral["price"];
							$netto+=$check_collateral["netto"];
							$update_field = array();
							$update_field["price"] = $price;
							$update_field["netto"] = $netto;
							q("UPDATE shop_orders_items SET price = ".$price.", netto = ".$netto." WHERE id = ".$check_collateral["id"], $dbshop, __FILE__, __LINE__);
						}
						else
						{
							//INSERT ADDITIONAL ORDERITEM 29999/1 (COLLATERAL) 
							$insert_field = array();
							$insert_field["order_id"] = $_POST["order_id"];
							$insert_field["foreign_transactionID"] = "";
							$insert_field["item_id"] = 28093; // item_id von 29999/1
							$insert_field["amount"] = 1;
							$insert_field["price"] = $price;
							$insert_field["netto"] = $netto;
							$insert_field["collateral"] =0;
							$insert_field["Currency_Code"] = $item["Currency_Code"];
							$insert_field["exchange_rate_to_EUR"] = $item["exchange_rate_to_EUR"];
							$insert_field["customer_vehicle_id"] = $item["customer_vehicle_id"];
							$insert_field["checked"] = $item["checked"];
							$insert_field["ckecked_by_user"] =$item["ckecked_by_user"];
						
							$res_insert = q_insert("shop_orders_items", $insert_field, $dbshop, __FILE__, __LINE__);
						}
					} // IF COLLATERAL > 0
				}
			}
		
		$data["id"]=$id_orderitem;
		
		//SET ORDEREVENT
		$id_event=save_order_event(2, $_POST["order_id"], $data);

		//SERVICE RESPONSE
		echo '	<id_orderItem>'.$id_orderitem.'</id_orderItem>'."\n";
		echo '	<id_event>'.$id_event.'</id_event>'."\n";

/*
		//GET "MOTHER" ORDER
		$res_m_order=q("SELECT * FROM shop_orders WHERE id_order = ".$_POST["order_id"], $dbshop, __FILE__, __LINE__);
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
			$order_id = $_POST["order_id"];
		}
*/
		//CALL PAYMENTNOTIFICATIONHANDLER
		$postfields["API"]="payments";
		$postfields["APIRequest"]="PaymentNotificationHandler";
		$postfields["mode"]="OrderAdjustment";
		$postfields["orderid"]=$_POST["order_id"];
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
			//echo "XMLERROR".$responseXML;
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
	
	if ($_POST["mode"]=="copy")
	{
		//GET DATA
		$res_data=q("SELECT * FROM shop_orders_items WHERE id = ".$_POST["id"].";", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_data)==0)
		{
			echo '<OrderItemAddResponse>'."\n";
			echo '	<Ack>Failure</Ack>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>Zu kopierendes OrderItem nicht gefunden</shortMsg>'."\n";
			echo '		<longMsg>Kein OrderItem zur ID '.$_POST["id"].' gefunden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</OrderItemAddResponse>'."\n";
			exit;
		}
		$itemolddata=mysqli_fetch_array($res_data);
		
		//INSERT DATA
			//get TableStructure
		$data=array();
		$fields="";
		$values="";
		$res_struct=q("SHOW COLUMNS FROM shop_orders_items;", $dbshop, __FILE__, __LINE__);
		while($struct=mysqli_fetch_assoc($res_struct))
		{
			if ($struct["Extra"]!="auto_increment")
			{
				// IF SET POST DATA 
				if (isset($_POST[$struct["Field"]]))
				{
					//DATA FOR ORDEREVENT
					$data[$struct["Field"]]=$_POST[$struct["Field"]];

					if ($fields!="") $fields.=", ";
					$fields.=$struct["Field"];
					
					if ($values!="") $values.=", ";
					$values.="'".mysqli_real_escape_string($dbshop, $_POST[$struct["Field"]])."'";
				}
				// ELSE USE DATA FROM COPY
				else	
				{
					//DATA FOR ORDEREVENT
					$data[$struct["Field"]]=$_POST[$struct["Field"]];

					if ($fields!="") $fields.=", ";
					$fields.=$struct["Field"];
					
					if ($values!="") $values.=", ";
					$values.="'".mysqli_real_escape_string($dbshop, $itemolddata[$struct["Field"]])."'";
				}
			}
		
		}
		
		$sql="INSERT INTO shop_orders_items (".$fields.") VALUES (".$values.");";
		
		$res_ins = q($sql,$dbshop, __FILE__, __LINE__);
		$id_orderitem = mysqli_insert_id($dbshop);

		$data["id"] = $id_orderitem;

		if (isset($_POST["order_id"]) && $_POST["order_id"]!=0)
		{
			$orderid=$_POST["order_id"];
		}
		else
		{
			$orderid=$itemolddata["order_id"];
		}

		//SET ORDEREVENT
		$id_event = save_order_event(2, $orderid, $data);

		//SERVICE RESPONSE
		echo '	<id_orderItem>'.$id_orderitem.'</id_orderItem>'."\n";
		echo '	<id_event>'.$id_event.'</id_event>'."\n";
		
		//CALL PAYMENTNOTIFICATIONHANDLER
		
		if (isset($_POST["order_id"]) && $_POST["order_id"]!=0)
		{
			$orderid=$_POST["order_id"];
		}
		else
		{
			$orderid=$itemolddata["order_id"];
		}
		
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
			//echo "XMLERROR".$responseXML;
			//show_error(9756, 7, __FILE__, __LINE__, $responseXML);
			//exit;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		
		if ($response->Ack[0] != "Success")
		{
			show_error(9797,8,__FILE__, __LINE__, $response);
			exit;
		}
	}

	if ($_POST["mode"] == "ebay")
	{
		//INSERT DATA
			//get TableStructure
		$data=array();
		$fields="";
		$values="";
		$res_struct=q("SHOW COLUMNS FROM shop_orders_items;", $dbshop, __FILE__, __LINE__);
		while($struct=mysqli_fetch_assoc($res_struct))
		{
			if ($struct["Extra"]!="auto_increment")
			{
				if (isset($_POST[$struct["Field"]]))
				{
					//DATA FOR ORDEREVENT
					$data[$struct["Field"]]=$_POST[$struct["Field"]];

					if ($fields!="") $fields.=", ";
					$fields.=$struct["Field"];
					
					if ($values!="") $values.=", ";
					$values.="'".mysqli_real_escape_string($dbshop, $_POST[$struct["Field"]])."'";
				}
			}
		
		}
		
		$sql="INSERT INTO shop_orders_items (".$fields.") VALUES (".$values.");";
		
		$res_ins=q($sql,$dbshop, __FILE__, __LINE__);
		$id_orderitem=mysqli_insert_id($dbshop);
		
		$data["id"]=$id_orderitem;
		
		//SET ORDEREVENT
		$id_event=save_order_event(2, $_POST["order_id"], $data);

		
		//SERVICE RESPONSE
		echo '	<id_orderItem>'.$id_orderitem.'</id_orderItem>'."\n";
		echo '	<id_event>'.$id_event.'</id_event>'."\n";
	}

	if ($_POST["mode"]=="shop")
	{
		//INSERT DATA
			//get TableStructure
		$data=array();
		$fields="";
		$values="";
		$res_struct=q("SHOW COLUMNS FROM shop_orders_items;", $dbshop, __FILE__, __LINE__);
		while($struct=mysqli_fetch_assoc($res_struct))
		{
			if ($struct["Extra"]!="auto_increment")
			{
				if (isset($_POST[$struct["Field"]]))
				{
					//DATA FOR ORDEREVENT
					$data[$struct["Field"]]=$_POST[$struct["Field"]];

					if ($fields!="") $fields.=", ";
					$fields.=$struct["Field"];
					
					if ($values!="") $values.=", ";
					$values.="'".mysqli_real_escape_string($dbshop, $_POST[$struct["Field"]])."'";
				}
			}
		
		}
		
		$sql="INSERT INTO shop_orders_items (".$fields.") VALUES (".$values.");";
		
		$res_ins=q($sql,$dbshop, __FILE__, __LINE__);
		$id_orderitem=mysqli_insert_id($dbshop);
		
		$data["id"]=$id_orderitem;


		//CHECK IF ARTICLES HAS COLLATERAL
			// GET ITEM 
			$res_item = q("SELECT * FROM shop_orders_items WHERE id = ".$id_orderitem, $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_item)==0)
			{
				// KEIN ITEM MIT ID GEFUNDEN
				show_error(9797,8,__FILE__, __LINE__, 'no Item found with id');
				exit;
			}
			else
			{
				$item = mysqli_fetch_assoc($res_item);
				//CHECK for collateral	
				$res_collateral = q("SELECT * FROM shop_items WHERE id_item = ".$item["item_id"], $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_collateral)==0)
				{
					//KEIN ITEM IN SHOP_ITEMS GEFUNDEN
					show_error(9797,8,__FILE__, __LINE__, 'no Items found in shop items');
					exit;
				}
				else
				{
					//GET COLLETERAL
					$collateral=mysqli_fetch_assoc($res_collateral);					
						
					if ($collateral["collateral"]>0)
					{
						//GET VAT
						$res_vat = q("SELECT * FROM shop_orders WHERE id_order = ".$_POST["order_id"], $dbshop, __FILE__, __LINE__);
						if (mysqli_num_rows($res_vat) == 0) {
							// ORDER NICHT GEFUNDEN
							show_error(9797,8,__FILE__, __LINE__, 'Order not found');
							exit;
						} else {
							$row_vat = mysqli_fetch_assoc($res_vat);	
							if ($row_vat["VAT"] > 0) {
								$vat = ($row_vat["VAT"]/100)+1;
							} else {
								$vat = 0;
							}
						}

						$netto = $collateral["collateral"]*$item["exchange_rate_to_EUR"];
						if ($vat == 0) {
							$price = $collateral["collateral"]*$item["exchange_rate_to_EUR"];
						} else {
							$price = $collateral["collateral"]*$item["exchange_rate_to_EUR"]*$vat;
						}
						
						$netto*=$item["amount"];
						$price*=$item["amount"];
						
						//CHECK FOR ALREADY EXISTING ORDERITEM 29999/1 (COLLATERAL)
						$res_check_collateral = q("
							SELECT * 
							FROM shop_orders_items 
							WHERE item_id = 28093 
							AND order_id = " . $_POST["order_id"], $dbshop, __FILE__, __LINE__);
						while ($row_check_collateral = mysqli_fetch_assoc($res_check_collateral))
						{
							if ($row_check_collateral["exchange_rate_to_EUR"] == $item["exchange_rate_to_EUR"] && !isset($check_collateral))
							{
								$check_collateral = array();
								$check_collateral["id"] = $row_check_collateral["id"];
								$check_collateral["price"] = $row_check_collateral["price"];
								$check_collateral["netto"] = $row_check_collateral["netto"];
							}
						}
						
						if (isset($check_collateral))
						{
							$price+=$check_collateral["price"];
							$netto+=$check_collateral["netto"];
							$update_field = array();
							$update_field["price"] = $price;
							$update_field["netto"] = $netto;
							q("UPDATE shop_orders_items SET price = ".$price.", netto = ".$netto." WHERE id = ".$check_collateral["id"], $dbshop, __FILE__, __LINE__);
						}
						else
						{
							//INSERT ADDITIONAL ORDERITEM 29999/1 (COLLATERAL) 
							$insert_field = array();
							$insert_field["order_id"] = $_POST["order_id"];
							$insert_field["foreign_transactionID"] = "";
							$insert_field["item_id"] = 28093; // item_id von 29999/1
							$insert_field["amount"] = 1;
							$insert_field["price"] = $price;
							$insert_field["netto"] = $netto;
							$insert_field["collateral"] =0;
							$insert_field["Currency_Code"] = $item["Currency_Code"];
							$insert_field["exchange_rate_to_EUR"] = $item["exchange_rate_to_EUR"];
							$insert_field["customer_vehicle_id"] = $item["customer_vehicle_id"];
							$insert_field["checked"] = $item["checked"];
							$insert_field["ckecked_by_user"] =$item["ckecked_by_user"];
							$res_insert = q_insert("shop_orders_items", $insert_field, $dbshop, __FILE__, __LINE__);
						}
					} // IF COLLATERAL > 0
				}
			}

		// SET ORDEREVENT
		//$id_event=save_order_event(2, $_POST["order_id"], $data);
		
		//SERVICE RESPONSE
		echo '	<id_orderItem>'.$id_orderitem.'</id_orderItem>'."\n";
		echo '	<id_event>'.$id_event.'</id_event>'."\n";
	}