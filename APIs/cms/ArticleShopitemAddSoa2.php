<?php
	/*************************
	********** SOA 2 *********
	*************************/
	
	$required=array("article_id"	=> "numeric", "text"	=> "text");
	check_man_params($required);
	
	$xml = '';
	
	$results=q("SELECT si.id_item, sid.title FROM shop_items AS si, shop_items_de AS sid WHERE si.MPN='".$_POST["text"]."' AND sid.id_item=si.id_item;", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		$xml .= '	<Error>Shopartikel nicht gefunden.</Error>'."\n";
	}
	else
	{
		$row=mysqli_fetch_array($results);
		
		$data['article_id'] = $_POST['article_id'];
		$data['item_id'] = $row["id_item"];
		$data['firstmod'] = time();
		$data['firstmod_user'] = $_SESSION["id_user"];
		$data['lastmod'] = $data['firstmod'];
		$data['lastmod_user'] = $data['firstmod_user'];
		
		$result = q_insert('cms_articles_shopitems',$data, $dbweb, __FILE__, __LINE__);
		$xml .= '<insert_id>'.mysqli_insert_id($dbweb).'</insert_id>';
		$xml .= '	<item_title><![CDATA['.$row['title'].']]></item_title>';
	}
	
	print $xml;
	
?>