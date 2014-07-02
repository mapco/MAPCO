<?php
	
	// SOA2-SERVICE
	
	check_man_params( array( "customer_id" => "numeric", "order_id"	=> "numeric", "conversation_id" => "numeric" ) );
	
	$xml='';
	$user_id=0;
	
	if ( $_POST['order_id'] >0 )
	{
		//GET USER
		$results=q("SELECT customer_id, shop_id FROM shop_orders WHERE id_order=".$_POST["order_id"].";", $dbshop, __FILE__, __LINE__);
		$shop_orders=mysqli_fetch_array($results);
		$user_id = $shop_orders['customer_id'];
	}
	elseif ( $_POST['customer_id'] >0 )
	{
		//GET USER
		$results=q("SELECT user_id FROM crm_customers WHERE id_crm_customer=".$_POST["customer_id"].";", $dbweb, __FILE__, __LINE__);
		$shop_orders=mysqli_fetch_array($results);
		$user_id = $shop_orders['user_id'];
	}
	
	//GET CONVERSATION
	$sql = 'SELECT * FROM crm_conversations WHERE ';
	$z = 0;
	
	if ($user_id > 0)
	{
		$where = 'user_id='.$user_id;
	}
	
	if ($_POST['order_id'] > 0)
	{
		$where = '('.$where.' AND order_id='.$_POST['order_id'].')';
	}	
	
	if ($_POST['customer_id'] > 0)
	{
		if ( $where != '' )
		{
			$where .= ' OR ';
		}
		$where .= 'customer_id='.$_POST['customer_id'];
	}
	
	if ( $where != '' )
	{
		$where = $where.' AND ';
	}
	$where .= 'conversation_id='.$_POST['conversation_id'];
	$sql .= $where." ORDER BY firstmod DESC;";

	$cnt_order=0;
 	$cnt_all=0;
	$results2=q($sql, $dbweb, __FILE__, __LINE__);
	while($crm_conversations=mysqli_fetch_array($results2))
	{
		$results3=q("SELECT * FROM crm_conversations_types WHERE id=".$crm_conversations["type_id"].";", $dbweb, __FILE__, __LINE__);
		$crm_conversations_types=mysqli_fetch_array($results3);
		$results4=q("SELECT site_id, title, article FROM cms_articles WHERE id_article=".$crm_conversations["article_id"].";", $dbweb, __FILE__, __LINE__);
		$cms_articles=mysqli_fetch_array($results4);
		
		$xml.='<contact>'."\n";
		$xml.='		<con_order_id><![CDATA['.$crm_conversations["order_id"].']]></con_order_id>'."\n";
		$xml.='		<con_from><![CDATA['.$crm_conversations["con_from"].']]></con_from>'."\n";
		$xml.='		<con_to><![CDATA['.$crm_conversations["con_to"].']]></con_to>'."\n";
		$xml.='		<subject><![CDATA['.$cms_articles["title"].']]></subject>'."\n";
		$xml.='		<message><![CDATA['.$cms_articles["article"].']]></message>'."\n";
		$xml.='		<firstmod><![CDATA['.$crm_conversations["firstmod"].']]></firstmod>'."\n";
		$xml.='		<type><![CDATA['.$crm_conversations_types["type"].']]></type>'."\n";
		$xml.='</contact>'."\n";
		
		if($crm_conversations["order_id"]==$_POST["order_id"])
			$cnt_order=$cnt_order+1;
		$cnt_all=$cnt_all+1;
	}
	
	//GET CONVERSATION TYPES
	$results5=q("SELECT * FROM crm_conversations_types;", $dbweb, __FILE__, __LINE__);
	while($crm_conversations_types=mysqli_fetch_array($results5))
	{
		$xml.='<con_type>'."\n";
		$xml.='		<id><![CDATA['.$crm_conversations_types["id"].']]></id>'."\n";
		$xml.='		<c_type><![CDATA['.$crm_conversations_types["type"].']]></c_type>'."\n";
		$xml.='</con_type>'."\n";
	}
	
	//GET SHOP EMAILADDRESS
	$shop_mail="test";
	$postfields=array();
	$postfields["API"]="shop";
	$postfields["Action"]="ShopsGet";
	//$response=soa2($postfields);
	
	$responseXml = post(PATH."soa/", $postfields);
	$use_errors = libxml_use_internal_errors(true);
	try
	{
		$response = new SimpleXMLElement($responseXml);
	}
	catch(Exception $e)
	{
	}
	libxml_clear_errors();
	libxml_use_internal_errors($use_errors);

	for($i=0; isset($response->Shop[$i]); $i++)
	{
		if((int)$response->Shop[$i]->id_shop==(int)$shop_orders["shop_id"])
		{
			$shop_mail=(string)$response->Shop[$i]->mail;
			//$shop_mail=(string)$response->Shop[$i]->id_shop;
		}
	}
	
	$xml.='<shop_mail><![CDATA['.$shop_mail.']]></shop_mail>'."\n";
	$xml.='<cnt_order><![CDATA['.$cnt_order.']]></cnt_order>'."\n";
	$xml.='<cnt_all><![CDATA['.$cnt_all.']]></cnt_all>'."\n";
	
	echo $xml;
	
?>