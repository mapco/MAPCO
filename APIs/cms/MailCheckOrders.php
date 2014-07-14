<?php 
	$charset;
	$htmlmsg;
	$plainmsg;
	$bytes;
	$attachments;
	$header;

	function getpart($mbox,$mid,$p,$partno) 
	{
		// $partno = '1', '2', '2.1', '2.1.3', etc for multipart, 0 if simple
		global $htmlmsg,$plainmsg,$charset,$attachments, $bytes;
	
		// DECODE DATA
		$data = ($partno)?
			imap_fetchbody($mbox,$mid,$partno, FT_UID):  // multipart
			imap_body($mbox,$mid, FT_UID);  // simple
		// Any part may be encoded, even plain text messages, so check everything.
		if ($p->encoding==4)
			$data = quoted_printable_decode($data);
		elseif ($p->encoding==3)
			$data = base64_decode($data);
	
		// PARAMETERS
		// get all parameters, like charset, filenames of attachments, etc.
		$params = array();
		if ($p->parameters)
			foreach ($p->parameters as $x)
				$params[strtolower($x->attribute)] = $x->value;
		if ($p->dparameters)
			foreach ($p->dparameters as $x)
				$params[strtolower($x->attribute)] = $x->value;
	
		// ATTACHMENT
		// Any part with a filename is an attachment,
		// so an attached text file (type 0) is not mistaken as the message.
		if ($params['filename'] || $params['name']) {
			// filename may be given as 'Filename' or 'Name' or both
			$filename = ($params['filename'])? $params['filename'] : $params['name'];
			// filename may be encoded, so see imap_mime_header_decode()
			$attachments[$filename] = $data;  // this is a problem if two files have same name
			$bytes[$filename] = $p->bytes;
		}
	
		// TEXT
		if ($p->type==0 && $data) {
			// Messages may be split in different parts because of inline attachments,
			// so append parts together with blank row.
			if (strtolower($p->subtype)=='plain')
			{
				$plainmsg .= trim($data) ."\n\n";
			}
			else 
			{
				$htmlmsg .= $data ."<br><br>";
			}
			$charset = $params['charset'];  // assume all parts are same charset
		}
	
		// EMBEDDED MESSAGE
		// Many bounce notifications embed the original message as type 2,
		// but AOL uses type 1 (multipart), which is not handled here.
		// There are no PHP functions to parse embedded messages,
		// so this just appends the raw source to the main message.
		elseif ($p->type==2 && $data) 
		{
			$plainmsg .= $data."\n\n";
		}
	
		// SUBPART RECURSION
		if ($p->parts) {
			foreach ($p->parts as $partno0=>$p2)
				getpart($mbox,$mid,$p2,$partno.'.'.($partno0+1));  // 1.2, 1.2.1, etc.
		}
	}

	require_once("../../mapco_shop_de/functions/mail_connect.php");
	
	check_man_params(array(
						"msg_num"	=> "numericNN",
						"account"	=> "numericNN"
						)
					);
	$mbox = mail_connect($_POST['account']);
	
	$xml = '';
	$msg = array();
	
	$mail_struct = imap_fetchstructure($mbox, $_POST['msg_num'], FT_UID);
//var_dump($mail_struct); die();

	$body = imap_body($mbox, $_POST['msg_num'], FT_UID);
	
	$hText = imap_fetchbody($mbox, $_POST['msg_num'], '0', FT_UID); 
	$header = imap_rfc822_parse_headers($hText); 
//var_dump(quoted_printable_decode($body)); die();
//var_dump($header); die();
	$msg['to'] = $header->to[0]->mailbox.'@'.$header->to[0]->host;
	$msg['from'] = $header->from[0]->mailbox.'@'.$header->from[0]->host;
	$msg['reply_toaddress'] = $header->reply_to[0]->mailbox.'@'.$header->reply_to[0]->host;
	//$msg['from'] = mb_decode_mimeheader($header->from);
	$msg['cc'] = '';
	$msg['bcc'] = '';
	$msg['subject'] = imap_utf8($header->subject);

	$xml = '<mail>'."\n";
	$xml .= '	<msg_from><![CDATA['.imap_utf8($header->from[0]->personal).' <'.$msg['reply_toaddress'].'>]]></msg_from>'."\n";
	$xml .= '	<msg_subject><![CDATA['.$msg['subject'].']]></msg_subject>'."\n";
	$xml .= '	<msg_date><![CDATA['.strtotime($header->date).']]></msg_date>'."\n";
	$xml .= '</mail>'."\n";
//var_dump($msg); die();


	if ( $header->from[0]->host == 'amazon.fr' or $header->from[0]->host == 'amazon.de' or $header->from[0]->host == 'amazon.it' or $header->from[0]->host == 'amazon.uk' or $header->from[0]->host == 'amazon.es')
	{
		if (!$mail_struct->parts)  // simple
		{
			getpart($mbox,$_POST['msg_num'],$mail_struct,0);  // pass 0 as part-number
		}
		else
		{  // multipart: cycle through each part
			foreach ($mail_struct->parts as $partno0=>$p)
			{
				getpart($mbox,$_POST['msg_num'],$p,$partno0+1);
			}
		}
		
		$newmsg = $plainmsg;
		//var_dump($plainmsg); die();
		preg_match_all("/([0-9]{3})-([0-9]{7})-([0-9]{7})/is", $newmsg, $out);
		for($j=0; $j<sizeof($out[0]); $j++)
		{
			if ( $out[0][$j] != '' )
			{
				$possible_ids[$x] = intval($out[0][$j]);
				$x++;
			}
		}
		
		if ( $x > 0 )
		{
			if ( $x == 1 )
			{
				$where_ids .= " AmazonOrderID=".$possible_ids[0];
			}
			elseif ( $x > 1 )
			{
				$where_ids .= " AmazonOrderID IN (".implode (',', $possible_ids).")";
			}			
			$res_amazon=q("SELECT id_orders FROM amazon_orders WHERE ".$where_ids.";", $dbshop, __FILE__, __LINE__);
			if ( mysqli_num_rows($res_amazon) == 0 )
			{
				$xml = '<Error>Id extrahiert, jedoch nicht in DB gefunden!</Error>';	
			}			
			while ($row_amazon=mysqli_fetch_assoc($res_amazon))
			{
				$res_shop=q("SELECT id_order FROM shop_orders WHERE foreign_OrderID=".$row_amazon['id_orders'].";", $dbshop, __FILE__, __LINE__);
				if ( mysqli_num_rows($res_shop) == 0 )
				{
					$xml = '<Error>Amazon Order gefunden, jedoch noch keine Shop Order!</Error>';	
				}
				$row_shop=mysqli_fetch_assoc($res_shop);
				$match_lvl4[$row_shop['id_order']]['match_lvl'] = 4;
			}
		}
	}
	else
	{
		$x = 0;
		// isoliere mögliche orderids
		preg_match_all("/[0-9]{4,16}/is", $msg['subject'], $out);
		for($j=0; $j<sizeof($out[0]); $j++)
		{
			if ( $out[0][$j] != '' )
			{
				$possible_ids[$x] = intval($out[0][$j]);
				$x++;
			}
		}
	
		if ( $x == 0 )
		{
			if (!$mail_struct->parts)  // simple
			{
				getpart($mbox,$_POST['msg_num'],$mail_struct,0);  // pass 0 as part-number
			}
			else
			{  // multipart: cycle through each part
				foreach ($mail_struct->parts as $partno0=>$p)
				{
					getpart($mbox,$_POST['msg_num'],$p,$partno0+1);
				}
			}
			
			$newmsg = $plainmsg;
		//var_dump($plainmsg); die();
			preg_match_all("/[0-9]{4,16}/is", $newmsg, $out);
	//var_dump($out); die();
			for($j=0; $j<sizeof($out[0]); $j++)
			{
				if ( $out[0][$j] != '' )
				{
					$possible_ids[$x] = intval($out[0][$j]);
					$x++;
				}
			}
			
			//var_dump($out); die();	
		}
		
		imap_close($mbox);
		
		if ( sizeof($possible_ids) > 0 )
		{
			array_unique($possible_ids);
		}
		
		//GET SHOPS
		$shop_type=array();
		$shops=array();
		$res_shop=q("SELECT id_shop, title, shop_type FROM shop_shops;", $dbshop, __FILE__, __LINE__);
		while ($row_shop=mysqli_fetch_assoc($res_shop))
		{
			$shop_type[$row_shop["id_shop"]]=$row_shop["shop_type"];
			$shops[$row_shop["id_shop"]]=$row_shop['title'];
		}
	
		if ( $x>0 )
		{
			$match_lvl1 = array();
			$match_lvl2 = array();
			$match_lvl3 = array();
			$match_lvl4 = array();
			$orders = array();
		
			$sql = "SELECT id_order, combined_with, usermail FROM shop_orders WHERE";
			
			if ( $x == 1 )
			{
				$where_ids .= " id_order=".$possible_ids[0];
			}
			elseif ( $x > 1 )
			{
				$where_ids .= " id_order IN (".implode (',', $possible_ids).")";
			}
			
			$sql .= $where_ids." ORDER BY lastmod DESC;";
			$result_order=q($sql, $dbshop, __FILE__, __LINE__);
			while ( $row_order=mysqli_fetch_assoc($result_order) )
			{
				if ( $row_order['combined_with'] > 0 )
				{
					$temp_order = $row_order['combined_with'];
				}
				else
				{
					$temp_order = $row_order['id_order'];
				}
				
				if ( in_array($row_order['id_order'], $possible_ids) && $row_order['usermail'] == $msg['reply_toaddress'] )
				{
					$match_lvl4[$temp_order] = array();
					$match_lvl4[$temp_order]['match_lvl'] = 4;
				}
				else
				{
					$match_lvl3[$temp_order] = array();
					$match_lvl3[$temp_order]['match_lvl'] = 3;
				}
			}
			
			$where_ids = '';
			$sql = "SELECT so.id_order, so.combined_with, so.usermail FROM shop_orders AS so, idims_auf_status AS ias WHERE";
			if ( $x == 1 )
			{
				$where_ids .= " ias.rng_nr=".$possible_ids[0];
			}
			elseif ( $x > 1 )
			{
				$where_ids .= " ias.rng_nr IN (".implode (',', $possible_ids).")";
			}
			$sql .= $where_ids." AND so.id_order=ias.auf_id";
			$result_rng=q($sql, $dbshop, __FILE__, __LINE__);
			while ( $row_rng=mysqli_fetch_assoc($result_rng) )
			{
				if ( $row_rng['combined_with'] >0 )
				{
					$id_order = $row_rng['combined_with'];
				}
				else
				{
					$id_order = $row_rng['id_order'];
				}
				
				if ( $row_rng['usermail'] == '' )
				{
					$sql2 = "SELECT usermail FROM cms_users WHERE id_user=".$id_order.";";
					$result_user=q($sql2, $dbweb, __FILE__, __LINE__);
					$row_user=mysqli_fetch_array($result_user);
					$row_rng['usermail'] = $row_user['usermail'];
				}
			//	var_dump($row_rng['usermail'] +'=='+ $msg['reply_toaddress']);
				if ( $row_rng['usermail'] == $msg['reply_toaddress'] )
				{
					$match_lvl2[$id_order] = array();
					$match_lvl2[$id_order]['match_lvl'] = 2;
				}
				else
				{
					$match_lvl1[$id_order] = array();
					$match_lvl1[$id_order]['match_lvl'] = 1;
				}
			}
				
			$where_ids = '';
			$sql = "SELECT DISTINCT so.id_order, so.combined_with, so.usermail FROM shop_orders AS so, shop_orders_items AS soi, ebay_orders_items AS eoi WHERE";
			if ( $x == 1 )
			{
				$where_ids .= " eoi.ItemItemID=".$possible_ids[0];
			}
			elseif ( $x > 1 )
			{
				$where_ids .= " eoi.ItemItemID IN (".implode (',', $possible_ids).")";
			}
			$sql .= $where_ids." AND soi.foreign_transactionID=eoi.TransactionID AND so.id_order=soi.order_id";
			$result_ebay=q($sql, $dbshop, __FILE__, __LINE__);
			while ( $row_ebay=mysqli_fetch_assoc($result_ebay) )
			{
				if ( $row_rng['combined_with'] >0 )
				{
					$id_order = $row_ebay['combined_with'];
				}
				else
				{
					$id_order = $row_ebay['id_order'];
				}
				
				if ( $row_ebay['usermail'] == '' )
				{
					$sql2 = "SELECT usermail FROM cms_users WHERE id_user=".$id_order.";";
					$result_user=q($sql2, $dbweb, __FILE__, __LINE__);
					$row_user=mysqli_fetch_array($result_user);
					$row_ebay['usermail'] = $row_user['usermail'];
				}
	//			var_dump($row_rng['usermail'] +'=='+ $msg['reply_toaddress']);
				if ( $row_ebay['usermail'] == $msg['reply_toaddress'] )
				{
					$match_lvl2[$id_order] = array();
					$match_lvl2[$id_order]['match_lvl'] = 2;
				}
				else
				{
					$match_lvl1[$id_order] = array();
					$match_lvl1[$id_order]['match_lvl'] = 1;
				}
			}
		}
	}
	
	if ( $x > 0 )
	{
		$orders += $match_lvl4;
		$orders += $match_lvl3;
		$orders += $match_lvl2;
		$orders += $match_lvl1;

		foreach ( $orders as $order_id => $order )
		{
			$arr_order_ids[] = $order_id;
		}
		
		if ( sizeof($arr_order_ids) > 0 )
		{
			$sql = "SELECT so.id_order, so.foreign_OrderID, ss.shop_type, sost.title AS order_status, so.shop_id, so.usermail, so.bill_adr_id, so.bill_company, so.bill_firstname, so.bill_lastname, so.bill_street, so.bill_number, so.bill_additional, so.bill_zip, so.bill_city, so.customer_id, so.firstmod, so.lastmod FROM shop_orders AS so, shop_orders_state_types AS sost, shop_orders_items AS soi, shop_items AS si WHERE";
			$where_ids = '';
			if ( sizeof($arr_order_ids) > 1)
			{
				$where_ids .= " id_order IN (".implode(",", $arr_order_ids).")";
			}
			else
			{
				$where_ids .= " id_order=".$arr_order_ids[0];
			}
	
			$sql .= $where_ids." AND soi.order_id=so.id_order AND soi.item_id=si.id_item AND sost.shop_orders_status_id=so.status_id AND ss.id_shop=so.shop_id ORDER BY so.lastmod DESC LIMIT 1;";

			$result_order_detail=q($sql, $dbshop, __FILE__, __LINE__);
			while ( $row_order_detail=mysqli_fetch_assoc($result_order_detail) )
			{
				$orders[$row_order_detail['id_order']]['shop'] = $shops[$row_order_detail['shop_id']];
				$orders[$row_order_detail['id_order']]['order_status'] = $row_order_detail['order_status'];
				$orders[$row_order_detail['id_order']]['bill_company'] = $row_order_detail['bill_company'];
				$orders[$row_order_detail['id_order']]['bill_firstname'] = $row_order_detail['bill_firstname'];
				$orders[$row_order_detail['id_order']]['bill_lastname'] = $row_order_detail['bill_lastname'];
				$orders[$row_order_detail['id_order']]['bill_street'] = $row_order_detail['bill_street'];
				$orders[$row_order_detail['id_order']]['bill_number'] = $row_order_detail['bill_number'];
				$orders[$row_order_detail['id_order']]['bill_additional'] = $row_order_detail['bill_additional'];
				$orders[$row_order_detail['id_order']]['bill_zip'] = $row_order_detail['bill_zip'];
				$orders[$row_order_detail['id_order']]['bill_city'] = $row_order_detail['bill_city'];
				$orders[$row_order_detail['id_order']]['user_id'] = $row_order_detail['customer_id'];
				$orders[$row_order_detail['id_order']]['usermail'] = $row_order_detail['usermail'];
				$orders[$row_order_detail['id_order']]['firstmod'] = $row_order_detail['firstmod'];
				
				if ( $row_order_detail['shop_type'] == 2 )
				{
					$orders[$row_order_detail['id_order']]['ebay_order_id'] = $row_order_detail['foreign_OrderID'];
				}
				elseif ( $row_order_detail['shop_type'] == 3 )
				{
					$orders[$row_order_detail['id_order']]['amazon_order_id'] = $row_order_detail['foreign_OrderID'];
				}
				
				if ( $row_order_detail['customer_id'] != NULL || $row_order_detail['customer_id'] != '' )
				{
					$sql2 = "SELECT username, usermail FROM cms_users WHERE id_user=".$row_order_detail['customer_id'].";";
					$result_user=q($sql2, $dbweb, __FILE__, __LINE__);
					$row_user=mysqli_fetch_array($result_user);
					$orders[$row_order_detail['id_order']]['username'] = $row_user['username'];
					
					if ( $row_order_detail['usermail'] == '' )
					{
						$orders[$row_order_detail['id_order']]['usermail'] = $row_user['usermail'];
					}							
				}
				
				if ( $row_mother['bill_adr_id'] > 0 )
				{
					$result_bill_addr = q("SELECT company, firstname, lastname, street, number, additional, zip, city FROM shop_bill_adr WHERE adr_id=".$row_order['bill_adr_id'].";", $dbshop, __FILE__, __LINE__);
					$row_bill_addr = mysqli_fetch_array($result_bill_addr);
					$orders[$row_order_detail['id_order']]['bill_company'] = $row_bill_addr['company'];
					$orders[$row_order_detail['id_order']]['bill_firstname'] = $row_bill_addr['firstname'];
					$orders[$row_order_detail['id_order']]['bill_lastname'] = $row_bill_addr['lastname'];
					$orders[$row_order_detail['id_order']]['bill_lastname'] = $row_bill_addr['lastname'];
					$orders[$row_order_detail['id_order']]['bill_street'] = $row_bill_addr['street'];
					$orders[$row_order_detail['id_order']]['bill_number'] = $row_bill_addr['number'];
					$orders[$row_order_detail['id_order']]['bill_additional'] = $row_bill_addr['additional'];
					$orders[$row_order_detail['id_order']]['bill_zip'] = $row_bill_addr['zip'];
					$orders[$row_order_detail['id_order']]['bill_city'] = $row_bill_addr['city'];
				}
				
				$sql = "SELECT sid.title, soi.amount FROM shop_orders_items AS soi, shop_items_de AS sid WHERE soi.order_id=".$row_order_detail['id_order']." AND sid.id_item=soi.item_id;";
				$i = 0;
				$res_items=q($sql,$dbshop, __FILE__, __LINE__);
				while($row_items=mysqli_fetch_assoc($res_items))
				{
					$orders[$row_order_detail['id_order']]['items'] = array();
					$orders[$row_order_detail['id_order']]['items'][$i] = $row_items["amount"].'x '.$row_items["title"];
					$i++;
				}
				
				$orders[$row_order_detail['id_order']]['exchanges'] = 0;
				$orders[$row_order_detail['id_order']]['returns'] = 0;
				$sql = "SELECT date_return, return_type, exchange_order_id FROM shop_returns2 WHERE order_id=".$row_order_detail['id_order'].";";
				$res_returns=q($sql,$dbshop, __FILE__, __LINE__);
				while($row_returns=mysqli_fetch_assoc($res_returns))
				{
					if ( $row_returns['return_type'] == 'return' )
					{
						if ( $row_return['date_return'] > $orders[$row_order_detail['id_order']]['last_return_date'])
					$orders[$row_order_detail['id_order']]['last_return'] = $row_return['date_return'];
						
						$orders[$row_order_detail['id_order']]['exchanges']++;
					}
					elseif ( $row_returns['return_type'] == 'exchange' )
					{
						if ( $row_return['date_return'] > $orders[$row_order_detail['id_order']]['last_exchange_date'])
					$orders[$row_order_detail['id_order']]['last_return'] = $row_return['date_return'];
						$orders[$row_order_detail['id_order']]['returns']++;
					}
				}
			}
			
			foreach ( $orders as $order_id => $order_details )
			{
				$xml .= '<order>'."\n";
				$xml .= '	<id_order>'.$order_id.'</id_order>'."\n";
				foreach ( $order_details as $key => $value )
				{
					if ( $key == 'items' )
					{	
						foreach ( $value as $item_value )
						{
							$xml .= '	<order_item><![CDATA['.$item_value.']]></order_item>'."\n";						
						}
					}
					else
					{
						if ( !is_numeric($value) )
						{
							$value = "<![CDATA[".$value."]]>";	
						}
						$xml .= '	<'.$key.'>'.$value.'</'.$key.'>'."\n";
					}
				}
				$xml .= '</order>'."\n";
			}
		}
		print $xml;
	}
?>