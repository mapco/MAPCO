<?php

	if ($_POST["OrderBy"]!="") $orderby=" ORDER BY ".$_POST["OrderBy"];
	if ($_POST["OrderBy"]!="" && $_POST["OrderDirection"]=="down") $orderby.=" DESC";
	
	$filter="";
	if ($_POST["FILTER_Platform"]>0)
	{
		$filter.=" AND shop_id = ".$_POST["FILTER_Platform"];
	}
	
	if ($_POST["FILTER_SearchFor"]>0 && $_POST["FILTER_Searchfield"]!="")
	{
		$searchForUserMail=false;
		$searchForUserName=false;
		$searchForName=false;
		$searchForAdresse=false;
		$searchForMPN=false;
		$searchForEbayItemID=false;
		$searchForPayPalID=false;
		

		switch ($_POST["FILTER_SearchFor"])
		{
			case 1: $searchForUserMail=true; break;
			case 2: $searchForUserName=true; break;
			case 3: $searchForName=true; break;
			case 4: $searchForAdresse=true; break;
			case 5: $searchForMPN=true; break;
			case 6: $searchForEbayItemID=true; break; 
			case 7: $searchForPayPalID=true; break;
		}
	}
	else {$searchForAll=true;}
	
	
	
	if ($_POST["date_from"]!="" && $_POST["date_to"]!="")
	{
		$rangestart= "firstmod>".strtotime($_POST["date_from"]);
		$rangeend=" AND firstmod<".(strtotime($_POST["date_to"])+86399);
	}
	else
	{
		//if ($searchForAll || (!$searchForAll && trim($_POST["FILTER_Searchfield"])==""))
		if ($searchForAll)
		{
			$rangestart="firstmod>".(time()-7776000); // Zeitpunkt vor 90 Tagen
		}
		else
		{
			$rangestart= "firstmod>0";
		}
		$rangeend=" AND firstmod<".time();
	}

	$xmldata="";
	$entriesCount=0;
	$from=($_POST["ResultPage"]-1)*$_POST["ResultRange"];
	$to=$from+$_POST["ResultRange"];


//ERMITTLE DATEN -------------------------------------------------------------------------------------------
//FILTER 0
	if ($searchForAll)
	{
		//$res=q("SELECT shop_id, foreign_order_id, crm_customer_id, id_order, usermail, bill_company, bill_firstname, bill_lastname, shipping_costs, firstmod, fz_fin_mail_count, fz_fin_mail_lastsent, shipping_details FROM shop_orders WHERE ".$rangestart.$rangeend.$filter.$orderby.$limit.";", $dbshop, __FILE__, __LINE__);
		$res=q("SELECT * FROM shop_orders WHERE ".$rangestart.$rangeend.$filter.$orderby.$limit.";", $dbshop, __FILE__, __LINE__);
		$k=0;
		while ($row=mysql_fetch_array($res))
		{
			$orders[$k]=$row;
			$orders[$k]["entry_pos"]=$k+1;
			/*
			$orders[$k]["shop_id"]=$row["shop_id"];
			//$orders[$k]["foreign_order_id"]=$row["foreign_order_id"];
			$orders[$k]["foreign_OrderID"]=$row["foreign_OrderID"];
			$orders[$k]["crm_customer_id"]=$row["crm_customer_id"];
			$orders[$k]["id_order"]=$row["id_order"];
			$orders[$k]["usermail"]=$row["usermail"];
			$orders[$k]["bill_company"]=$row["bill_company"];
			$orders[$k]["bill_firstname"]=$row["bill_firstname"];
			$orders[$k]["bill_lastname"]=$row["bill_lastname"];
			$orders[$k]["shipping_costs"]=$row["shipping_costs"];
			$orders[$k]["firstmod"]=$row["firstmod"];
			$orders[$k]["Payments_TransactionStateDate"]=$row["Payments_TransactionStateDate"];
			$orders[$k]["Payments_Type"]=$row["Payments_Type"];
			$orders[$k]["shipping_details"]=$row["shipping_details"];
			$orders[$k]["shipping_type_id"]=$row["shipping_type_id"];
			$orders[$k]["order_note"]=$row["order_note"];
			$orders[$k]["status_id"]=$row["status_id"];
			$orders[$k]["status_date"]=$row["status_date"];
			$orders[$k]["payments_type_id"]=$row["payments_type_id"];
			
			$orders[$k]["fz_fin_mail_count"]=$row["fz_fin_mail_count"];
			$orders[$k]["fz_fin_mail_lastsent"]=$row["fz_fin_mail_lastsent"];
			*/
			$entriesCount++;

			$k++;
		}
	}


//Filter  1
	if ($searchForUserMail)
	{
		/*
		$res_count=q("SELECT COUNT(id_order) FROM shop_orders WHERE ".$rangestart.$rangeend.";",$dbshop, __FILE__, __LINE__);
		$anz=mysql_fetch_row($res_count);
		$entriesCount=$anz[0];
		*/
		$qry_string=strtolower(trim($_POST["FILTER_Searchfield"]));
	
		//$res=q("SELECT shop_id, foreign_order_id, crm_customer_id, id_order, usermail, bill_company, bill_firstname, bill_lastname, shipping_costs, firstmod, fz_fin_mail_count, fz_fin_mail_lastsent, shipping_details FROM shop_orders WHERE ".$rangestart.$rangeend.$filter.$orderby.$limit.";", $dbshop, __FILE__, __LINE__);
		$res=q("SELECT * FROM shop_orders WHERE ".$rangestart.$rangeend.$filter.$orderby.$limit.";", $dbshop, __FILE__, __LINE__);
		$k=0;
		while ($row=mysql_fetch_array($res))
		{
			if (strpos(strtolower(" ".$row["usermail"]), $qry_string)>0 )
			{
				$orders[$k]=$row;
				$orders[$k]["entry_pos"]=$k+1;
				/*
				$orders[$k]["shop_id"]=$row["shop_id"];
				//$orders[$k]["foreign_order_id"]=$row["foreign_order_id"];
				$orders[$k]["foreign_OrderID"]=$row["foreign_OrderID"];
				$orders[$k]["crm_customer_id"]=$row["crm_customer_id"];
				$orders[$k]["id_order"]=$row["id_order"];
				$orders[$k]["usermail"]=$row["usermail"];
				$orders[$k]["bill_company"]=$row["bill_company"];
				$orders[$k]["bill_firstname"]=$row["bill_firstname"];
				$orders[$k]["bill_lastname"]=$row["bill_lastname"];
				$orders[$k]["shipping_costs"]=$row["shipping_costs"];
				$orders[$k]["firstmod"]=$row["firstmod"];
				$orders[$k]["Payments_TransactionStateDate"]=$row["Payments_TransactionStateDate"];
				$orders[$k]["Payments_Type"]=$row["Payments_Type"];
				$orders[$k]["shipping_details"]=$row["shipping_details"];
				$orders[$k]["shipping_type_id"]=$row["shipping_type_id"];
				$orders[$k]["order_note"]=$row["order_note"];
				$orders[$k]["status_id"]=$row["status_id"];
				$orders[$k]["status_date"]=$row["status_date"];
				$orders[$k]["payments_type_id"]=$row["payments_type_id"];
				
				$orders[$k]["fz_fin_mail_count"]=$row["fz_fin_mail_count"];
				$orders[$k]["fz_fin_mail_lastsent"]=$row["fz_fin_mail_lastsent"];
				*/
				$entriesCount++;

				$k++;
			}
		}
	}
	
//FILTER 2 - search for Username
	if ($searchForUserName)
	{
		$qry_string=strtolower(trim($_POST["FILTER_Searchfield"]));

		
		if (($_POST["FILTER_Platform"]==3 || $_POST["FILTER_Platform"]==4) && !$qry_string=="" )
		{
			$k=0;
			
			if ($_POST["FILTER_Platform"]==3)
			{
				//$res_UserName=q("SELECT id_order, BuyerUserID FROM ebay_orders2 WHERE account_id=1;", $dbshop, __FILE__, __LINE__);
				$res_UserName=q("SELECT OrderID, BuyerUserID FROM ebay_orders WHERE account_id=1;", $dbshop, __FILE__, __LINE__);
				
			}
			if ($_POST["FILTER_Platform"]==4)
			{
				//$res_UserName=q("SELECT id_order, BuyerUserID FROM ebay_orders2 WHERE account_id=2;", $dbshop, __FILE__, __LINE__);
				$res_UserName=q("SELECT OrderID, BuyerUserID FROM ebay_orders WHERE account_id=2;", $dbshop, __FILE__, __LINE__);
				
			}		

			while ($row_UserName=mysql_fetch_array($res_UserName))
			{

				if (strpos(strtolower(" ".$row_UserName["BuyerUserID"]), $qry_string)>0 )
				{
					//$res=q("SELECT shop_id, foreign_order_id, crm_customer_id, id_order, usermail, bill_company, bill_firstname, bill_lastname, shipping_costs, firstmod, fz_fin_mail_count, fz_fin_mail_lastsent, shipping_details FROM shop_orders WHERE foreign_order_id = ".$row_UserName["id_order"]." AND ".$rangestart.$rangeend.$filter.$orderby.$limit.";", $dbshop, __FILE__, __LINE__);
					$res=q("SELECT * FROM shop_orders WHERE foreign_OrderID = ".$row_UserName["OrderID"]." AND ".$rangestart.$rangeend.$filter.$orderby.$limit.";", $dbshop, __FILE__, __LINE__);

					if (mysql_num_rows($res)>0)
					{
						$row=mysql_fetch_array($res);
						$orders[$k]=$row;						
						$orders[$k]["entry_pos"]=$k+1;
						/*
						$orders[$k]["shop_id"]=$row["shop_id"];
						//$orders[$k]["foreign_order_id"]=$row["foreign_order_id"];
						$orders[$k]["foreign_OrderID"]=$row["foreign_OrderID"];
						$orders[$k]["crm_customer_id"]=$row["crm_customer_id"];
						$orders[$k]["id_order"]=$row["id_order"];
						$orders[$k]["usermail"]=$row["usermail"];
						$orders[$k]["bill_company"]=$row["bill_company"];
						$orders[$k]["bill_firstname"]=$row["bill_firstname"];
						$orders[$k]["bill_lastname"]=$row["bill_lastname"];
						$orders[$k]["shipping_costs"]=$row["shipping_costs"];
						$orders[$k]["firstmod"]=$row["firstmod"];
						$orders[$k]["Payments_TransactionStateDate"]=$row["Payments_TransactionStateDate"];
						$orders[$k]["Payments_Type"]=$row["Payments_Type"];
						$orders[$k]["shipping_details"]=$row["shipping_details"];						
						$orders[$k]["shipping_type_id"]=$row["shipping_type_id"];
						$orders[$k]["order_note"]=$row["order_note"];
						$orders[$k]["status_id"]=$row["status_id"];
						$orders[$k]["status_date"]=$row["status_date"];
						$orders[$k]["payments_type_id"]=$row["payments_type_id"];
						
						$orders[$k]["fz_fin_mail_count"]=$row["fz_fin_mail_count"];
						$orders[$k]["fz_fin_mail_lastsent"]=$row["fz_fin_mail_lastsent"];
						*/
						$entriesCount++;
						$k++;
					}
				}
			}
		} // IF SHOPID = 3 || 4
		else
		{
			//$res=q("SELECT shop_id, foreign_order_id, crm_customer_id, id_order, usermail, bill_company, bill_firstname, bill_lastname, shipping_costs, firstmod, fz_fin_mail_count, fz_fin_mail_lastsent, shipping_details FROM shop_orders WHERE ".$rangestart.$rangeend.$filter.$orderby.$limit.";", $dbshop, __FILE__, __LINE__);
			$res=q("SELECT * FROM shop_orders WHERE ".$rangestart.$rangeend.$filter.$orderby.$limit.";", $dbshop, __FILE__, __LINE__);
			$k=0;
			while ($row=mysql_fetch_array($res))
			{
				$orders[$k]=$row;
				$orders[$k]["entry_pos"]=$k+1;
				/*
				$orders[$k]["shop_id"]=$row["shop_id"];
				//$orders[$k]["foreign_order_id"]=$row["foreign_order_id"];
				$orders[$k]["foreign_OrderID"]=$row["foreign_OrderID"];
				$orders[$k]["crm_customer_id"]=$row["crm_customer_id"];
				$orders[$k]["id_order"]=$row["id_order"];
				$orders[$k]["usermail"]=$row["usermail"];
				$orders[$k]["bill_company"]=$row["bill_company"];
				$orders[$k]["bill_firstname"]=$row["bill_firstname"];
				$orders[$k]["bill_lastname"]=$row["bill_lastname"];
				$orders[$k]["shipping_costs"]=$row["shipping_costs"];
				$orders[$k]["firstmod"]=$row["firstmod"];
				$orders[$k]["Payments_TransactionStateDate"]=$row["Payments_TransactionStateDate"];
				$orders[$k]["Payments_Type"]=$row["Payments_Type"];
				$orders[$k]["shipping_details"]=$row["shipping_details"];
				$orders[$k]["shipping_type_id"]=$row["shipping_type_id"];
				$orders[$k]["order_note"]=$row["order_note"];
				$orders[$k]["status_id"]=$row["status_id"];
				$orders[$k]["status_date"]=$row["status_date"];
				$orders[$k]["payments_type_id"]=$row["payments_type_id"];
								
				$orders[$k]["fz_fin_mail_count"]=$row["fz_fin_mail_count"];
				$orders[$k]["fz_fin_mail_lastsent"]=$row["fz_fin_mail_lastsent"];
				*/
				$entriesCount++;
				$k++;
			}
		}
	}
	
//FILTER 3 & 4
	if ($searchForName || $searchForAdresse)	
	{
		$qry_string = array();
		$qry_string=explode(' ', $_POST["FILTER_Searchfield"]);
		$qry_string_size=sizeof($qry_string);
		for ($i=0; $i<$qry_string_size; $i++)
		{
			$qry_string[$i]=strtolower(trim($qry_string[$i]));
		}

		$from=($_POST["ResultPage"]-1)*$_POST["ResultRange"];
		$to=$from+$_POST["ResultRange"];
		$k=0;
		
		//$res=q("SELECT shop_id, foreign_order_id, crm_customer_id, id_order, usermail, bill_company, bill_firstname, bill_lastname, shipping_costs, bill_zip, bill_city, bill_street, bill_number, bill_additional, bill_country, firstmod, fz_fin_mail_count, fz_fin_mail_lastsent, shipping_details FROM shop_orders WHERE ".$rangestart.$rangeend.$filter.$orderby.";", $dbshop, __FILE__, __LINE__);
		$res=q("SELECT * FROM shop_orders WHERE ".$rangestart.$rangeend.$filter.$orderby.";", $dbshop, __FILE__, __LINE__);
		while ($row=mysql_fetch_array($res))
		{
			$match=true;
			if ($searchForName) $haystack=strtolower(" ".$row["bill_company"].$row["bill_firstname"].$row["bill_lastname"]);
			if ($searchForAdresse) $haystack=strtolower(" ".$row["bill_company"].$row["bill_firstname"].$row["bill_lastname"].$row["bill_zip"].$row["bill_city"].$row["bill_street"].$row["bill_number"].$row["bill_additional"].$row["bill_country"]);
			for ($i=0; $i<$qry_string_size; $i++)
			{
				if (strpos($haystack, $qry_string[$i])===false)
				{
					$match=false;
				}
			
			}
			
			if ($match)
			{
				$orders[$k]=$row;
				$orders[$k]["entry_pos"]=$k+1;
				/*
				$orders[$k]["shop_id"]=$row["shop_id"];
				//$orders[$k]["foreign_order_id"]=$row["foreign_order_id"];
				$orders[$k]["foreign_OrderID"]=$row["foreign_OrderID"];
				$orders[$k]["crm_customer_id"]=$row["crm_customer_id"];
				$orders[$k]["id_order"]=$row["id_order"];
				$orders[$k]["usermail"]=$row["usermail"];
				$orders[$k]["bill_company"]=$row["bill_company"];
				$orders[$k]["bill_firstname"]=$row["bill_firstname"];
				$orders[$k]["bill_lastname"]=$row["bill_lastname"];
				$orders[$k]["shipping_costs"]=$row["shipping_costs"];
				$orders[$k]["firstmod"]=$row["firstmod"];
				$orders[$k]["Payments_TransactionStateDate"]=$row["Payments_TransactionStateDate"];
				$orders[$k]["Payments_Type"]=$row["Payments_Type"];
				$orders[$k]["shipping_details"]=$row["shipping_details"];				
				$orders[$k]["shipping_type_id"]=$row["shipping_type_id"];
				$orders[$k]["order_note"]=$row["order_note"];
				$orders[$k]["status_id"]=$row["status_id"];
				$orders[$k]["status_date"]=$row["status_date"];
				$orders[$k]["payments_type_id"]=$row["payments_type_id"];

				$orders[$k]["fz_fin_mail_count"]=$row["fz_fin_mail_count"];
				$orders[$k]["fz_fin_mail_lastsent"]=$row["fz_fin_mail_lastsent"];
				*/
				$k++;
				$entriesCount++;
			}
		}
	}
	
//FILTER 5

	if ($searchForMPN)
	{
		$resMPN=q("SELECT MPN, id_item FROM shop_items WHERE MPN = '".trim($_POST["FILTER_Searchfield"])."';",$dbshop, __FILE__, __LINE__);
		if (mysql_num_rows($resMPN)>0) 
		{
			$rowMPN=mysql_fetch_array($resMPN);
			// GET ORDER-ITEMS WITH MPN
			$res_order_items=q("SELECT * FROM shop_orders_items_crm WHERE item_id = '".$rowMPN["id_item"]."';",$dbshop, __FILE__, __LINE__);
			while ($row_order_items=mysql_fetch_array($res_order_items))
			{
				$order_items[$row_order_items["order_id"]]=$row_order_items["item_id"];
			}
		
			//$res=q("SELECT shop_id, foreign_order_id, crm_customer_id, id_order, usermail, bill_company, bill_firstname, bill_lastname, shipping_costs, firstmod, fz_fin_mail_count, fz_fin_mail_lastsent, shipping_details FROM shop_orders WHERE ".$rangestart.$rangeend.$filter.$orderby.";", $dbshop, __FILE__, __LINE__);
			$res=q("SELECT * FROM shop_orders WHERE ".$rangestart.$rangeend.$filter.$orderby.";", $dbshop, __FILE__, __LINE__);
	
			$from=($_POST["ResultPage"]-1)*$_POST["ResultRange"];
			$to=$from+$_POST["ResultRange"];
			$k=0;
			while ($row=mysql_fetch_array($res))
			{
				if (isset($order_items[$row["id_order"]]))
				{
					$orders[$k]=$row;
					$orders[$k]["entry_pos"]=$k+1;
					/*
					$orders[$k]["shop_id"]=$row["shop_id"];
					//$orders[$k]["foreign_order_id"]=$row["foreign_order_id"];
					$orders[$k]["foreign_OrderID"]=$row["foreign_OrderID"];
					$orders[$k]["crm_customer_id"]=$row["crm_customer_id"];
					$orders[$k]["id_order"]=$row["id_order"];
					$orders[$k]["usermail"]=$row["usermail"];
					$orders[$k]["bill_company"]=$row["bill_company"];
					$orders[$k]["bill_firstname"]=$row["bill_firstname"];
					$orders[$k]["bill_lastname"]=$row["bill_lastname"];
					$orders[$k]["shipping_costs"]=$row["shipping_costs"];
					$orders[$k]["firstmod"]=$row["firstmod"];
					$orders[$k]["Payments_TransactionStateDate"]=$row["Payments_TransactionStateDate"];
					$orders[$k]["Payments_Type"]=$row["Payments_Type"];
					$orders[$k]["shipping_details"]=$row["shipping_details"];
					$orders[$k]["shipping_type_id"]=$row["shipping_type_id"];
		 			$orders[$k]["order_note"]=$row["order_note"];
					$orders[$k]["status_id"]=$row["status_id"];
					$orders[$k]["status_date"]=$row["status_date"];
					$orders[$k]["payments_type_id"]=$row["payments_type_id"];

					$orders[$k]["fz_fin_mail_count"]=$row["fz_fin_mail_count"];
					$orders[$k]["fz_fin_mail_lastsent"]=$row["fz_fin_mail_lastsent"];
					*/
					$entriesCount++;
					$k++;
				}
			}
		}
	}
	
//FILTER 6
	if ($searchForEbayItemID)
	{
		$qry_string=trim($_POST["FILTER_Searchfield"]);

		$k=0;
		
		//$res_ItemID=q("SELECT a.id_order, b.ItemItemID FROM ebay_orders2 as a, ebay_orders_items2 as b WHERE a.OrderID=b.OrderID AND b.ItemItemID LIKE '%".$qry_string."%';", $dbshop, __FILE__, __LINE__);
		$res_ItemID=q("SELECT a.OrderID, b.ItemItemID FROM ebay_orders as a, ebay_orders_items as b WHERE a.OrderID=b.OrderID AND b.ItemItemID LIKE '%".$qry_string."%';", $dbshop, __FILE__, __LINE__);
			
		while ($row_ItemID=mysql_fetch_array($res_ItemID))
		{
			//$res=q("SELECT shop_id, foreign_order_id, crm_customer_id, id_order, usermail, bill_company, bill_firstname, bill_lastname, shipping_costs, firstmod, fz_fin_mail_count, fz_fin_mail_lastsent, shipping_details FROM shop_orders WHERE foreign_order_id = ".$row_ItemID["id_order"]." AND ".$rangestart.$rangeend.$filter.$orderby.$limit.";", $dbshop, __FILE__, __LINE__);
			$res=q("SELECT * FROM shop_orders WHERE foreign_OrderID = ".$row_ItemID["OrderID"]." AND ".$rangestart.$rangeend.$filter.$orderby.$limit.";", $dbshop, __FILE__, __LINE__);

			if (mysql_num_rows($res)>0)
			{
				$row=mysql_fetch_array($res);
				$orders[$k]=$row;
				$orders[$k]["entry_pos"]=$k+1;
				/*
				$orders[$k]["shop_id"]=$row["shop_id"];
				//$orders[$k]["foreign_order_id"]=$row["foreign_order_id"];
				$orders[$k]["foreign_OrderID"]=$row["foreign_OrderID"];
				$orders[$k]["crm_customer_id"]=$row["crm_customer_id"];
				$orders[$k]["id_order"]=$row["id_order"];
				$orders[$k]["usermail"]=$row["usermail"];
				$orders[$k]["bill_company"]=$row["bill_company"];
				$orders[$k]["bill_firstname"]=$row["bill_firstname"];
				$orders[$k]["bill_lastname"]=$row["bill_lastname"];
				$orders[$k]["shipping_costs"]=$row["shipping_costs"];
				$orders[$k]["firstmod"]=$row["firstmod"];
				$orders[$k]["Payments_TransactionStateDate"]=$row["Payments_TransactionStateDate"];
				$orders[$k]["Payments_Type"]=$row["Payments_Type"];
				$orders[$k]["shipping_details"]=$row["shipping_details"];
				$orders[$k]["shipping_type_id"]=$row["shipping_type_id"];
				$orders[$k]["order_note"]=$row["order_note"];
				$orders[$k]["status_id"]=$row["status_id"];
				$orders[$k]["status_date"]=$row["status_date"];
				$orders[$k]["payments_type_id"]=$row["payments_type_id"];
				
				$orders[$k]["fz_fin_mail_count"]=$row["fz_fin_mail_count"];
				$orders[$k]["fz_fin_mail_lastsent"]=$row["fz_fin_mail_lastsent"];
				*/
				$entriesCount++;
				$k++;
			}
		}
	}

// STATUS FILTER ################################################################################################
// STATUS 1
	if ($_POST["FILTER_Status"]==1)
	{
		$payment = array();
		$res_status=q("SELECT * FROM shop_orders_events WHERE event = 'Payment';", $dbshop, __FILE__, __LINE__);
		while ($row_status=mysql_fetch_array($res_status))
		{
			if (strpos($row_status["message"],"<PaymentType><![CDATA[paymentrecieved]]></PaymentType>")>0 && strpos($row_status["message"],"<PaymentState><![CDATA[Completed]]></PaymentState>")>0)
			{
				$payment[$row_status["order_id"]]=1;
			}
		}

		$tmp_orders=array();
		$j=0;
		for ($k=0; $k<sizeof($orders); $k++)
		{
			if (isset($payment[$orders[$k]["id_order"]])) 
			{
				$tmp_orders[$j]=$orders[$k];
				/*
				$tmp_orders[$j]["entry_pos"]=$orders[$k]["entry_pos"];
				$tmp_orders[$j]["shop_id"]=$orders[$k]["shop_id"];
				//$tmp_orders[$j]["foreign_order_id"]=$orders[$k]["foreign_order_id"];
				$tmp_orders[$j]["foreign_OrderID"]=$orders[$k]["foreign_OrderID"];
				$tmp_orders[$j]["crm_customer_id"]=$orders[$k]["crm_customer_id"];
				$tmp_orders[$j]["id_order"]=$orders[$k]["id_order"];
				$tmp_orders[$j]["usermail"]=$orders[$k]["usermail"];
				$tmp_orders[$j]["bill_company"]=$orders[$k]["bill_company"];
				$tmp_orders[$j]["bill_firstname"]=$orders[$k]["bill_firstname"];
				$tmp_orders[$j]["bill_lastname"]=$orders[$k]["bill_lastname"];
				$tmp_orders[$j]["shipping_costs"]=$orders[$k]["shipping_costs"];
				$tmp_orders[$j]["firstmod"]=$orders[$k]["firstmod"];
				$tmp_orders[$j]["Payments_TransactionStateDate"]=$orders[$k]["Payments_TransactionStateDate"];
				$tmp_orders[$j]["Payments_Type"]=$orders[$k]["Payments_Type"];
			 	$tmp_orders[$j]["shipping_details"]=$orders[$k]["shipping_details"];
				$tmp_orders[$j]["shipping_type_id"]=$orders[$k]["shipping_type_id"];
				$tmp_orders[$j]["order_note"]=$orders[$k]["order_note"];
				$tmp_orders[$j]["status_id"]=$orders[$k]["status_id"];
			 	$tmp_orders[$j]["status_date"]=$orders[$k]["status_date"];
			 	$tmp_orders[$j]["payments_type_id"]=$orders[$k]["payments_type_id"];
				
				$tmp_orders[$j]["fz_fin_mail_count"]=$orders[$k]["fz_fin_mail_count"];
				$tmp_orders[$j]["fz_fin_mail_lastsent"]=$orders[$k]["fz_fin_mail_lastsent"];
				*/
				$j++;
			}
		}
	}

// FILTERSTATUS 2 - bezahlt & nicht versand beauftragt
// FILTERSTATUS 3 - bezahlt & nicht versand
// FILTERSTATUS 4 - versand beauftragt & versand

	elseif ($_POST["FILTER_Status"]==2 || $_POST["FILTER_Status"]==3 || $_POST["FILTER_Status"]==4 )
	{
		$payment = array();
		$res_status=q("SELECT * FROM shop_orders_events WHERE event = 'Payment';", $dbshop, __FILE__, __LINE__);
		while ($row_status=mysql_fetch_array($res_status))
		{
			if (strpos($row_status["message"],"<PaymentType><![CDATA[paymentrecieved]]></PaymentType>")>0 && strpos($row_status["message"],"<PaymentState><![CDATA[Completed]]></PaymentState>")>0)
			{
				$payment[$row_status["order_id"]]=1;
			}
		}
		$shipment_assigned = array();
		$res_status=q("SELECT * FROM shop_orders_events WHERE event = 'ShipmentAssigned';", $dbshop, __FILE__, __LINE__);
		while ($row_status=mysql_fetch_array($res_status))
		{
			$shipment_assigned[$row_status["order_id"]]=1;
		}
		$shipment_executed = array();
		$res_status=q("SELECT * FROM shop_orders_events WHERE event = 'ShipmentExecuted';", $dbshop, __FILE__, __LINE__);
		while ($row_status=mysql_fetch_array($res_status))
		{
			$shipment_executed[$row_status["order_id"]]=1;
		}

		$tmp_orders=array();
		$j=0;
		for ($k=0; $k<sizeof($orders); $k++)
		{
			$unset=true;
			if ($_POST["FILTER_Status"]==2 && isset($payment[$orders[$k]["id_order"]]) && !isset($shipment_assigned[$orders[$k]["id_order"]]) && !isset($shipment_executed[$orders[$k]["id_order"]])) $unset=false;
			
			if ($_POST["FILTER_Status"]==3 && isset($payment[$orders[$k]["id_order"]]) && isset($shipment_assigned[$orders[$k]["id_order"]]) && !isset($shipment_executed[$orders[$k]["id_order"]])) $unset=false;
			
			if ($_POST["FILTER_Status"]==4 && isset($shipment_assigned[$orders[$k]["id_order"]]) && isset($shipment_executed[$orders[$k]["id_order"]])) $unset=false;

			if (!$unset)
			{
				$tmp_orders[$j]=$orders[$k];
				/*
				$tmp_orders[$j]["entry_pos"]=$orders[$k]["entry_pos"];
				$tmp_orders[$j]["shop_id"]=$orders[$k]["shop_id"];
				//$tmp_orders[$j]["foreign_order_id"]=$orders[$k]["foreign_order_id"];
				$tmp_orders[$j]["foreign_OrderID"]=$orders[$k]["foreign_OrderID"];
				$tmp_orders[$j]["crm_customer_id"]=$orders[$k]["crm_customer_id"];
				$tmp_orders[$j]["id_order"]=$orders[$k]["id_order"];
				$tmp_orders[$j]["usermail"]=$orders[$k]["usermail"];
				$tmp_orders[$j]["bill_company"]=$orders[$k]["bill_company"];
				$tmp_orders[$j]["bill_firstname"]=$orders[$k]["bill_firstname"];
				$tmp_orders[$j]["bill_lastname"]=$orders[$k]["bill_lastname"];
				$tmp_orders[$j]["shipping_costs"]=$orders[$k]["shipping_costs"];
				$tmp_orders[$j]["firstmod"]=$orders[$k]["firstmod"];
				$tmp_orders[$j]["Payments_TransactionStateDate"]=$orders[$k]["Payments_TransactionStateDate"];
				$tmp_orders[$j]["Payments_Type"]=$orders[$k]["Payments_Type"];
			 	$tmp_orders[$j]["shipping_details"]=$orders[$k]["shipping_details"];
				$tmp_orders[$j]["shipping_type_id"]=$orders[$k]["shipping_type_id"];
				$tmp_orders[$j]["order_note"]=$orders[$k]["order_note"];
				$tmp_orders[$j]["status_id"]=$orders[$k]["status_id"];
			 	$tmp_orders[$j]["status_date"]=$orders[$k]["status_date"];
				$tmp_orders[$j]["payments_type_id"]=$orders[$k]["payments_type_id"];

				$tmp_orders[$j]["fz_fin_mail_count"]=$orders[$k]["fz_fin_mail_count"];
				$tmp_orders[$j]["fz_fin_mail_lastsent"]=$orders[$k]["fz_fin_mail_lastsent"];
				*/
				$j++;
			}
		}
	}
	
	//FILTERSTATUS 10 - Fz-FIN MAIL gesendet
	elseif ($_POST["FILTER_Status"]==10)
	{
		$j=0;
		for ($k=0; $k<sizeof($orders); $k++)
		{
			if ($orders[$k]["fz_fin_mail_count"]>0)
			{
				$tmp_orders[$j]=$orders[$k];
				/*
				$tmp_orders[$j]["entry_pos"]=$orders[$k]["entry_pos"];
				$tmp_orders[$j]["shop_id"]=$orders[$k]["shop_id"];
				//$tmp_orders[$j]["foreign_order_id"]=$orders[$k]["foreign_order_id"];
				$tmp_orders[$j]["foreign_OrderID"]=$orders[$k]["foreign_OrderID"];
				$tmp_orders[$j]["crm_customer_id"]=$orders[$k]["crm_customer_id"];
				$tmp_orders[$j]["id_order"]=$orders[$k]["id_order"];
				$tmp_orders[$j]["usermail"]=$orders[$k]["usermail"];
				$tmp_orders[$j]["bill_company"]=$orders[$k]["bill_company"];
				$tmp_orders[$j]["bill_firstname"]=$orders[$k]["bill_firstname"];
				$tmp_orders[$j]["bill_lastname"]=$orders[$k]["bill_lastname"];
				$tmp_orders[$j]["shipping_costs"]=$orders[$k]["shipping_costs"];
				$tmp_orders[$j]["firstmod"]=$orders[$k]["firstmod"];
				$tmp_orders[$j]["Payments_TransactionStateDate"]=$orders[$k]["Payments_TransactionStateDate"];
				$tmp_orders[$j]["Payments_Type"]=$orders[$k]["Payments_Type"];
			 	$tmp_orders[$j]["shipping_details"]=$orders[$k]["shipping_details"];
				$tmp_orders[$j]["shipping_type_id"]=$orders[$k]["shipping_type_id"];
				$tmp_orders[$j]["order_note"]=$orders[$k]["order_note"];
				$tmp_orders[$j]["status_id"]=$orders[$k]["status_id"];
			 	$tmp_orders[$j]["status_date"]=$orders[$k]["status_date"];
				$tmp_orders[$j]["payments_type_id"]=$orders[$k]["payments_type_id"];

				$tmp_orders[$j]["fz_fin_mail_count"]=$orders[$k]["fz_fin_mail_count"];
				$tmp_orders[$j]["fz_fin_mail_lastsent"]=$orders[$k]["fz_fin_mail_lastsent"];
				*/
				$j++;
			}
		}
	}

	//FILTERSTATUS 11 - Fz-FIN MAIL NICHT gesendet
	elseif ($_POST["FILTER_Status"]==11)
	{
		$j=0;
		for ($k=0; $k<sizeof($orders); $k++)
		{
			if ($orders[$k]["fz_fin_mail_count"]==0)
			{
				$tmp_orders[$j]=$orders[$k];
				/*
				$tmp_orders[$j]["entry_pos"]=$orders[$k]["entry_pos"];
				$tmp_orders[$j]["shop_id"]=$orders[$k]["shop_id"];
				//$tmp_orders[$j]["foreign_order_id"]=$orders[$k]["foreign_order_id"];
				$tmp_orders[$j]["foreign_OrderID"]=$orders[$k]["foreign_OrderID"];
				$tmp_orders[$j]["crm_customer_id"]=$orders[$k]["crm_customer_id"];
				$tmp_orders[$j]["id_order"]=$orders[$k]["id_order"];
				$tmp_orders[$j]["usermail"]=$orders[$k]["usermail"];
				$tmp_orders[$j]["bill_company"]=$orders[$k]["bill_company"];
				$tmp_orders[$j]["bill_firstname"]=$orders[$k]["bill_firstname"];
				$tmp_orders[$j]["bill_lastname"]=$orders[$k]["bill_lastname"];
				$tmp_orders[$j]["shipping_costs"]=$orders[$k]["shipping_costs"];
				$tmp_orders[$j]["firstmod"]=$orders[$k]["firstmod"];
				$tmp_orders[$j]["Payments_TransactionStateDate"]=$orders[$k]["Payments_TransactionStateDate"];
				$tmp_orders[$j]["Payments_Type"]=$orders[$k]["Payments_Type"];
			 	$tmp_orders[$j]["shipping_details"]=$orders[$k]["shipping_details"];
				$tmp_orders[$j]["shipping_type_id"]=$orders[$k]["shipping_type_id"];
				$tmp_orders[$j]["order_note"]=$orders[$k]["order_note"];
				$tmp_orders[$j]["status_id"]=$orders[$k]["status_id"];
			 	$tmp_orders[$j]["status_date"]=$orders[$k]["status_date"];
				$tmp_orders[$j]["payments_type_id"]=$orders[$k]["payments_type_id"];
				
				$tmp_orders[$j]["fz_fin_mail_count"]=$orders[$k]["fz_fin_mail_count"];
				$tmp_orders[$j]["fz_fin_mail_lastsent"]=$orders[$k]["fz_fin_mail_lastsent"];
				*/
				$j++;
			}
		}
	}
	//FILTERSTATUS 20 - Versand EXPRESS
	elseif ($_POST["FILTER_Status"]==20)
	{
		$j=0;
		for ($k=0; $k<sizeof($orders); $k++)
		{
			if (strpos(" ".$orders[$k]["shipping_details"], "Express")>0)
			{
				$tmp_orders[$j]=$orders[$k];
				/*
				$tmp_orders[$j]["entry_pos"]=$orders[$k]["entry_pos"];
				$tmp_orders[$j]["shop_id"]=$orders[$k]["shop_id"];
				//$tmp_orders[$j]["foreign_order_id"]=$orders[$k]["foreign_order_id"];
				$tmp_orders[$j]["foreign_OrderID"]=$orders[$k]["foreign_OrderID"];
				$tmp_orders[$j]["crm_customer_id"]=$orders[$k]["crm_customer_id"];
				$tmp_orders[$j]["id_order"]=$orders[$k]["id_order"];
				$tmp_orders[$j]["usermail"]=$orders[$k]["usermail"];
				$tmp_orders[$j]["bill_company"]=$orders[$k]["bill_company"];
				$tmp_orders[$j]["bill_firstname"]=$orders[$k]["bill_firstname"];
				$tmp_orders[$j]["bill_lastname"]=$orders[$k]["bill_lastname"];
				$tmp_orders[$j]["shipping_costs"]=$orders[$k]["shipping_costs"];
				$tmp_orders[$j]["firstmod"]=$orders[$k]["firstmod"];
				$tmp_orders[$j]["Payments_TransactionStateDate"]=$orders[$k]["Payments_TransactionStateDate"];
				$tmp_orders[$j]["Payments_Type"]=$orders[$k]["Payments_Type"];
			 	$tmp_orders[$j]["shipping_details"]=$orders[$k]["shipping_details"];
				$tmp_orders[$j]["shipping_type_id"]=$orders[$k]["shipping_type_id"];
				$tmp_orders[$j]["order_note"]=$orders[$k]["order_note"];
				$tmp_orders[$j]["status_id"]=$orders[$k]["status_id"];
			 	$tmp_orders[$j]["status_date"]=$orders[$k]["status_date"];
				$tmp_orders[$j]["payments_type_id"]=$orders[$k]["payments_type_id"];
				
				$tmp_orders[$j]["fz_fin_mail_count"]=$orders[$k]["fz_fin_mail_count"];
				$tmp_orders[$j]["fz_fin_mail_lastsent"]=$orders[$k]["fz_fin_mail_lastsent"];
				*/
				$j++;
			}
		}
	}

	//FILTERSTATUS 21 - Versand DPD
	elseif ($_POST["FILTER_Status"]==21)
	{
		$j=0;
		for ($k=0; $k<sizeof($orders); $k++)
		{
			if (strpos(" ".$orders[$k]["shipping_details"], "DPD")>0 || strpos(" ".$orders[$k]["shipping_details"], "PaketInternational")>0)
			{
				$tmp_orders[$j]=$orders[$k];
				/*
				$tmp_orders[$j]["entry_pos"]=$orders[$k]["entry_pos"];
				$tmp_orders[$j]["shop_id"]=$orders[$k]["shop_id"];
				//$tmp_orders[$j]["foreign_order_id"]=$orders[$k]["foreign_order_id"];
				$tmp_orders[$j]["foreign_OrderID"]=$orders[$k]["foreign_OrderID"];
				$tmp_orders[$j]["crm_customer_id"]=$orders[$k]["crm_customer_id"];
				$tmp_orders[$j]["id_order"]=$orders[$k]["id_order"];
				$tmp_orders[$j]["usermail"]=$orders[$k]["usermail"];
				$tmp_orders[$j]["bill_company"]=$orders[$k]["bill_company"];
				$tmp_orders[$j]["bill_firstname"]=$orders[$k]["bill_firstname"];
				$tmp_orders[$j]["bill_lastname"]=$orders[$k]["bill_lastname"];
				$tmp_orders[$j]["shipping_costs"]=$orders[$k]["shipping_costs"];
				$tmp_orders[$j]["firstmod"]=$orders[$k]["firstmod"];
				$tmp_orders[$j]["Payments_TransactionStateDate"]=$orders[$k]["Payments_TransactionStateDate"];
				$tmp_orders[$j]["Payments_Type"]=$orders[$k]["Payments_Type"];
			 	$tmp_orders[$j]["shipping_details"]=$orders[$k]["shipping_details"];
				$tmp_orders[$j]["shipping_type_id"]=$orders[$k]["shipping_type_id"];
				$tmp_orders[$j]["order_note"]=$orders[$k]["order_note"];
				$tmp_orders[$j]["status_id"]=$orders[$k]["status_id"];
			 	$tmp_orders[$j]["status_date"]=$orders[$k]["status_date"];
				$tmp_orders[$j]["payments_type_id"]=$orders[$k]["payments_type_id"];
				
				$tmp_orders[$j]["fz_fin_mail_count"]=$orders[$k]["fz_fin_mail_count"];
				$tmp_orders[$j]["fz_fin_mail_lastsent"]=$orders[$k]["fz_fin_mail_lastsent"];
				*/
				$j++;
			}
		}
	}

	//FILTERSTATUS 22 - Versand DHL
	elseif ($_POST["FILTER_Status"]==22)
	{
		$j=0;
		for ($k=0; $k<sizeof($orders); $k++)
		{
			if (strpos(" ".$orders[$k]["shipping_details"], "DHL")>0)
			{
				$tmp_orders[$j]=$orders[$k];
				/*
				$tmp_orders[$j]["entry_pos"]=$orders[$k]["entry_pos"];
				$tmp_orders[$j]["shop_id"]=$orders[$k]["shop_id"];
				//$tmp_orders[$j]["foreign_order_id"]=$orders[$k]["foreign_order_id"];
				$tmp_orders[$j]["foreign_OrderID"]=$orders[$k]["foreign_OrderID"];
				$tmp_orders[$j]["crm_customer_id"]=$orders[$k]["crm_customer_id"];
				$tmp_orders[$j]["id_order"]=$orders[$k]["id_order"];
				$tmp_orders[$j]["usermail"]=$orders[$k]["usermail"];
				$tmp_orders[$j]["bill_company"]=$orders[$k]["bill_company"];
				$tmp_orders[$j]["bill_firstname"]=$orders[$k]["bill_firstname"];
				$tmp_orders[$j]["bill_lastname"]=$orders[$k]["bill_lastname"];
				$tmp_orders[$j]["shipping_costs"]=$orders[$k]["shipping_costs"];
				$tmp_orders[$j]["firstmod"]=$orders[$k]["firstmod"];
				$tmp_orders[$j]["Payments_TransactionStateDate"]=$orders[$k]["Payments_TransactionStateDate"];
				$tmp_orders[$j]["Payments_Type"]=$orders[$k]["Payments_Type"];
			 	$tmp_orders[$j]["shipping_details"]=$orders[$k]["shipping_details"];
				$tmp_orders[$j]["shipping_type_id"]=$orders[$k]["shipping_type_id"];
				$tmp_orders[$j]["order_note"]=$orders[$k]["order_note"];
				$tmp_orders[$j]["status_id"]=$orders[$k]["status_id"];
			 	$tmp_orders[$j]["status_date"]=$orders[$k]["status_date"];
				$tmp_orders[$j]["payments_type_id"]=$orders[$k]["payments_type_id"];
				
				$tmp_orders[$j]["fz_fin_mail_count"]=$orders[$k]["fz_fin_mail_count"];
				$tmp_orders[$j]["fz_fin_mail_lastsent"]=$orders[$k]["fz_fin_mail_lastsent"];
				*/
				$j++;
			}
		}
	}
	

	else {$tmp_orders=$orders;}
	

	
//AUSGABE vorbereiten

//	$tmp_orders=$orders;
	$orders=array();
	$entriesCount=0;
	$j=0;
	for ($k=0; $k<sizeof($tmp_orders); $k++)
	{
		if ($k>=$from && $k<=$to)
		{
			$orders[$j]=$tmp_orders[$k];
			/*
			$orders[$j]["entry_pos"]=$tmp_orders[$k]["entry_pos"];
			$orders[$j]["shop_id"]=$tmp_orders[$k]["shop_id"];
			//$orders[$j]["foreign_order_id"]=$tmp_orders[$k]["foreign_order_id"];
			$orders[$j]["foreign_OrderID"]=$tmp_orders[$k]["foreign_OrderID"];
			$orders[$j]["crm_customer_id"]=$tmp_orders[$k]["crm_customer_id"];
			$orders[$j]["id_order"]=$tmp_orders[$k]["id_order"];
			$orders[$j]["usermail"]=$tmp_orders[$k]["usermail"];
			$orders[$j]["bill_company"]=$tmp_orders[$k]["bill_company"];
			$orders[$j]["bill_firstname"]=$tmp_orders[$k]["bill_firstname"];
			$orders[$j]["bill_lastname"]=$tmp_orders[$k]["bill_lastname"];
			$orders[$j]["shipping_costs"]=$tmp_orders[$k]["shipping_costs"];
		 	$orders[$j]["firstmod"]=$tmp_orders[$k]["firstmod"];
			$orders[$j]["Payments_TransactionStateDate"]=$tmp_orders[$k]["Payments_TransactionStateDate"];
			$orders[$j]["Payments_Type"]=$tmp_orders[$k]["Payments_Type"];
			$orders[$j]["shipping_details"]=$tmp_orders[$k]["shipping_details"];
			$orders[$j]["shipping_type_id"]=$tmp_orders[$k]["shipping_type_id"];
			$orders[$j]["order_note"]=$tmp_orders[$k]["order_note"];
			$orders[$j]["status_id"]=$tmp_orders[$k]["status_id"];
			$orders[$j]["status_date"]=$tmp_orders[$k]["status_date"];
			$orders[$j]["payments_type_id"]=$tmp_orders[$k]["payments_type_id"];

			$orders[$j]["fz_fin_mail_count"]=$tmp_orders[$k]["fz_fin_mail_count"];
			$orders[$j]["fz_fin_mail_lastsent"]=$tmp_orders[$k]["fz_fin_mail_lastsent"];
			*/
			$j++;
		}
		$entriesCount++;
	}


//ERSTELLE XML ------------------------------------------------------------------------------------------------
	for ($k=0; $k<sizeof($orders); $k++)
	{
		$orderItemTotal=0;
		$orderItemsTotal=0;
		$orderTotal=0;
		$orderTotalCount=0;

		$xmldata.="<Order>\n";
		$xmldata.="	<entry_pos>".$orders[$k]["entry_pos"]."</entry_pos>\n";
		$xmldata.="	<id_order>".$orders[$k]["id_order"]."</id_order>\n";
		$xmldata.="	<shop_id>".$orders[$k]["shop_id"]."</shop_id>\n";
		$xmldata.="	<crm_customer_id>".$orders[$k]["crm_customer_id"]."</crm_customer_id>\n";
		//$xmldata.="	<foreign_order_id>".$orders[$k]["foreign_order_id"]."</foreign_order_id>\n";
		$xmldata.="	<foreign_OrderID>".$orders[$k]["foreign_OrderID"]."</foreign_OrderID>\n";
		$xmldata.="	<usermail><![CDATA[".$orders[$k]["usermail"]."]]></usermail>\n";
		$xmldata.="	<bill_company><![CDATA[".$orders[$k]["bill_company"]."]]></bill_company>\n";
		$xmldata.="	<bill_firstname><![CDATA[".$orders[$k]["bill_firstname"]."]]></bill_firstname>\n";
		$xmldata.="	<bill_lastname><![CDATA[".$orders[$k]["bill_lastname"]."]]></bill_lastname>\n";
		$xmldata.="	<firstmod>".$orders[$k]["firstmod"]."</firstmod>\n";
		$xmldata.=" <orderDate>".$orders[$k]["firstmod"]."</orderDate>\n";
		$xmldata.=" <PaymentDate>".$orders[$k]["Payments_TransactionStateDate"]."</PaymentDate>\n";
		$xmldata.=" <PaymentType>".$orders[$k]["Payments_Type"]."</PaymentType>\n";
		$xmldata.=" <shipping_details><![CDATA[".$orders[$k]["shipping_details"]."]]></shipping_details>\n";
		$xmldata.=" <shipping_type_id>".$orders[$k]["shipping_type_id"]."</shipping_type_id>\n";
		$xmldata.=" <shipping_number><![CDATA[".$orders[$k]["shipping_number"]."]]></shipping_number>\n";
		$xmldata.=" <status_id>".$orders[$k]["status_id"]."</status_id>\n";
		$xmldata.=" <status_date>".$orders[$k]["status_date"]."</status_date>\n";
		$xmldata.=" <PaymentTypeID>".$orders[$k]["payments_type_id"]."</PaymentTypeID>\n";

		//FINABFRAGEMAILS	
		$xmldata.=" <fz_fin_mail_count>".$orders[$k]["fz_fin_mail_count"]."</fz_fin_mail_count>\n";
		$xmldata.=" <fz_fin_mail_lastsent>".$orders[$k]["fz_fin_mail_lastsent"]."</fz_fin_mail_lastsent>\n";
		
		//USERID - PLATFORM
		$buyerid="";
		$VPN=0;
		if ($orders[$k]["shop_id"]==3 || $orders[$k]["shop_id"]==4)
		{
			//$res_buyerid=q("SELECT * FROM ebay_orders2 WHERE id_order = ".$orders[$k]["foreign_order_id"].";", $dbshop, __FILE__, __LINE__);
			$res_buyerid=q("SELECT * FROM ebay_orders WHERE OrderID = '".$orders[$k]["foreign_OrderID"]."';", $dbshop, __FILE__, __LINE__);
			if (mysql_num_rows($res_buyerid)>0)
			{
				$row_buyerid=mysql_fetch_array($res_buyerid);
				$buyerid=$row_buyerid["BuyerUserID"];	
				$VPN=$row_buyerid["ShippingDetailsSellingManagerSalesRecordNumber"];	
							
			}
		}
		else
		{
			$VPN=$orders[$k]["id_order"];
		}

		$xmldata.=" <buyerUserID>".$buyerid."</buyerUserID>\n";
		$xmldata.=" <VPN>".$VPN."</VPN>\n";

		$xmldata.="	<OrderItems>\n";
		//ITEMS
		
		$res_items=q("SELECT * FROM shop_orders_items WHERE order_id = ".$orders[$k]["id_order"].";", $dbshop, __FILE__, __LINE__);
		while ($row_items=mysql_fetch_array($res_items))
		{
			//ITEM DESCRIPTION
			$res_items_desc=q("SELECT * FROM shop_items_de WHERE id_item = ".$row_items["item_id"].";", $dbshop, __FILE__, __LINE__);
			$row_items_desc=mysql_fetch_array($res_items_desc);
						
			$orderItemTotal=$row_items["price"]*$row_items["amount"];
			$orderItemsTotal+=$orderItemTotal;
			$orderTotalCount+=$row_items["amount"];

			$xmldata.="		<Item>\n";
			$xmldata.="			<OrderItemID>".$row_items["id"]."</OrderItemID>\n";
			$xmldata.="			<OrderItemItemID>".$row_items["item_id"]."</OrderItemItemID>\n";
			$xmldata.="			<OrderItemDesc><![CDATA[".$row_items_desc["title"]."]]></OrderItemDesc>\n";
			$xmldata.="			<OrderItemAmount>".$row_items["amount"]."</OrderItemAmount>\n";
			$xmldata.="			<OrderItemPrice>".$row_items["price"]."</OrderItemPrice>\n";
			$xmldata.="			<OrderItemNetto>".$row_items["netto"]."</OrderItemNetto>\n";
			$xmldata.="			<OrderItemTotal>".number_format($orderItemTotal, 2,",",".")."</OrderItemTotal>\n";
			$xmldata.="			<OrderItemCustomerVehicleID>".$row_items["customer_vehicle_id"]."</OrderItemCustomerVehicleID>\n";
			$xmldata.="			<OrderItemChecked>".$row_items["checked"]."</OrderItemChecked>\n";
			$xmldata.="			<OrderItemckecked_by_user>".$row_items["ckecked_by_user"]."</OrderItemckecked_by_user>\n";

			$xmldata.="		</Item>\n";
			
		}
		
		$xmldata.="	</OrderItems>\n";
		$xmldata.="	<OrderShippingCosts>".number_format($orders[$k]["shipping_costs"], 2,",",".")."</OrderShippingCosts>\n";
		$xmldata.="	<OrderItemsTotal>".number_format($orderItemsTotal, 2,",",".")."</OrderItemsTotal>\n";
		$xmldata.="	<OrderTotal>".number_format($orderItemsTotal+$orders[$k]["shipping_costs"], 2,",",".")."</OrderTotal>\n";
		$xmldata.="	<OrderTotalCount>".number_format($orderTotalCount, 2,",",".")."</OrderTotalCount>\n";
		$xmldata.="</Order>\n";
	}


echo "<crm_orders_listResponse>\n";
echo "<Ack>Success</Ack>\n";
echo "<Entries>".$entriesCount."</Entries>\n";
echo "<OrderList>\n";
	echo $xmldata;
echo "</OrderList>\n";
echo "</crm_orders_listResponse>";

?>