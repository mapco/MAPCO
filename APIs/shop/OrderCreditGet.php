<?php

	//CHECK $_POST PARAMS
	$required 				= array();
	$required["id_credit"]	= "numericNN";

	check_man_params( $required );
	
	define('TABLE_CREDITS', 'shop_orders_credits2');
	define('TABLE_CREDITS_POSITIONS', 'shop_orders_credits_positions');
	define('TABLE_RETURNS', 'shop_returns2');
	define('TABLE_RETURNS_ITEMS', 'shop_returns_items');
	define('TABLE_SHOP_ORDERS', 'shop_orders');
	define('TABLE_SHOP_ORDERS_ITEMS', 'shop_orders_items');
	define('TABLE_SHOP_ITEMS', 'shop_items');
	define('TABLE_SHOP_ITEMS_DE', '	shop_items_de');
	
	define('TABLE_CMS_USERS', '	cms_users');

	
	
	//GET CREDIT
	$res_credit = q('SELECT * FROM '.TABLE_CREDITS.' WHERE id_shop_order_credit = '.$_POST['id_credit'], $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows( $res_credit ) == 0 )
	{
		//show_error
		echo 'KEIN CREDIT GEFUNDEN';
		exit;	
	}
	
	$credit = mysqli_fetch_assoc( $res_credit );
	
	//GET CREDIT POSITIONS
	$credit_positions = array();
	$res_credit_positions = q('SELECT * FROM '.TABLE_CREDITS_POSITIONS.' WHERE order_credit_id = '.$credit["id_shop_order_credit"], $dbshop, __FILE__, __LINE__);
	while ( $row_credit_positions = mysqli_fetch_assoc( $res_credit_positions ) )
	{
		$credit_positions[] = $row_credit_positions;
	}


	$return_items = array();
	
	$shop_orders_items_ids=array();
	$orderitems=array();


	// CHECK IF CREDIT is return or exchange -> GET RETURN
	if ( $credit['return_id'] != 0 )
	{
		// GET RETURN
		$res_return = q('SELECT * FROM '.TABLE_RETURNS.' WHERE id_return = '.$credit['return_id'], $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows( $res_return ) == 0 )
		{
			//show_error();
			echo 'KEINE RÜCKGABE GEFUNDEN';
			exit;
		
		}
		
		$return = mysqli_fetch_assoc( $res_return );
		
		//GET RETURN_ITEMS
		$res_return_items = q('SELECT * FROM '.TABLE_RETURNS_ITEMS.' WHERE return_id = '.$return['id_return'], $dbshop, __FILE__, __LINE__);
		while ( $row_return_items = mysqli_fetch_assoc( $res_return_items ) )
		{
			$return_items[] = $row_return_items;
			$shop_orders_items_ids[]=$row_return_items["shop_orders_items_id"];
		}
		
		//GET EXCHANGE_ORDER
		if ( $return["exchange_order_id"] != 0 )
		{
			$res_order_exchange = q("SELECT * FROM ".TABLE_SHOP_ORDERS." WHERE id_order = ".$return["exchange_order_id"].";", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_order_exchange)==0)
			{
				//show_error(9774, 7, __FILE__, __LINE__, "id_order: ".$return["exchange_order_id"]);
			}
			$order_exchange = mysqli_fetch_assoc($res_order_exchange);
		}
		
		//GET EXCHANGE ORDERITEMS
		if (isset($order_exchange))
		{
			$res_orderitems_exchange = q("SELECT * FROM ".TABLE_SHOP_ORDERS_ITEMS." WHERE order_id = ".$order_exchange["id_order"].";", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res_orderitems_exchange)>0)	
			{
				$order_items_exchange = array();
				while ($row_orderitems_exchange = mysqli_fetch_assoc($res_orderitems_exchange))
				{
					$order_items_exchange[]=$row_orderitems_exchange;
				}
			}
			
		}


		//GET ORDER_ITEMS
		if (sizeof($shop_orders_items_ids)>0)
		{
			$res_orderitems = q("SELECT * FROM shop_orders_items WHERE id IN (".implode(",", $shop_orders_items_ids).")", $dbshop, __FILE__, __LINE__);
			while ($row_orderitems = mysqli_fetch_array($res_orderitems))
			{
				$orderitems[$row_orderitems["id"]]=$row_orderitems;
			}
		}


	}
	
	//GET ORDER
	$res_order = q("SELECT * FROM ".TABLE_SHOP_ORDERS." WHERE id_order = ".$credit["order_id"], $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($res_order)==0)
	{
		//show_error(9774, 7, __FILE__, __LINE__, "id_order: ".$credit["order_id"]);
	}
	$order = mysqli_fetch_array($res_order);

	
//**********************************************************************
//PREPARE DATA FOR OUTPUT
//**********************************************************************
	$item_id = array();
	if (sizeof($return_items)>0)
	{
		foreach ($return_items as $returnitem_id => $returnitem)
		{
			if ($returnitem["item_id"] != "")
			{
				$item_id[]=$returnitem["item_id"];
			}
		}
	}
	
	if (sizeof($orderitems)>0)
	{
		foreach ($orderitems as $id => $orderitem)
		{
			if ($orderitem["item_id"] != "")
			{
				$item_id[]=$orderitem["item_id"];
			}
		}
	}
	
	if (sizeof($order_items_exchange)>0)
	{
		foreach ($order_items_exchange as $id => $order_returnitem)
		{
			if ($order_returnitem["item_id"] != "")
			{
				$item_id[]=$order_returnitem["item_id"];
			}
		}
	}
	
	//GET MPN
	$MPN = array();
	if (sizeof($item_id)>0)
	{
		$res_MPN = q("SELECT id_item, MPN FROM ".TABLE_SHOP_ITEMS." WHERE id_item IN (".implode(",", $item_id).");", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_return)==0)
		{
			show_error(9779, 7, __FILE__, __LINE__, "ItemIDs: ".implode(",", $item_id));
		}
		while ($row_MPN = mysqli_fetch_array($res_MPN))
		{
			$MPN[$row_MPN["id_item"]] = $row_MPN["MPN"];
		}
	}
	
	// GET ITEMTITLE
	$itemtitle = array();
	if (sizeof($item_id)>0)
	{
		$res_title = q("SELECT id_item, title FROM ".TABLE_SHOP_ITEMS_DE." WHERE id_item IN (".implode(",", $item_id).");", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($res_title)==0)
		{
			show_error(9780, 7, __FILE__, __LINE__, "ItemIDs: ".implode(",", $item_id));
		}
		while ($row_title = mysqli_fetch_array($res_title))
		{
			$itemtitle[$row_title["id_item"]] = $row_title["title"];
		}
	}
	
	
	$user_id = array();
	if ( isset( $credit ) )
	{
		$user_id[] = $credit["firstmod_user"];
		$user_id[] = $credit["lastmod_user"];	
	}
	if ( isset( $return ) )
	{
		$user_id[] = $return["firstmod_user"];
		$user_id[] = $return["lastmod_user"];	
	}
	if ( isset( $order_exchange ) )
	{
		$user_id[] = $order_exchange["firstmod_user"];
		$user_id[] = $order_exchange["lastmod_user"];	
	}

	$usernames = array();
	$res_usernames = q("SELECT * FROM ".TABLE_CMS_USERS." WHERE id_user IN (".implode(',', $user_id ).")", $dbweb, __FILE__, __LINE__);
	while ( $row_usernames = mysqli_fetch_assoc( $res_usernames ) )
	{
		$usernames[$row_usernames["id_user"]] = $row_usernames["firstname"]." ".$row_usernames["lastname"];
	}


	//DEFINE CREDIT TYPE
	if ( isset( $return ) )
	{
		$credit_type = $return["return_type"];
	}
	else
	{
		$credit_type = "credit";	
	}

//********************************************************************************
//SERVICE RESPONSE
//********************************************************************************

	//DEFINE NAMESPACES
	$namespace = array();
	$namespace['credit'] = 'dbshop.'.TABLE_CREDITS;
	$namespace['credit_position'] = 'dbshop.'.TABLE_CREDITS_POSITIONS;
	
//	define_xml_namespace ( $namespace );

	$xmldata = '';
	$xmldata .= '<credit type="'.$credit_type.'">'."\n";
	$xmldata .= '	<type>'.$credit_type.'</type>'."\n";
	//C R E D I T
	foreach ( $credit as $key => $val )
	{
		if ( is_numeric( $val ) )
		{
			$xmldata .= '	<'.$key.'>'.$val.'</'.$key.'>'."\n";
		}
		else
		{
			$xmldata .= '	<'.$key.'><![CDATA['.$val.']]></'.$key.'>'."\n";
		}
	}
	//USERNAMES
	$xmldata .= '	<firstmod_user_name><![CDATA['.$usernames[$credit['firstmod_user']].']]></firstmod_user_name>'."\n";
	$xmldata .= '	<lastmod_user_name><![CDATA['.$usernames[$credit['lastmod_user']].']]></lastmod_user_name>'."\n";
	
	// C R E D I T P O S I T I O N
	if ( sizeof( $credit_positions ) > 0  )
	{
		$xmldata .= '	<creditpositions>'."\n";
		foreach ( $credit_positions as $credit_position )
		{
			$xmldata .= '		<creditposition>'."\n";
			foreach ( $credit_position as $key => $val )
			{
				if ( is_numeric( $val ) )
				{
					$xmldata .= '			<'.$key.'>'.$val.'</'.$key.'>'."\n";
				}
				else
				{
					$xmldata .= '			<'.$key.'><![CDATA['.$val.']]></'.$key.'>'."\n";
				}
			}
			// R E T U R N
			if ( $credit_position['reason_id'] == 1 && isset( $return ) )
			{
				$xmldata .= '			<return>'."\n";
				foreach ( $return as $key => $val )
				{
					if ( is_numeric( $val ) )
					{
						$xmldata .= '				<'.$key.'>'.$val.'</'.$key.'>'."\n";
					}
					else
					{
						$xmldata .= '				<'.$key.'><![CDATA['.$val.']]></'.$key.'>'."\n";
					}
				}
				// R E T U R N I T E M S
				if ( sizeof( $return_items ) > 0 )
				{
					$xmldata .= '				<returnitems>'."\n";
					foreach ( $return_items as $return_item )
					{
						$xmldata .= '					<returnitem>'."\n";
						foreach ( $return_item as $key => $val )
						{
							if ( is_numeric( $val ) )
							{
								$xmldata .= '						<'.$key.'>'.$val.'</'.$key.'>'."\n";
							}
							else
							{
								$xmldata .= '						<'.$key.'><![CDATA['.$val.']]></'.$key.'>'."\n";
							}
						}
						//ITEMTITLE
						$xmldata .= '						<returnitem_title><![CDATA['.$itemtitle[$return_item['item_id']].']]></returnitem_title>'."\n";
						
						$xmldata .= '					</returnitem>'."\n";
					}
					$xmldata .= '				</returnitems>'."\n";
				}
				$xmldata .= '			</return>'."\n";
			}

			//E X C H N A G E O R D E R
			if ( isset( $return ) && $return['return_type'] == 'exchange' && isset( $order_exchange ) )
			{
				$xmldata .= '			<exchange_order>'."\n";
				foreach ( $order_exchange as $key => $val )
				{
					if ( is_numeric( $val ) )
					{
						$xmldata .= '				<'.$key.'>'.$val.'</'.$key.'>'."\n";
					}
					else
					{
						$xmldata .= '				<'.$key.'><![CDATA['.$val.']]></'.$key.'>'."\n";
					}
				}
				// E X C H N A G E O R D E R I T E M S
				if ( sizeof( $order_items_exchange ) > 0 )
				{
					$xmldata .= '				<exchangeorderitems>'."\n";
					foreach ( $order_items_exchange as $order_item_exchange )
					{
						$xmldata .= '					<exchangordereitem>'."\n";
						foreach ( $order_item_exchange as $key => $val )
						{
							if ( is_numeric( $val ) )
							{
								$xmldata .= '						<'.$key.'>'.$val.'</'.$key.'>'."\n";
							}
							else
							{
								$xmldata .= '						<'.$key.'><![CDATA['.$val.']]></'.$key.'>'."\n";
							}
						}
						//ITEMTITLE
						$xmldata .= '						<exchangordereitem_title><![CDATA['.$itemtitle[$order_item_exchange['item_id']].']]></exchangordereitem_title>'."\n";

						$xmldata .= '					</exchangordereitem>'."\n";
					}
					$xmldata .= '				</exchangeorderitems>'."\n";
				}
				$xmldata .= '			</exchange_order>'."\n";
				
			}
			$xmldata .= '		</creditposition>'."\n";
		}
		$xmldata .= '	</creditpositions>'."\n";
	}
	$xmldata .= '</credit>'."\n";

echo $xmldata;

?>