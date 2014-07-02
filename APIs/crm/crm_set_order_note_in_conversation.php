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
			'".mysqli_real_escape_string($dbshop,$xml)."',
			".time().",
			".$_SESSION["id_user"]."
		);", $dbshop, __FILE__, __LINE__);
		
		return mysqli_insert_id($dbshop);
	}
	
	//GET USER
	$results=q("SELECT customer_id, shop_id FROM shop_orders WHERE id_order=".$_POST["OrderID"].";", $dbshop, __FILE__, __LINE__);
	$shop_orders=mysqli_fetch_array($results);
	
	//get article ordering
	$results=q("SELECT COUNT(id_article) AS articles FROM cms_articles;", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$ordering_articles=$row['articles']+1;
	
	//get articles_lables ordering
	$results=q("SELECT COUNT(article_id) AS articles_labels FROM cms_articles_labels;", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$ordering_labels=$row['articles_lables']+1;

	// erstelle artikel mit dem Text der Notiz	
	$data['title'] = 'Kommentar zur Bestellung '.$_POST["OrderID"];
	$data['article'] = mysqli_real_escape_string($dbshop,$_POST["note"]);
	$data['firstmod_user'] = $_SESSION["id_user"];
	$data['firstmod'] = time();
	$data['lastmod_user'] = $_SESSION["id_user"];
	$data['lastmod'] = time();
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
	$data['user_id'] = $shop_orders['customer_id'];
	$data['order_id'] = $_POST["OrderID"];
	$data['type_id'] = 4;
	$data['firstmod'] = time();
	$data['firstmod_user'] = $_SESSION["id_user"];
	$data['lastmod'] = time();
	$data['lastmod_user'] = $_SESSION["id_user"];
	q_insert('crm_conversations', $data, $dbweb,  __FILE__, __LINE__);
	unset($data);
	
	//SAVE ORDEREVENT
	$data["order_note"]=$_POST["note"];
	$data["SELECTOR_id_order"]=$_POST["OrderID"];
	$id_event=save_order_event(10, $_POST["OrderID"], $data);
?>