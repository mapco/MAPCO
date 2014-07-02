<?php 

	$checks = 0;
	$entries = 0;
	
	//get article ordering
	$results=q("SELECT COUNT(id_article) AS articles FROM cms_articles;", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$ordering_articles=$row['articles']+1;
	
	//get articles_lables ordering
	$results=q("SELECT COUNT(article_id) AS articles_labels FROM cms_articles_labels WHERE type_id=62;", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$ordering_labels=$row['articles_lables']+1;
	
	// Daten auslesen	
	// lese Notizen aus Shop Orders
	$res_orders = q("SELECT id_order, customer_id, shop_id, order_note, lastmod_user, lastmod FROM shop_orders WHERE order_note!='' ORDER BY id_order ASC;", $dbshop, __FILE__, __LINE__);
	while ( $row_orders = (mysqli_fetch_assoc($res_orders)) )
	{
		$row_orders['insert'] = 1;
		$orders[$row_orders['id_order']] = $row_orders;
		$order_id_arr[] = $row_orders['id_order'];
		
		$res_shop = q("SELECT site_id FROM shop_shops WHERE id_shop=".$row_orders['shop_id'].";", $dbshop, __FILE__, __LINE__);
	}
	
	$order_ids = implode(",", $order_id_arr);
	
	// hole sofern vorhanden aus der shop_orders_events den Benutzer und Erstellungszeitpunkt der Notiz
	$res_events = q("SELECT firstmod_user, firstmod, order_id FROM `shop_orders_events` WHERE order_id IN (".$order_ids.") AND eventtype_id=10 ORDER BY firstmod ASC LIMIT 1;", $dbshop, __FILE__, __LINE__);
	while ( $row_events = (mysqli_fetch_assoc($res_events)) )
	{
		$orders[$row_events['order_id']]['id_user'] = $row_events['firstmod_user'];
		$orders[$row_events['order_id']]['firstmod'] = $row_events['firstmod'];
	}	
	
	// Prüfen
	for ( $x=0; $x<sizeof($order_id_arr); $x++ )
	{	
		$checks++;
		// sofern kein Event für eine Order existiert, nimm Benutzer und Zeitpunkt der Order
		if ( !isset($orders[$order_id_arr[$x]]['id_user']) )
		{
			$orders[$order_id_arr[$x]]['id_user'] = $orders[$order_id_arr[$x]]['lastmod_user'];
			$orders[$order_id_arr[$x]]['firstmod'] = $orders[$order_id_arr[$x]]['lastmod'];
		}
		unset($orders[$order_id_arr[$x]]['lastmod_user']);
		unset($orders[$order_id_arr[$x]]['lastmod']);
		
		// Prüfe ob User und Order bereits in crm_conversations vorhanden
		$res_convers = q("SELECT con.id, con.article_id, art.article FROM crm_conversations AS con, cms_articles AS art WHERE con.order_id=".$order_id_arr[$x]." AND con.user_id=".$orders[$order_id_arr[$x]]['id_user']." AND con.type_id=4 AND art.id_article=con.article_id;", $dbweb, __FILE__, __LINE__);
		if ( mysqli_num_rows($res_convers) > 0 )
		{
			$orders[$order_id_arr[$x]]['insert'] = 0;
		}
		
		while ( $row_convers = (mysqli_fetch_assoc($res_convers)) )
		{	
			//// Prüfe ob User und Order bereits in crm_conversations vorhanden
			if ( $row_convers['article'] == $orders[$order_id_arr[$x]]['order_note'] )
			{	
				$orders[$order_id_arr[$x]]['insert'] = 0;
				break;
			}
		}
		
		if ( $orders[$order_id_arr[$x]]['insert'] == 1 )
		{ 
			// erstelle artikel mit dem Text der Notiz	
			$data['title'] = 'Kommentar zur Bestellung '.$order_id_arr[$x];
			$data['article'] = $orders[$order_id_arr[$x]]['order_note'];
			$data['firstmod'] = $orders[$order_id_arr[$x]]['firstmod'];
			$data['firstmod_user'] = $orders[$order_id_arr[$x]]['id_user'];
			$data['lastmod'] = $orders[$order_id_arr[$x]]['firstmod'];
			$data['lastmod_user'] = $orders[$order_id_arr[$x]]['id_user'];
			$data['published'] = 0;
			$data['format'] = 0;
			$data['ordering'] = $ordering_articles;
			q_insert('cms_articles', $data, $dbweb,  __FILE__, __LINE__);
			$article_id = mysqli_insert_id($dbweb);
			unset($data);		
			
			// setze Label für diesen Artikel
			$data['article_id'] = $article_id;
			$data['label_id'] = 62;
			$data['ordering'] = $ordering_labels;
			q_insert('cms_articles_labels', $data, $dbweb,  __FILE__, __LINE__);
			unset($data);
			
			// schreibe Eintrag in die crm_conversations
			$data['article_id'] = $article_id;
			$data['user_id'] = $orders[$order_id_arr[$x]]['customer_id'];
			$data['order_id'] = $orders[$order_id_arr[$x]]['id_order'];
			$data['type_id'] = 4;
			$data['firstmod'] = $orders[$order_id_arr[$x]]['firstmod'];
			$data['firstmod_user'] = $orders[$order_id_arr[$x]]['id_user'];
			$data['lastmod'] = $orders[$order_id_arr[$x]]['firstmod'];
			$data['lastmod_user'] = $orders[$order_id_arr[$x]]['id_user'];
			q_insert('crm_conversations', $data, $dbweb,  __FILE__, __LINE__);
			unset($data);
			
			$ordering_articles++;
			$ordering_labels++;
			$entries++;
		}
	}

	print 'Anzahl Checks'. $checks."\n";
	print 'Anzahl Eintragungen'. $entries."\n";
?>