<?php

	//GET SHOPS
	$shops = array();
	$res_shops = q("SELECT * FROM shop_shops;", $dbshop, __FILE__, __LINE__);
	while ($row_shops = mysqli_fetch_assoc($res_shops)) 
	{
		$shops[$row_shops["id_shop"]] = $row_shops;
	}

	//GET RETURNS

		if ($_POST["date_from"]!="" && $_POST["date_to"]!="")
		{
			$rangestart= "firstmod>".strtotime($_POST["date_from"]);
			$rangeend=" AND firstmod<".(strtotime($_POST["date_to"])+86399);
		}
		else
		{
			$rangestart="firstmod>".(time()-3600*24*30); // Zeitpunkt vor 90 Tagen
			$rangeend=" AND firstmod<".time();
		}
		$filter="";
		if ($_POST["FILTER_platform"]>0)
		{
			$filter.=" AND shop_id = ".$_POST["FILTER_platform"];
		}
		if ($_POST["FILTER_status"]==0 || $_POST["FILTER_status"]==1)
		{
			$filter.=" AND state = ".$_POST["FILTER_status"];
		}
		if ($_POST["FILTER_return_type"]!="")
		{
			$filter.=" AND return_type = '".$_POST["FILTER_return_type"]."'";
		}




	$returns = array();
	$orderids = array();
	$res_returns = q("SELECT * FROM shop_returns2 WHERE ".$rangestart.$rangeend.$filter.";", $dbshop, __FILE__, __LINE__);
	while ($row_returns = mysqli_fetch_assoc($res_returns))
	{
		$returns[$row_returns["id_return"]] = $row_returns;
		$orderids[]=$row_returns["order_id"];	
	}

	//GET RETURNS ITEMS		
	$returns_items = array();
	$item_ids = array();
	$res_returns_items = q("SELECT * FROM shop_returns_items;", $dbshop, __FILE__, __LINE__);
	while ($row_returns_items = mysqli_fetch_assoc ($res_returns_items))
	{
		if (isset($returns_items[$row_returns_items["return_id"]]))
		{
			$returns_items[$row_returns_items["return_id"]][sizeof($row_returns_items["return_id"])]=$row_returns_items;	
		}
		else
		{
			$returns_items[$row_returns_items["return_id"]][0]=$row_returns_items;
		}
		$item_ids[] = $row_returns_items["item_id"];
	}
	
	//GET ORDERS
	$orders = array();
	$bill_adr_ids = array();
	$user_ids = array();
	if (sizeof($orderids)>0)
	{
		$res_orders = q("SELECT * FROM shop_orders WHERE id_order IN (".implode(", ", $orderids).");", $dbshop, __FILE__, __LINE__);
		while ($row_orders = mysqli_fetch_assoc($res_orders))
		{
			$orders[$row_orders["id_order"]] = $row_orders;
			$bill_adr_ids[]=$row_orders["bill_adr_id"];
			$user_ids[]=$row_orders["customer_id"];	
		}
	}

	//GET BILL ADRESSES
	$bill_adr = array();
	if (sizeof($bill_adr_ids)>0)
	{
		$res_adr = q("SELECT * FROM shop_bill_adr WHERE adr_id IN (".implode(", ", $bill_adr_ids).");", $dbshop, __FILE__, __LINE__);
		while ($row_adr = mysqli_fetch_assoc($res_adr))
		{
			$bill_adr[$row_adr["adr_id"]] = $row_adr;
		}
	}
	
	//GET CMS USERDATA
	$cms_users = array();
	if (sizeof($user_ids)>0)
	{
		$res_cms_users = q("SELECT * FROM cms_users WHERE id_user IN (".implode(", ", $user_ids).");", $dbweb, __FILE__, __LINE__);
		while ($row_cms_users = mysqli_fetch_assoc($res_cms_users))
		{
			$cms_users[$row_cms_users["id_user"]] = $row_cms_users;
		}
	}
	
	//GET ACCOUNT NAMES (EBAY, etc.)
	$accounts = array();
	if (sizeof($user_ids)>0)
	{
		$res_accounts = q("SELECT * FROM crm_customer_accounts3 WHERE cms_user_id IN (".implode(", ", $user_ids).");", $dbweb, __FILE__, __LINE__);
		while ($row_accounts = mysqli_fetch_assoc($res_accounts))
		{
			$account[$row_accounts["cms_user_id"]][$row_accounts["shop_type"]] = $row_accounts["shop_user_id"];
		}
	}
	
	//GET ITEMSDESCRIPTIONS
	$items = array();
	if (sizeof($item_ids)>0)
	{
		$res_items = q("SELECT * FROM shop_items_de WHERE id_item IN (".implode(", ", $item_ids).");", $dbshop, __FILE__, __LINE__);
		while ($row_items = mysqli_fetch_assoc($res_items))
		{
			$items[$row_items["id_item"]] = $row_items;
		}
	}

	//SERVICE RESPONSE -> building XML
	echo '<returns>'."\n";
	foreach ($returns as $return_id => $returndata)
	{
		echo '	<return>'."\n";
		//RETURNDATA
		while (list ($key, $val) = each ($returndata))
		{
			echo '		<'.$key.'><![CDATA['.$val.']]></'.$key.'>'."\n";
		}
		// BUYER USERNAME
		if ($bill_adr[$orders[$returndata["order_id"]]["bill_adr_id"]]["lastname"]!="")
		{
			$name = $bill_adr[$orders[$returndata["order_id"]]["bill_adr_id"]]["firstname"]." ".$bill_adr[$orders[$returndata["order_id"]]["bill_adr_id"]]["lastname"];
		}
		else
		{
			$name = $bill_adr[$orders[$returndata["order_id"]]["bill_adr_id"]]["name"];
		}
		echo '		<buyer_name><![CDATA['.$name.']]></buyer_name>'."\n";
		// BUYER USERID
		if ($shops[$orders[$returndata["order_id"]]["shop_id"]]["shop_type"]==2)
		{
			$username = $accounts[$orders[$returndata["order_id"]]["customer_id"]][2]["shop_user_id"];
		}
		elseif ($shops[$orders[$returndata["order_id"]]["shop_id"]]["shop_type"]==3)
		{
			$username = $accounts[$orders[$returndata["order_id"]]["customer_id"]][3]["shop_user_id"];
		}
		else
		{
			$username = $cms_users[$orders[$returndata["order_id"]]["customer_id"]]["username"];
		}
		echo '		<buyer_username><![CDATA['.$username.']]></buyer_username>'."\n";
		
		//KAUFDATUM
		echo '		<date_bought>'.$orders[$returndata["order_id"]]["firstmod"].'</date_bought>';
		
		//RETURNITEMS
		if (isset($returns_items[$return_id]))
		{
			echo '		<items>'."\n";
			for ($i=0; $i<sizeof($returns_items[$return_id]); $i++)
			{
				echo '			<item>'."\n";
				
				$item_id=0;
				while (list ($keyitem, $valitem) = each ($returns_items[$return_id][$i]))
				{
					echo '				<'.$keyitem.'><![CDATA['.$valitem.']]></'.$keyitem.'>'."\n";
					if ($keyitem == "item_id") $item_id = $valitem;
				}
				//ITEMTITLE
				echo '				<itemtitle><![CDATA['.$items[$item_id]["title"].']]></itemtitle>'."\n";
				echo '			</item>'."\n";
			}
			echo '		</items>'."\n";
		}
		echo '	</return>'."\n";
	}
	echo '</returns>'."\n";

?>