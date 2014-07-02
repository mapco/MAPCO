<?php
	include_once("../functions/mapco_gewerblich.php");

	function get_order_note_from_conversation($ord_id)
	{
		/*global $dbweb;
	
		$res_convers = q("SELECT con.id, con.article_id, art.article FROM crm_conversations AS con, cms_articles AS art WHERE con.order_id=".$ord_id." AND con.type_id=4 AND art.id_article=con.article_id ORDER BY con.firstmod DESC LIMIT 1;", $dbweb, __FILE__, __LINE__);
		if ( mysqli_num_rows($res_convers) > 0 )
		{
			$row_convers = (mysqli_fetch_assoc($res_convers));
			$art = $row_convers['article'];
		}
		else
		{
			$art = '';
		}
		return $art; */

		global $dbweb;
		$art = array();
		
		$res_convers = q("SELECT con.id, con.article_id, art.article FROM crm_conversations AS con, cms_articles AS art WHERE con.order_id=".$ord_id." AND con.type_id=4 AND art.id_article=con.article_id ORDER BY con.firstmod DESC;", $dbweb, __FILE__, __LINE__);
		if ( mysqli_num_rows($res_convers) > 0 )
		{
			while($row_convers = (mysqli_fetch_assoc($res_convers)))
			{
				$art[] = $row_convers['article'];
			}
		}
		else
		{
			$art[0] = '';
		}
		
		return $art; 	
	}

	$orders=array();
	$tmp_orders=array();
	//GET SHOPS
	$shop_type=array();
	$shops=array();
	$res_shop=q("SELECT * FROM shop_shops;", $dbshop, __FILE__, __LINE__);
	while ($row_shop=mysqli_fetch_assoc($res_shop))
	{
		$shop_type[$row_shop["id_shop"]]=$row_shop["shop_type"];
		$shops[$row_shop["id_shop"]]=$row_shop;
	}

	//GET USER SITES
	$user_sites = array();
	$res_user_sites = q("SELECT * FROM cms_users_sites WHERE user_id = ".$_SESSION["id_user"], $dbweb, __FILE__, __LINE__);
	while ($row_user_sites = mysqli_fetch_assoc($res_user_sites))
	{
		$user_sites[]=$row_user_sites["site_id"];
	}

	//GET SHOPS FOR SITEs
	$usershops=array();
	if (sizeof($user_sites)>0)
	{
		foreach ($shops as $shop_id => $shop)
		{
			if (in_array($shop["site_id"],$user_sites) )
			{
				//PARENTSHOP
				$usershops[]=$shop_id;
				$childshops=$shops;
				//CHILDSHOPS
				foreach ($childshops as $childshop_id => $childshop)
				{
					if ($childshop["parent_shop_id"]==$shop_id) $usershops[]=$childshop_id;
				}
			}
		}
	}
	
	//get Payments
	$res_payments=q("SELECT * FROM shop_payment_types;", $dbshop, __FILE__, __LINE__);
	while ($row_payments=mysqli_fetch_assoc($res_payments))
	{
		$payment[$row_payments["id_paymenttype"]]["method"]=$row_payments["method"];
		$payment[$row_payments["id_paymenttype"]]["ship_at_once"]=$row_payments["ship_at_once"];
	}

	if ((isset($_POST["mode"]) && $_POST["mode"]=="single") || $_POST["FILTER_SearchFor"]==8 && is_numeric(trim($_POST["FILTER_Searchfield"])))
	{
		if ($_POST["mode"]=="single") $orderid = $_POST["order_id"];
		if ($_POST["FILTER_SearchFor"]==8) $orderid = trim($_POST["FILTER_Searchfield"]);
		
		if (sizeof($usershops)>0)
		{
			$res=q("SELECT * FROM shop_orders WHERE id_order = ".$orderid." AND shop_id IN (".implode(", ", $usershops).")", $dbshop, __FILE__, __LINE__);
		}
		else
		{
			$res=q("SELECT * FROM shop_orders WHERE id_order = ".$orderid, $dbshop, __FILE__, __LINE__);
		}
		
		//$z = 0;
		while ($row=mysqli_fetch_assoc($res))
		{
			$tmp_orders[]=$row;
		//	$tmp_orders[$z]['order_note'] = get_order_note_from_conversation($row['id_order']);
		//	$z++;
		}
	}
	//SUCHE ORDER NACH INVOICE NR
	elseif($_POST["FILTER_SearchFor"]==10 && $_POST["FILTER_Searchfield"]!="")
	{
		//if ($_POST["mode"]=="single") $orderid = $_POST["order_id"];
		$invoice_nr = trim($_POST["FILTER_Searchfield"]);
		$tmp_orders = array();
		if ($invoice_nr!="")
		{
			while (strlen($invoice_nr)<11)
			{
				$invoice_nr="0".$invoice_nr;	
			}
			
			
			
			if (sizeof($usershops)>0)
			{
				$res=q("SELECT * FROM shop_orders WHERE invoice_nr = '".$invoice_nr."' AND shop_id IN (".implode(", ", $usershops).")", $dbshop, __FILE__, __LINE__);
			}
			else
			{
				$res=q("SELECT * FROM shop_orders WHERE invoice_nr = '".$invoice_nr."'", $dbshop, __FILE__, __LINE__);
			}
			//$z = 0;
			while ($row=mysqli_fetch_assoc($res))
			{
				$tmp_orders[]=$row;
				//$tmp_orders[$z]['order_note'] = get_order_note_from_conversation($row['id_order']);
				//$z++;
			}
		}
	}
	//SUCHE ORDER NACH AUF_ID
	elseif($_POST["FILTER_SearchFor"]==11 && $_POST["FILTER_Searchfield"]!="")
	{
		$AUF_ID = trim($_POST["FILTER_Searchfield"]);
		$tmp_orders = array();
		if ($AUF_ID!="")
		{
			$res_aufid = q("SELECT * FROM shop_orders_auf_id WHERE AUF_ID =".$AUF_ID." OR parent_auf_id =".$AUF_ID , $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_aufid)>0)
			{
				$row_aufid = mysqli_fetch_assoc($res_aufid);
				$orderid = $row_aufid["order_id"];
				if (sizeof($usershops)>0)
				{
					$res=q("SELECT * FROM shop_orders WHERE id_order = ".$orderid." AND shop_id IN (".implode(", ", $usershops).")", $dbshop, __FILE__, __LINE__);
				}
				else
				{
					$res=q("SELECT * FROM shop_orders WHERE id_order = ".$orderid, $dbshop, __FILE__, __LINE__);
				}
				//$z=0;
				while ($row=mysqli_fetch_assoc($res))
				{
					$tmp_orders[]=$row;
					//$tmp_orders[$z]['order_note'] = get_order_note_from_conversation($row['id_order']);
					//$z++;
				}
			}
		}
	}
		elseif($_POST["FILTER_SearchFor"]==12 && $_POST["FILTER_Searchfield"]!="")
	{
		$ama_order_id = trim($_POST["FILTER_Searchfield"]);
		$tmp_orders = array();
		if ($ama_order_id!="")
		{
			if (sizeof($usershops)>0)
			{
				$res=q("SELECT * FROM shop_orders WHERE foreign_OrderID = '".$ama_order_id."' AND shop_id IN (".implode(", ", $usershops).")", $dbshop, __FILE__, __LINE__);
			}
			else
			{
				$res=q("SELECT * FROM shop_orders WHERE foreign_OrderID = '".$ama_order_id."'", $dbshop, __FILE__, __LINE__);
			}
		//	$z=0;
			while ($row=mysqli_fetch_assoc($res))
			{
				$tmp_orders[]=$row;
			//	$tmp_orders[$z]['order_note'] = get_order_note_from_conversation($row['id_order']);
			//	$z++;
			}
		}
	}
	else
	{

		if ($_POST["OrderBy"]!="") $orderby=" ORDER BY ".$_POST["OrderBy"];
		if ($_POST["OrderBy"]!="" && $_POST["OrderDirection"]=="down") $orderby.=" DESC";
		
		$filter="";
		if ($_POST["FILTER_Platform"]>0)
		{
			$filter.=" AND shop_id = ".$_POST["FILTER_Platform"];
		}
		//SHOPS AUSBLENDEN, DIE NICHT ZUR SITE gehören	
		if ($_POST["FILTER_Platform"]==0 || $_POST["FILTER_Platform"]=="" || !isset($_POST["FILTER_Platform"]))
		{
			$filter.=" AND shop_id IN (".implode(", ", $usershops).")";
		}
	

		if ($_POST["FILTER_Country"]=="national")
		{
			$filter.=" AND (bill_country_code = 'DE' OR bill_country_code = 'AT' OR bill_country_code ='')";
		}
		if ($_POST["FILTER_Country"]=="international")
		{
			$filter.=" AND NOT bill_country_code = 'DE' AND NOT bill_country_code = 'AT'";
		}
		if ($_POST["FILTER_Country"]=="ES+PT")
		{
			$filter.=" AND (bill_country_code = 'ES' OR bill_country_code = 'PT')";
		}

		if ($_POST["FILTER_Ordertype"]==0)
		{
			$filter.=" AND ordertype_id IN (1,2,3)";
		}
		else
		{
			$filter.=" AND ordertype_id = ".$_POST["FILTER_Ordertype"];		
		}
		//OrderTypeID = 6 ausschließen -> deaktivierte Orders von Ebay
		//$filter.=" AND NOT ordertype_id = 6";

		
			$searchForUserMail=false;
			$searchForUserName=false;
			$searchForName=false;
			$searchForAdresse=false;
			$searchForMPN=false;
			$searchForEbayItemID=false;
			$searchForPayPalID=false;
			$searchForShopOrderID=false;
			$searchForTrackingID=false;
	
		if ($_POST["FILTER_SearchFor"]>0 && $_POST["FILTER_Searchfield"]!="")
		{
			$searchForAll=false;
			switch ($_POST["FILTER_SearchFor"])
			{
				case 1: $searchForUserMail=true; break;
				case 2: $searchForUserName=true; break;
				case 3: $searchForName=true; break;
				case 4: $searchForAdresse=true; break;
				case 5: $searchForMPN=true; break;
				case 6: $searchForEbayItemID=true; break; 
				case 7: $searchForPayPalID=true; break;
				case 8: $searchForShopOrderID=true; break;
				case 9: $searchForTrackingID=true; break;
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
				$rangestart="firstmod>".(time()-3600*24*30); // Zeitpunkt vor 90 Tagen
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
		
		$limit='';
		$exchange_rate=0;
		
	
	//ERMITTLE DATEN -------------------------------------------------------------------------------------------
	//FILTER 0
		if ($searchForAll)
		{
			$res=q("SELECT * FROM shop_orders WHERE ".$rangestart.$rangeend.$filter.$orderby.$limit.";", $dbshop, __FILE__, __LINE__);
			$k=0;
			while ($row=mysqli_fetch_assoc($res))
			{
				if ($row["combined_with"]==0 || $row["combined_with"]==-1 || $row["combined_with"]==$row["id_order"])
				{
					$orders[$k]=$row;
					$orders[$k]["entry_pos"]=$k+1;
				
					//$orders[$k]['order_note'] = get_order_note_from_conversation($row['id_order']);
					
					$entriesCount++;
	
					$k++;
				}
			}
		}
	
	
	//Filter  1
		if ($searchForUserMail)
		{
	
			$qry_string=strtolower(trim($_POST["FILTER_Searchfield"]));
		
			$res=q("SELECT * FROM shop_orders WHERE ".$rangestart.$rangeend.$filter.$orderby.$limit.";", $dbshop, __FILE__, __LINE__);
			$k=0;
			while ($row=mysqli_fetch_assoc($res))
			{
				if ($row["combined_with"]==0 || $row["combined_with"]==-1 || $row["combined_with"]==$row["id_order"])
				{
					if (strpos(strtolower(" ".$row["usermail"]), $qry_string)>0 )
					{
						$orders[$k]=$row;
						$orders[$k]["entry_pos"]=$k+1;
		
						//$orders[$k]['order_note'] = get_order_note_from_conversation($row['id_order']);
		
						$entriesCount++;
		
						$k++;
		
					}
				}
			}
		}
		
	//FILTER 2 - search for Username
		if ($searchForUserName)
		{
			$qry_string=strtolower(trim($_POST["FILTER_Searchfield"]));
	
			$res_UserName=q("SELECT OrderID, BuyerUserID FROM ebay_orders GROUP BY OrderID;", $dbshop, __FILE__, __LINE__);
			$k=0;
			while ($row_UserName=mysqli_fetch_assoc($res_UserName))
			{
	
				if (strpos(strtolower(" ".$row_UserName["BuyerUserID"]), $qry_string)>0 )
				{
					$res=q("SELECT * FROM shop_orders WHERE foreign_OrderID = '".$row_UserName["OrderID"]."' AND ".$rangestart.$rangeend.$filter.$orderby.$limit.";", $dbshop, __FILE__, __LINE__);
	
					if (mysqli_num_rows($res)>0)
					{
						while($row=mysqli_fetch_assoc($res))
						{
							if ($row["combined_with"]==0 || $row["combined_with"]==-1 || $row["combined_with"]==$row["id_order"])
							{
	
								$orders[$k]=$row;						
								$orders[$k]["entry_pos"]=$k+1;
		
					//			$orders[$k]['order_note'] = get_order_note_from_conversation($row['id_order']);
		
								$entriesCount++;
								$k++;
							}
						}
					}
				}
			}
		}
		
	//FILTER 3 & 4
		if ($searchForName || $searchForAdresse)	
		{
			$qry_string = array();
			$qry_string=explode(' ', trim($_POST["FILTER_Searchfield"]));
			$qry_string_size=sizeof($qry_string);
			for ($i=0; $i<$qry_string_size; $i++)
			{
				$qry_string[$i]=strtolower(trim($qry_string[$i]));
			}
	
			$from=($_POST["ResultPage"]-1)*$_POST["ResultRange"];
			$to=$from+$_POST["ResultRange"];
			$k=0;
			
			$res=q("SELECT * FROM shop_orders WHERE ".$rangestart.$rangeend.$filter.$orderby.";", $dbshop, __FILE__, __LINE__);
			while ($row=mysqli_fetch_assoc($res))
			{
				if ($row["combined_with"]==0 || $row["combined_with"]==-1 || $row["combined_with"]==$row["id_order"])
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
		
					//	$orders[$k]['order_note'] = get_order_note_from_conversation($row['id_order']);
		
						$k++;
						$entriesCount++;
					}
				}
			}
		}
		
	//FILTER 5
	
		if ($searchForMPN)
		{
			$resMPN=q("SELECT MPN, id_item FROM shop_items WHERE MPN = '".trim($_POST["FILTER_Searchfield"])."';",$dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($resMPN)>0) 
			{
				$rowMPN=mysqli_fetch_assoc($resMPN);
				// GET ORDER-ITEMS WITH MPN
				$res_order_items=q("SELECT * FROM shop_orders_items WHERE item_id = '".$rowMPN["id_item"]."';",$dbshop, __FILE__, __LINE__);
				while ($row_order_items=mysqli_fetch_assoc($res_order_items))
				{
					$order_items[$row_order_items["order_id"]]=$row_order_items["item_id"];
				}
			
				$res=q("SELECT * FROM shop_orders WHERE ".$rangestart.$rangeend.$filter.$orderby.";", $dbshop, __FILE__, __LINE__);
		
				$from=($_POST["ResultPage"]-1)*$_POST["ResultRange"];
				$to=$from+$_POST["ResultRange"];
				$k=0;
				while ($row=mysqli_fetch_assoc($res))
				{
					if ($row["combined_with"]==0 || $row["combined_with"]==-1 || $row["combined_with"]==$row["id_order"])
					{
		
						if (isset($order_items[$row["id_order"]]))
						{
							$orders[$k]=$row;
							$orders[$k]["entry_pos"]=$k+1;
		
						//	$orders[$k]['order_note'] = get_order_note_from_conversation($row['id_order']);
							
							$entriesCount++;
							$k++;
						}
					}
				}
			}
		}
		
	//FILTER 6
		if ($searchForEbayItemID)
		{
			$qry_string=trim($_POST["FILTER_Searchfield"]);
	
			$k=0;
			
			$res_ItemID=q("SELECT a.OrderID, b.ItemItemID FROM ebay_orders as a, ebay_orders_items as b WHERE a.OrderID=b.OrderID AND b.ItemItemID LIKE '%".$qry_string."%';", $dbshop, __FILE__, __LINE__);
				
			while ($row_ItemID=mysqli_fetch_assoc($res_ItemID))
			{
				$res=q("SELECT * FROM shop_orders WHERE foreign_OrderID = '".$row_ItemID["OrderID"]."' AND ".$rangestart.$rangeend.$filter.$orderby.";", $dbshop, __FILE__, __LINE__);
	
				if (mysqli_num_rows($res)>0)
				{
					$row=mysqli_fetch_assoc($res);
					if ($row["combined_with"]==0 || $row["combined_with"]==-1 || $row["combined_with"]==$row["id_order"])
					{
		
						$orders[$k]=$row;
						$orders[$k]["entry_pos"]=$k+1;
		
						//$orders[$k]['order_note'] = get_order_note_from_conversation($row['id_order']);
					
						$entriesCount++;
						$k++;
					}
				}
			}
		}
		
	// FILTER 8
		if ($searchForShopOrderID)
		{
			$qry_string=strtolower(trim($_POST["FILTER_Searchfield"]));
		
			$res=q("SELECT * FROM shop_orders WHERE ".$rangestart.$rangeend.$filter.$orderby.$limit.";", $dbshop, __FILE__, __LINE__);
			$k=0;
			while ($row=mysqli_fetch_assoc($res))
			{
				if ($row["combined_with"]==0 || $row["combined_with"]==-1 || $row["combined_with"]==$row["id_order"])
				{
	
					if (strpos(" ".$row["id_order"], $qry_string)>0 )
					{
						$orders[$k]=$row;
						$orders[$k]["entry_pos"]=$k+1;
		
					//	$orders[$k]['order_note'] = get_order_note_from_conversation($row['id_order']);
						
						$entriesCount++;
		
						$k++;
		
					}
				}
			}
		}
		
	//searchForTrackingID
		if ($searchForTrackingID)
		{
			$qry_string=strtolower(trim($_POST["FILTER_Searchfield"]));
		
			$res=q("SELECT * FROM shop_orders WHERE ".$rangestart.$rangeend.$filter.$orderby.$limit.";", $dbshop, __FILE__, __LINE__);
			$k=0;
			while ($row=mysqli_fetch_assoc($res))
			{
				if ($row["combined_with"]==0 || $row["combined_with"]==-1 || $row["combined_with"]==$row["id_order"])
				{
	
					if (strpos(" ".$row["shipping_number"], $qry_string)>0 || strpos(" ".$row["RetourLabelID"], $qry_string)>0 )
					{
						$orders[$k]=$row;
						$orders[$k]["entry_pos"]=$k+1;
		
					//	$orders[$k]['order_note'] = get_order_note_from_conversation($row['id_order']);
						
						$entriesCount++;
		
						$k++;
		
					}
				}
			}
		}
	
		
	// STATUS FILTER ################################################################################################
	// STATUS 1
		if ($_POST["FILTER_Status"]==1)
		{
	//		$res=q("SELECT * FROM shop_orders WHERE payment_type_id = 0 AND ".$rangestart.$rangeend.$filter.$orderby.$limit.";", $dbshop, __FILE__, __LINE__);
			$j=0;
			for ($k=0; $k<sizeof($orders); $k++)
			{
				if ($payment[$orders[$k]["payments_type_id"]]["ship_at_once"]==0 && $orders[$k]["Payments_TransactionState"]!="Completed" && $orders[$k]["Payments_TransactionState"]!="OK" && $orders[$k]["Payments_TransactionState"]!="Refunded" && $orders[$k]["status_id"]!=4 )
				{
					$tmp_orders[$j]=$orders[$k];
					$j++;
				}
			}
		}
	
	// FILTERSTATUS 2 - bezahlt & nicht versand beauftragt
	
		elseif ($_POST["FILTER_Status"]==2)
		{
			$j=0;
			for ($k=0; $k<sizeof($orders); $k++)
			{
	
				//if ((($orders[$k]["Payments_TransactionStateDate"]!=0 && $orders[$k]["payments_type_id"]==2) || $orders[$k]["payments_type_id"]!=0) && $orders[$k]["status_id"]==1)
				if ( ($payment[$orders[$k]["payments_type_id"]]["ship_at_once"]==1 || ($payment[$orders[$k]["payments_type_id"]]["ship_at_once"]==0 && ($orders[$k]["Payments_TransactionState"]=="Completed" || $orders[$k]["Payments_TransactionState"]=="OK"))) && (in_array($orders[$k]["status_id"], array(1,7)))  )
				{
					$tmp_orders[$j]=$orders[$k];
					$j++;
				}
			}
			$entriesCount=$j;
		}
	
	
	// FILTERSTATUS 3 - Auftrag & nicht versand
	
		elseif ($_POST["FILTER_Status"]==3)
		{
			$j=0;
			for ($k=0; $k<sizeof($orders); $k++)
			{
				if ($orders[$k]["status_id"]==2)
				{
					$tmp_orders[$j]=$orders[$k];
					$j++;
				}
			}
			$entriesCount=$j;
		}
	// FILTERSTATUS 4 - versand beauftragt & versand
	
		elseif ( $_POST["FILTER_Status"]==4 )
		{
			$j=0;
			for ($k=0; $k<sizeof($orders); $k++)
			{
				if ($orders[$k]["status_id"]==3 || $orders[$k]["status_id"]==6)
				{
					$tmp_orders[$j]=$orders[$k];
					$j++;
				}
			}
			$entriesCount=$j;
		}
	
	// FILTERSTATUS 5 - Bestellung abgebrochen
		elseif ( $_POST["FILTER_Status"]==5 )
		{
			$j=0;
			for ($k=0; $k<sizeof($orders); $k++)
			{
				if ($orders[$k]["status_id"]==4)
				{
					$tmp_orders[$j]=$orders[$k];
					$j++;
				}
			}
			$entriesCount=$j;
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
	
					$j++;
				}
			}
			$entriesCount=$j;
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
	
					$j++;
				}
			}
			$entriesCount=$j;
		}
		//FILTERSTATUS 20 - Versand EXPRESS
		elseif ($_POST["FILTER_Status"]==20)
		{
			$j=0;
			for ($k=0; $k<sizeof($orders); $k++)
			{
				if ( in_array($orders[$k]["shipping_type_id"], array(2,7)) )
				{
					$tmp_orders[$j]=$orders[$k];
	
					$j++;
				}
			}
			$entriesCount=$j;
		}
	
		//FILTERSTATUS 21 - Versand DPD
		elseif ($_POST["FILTER_Status"]==21)
		{
			$j=0;
			for ($k=0; $k<sizeof($orders); $k++)
			{
				if ( in_array($orders[$k]["shipping_type_id"], array(3,6,9)) )
				{
					$tmp_orders[$j]=$orders[$k];
	
					$j++;
				}
			}
			$entriesCount=$j;
		}
	
		//FILTERSTATUS 22 - Versand DHL
		elseif ($_POST["FILTER_Status"]==22)
		{
			$j=0;
			for ($k=0; $k<sizeof($orders); $k++)
			{
				if ( in_array($orders[$k]["shipping_type_id"], array(1,2,5,7,15)) )
				{
					$tmp_orders[$j]=$orders[$k];
	
					$j++;
				}
			}
			$entriesCount=$j;
		}
		
		//FILTERSTATUS 30 - Bestellung versendbar (Car = green || blue  &&  Payment done  &&  Shop_type = 2)
		/*
			VERSENDBAR:
			- Car Blue||Green & Payment: ship_at_once==1
			- Car Blue||Green & Payment: ship_at_once==0 && Payment_status = Completed||OK
		*/
		elseif ($_POST["FILTER_Status"]==30)
		{
			//$state_ids=array(1,2,7);
			$j=0;
			for ($k=0; $k<sizeof($orders); $k++)
			{
				//if ($shop_type[$orders[$k]["shop_id"]]==2 && in_array($orders[$k]["status_id"], array(1,2,7)) &&  $orders[$k]["payments_type_id"]!=0 && $orders[$k]["Payments_TransactionStateDate"]!=0)
				//if (in_array($orders[$k]["status_id"], array(1,7)) && $orders[$k]["payments_type_id"]!=0 && $orders[$k]["Payments_TransactionStateDate"]!=0)
				
				//CHECK IF NOT SHIPPED
				if (in_array($orders[$k]["status_id"], array(1,7)))
				{
					//CHECK PAYMENT (ship at once || Payment = completed)
					if ($payment[$orders[$k]["payments_type_id"]]["ship_at_once"]==1 || ($payment[$orders[$k]["payments_type_id"]]["ship_at_once"]==0 && $orders[$k]["Payments_TransactionState"]=="Completed"))
					{
						$checked_green_count=0;
						$items_count=0;
						//GET COMBINED ITEMS
						if ($orders[$k]["combined_with"]>0)
						{
							$res_c_orders=q("SELECT * FROM shop_orders WHERE combined_with = ".$orders[$k]["combined_with"].";", $dbshop, __FILE__, __LINE__);
							while($row_c_orders=mysqli_fetch_assoc($res_c_orders))
							{
								$res_c_items=q("SELECT * FROM shop_orders_items WHERE order_id = ".$row_c_orders["id_order"].";", $dbshop, __FILE__, __LINE__);
								while ($row_c_items=mysqli_fetch_assoc($res_c_items))
								{
									$items_count++;
									if ($row_c_items["checked"]==1) $checked_green_count++;
								}
							}
						}
						else
						{
							$res_items=q("SELECT * FROM shop_orders_items WHERE order_id = ".$orders[$k]["id_order"].";", $dbshop, __FILE__, __LINE__);
							$items_count=mysqli_num_rows($res_items);
							while ($row_items=mysqli_fetch_assoc($res_items))
							{
								if ($row_items["checked"]==1) $checked_green_count++;
							}
						}
						//echo "Greencount: ".$checked_green_count." FZ-MAIL: ".$orders[$k]["fz_fin_mail_count"]."<br />";
		
						//CHECK CAR COLOR - green || blue
						if ($checked_green_count==$items_count || $orders[$k]["fz_fin_mail_count"]>=3)	
						{
							$tmp_orders[$j]=$orders[$k];
		
							$j++;
						}
					}
				}
			}
			$entriesCount=$j;
		}
		
		
	
		else {$tmp_orders=$orders;}
	}

	
//AUSGABE vorbereiten
	$xmldata="";
	$entriesCount=0;
	$from=($_POST["ResultPage"]-1)*$_POST["ResultRange"];
	$to=$from+$_POST["ResultRange"];
	
	$exchange_rate=0;
//	$tmp_orders=$orders;
	$orders=array();
	$entriesCount=0;
	$j=0;
	for ($k=0; $k<sizeof($tmp_orders); $k++)
	{
		if ($k>=$from && $k<=$to)
		{
			$orders[$j]=$tmp_orders[$k];
			$orders[$j]["entry_pos"]=$k+1;
			
			$j++;
		}
		$entriesCount++;
	}

//ERSTELLE XML ------------------------------------------------------------------------------------------------

	//GET RETURNS
		//GET ARRAY OF ORDERIDs
		$orderids=array();
		for ($k=0; $k<sizeof($orders); $k++)
		{
			$orderids[]=$orders[$k]["id_order"];
		}
		$returns=array();
		$returns_ids=array();
		if (sizeof($orderids)>0)
		{
			$res_returns = q("SELECT * FROM shop_returns2 WHERE order_id IN (".implode(",",$orderids).");", $dbshop, __FILE__, __LINE__);
			while ($row_returns = mysqli_fetch_assoc($res_returns))
			{
				if (isset($returns[$row_returns["order_id"]]))
				{
					$returns[$row_returns["order_id"]][sizeof($returns[$row_returns["order_id"]])]=$row_returns;
				}
				else
				{
					$returns[$row_returns["order_id"]][0]=$row_returns;
				}
				$returns_ids[]=$row_returns["id_return"];
			}
		}
	//GET RETURNS ITEMS 
	$returns_items = array();
	if (sizeof($returns_ids)>0)
	{
		$res_returns_items = q("SELECT * FROM shop_returns_items WHERE return_id IN (".implode(",",$returns_ids).");", $dbshop, __FILE__, __LINE__);
		while ($row_returns_items=mysqli_fetch_assoc($res_returns_items))
		{
			if (isset($returns_items[$row_returns_items["return_id"]]))
			{
				$returns_items[$row_returns_items["return_id"]][sizeof($returns_items[$row_returns_items["return_id"]])]=$row_returns_items;
			}
			else
			{
				$returns_items[$row_returns_items["return_id"]][0]=	$row_returns_items;
			}
			
		}

	}
	
	$usernames = array();
	
	// GET INVOICE FILE IDs
	$invoice_ids = array();
	for ($k=0; $k<sizeof($orders); $k++)
	{
		$invoice_ids[]=$orders[$k]["invoice_id"];
	}
	
	$invoices = array();
	if (sizeof($invoice_ids)>0)
	{
		$res_invoices = q("SELECT * FROM idims_auf_status WHERE rng_id IN (".implode(", ", $invoice_ids).")",$dbshop, __FILE__, __LINE__);
		
		while ($row_invoices = mysqli_fetch_assoc($res_invoices))
		{
			$invoices[$row_invoices["rng_id"]] = $row_invoices["rng_file_id"];	
		}
	}

	//GET SHOP_SITES
	$shop_sites = array();
	$res_shops = q("SELECT * FROM shop_shops", $dbshop, __FILE__, __LINE__);
	while ($row_shops = mysqli_fetch_assoc($res_shops))
	{
		if ($row_shops["parent_shop_id"] == 0)
		{
			$shop_sites[$row_shops["id_shop"]] = $row_shops["site_id"];
		}
	}
	mysqli_data_seek($res_shops, 0);
	while ($row_shops = mysqli_fetch_assoc($res_shops))
	{
		if ($row_shops["parent_shop_id"] != 0)
		{
			if (isset($shop_sites[$row_shops["parent_shop_id"]]))
			{
				$shop_sites[$row_shops["id_shop"]] = $shop_sites[$row_shops["parent_shop_id"]];
			}
		}
	}

	$user_ids = array();
	for ($k=0; $k<sizeof($orders); $k++)
	{
		$user_ids[]=$orders[$k]["customer_id"];
	}
	//USER CARFLEET COUNT
	$usercarfleet = array();
	if (sizeof($user_ids)>0)
	{
		$res_carfleet = q("SELECT * FROM shop_carfleet WHERE user_id IN (".implode(", ", $user_ids).") AND active = 1",$dbshop, __FILE__, __LINE__);
		
		while ($row_carfleet = mysqli_fetch_assoc($res_carfleet))
		{
			if (isset($usercarfleet[$row_carfleet["user_id"]][$shop_sites[$row_carfleet["shop_id"]]]))
			{
				$usercarfleet[$row_carfleet["user_id"]][$shop_sites[$row_carfleet["shop_id"]]]++;
			}
			else
			{
				$usercarfleet[$row_carfleet["user_id"]][$shop_sites[$row_carfleet["shop_id"]]] = 1;
			}
		}
	}
	
	

	for ($k=0; $k<sizeof($orders); $k++)
	{
		$orderItemTotal=0;
		$orderItemsTotal=0;
		$orderTotal=0;
		$orderTotalCount=0;
		
		if (gewerblich($orders[$k]["customer_id"])) $gewerblich = 1; else $gewerblich =0;
		

		$xmldata.="<Order>\n";
		$xmldata.="	<gewerblich>".$gewerblich."</gewerblich>\n";
		$xmldata.="	<entry_pos>".$orders[$k]["entry_pos"]."</entry_pos>\n";
		$xmldata.="	<id_order>".$orders[$k]["id_order"]."</id_order>\n";
		$xmldata.="	<AUF_ID>".$orders[$k]["AUF_ID"]."</AUF_ID>\n";
		$xmldata.="	<shop_id>".$orders[$k]["shop_id"]."</shop_id>\n";
		$xmldata.="	<ordertype_id>".$orders[$k]["ordertype_id"]."</ordertype_id>\n";
		$xmldata.="	<customer_id>".$orders[$k]["customer_id"]."</customer_id>\n";
		$xmldata.="	<Currency_Code>".$orders[$k]["Currency_Code"]."</Currency_Code>\n";
		$xmldata.="	<VAT>".$orders[$k]["VAT"]."</VAT>\n";
		$xmldata.="	<foreign_OrderID>".$orders[$k]["foreign_OrderID"]."</foreign_OrderID>\n";
		$xmldata.="	<usermail><![CDATA[".$orders[$k]["usermail"]."]]></usermail>\n";
		$xmldata.="	<userphone><![CDATA[".$orders[$k]["userphone"]."]]></userphone>\n";
		//KUNDENEINGABE ONLINESHOP		
		$xmldata.="	<comment><![CDATA[".$orders[$k]["comment"]."]]></comment>\n";
		//KUNDENEINGABE ONLINESHOP
		$xmldata.="	<ordernr><![CDATA[".$orders[$k]["ordernr"]."]]></ordernr>\n";
				
		$xmldata.="	<bill_company><![CDATA[".$orders[$k]["bill_company"]."]]></bill_company>\n";
		$xmldata.="	<bill_firstname><![CDATA[".$orders[$k]["bill_firstname"]."]]></bill_firstname>\n";
		$xmldata.="	<bill_lastname><![CDATA[".$orders[$k]["bill_lastname"]."]]></bill_lastname>\n";
		$xmldata.="	<bill_zip><![CDATA[".$orders[$k]["bill_zip"]."]]></bill_zip>\n";
		$xmldata.="	<bill_city><![CDATA[".$orders[$k]["bill_city"]."]]></bill_city>\n";
		$xmldata.="	<bill_street><![CDATA[".$orders[$k]["bill_street"]."]]></bill_street>\n";
		$xmldata.="	<bill_number><![CDATA[".$orders[$k]["bill_number"]."]]></bill_number>\n";
		$xmldata.="	<bill_additional><![CDATA[".$orders[$k]["bill_additional"]."]]></bill_additional>\n";
		$xmldata.="	<bill_country><![CDATA[".$orders[$k]["bill_country"]."]]></bill_country>\n";
		$xmldata.="	<bill_country_code><![CDATA[".$orders[$k]["bill_country_code"]."]]></bill_country_code>\n";
		$xmldata.="	<bill_adr_id><![CDATA[".$orders[$k]["bill_adr_id"]."]]></bill_adr_id>\n";
		
		$xmldata.="	<ship_company><![CDATA[".$orders[$k]["ship_company"]."]]></ship_company>\n";
		$xmldata.="	<ship_firstname><![CDATA[".$orders[$k]["ship_firstname"]."]]></ship_firstname>\n";
		$xmldata.="	<ship_lastname><![CDATA[".$orders[$k]["ship_lastname"]."]]></ship_lastname>\n";
		$xmldata.="	<ship_zip><![CDATA[".$orders[$k]["ship_zip"]."]]></ship_zip>\n";
		$xmldata.="	<ship_city><![CDATA[".$orders[$k]["ship_city"]."]]></ship_city>\n";
		$xmldata.="	<ship_street><![CDATA[".$orders[$k]["ship_street"]."]]></ship_street>\n";
		$xmldata.="	<ship_number><![CDATA[".$orders[$k]["ship_number"]."]]></ship_number>\n";
		$xmldata.="	<ship_additional><![CDATA[".$orders[$k]["ship_additional"]."]]></ship_additional>\n";
		$xmldata.="	<ship_country><![CDATA[".$orders[$k]["ship_country"]."]]></ship_country>\n";
		$xmldata.="	<ship_country_code><![CDATA[".$orders[$k]["ship_country_code"]."]]></ship_country_code>\n";
		$xmldata.="	<ship_adr_id><![CDATA[".$orders[$k]["ship_adr_id"]."]]></ship_adr_id>\n";
		$xmldata.="	<invoice_nr><![CDATA[".$orders[$k]["invoice_nr"]."]]></invoice_nr>\n";
		if ( isset($invoices[$orders[$k]["invoice_id"]]))
		{
			$xmldata.="	<invoice_file_id>".$invoices[$orders[$k]["invoice_id"]]."</invoice_file_id>\n";
		}
		else
		{
			$xmldata.="	<invoice_file_id>0</invoice_file_id>\n";
		}
		
		$xmldata.="	<bill_address_manual_update><![CDATA[".$orders[$k]["bill_address_manual_update"]."]]></bill_address_manual_update>\n";
		$xmldata.="	<bill_address_manual_update><![CDATA[".$orders[$k]["bill_address_manual_update"]."]]></bill_address_manual_update>\n";
		
		$xmldata.="	<firstmod>".$orders[$k]["firstmod"]."</firstmod>\n";
		$xmldata.="	<firstmod_user>".$orders[$k]["firstmod_user"]."</firstmod_user>\n";
		$xmldata.=" <orderDate>".$orders[$k]["firstmod"]."</orderDate>\n";
		$xmldata.=" <PaymentDate>".$orders[$k]["Payments_TransactionStateDate"]."</PaymentDate>\n";
		$xmldata.=" <PaymentType>".$orders[$k]["Payments_Type"]."</PaymentType>\n";
		$xmldata.=" <shipping_details><![CDATA[".$orders[$k]["shipping_details"]."]]></shipping_details>\n";
		$xmldata.=" <shipping_type_id>".$orders[$k]["shipping_type_id"]."</shipping_type_id>\n";
		$xmldata.=" <shipping_number><![CDATA[".$orders[$k]["shipping_number"]."]]></shipping_number>\n";
		$xmldata.=" <shipping_label_file_id><![CDATA[".$orders[$k]["shipping_label_file_id"]."]]></shipping_label_file_id>\n";
		$xmldata.=" <status_id>".$orders[$k]["status_id"]."</status_id>\n";
		$xmldata.=" <status_date>".$orders[$k]["status_date"]."</status_date>\n";
		$xmldata.=" <PaymentTypeID>".$orders[$k]["payments_type_id"]."</PaymentTypeID>\n";
		$xmldata.=" <Payments_TransactionID><![CDATA[".$orders[$k]["Payments_TransactionID"]."]]></Payments_TransactionID>\n";
		$xmldata.=" <Payments_TransactionState><![CDATA[".$orders[$k]["Payments_TransactionState"]."]]></Payments_TransactionState>\n";
		//$xmldata.=" <order_note><![CDATA[".$orders[$k]["order_note"]."]]></order_note>\n";
	   	//$xmldata.=" <order_note><![CDATA[".get_order_note_from_conversation($orders[$k]["id_order"])."]]></order_note>\n";
		$order_notes = get_order_note_from_conversation($orders[$k]["id_order"]);
		//	$xmldata.=" <order_notes>";
		foreach( $order_notes as $value )
		{
			$xmldata.=" <order_note><![CDATA[".$value."]]></order_note>\n";	
		}
		//$xmldata.=" </order_notes>\n";	
			
		$xmldata.=" <PayPal_BuyerNote><![CDATA[".$orders[$k]["PayPal_BuyerNote"]."]]></PayPal_BuyerNote>\n";
		$xmldata.=" <RetourLabelID><![CDATA[".$orders[$k]["RetourLabelID"]."]]></RetourLabelID>\n";
		$xmldata.=" <RetourLabelTimestamp><![CDATA[".$orders[$k]["RetourLabelTimestamp"]."]]></RetourLabelTimestamp>\n";
		$xmldata.=" <combined_with><![CDATA[".$orders[$k]["combined_with"]."]]></combined_with>\n";

		//USERCARFLEETCOUNT
		if (isset($usercarfleet[$orders[$k]["customer_id"]][$shop_sites[$orders[$k]["shop_id"]]]))
		{
			$xmldata.=" <user_carfleet_count>".$usercarfleet[$orders[$k]["customer_id"]][$shop_sites[$orders[$k]["shop_id"]]]."</user_carfleet_count>\n";
		}
		else
		{
			$xmldata.=" <user_carfleet_count>0</user_carfleet_count>\n";	
		}
		
		//FINABFRAGEMAILS	
		$xmldata.=" <fz_fin_mail_count>".$orders[$k]["fz_fin_mail_count"]."</fz_fin_mail_count>\n";
		$xmldata.=" <fz_fin_mail_lastsent>".$orders[$k]["fz_fin_mail_lastsent"]."</fz_fin_mail_lastsent>\n";
		
		//SELLERINFO
		$xmldata.=" <lastmod>".$orders[$k]["lastmod"]."</lastmod>\n";
		$xmldata.=" <lastmod_user>".$orders[$k]["lastmod_user"]."</lastmod_user>\n";
		
		//DEFINE USERNAME
		if (!isset($usernames[$orders[$k]["lastmod_user"]]))
		{
			$res_user = q("SELECT * from cms_users WHERE id_user = ".$orders[$k]["lastmod_user"], $dbweb, __FILE__, __LINE__);
			if ( mysqli_num_rows($res_user)==0 )
			{
				$usernames[$orders[$k]["lastmod_user"]] = "UNKNOWN";
			}
			else
			{
				$row_user = mysqli_fetch_assoc($res_user);
				if ( $row_user["firstname"]!="" || $row_user["lastname"]!="" )
				{
					if ($row_user["firstname"]!="")	
					{
						$usernames[$orders[$k]["lastmod_user"]] = $row_user["firstname"]." ".$row_user["lastname"];
					}
					else
					{
						$usernames[$orders[$k]["lastmod_user"]] = $row_user["lastname"];
					}
				}
				else
				{
					$usernames[$orders[$k]["lastmod_user"]] = $row_user["name"];
				}
			}
		
		}
		
		$xmldata.=" <lastmod_username><![CDATA[".$usernames[$orders[$k]["lastmod_user"]]."]]></lastmod_username>\n";
		
		//RETURNS
		if (sizeof ($returns[$orders[$k]["id_order"]])>0)
		{
			$xmldata.=" <returns>\n";
			for ($l=0; $l<sizeof($returns[$orders[$k]["id_order"]]); $l++)
			{
				$xmldata.="		<return>\n";
				$id_return=0;
				while (list ($key, $val) = each ($returns[$orders[$k]["id_order"]][$l]))
				{		
					$xmldata.="			<".$key."><![CDATA[".$val."]]></".$key.">\n";
					if ($key=="id_return") $id_return = $val;
				}
				//ADD RETURNITEMS
				if (isset($returns_items[$id_return]))
				{
					$xmldata.="			<returnitems>\n";
					for ($m=0; $m<sizeof($returns_items[$id_return]); $m++)
					{
						$xmldata.="			<returnitem>\n";
						while (list ($key, $val) = each ($returns_items[$id_return][$m]))
						{		
							$xmldata.="			<".$key."><![CDATA[".$val."]]></".$key.">\n";
						}
						$xmldata.="			</returnitem>\n";
					}
					$xmldata.="			</returnitems>\n";
				}
				$xmldata.="		</return>\n";
			}
			$xmldata.="	</returns>\n";
		}
		
		//USERID - PLATFORM
		$buyerid="";
		$VPN=0;

		if ($shop_type[$orders[$k]["shop_id"]]==2)
		{
			
			$res_buyerid=q("SELECT * FROM ebay_orders WHERE OrderID = '".$orders[$k]["foreign_OrderID"]."';", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_buyerid)>0)
			{
				$row_buyerid=mysqli_fetch_assoc($res_buyerid);
				$buyerid=$row_buyerid["BuyerUserID"];	
				$VPN=$row_buyerid["ShippingDetailsSellingManagerSalesRecordNumber"];	
							
			}
			else
			{
				$VPN = 0;
				$buyerid ="";	
				
			}
			
		}
		elseif ($shop_type[$orders[$k]["shop_id"]]==1)
		{
			
			$res_buyerid=q("SELECT * FROM cms_users WHERE id_user = ".$orders[$k]["customer_id"].";", $dbweb, __FILE__, __LINE__);
			if (mysqli_num_rows($res_buyerid)>0)
			{
				$row_buyerid=mysqli_fetch_assoc($res_buyerid);
				$buyerid=$row_buyerid["username"];	
				$VPN=$orders[$k]["id_order"];	
							
			}
			else
			{
				$VPN = $orders[$k]["id_order"];
				$buyerid ="";	
			}
		}
		elseif ($shop_type[$orders[$k]["shop_id"]]==3)
		{
			$VPN = $orders[$k]["foreign_OrderID"];
			$buyerid ="";	
		}

		else
		{
			
			$VPN=$orders[$k]["id_order"];
			$buyerid = "";
		}

		$xmldata.=" <buyerUserID><![CDATA[".$buyerid."]]></buyerUserID>\n";
		$xmldata.=" <VPN>".$VPN."</VPN>\n";
		
		//CONVERSATION
		$post_data["API"]="crm";
		$post_data["APIRequest"]="ConversationGet";
		$post_data["order_id"]=$orders[$k]["id_order"];
		
		$postdata=http_build_query($post_data);
		
		$responseXml = post(PATH."soa2/", $postdata);
		$use_errors = libxml_use_internal_errors(true);
		try
		{
			$response = new SimpleXMLElement($responseXml);
		}
		catch(Exception $e)
		{
			//echo $e;
		}
		libxml_clear_errors();
		libxml_use_internal_errors($use_errors);
		$con_cnt_order=(int)$response->cnt_order[0];
		$con_cnt_all=(int)$response->cnt_all[0];		
		
		
		$xmldata.="	<OrderItems>\n";
		//ITEMS
		$items=array();
		$shipping_costs=0;
		//GET KOMBINED ITEMS
		if ($orders[$k]["combined_with"]>0)
		{
			$res_c_orders=q("SELECT * FROM shop_orders WHERE combined_with = ".$orders[$k]["combined_with"].";", $dbshop, __FILE__, __LINE__);
			while($row_c_orders=mysqli_fetch_assoc($res_c_orders))
			{
				$res_c_items=q("SELECT * FROM shop_orders_items WHERE order_id = ".$row_c_orders["id_order"].";", $dbshop, __FILE__, __LINE__);
				while ($row_c_items=mysqli_fetch_assoc($res_c_items))
				{
					$items[]=$row_c_items;
					
				}
				$shipping_costs+=$row_c_orders["shipping_costs"];
			}
		}
		else
		{

			$res_items=q("SELECT * FROM shop_orders_items WHERE order_id = ".$orders[$k]["id_order"].";", $dbshop, __FILE__, __LINE__);
			while ($row_items=mysqli_fetch_assoc($res_items))
			{
				$items[]=$row_items;
			}
			$shipping_costs+=$orders[$k]["shipping_costs"];
		}
			
	//	for ($i=0; $i<sizeof($shop_orders_items[$orders[$k]["id_order"]]); $i++)
	foreach ($items as $item)
		{
		
			//ITEM DESCRIPTION
			//get Ebay Title
			$ebay_title="";
			$ItemItemID="";
			$itemsku="";
			if ($shop_type[$orders[$k]["shop_id"]]==2 && $item["foreign_transactionID"]!="")
			{
				$res_items_desc=q("SELECT * FROM ebay_orders_items WHERE TransactionID = '".$item["foreign_transactionID"]."';", $dbshop, __FILE__, __LINE__);
				if (mysqli_num_rows($res_items_desc)>0)
				{
					$row_items_desc=mysqli_fetch_assoc($res_items_desc);
					$ebay_title=$row_items_desc["ItemTitle"];
					
					//MPN
					$itemsku = $row_items_desc["ItemSKU"];
					
					$ItemItemID=$row_items_desc["ItemItemID"];
				}
				else
				{
					$ebay_title="";
					$itemsku="";
				}
				
			}
			
			//GET SHOP_ITEM Title
			$shop_title="";
			$res_items_desc=q("SELECT * FROM shop_items_de WHERE id_item = ".$item["item_id"].";", $dbshop, __FILE__, __LINE__);
			$row_items_desc=mysqli_fetch_assoc($res_items_desc);
			$item_title_raw=$row_items_desc["title"];
			if (strpos($item_title_raw, "(")!== false)
			{
				$shop_title=trim(substr($item_title_raw, 0, strpos($item_title_raw, "(")));
			}
			else
			{
				$shop_title = $item_title_raw;
			}
			
		
			//GET MPN
			$res_MPN=q("SELECT * FROM shop_items WHERE id_item = ".$item["item_id"].";", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_MPN)>0)
			{
				$row_MPN=mysqli_fetch_assoc($res_MPN);
				$MPN=$row_MPN["MPN"];
			}
			else $MPN="";
			
			if ($MPN!=$itemsku)
			{
				$item_title=$shop_title;
			}
			elseif ($shop_type[$orders[$k]["shop_id"]]==2 && $item["foreign_transactionID"]!="")
			{
				$item_title=$ebay_title;
			}
			else $item_title=$shop_title;

			$exchange_rate=1/($item["exchange_rate_to_EUR"]*1);

			$orderItemTotal=round(($item["price"]*$exchange_rate),2)*$item["amount"];
			$orderItemsTotal+=$orderItemTotal;
			$orderTotalCount+=$item["amount"];

			$xmldata.="		<Item>\n";
			$xmldata.="			<OrderItemID>".$item["id"]."</OrderItemID>\n";
			$xmldata.="			<OrderItemItemID><![CDATA[".$ItemItemID."]]></OrderItemItemID>\n";
			$xmldata.="			<OrderItemMPN><![CDATA[".$MPN."]]></OrderItemMPN>\n";
			$xmldata.="			<OrderItemDesc><![CDATA[".$item_title."]]></OrderItemDesc>\n";
			$xmldata.="			<OrderItemAmount>".$item["amount"]."</OrderItemAmount>\n";
			$xmldata.="			<OrderItemPrice>".($item["price"]*$exchange_rate)."</OrderItemPrice>\n";
			$xmldata.="			<OrderItemNetto>".($item["netto"]*$exchange_rate)."</OrderItemNetto>\n";
			$xmldata.="			<OrderItemTotal>".number_format($orderItemTotal, 2,",",".")."</OrderItemTotal>\n";
			$xmldata.="			<OrderItemCustomerVehicleID>".$item["customer_vehicle_id"]."</OrderItemCustomerVehicleID>\n";
			$xmldata.="			<OrderItemChecked>".$item["checked"]."</OrderItemChecked>\n";
			$xmldata.="			<OrderItemckecked_by_user>".$item["ckecked_by_user"]."</OrderItemckecked_by_user>\n";

			$xmldata.="		</Item>\n";
	
			
		}
		
		$xmldata.="	</OrderItems>\n";
		
		$xmldata.="	<OrderConCntOrder>".$con_cnt_order."</OrderConCntOrder>\n";
		$xmldata.="	<OrderConCntAll>".$con_cnt_all."</OrderConCntAll>\n";
		
		$shipping_costs=round(($exchange_rate*$shipping_costs),2);
		
		$xmldata.="	<OrderShippingCosts>".number_format($shipping_costs, 2,",",".")."</OrderShippingCosts>\n";
		$xmldata.="	<OrderItemsTotal>".number_format($orderItemsTotal, 2,",",".")."</OrderItemsTotal>\n";
		$xmldata.="	<OrderTotal>".number_format(($orderItemsTotal+$shipping_costs), 2,",",".")."</OrderTotal>\n";
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