<?php

	/*	 
	*	SOA2-SERVICE
	*/
	
	$required = array( "mode" => "text" );
	check_man_params( $required );
	
	
	if ( $_POST['mode'] == 'online' ) 
	{
		$required = array( "OrderID" => "numeric" );				
		check_man_params( $required );				
	}
	
	if ( $_POST['mode'] == 'other' )
	{
		$required = array( "shipping_number" => "text",
						   "ToReceiver"  => 	"text",
						   "company" => 		"text",
						   "company2" => 		"text",
						   "firstname" => 		"text",
						   "lastname" => 		"text",
						   "origin" =>			"text" );
		check_man_params( $required );
	}
	
	if ( $_POST['mode'] == 'online' )
	{
		//GET ORDER-DATA
		$res_order = q( "SELECT * FROM shop_orders WHERE id_order = " . $_POST["OrderID"], $dbshop, __FILE__, __LINE__ );
		if ( mysqli_num_rows( $res_order )==0 )
		{
			show_error( 9811, 7, __FILE__, __LINE__, "Keine Bestellung gefunden. OrderID: " . $_POST["OrderID"] );
			exit;
		}
		$row_order = mysqli_fetch_array( $res_order );

		//GET ORDER DETAIL
		$post_data = 				array();
		$post_data["API"] = 		"shop";
		$post_data["APIRequest"] = 	"OrderDetailGet";
		$post_data["OrderID"] = 	$_POST["OrderID"];
				
		$postdata = http_build_query( $post_data );
		
		$xml = soa2( $postdata );
		if ( $xml->Ack[0] != "Success" )
		{
			show_error( 9812, 7, __FILE__, __LINE__, "Keine Bestelldaten gefunden. OrderID: " . $_POST["OrderID"] );
			exit;
		}
		
		//Get shop_id
		$shop_id = (int)$xml->Order[0]->shop_id[0];
		
		// get article_id, shop_type and site_id
		$article_id = 	0;
		$site_id = 		1;
		$shop_type = 	1;
		$res3 = q( "SELECT * FROM shop_shops WHERE id_shop=" . $shop_id, $dbshop, __FILE__, __LINE__ ); 
		if ( mysqli_num_rows( $res3 ) > 0 )
		{
			$shop_shops = mysqli_fetch_assoc( $res3 );
			$article_id = $shop_shops['sent_mail_article_id'];
			if ( $shop_shops['site_id'] > 0 )
			{
				$site_id = 		$shop_shops['site_id'];
				$shop_type = 	$shop_shops['shop_type'];
			}
			elseif ( $shop_shops['site_id'] == 0 )
			{
				$res6 = q( "SELECT * FROM shop_shops WHERE id_shop=" . $shop_shops['parent_shop_id'], $dbshop, __FILE__, __LINE__ );
				if ( mysqli_num_rows( $res6 ) >0 )
				{
					$shop_shops_2 = mysqli_fetch_assoc( $res6 );
					$site_id = 		$shop_shops_2['site_id'];
					$shop_type = 	$shop_shops['shop_type'];
				}
			}
		}
			
		//CREATE MAIL DATA
		//get Items	
		$item = array();
		for ( $i = 0; $i < sizeof( $xml->Order[0]->OrderItems[0]->Item ); $i++ )
		{
			//get Ebay Title
			$ebay_title = "";
			$ItemItemID = "";
			$itemsku = "";
			if ( $shop_type == 2 && (string)$xml->Order[0]->OrderItems[0]->Item[$i]->OrderItemforeign_transactionID[0] != "" )
			{
				$res_items_desc = q("SELECT * FROM ebay_orders_items WHERE TransactionID = '" . (string)$xml->Order[0]->OrderItems[0]->Item[$i]->OrderItemforeign_transactionID[0] . "';", $dbshop, __FILE__, __LINE__);
				if ( mysqli_num_rows( $res_items_desc ) > 0 )
				{
					$row_items_desc = 	mysqli_fetch_assoc( $res_items_desc );
					$ebay_title = 		$row_items_desc["ItemTitle"];
					
					//MPN
					$itemsku = $row_items_desc["ItemSKU"];
					
					$ItemItemID = $row_items_desc["ItemItemID"];
				}
				else
				{
					$ebay_title = "";
					$itemsku = "";
				}
				
			}
			
			//GET SHOP_ITEM Title
			$shop_title = "";
			$res_items_desc = q( "SELECT * FROM shop_items_de WHERE id_item = " . (int)$xml->Order[0]->OrderItems[0]->Item[$i]->OrderItemItemID[0] . ";", $dbshop, __FILE__, __LINE__ );
			$row_items_desc = mysqli_fetch_assoc( $res_items_desc );
			$shop_title = $row_items_desc["title"];
			//$item_title_raw = $row_items_desc["title"];
			//$shop_title = trim( substr( $item_title_raw, 0, strpos( $item_title_raw, "(" ) ) );
			
		
			//GET MPN
			$res_MPN = q( "SELECT * FROM shop_items WHERE id_item = " . (int)$xml->Order[0]->OrderItems[0]->Item[$i]->OrderItemItemID[0] . ";", $dbshop, __FILE__, __LINE__ );
			if ( mysqli_num_rows( $res_MPN ) > 0 )
			{
				$row_MPN = mysqli_fetch_assoc( $res_MPN );
				$MPN = $row_MPN["MPN"];
			}
			else $MPN = "";
			
			if ($MPN != $itemsku)
			{
				$item_title = $shop_title;
			}
			elseif ( $shop_type == 2 && (string)$xml->Order[0]->OrderItems[0]->Item[$i]->OrderItemforeign_transactionID[0] != "" )
			{
				$item_title = $ebay_title;
			}
			else $item_title = $shop_title;
			
			$item[] = $xml->Order[0]->OrderItems[0]->Item[$i]->OrderItemAmount[0] . "x " . $item_title;
		
		}
			
	/*	
		$item = array();
		for ( $i = 0; $i < sizeof( $xml->Order[0]->OrderItems[0]->Item ); $i++ )
		{
			$item[] = $xml->Order[0]->OrderItems[0]->Item[$i]->OrderItemAmount[0] . "x " . $xml->Order[0]->OrderItems[0]->Item[$i]->OrderItemDesc[0];
		}
	*/
		//get Mail-Address
		$mail = (string)$xml->Order[0]->usermail[0];
		if ( $mail == "" || $mail == "Invalid Request" )
		{
			show_error( 9814, 7, __FILE__, __LINE__, "Keine email-Adresse gefunden. OrderID: " . $_POST["OrderID"] );
			exit;
		}
		
		//Get BuyerUserID
		$BuyerUserID = '';
		$BuyerUserID = (string)$xml->Order[0]->buyerUserID[0];
								$BuyerUserID2 = '';
								$BuyerUserID2 = (string)$xml->Order[0]->buyerUserID[0];
		
		//Get Name
		$Name = "";
		$Name = (string)$xml->Order[0]->bill_company[0];
		if ( $Name != "" ) $Name .= ", ";
		$Name .= (string)$xml->Order[0]->bill_firstname[0] . " " . (string)$xml->Order[0]->bill_lastname[0];
								$Name2 = "";
								$Nam2e = (string)$xml->Order[0]->bill_company[0];
								if ( $Name2 != "" ) $Name2 .= ", ";
								$Name2 .= (string)$xml->Order[0]->bill_firstname[0] . " " . (string)$xml->Order[0]->bill_lastname[0];
		
		//GET CMS_USER_ID
		$customer_id = (int)$xml->Order[0]->customer_id[0];
		
		//GET TRACKING-ID
		$tracking_id = '';
		$tracking_id = (int)$xml->Order[0]->shipping_number[0];
		
		//GET cms_users DATA
		$user_origin = "";
		$res = q( "SELECT * FROM cms_users WHERE id_user = " . $customer_id, $dbweb, __FILE__, __LINE__ );
		if ( mysqli_num_rows( $res ) == 1 )
		{
			$cms_users = mysqli_fetch_array( $res );
			$user_origin = $cms_users["origin"];
		}
		
		//COUNTRY-CODE FÜR TRACKING-LINK
		$country_code = 'de';
		if ( ( $user_origin != 'DE' and $user_origin != 'AT' and $user_origin != 'CH' and $user_origin != '' ) or $shop_id == 5 )
		{
			$country_code = 'en';
		}
		
		// get order-seller
		$seller_name = 	'';
		$seller_phone = '';
		$seller_mail = 	'';
		$firstmod_user = 0;
		
		$res4 = q( "SELECT * FROM shop_orders_events WHERE order_id=" . $_POST["OrderID"] . " AND eventtype_id IN (11,21)", $dbshop, __FILE__, __LINE__ );
		if ( mysqli_num_rows( $res4 ) > 0 )
		{
			$shop_orders_events = 	mysqli_fetch_assoc( $res4 );
			$firstmod_user = 		$shop_orders_events['firstmod_user'];
		}
		if ( $firstmod_user > 0 )
		{
			$res8 = q( "SELECT a.firstname, a.lastname, a.phone, a.mail FROM cms_contacts AS a, cms_contacts_departments AS b, cms_contacts_locations AS c WHERE a.idCmsUser=" . $firstmod_user . " AND a.department_id=b.id_department AND b.location_id=c.id_location AND c.site_id=" . $site_id, $dbweb, __FILE__, __LINE__ );
			if ( mysqli_num_rows( $res8 ) > 0 )
			{
				$cms_contacts_join = mysqli_fetch_assoc( $res8 );
				$seller_name = 	$cms_contacts_join['firstname'] . ' ' . $cms_contacts_join['lastname'];
				$seller_phone = $cms_contacts_join['phone'];
				$seller_mail = 	$cms_contacts_join['mail'];
			}
			else
			{
				$res5 = q( "SELECT * FROM cms_contacts WHERE idCmsUser=" . $firstmod_user, $dbweb, __FILE__, __LINE__ );
				if ( mysqli_num_rows( $res5 ) > 0 )
				{
					$cms_contacts = mysqli_fetch_assoc( $res5 );
					$seller_name = 	$cms_contacts['firstname'] . ' ' . $cms_contacts['lastname'];
					$seller_phone = $cms_contacts['phone'];
					$seller_mail = 	$cms_contacts['mail'];
				}
			}
		}
		
		// get language-id for article-translation
		$language_id = 1;
		$res7 = q( "SELECT * FROM cms_languages WHERE code='" . strtolower( $user_origin ) . "'", $dbweb, __FILE__, __LINE__ );
		if ( mysqli_num_rows( $res7 ) == 1 )
		{
			$cms_languages = 	mysqli_fetch_assoc( $res7 );
			$language_id = 		$cms_languages['id_language'];
		}
		elseif ( mysqli_num_rows( $res7 ) == 0 )
		{
			$language_id = 2;
		}
		if ( $user_origin == 'AT' or $user_origin == 'CH' or $user_origin == 'DE' )
		{
			$language_id = 1;
		}
		if ( $shop_id == 5 )
		{
			$language_id = 2;
		}		
		
/*			
		if ( $shop_id != 19 and $shop_id != 20 and $shop_id != 21 )
		{	
			
									//GET ARTICLES
									$article_id_2 = 52233;
									$msgtext = 		"";
									$subject = 		"";
									
									if ( $user_origin != "DE" and $user_origin != "CH" and $user_origin != "AT" and $user_origin != '' )
									{
										$post_data = 				array();
										$post_data["API"] = 		"cms";
										$post_data["APIRequest"] = 	"ArticleTranslationGet";
										$post_data["article_id"] = 	$article_id_2;
										$post_data["id_language"] = 2;
												
										$postdata = http_build_query( $post_data );
										
										$xml = soa2( $postdata );
										if ( $xml->Ack[0] != "Success" )
										{
											show_error( 9815, 1, __FILE__, __LINE__, "Keine Übersetzung des Beitrags gefunden. article_id: " . $article_id_2 );
										}
										else
											$article_id_2 = $xml->article_id_trans[0];
									}
								
									$res_cms_articles = q( "SELECT introduction, article FROM cms_articles WHERE id_article=" . $article_id_2, $dbweb, __FILE__, __LINE__ );
									$cms_articles = 	mysqli_fetch_assoc( $res_cms_articles );
									$subject = 			$cms_articles["introduction"];
									$msgtext = 			$cms_articles["article"];	
		
									//SHOPSPEZIFISCHE DATEN	
									$sendermail = 	"";
									$shop = 		"";
									$mail_header = 	"";
									$phone = 		"";
									
									$res = q( "SELECT * FROM shop_shops WHERE id_shop=" . $shop_id, $dbshop, __FILE__, __LINE__ );
									$shop_shops = mysqli_fetch_assoc( $res );
									
									$sendermail = 	$shop_shops['mail'];
									$shop = 		$shop_shops['domain'];
									if ( $shop_shops['parent_shop_id'] > 0 )
									{
										$res2 = 		q( "SELECT * FROM shop_shops WHERE id_shop=" . $shop_shops['parent_shop_id'], $dbshop, __FILE__, __LINE__ );
										$shop_shops2 = 	mysqli_fetch_assoc( $res2 );
										$shop = 		$shop_shops2['domain'];
										$sendermail = 	$shop_shops['mail'];
									}
									$mail_header = 	'<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="' . PATH . 'images/newsletter_header.jpg" alt="MAPCO" title="MAPCO">';
									
									if ( $shop_id == 2 )
									{
										$mail_header = 	'<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="http://www.ihr-autopartner.de/images/mail_header.jpg" alt="Ihr Autopartner" title="Ihr Autopartner">';
									}
									elseif ( $shop_id == 3 )
									{
										$sendermail = 	"ebay@mapco.de";
										$shop = 		"MAPCO (ebay)";
									}
									elseif ( $shop_id == 4 )
									{
										$sendermail = 	"ebay@ihr-autopartner.com";
										$shop = 		"Ihr Autopartner (ebay)";
										$mail_header = 	'<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="http://www.ihr-autopartner.de/images/mail_header.jpg" alt="Ihr Autopartner" title="Ihr Autopartner">';
									}
									elseif ( $shop_id == 5 )
									{
										$sendermail = 	'ebay_uk@mapco.de';
										$shop = 		'MAPCO (ebay)';
									}
									elseif ( $shop_id == 6 )
									{
										$sendermail = 	'amazon_ap@ihr-autopartner.de';
										$shop = 		'Ihr Autopartner (ebay)';
										$mail_header = 	'<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="http://www.ihr-autopartner.de/images/mail_header.jpg" alt="Ihr Autopartner" title="Ihr Autopartner">';
									}
			
									//CREATE MAIL
									$subject = str_replace( '<!-- ORDER-ID -->', $_POST['OrderID'], $subject );
									$subject = str_replace( '<!-- SHOP -->', $shop, $subject );
			
									$msgtext = str_replace( '<!-- MAIL-HEADER -->', $mail_header, $msgtext );
			
									if ( trim( $Name2 ) == "" )
									{
										if ( $BuyerUserID2 != "" )
										{
											$msgtext = str_replace( "<!-- NAME -->" , $BuyerUserID2, $msgtext );
											$msgtext = cutout( $msgtext, "<!-- BUYER-START -->", "<!-- BUYER-END -->" );
										}
									}
									else
									{
										if ( $BuyerUserID2 != "" )
										{
											$Name2 = $Name2 . " (" . $BuyerUserID2 . ")";
										}
										$msgtext = str_replace( "<!-- NAME -->", $Name2, $msgtext );	
										$msgtext = cutout( $msgtext, "<!-- BUYER-START -->", "<!-- BUYER-END -->" );
									}
									//****************
									if ( sizeof($item) == 1 && $item[0] != "" )
									{
										$msgtext = str_replace( "<!-- ITEMS -->", $item[0], $msgtext );
										$msgtext = cutout( $msgtext, "<!-- THANKS-2-START -->", "<!-- THANKS-3-END -->" );
									}
									elseif ( sizeof($item) > 1 && !in_array( "", $item ) )
									{
										$buf = "";
										for ( $i = 0; $i < sizeof($item); $i++ ) {
											$buf .= '<b>' . $item[$i] . '</b><br />';
										}
										$msgtext = str_replace( "<!-- ITEMS -->", $buf, $msgtext );
										$msgtext = cutout( $msgtext, "<!-- THANKS-1-START -->", "<!-- THANKS-1-END -->" );
										$msgtext = cutout( $msgtext, "<!-- THANKS-3-START -->", "<!-- THANKS-3-END -->" );
									}
									else
									{
										$msgtext = cutout( $msgtext, "<!-- THANKS-1-START -->", "<!-- THANKS-2-END -->" );
									}
									//***********************
									$msgtext = str_replace( '<!-- COUNTRY-CODE -->', $country_code, $msgtext );
									$msgtext = str_replace( '<!-- TRACKING-ID -->', $tracking_id, $msgtext );
									
									$msgtext = str_replace( '<!-- SHOP -->', $shop, $msgtext );
									$msgtext = str_replace( '<!-- MAIL -->', $sendermail, $msgtext );	
									$msgtext = str_replace( '<!-- PATH -->', PATH, $msgtext );
									
									if ( $shop_id == 2 or $shop_id == 4 or $shop_id == 6 )
									{
										$msgtext = cutout( $msgtext, '<!-- SCHRAUBER-START -->', '<!-- SCHRAUBER-END -->' );
									}
			
//***************** mail versand und beitrag speichern **********************************************************
	
			//MailSend
			$post_data = 				array();
			$post_data["API"] = 		"cms";
			$post_data["APIRequest"] = 	"MailSend";
			$post_data["ToReceiver"] = 	$mail;	
			$post_data["FromSender"] = 	$sendermail;
			$post_data["Subject"] = 	$subject;
			$post_data["MsgText"] = 	$msgtext;
			
			$postdata = http_build_query( $post_data );
			
			$response = soa2( $postdata, __FILE__, __LINE__ );
	
			//save email in cms_articles
			$post_data = 				array();
			$post_data["API"] = 		"cms";
			$post_data["APIRequest"] = 	"ArticleAdd";
			$post_data["title"] = 		$subject;
			$post_data["article"] = 	$msgtext;	
			$post_data["format"] = 		1;
			
			$postdata = http_build_query( $post_data );
	
			$response = soa2( $postdata, __FILE__, __LINE__ );
			
			$article_id_2 = (int)$response->article_id[0];
			
			//save conversation in crm_conversations
			$post_data = 				array();
			$post_data["API"] = 		"crm";
			$post_data["APIRequest"] = 	"ConversationAdd";
			$post_data["user_id"] = 	$customer_id;	
			$post_data["order_id"] = 	$_POST["OrderID"];
			$post_data["article_id"] = 	$article_id_2;
			$post_data["type_id"] = 	1;
			$post_data["con_from"] = 	$sendermail;
			$post_data["con_to"] = 		$mail;
			
			$postdata = http_build_query( $post_data );
			
			$response = soa2( $postdata, __FILE__, __LINE__ );
			
			//save article label in cms_articles_labels
			$post_data = 				array();
			$post_data["API"] = 		"cms";
			$post_data["APIRequest"] = 	"ArticleLabelAdd";
			$post_data["article_id"] = 	$article_id_2;
			$post_data["label_id"] = 	21;
			
			$postdata = http_build_query( $post_data );
	
			$response = soa2( $postdata, __FILE__, __LINE__ );
	
//**************************** mail versand und beitrag speichern ende ********************************************
			
			//Kopie an mwosgien	
			$post_data = 				array();
			$post_data["API"] = 		"cms";
			$post_data["APIRequest"] = 	"MailSend";
			$post_data["ToReceiver"] = 	"mwosgien@mapco.de";	
			$post_data["FromSender"] = 	$sendermail;
			$post_data["Subject"] = 	$subject . " *****KOPIE*****";
			$post_data["MsgText"] = 	$msgtext . '<br />' . $customer_id . '<br />' . $mail;
			
			$postdata=http_build_query($post_data);
			
			$response = soa2( $postdata, __FILE__, __LINE__ );
			
		} // end if ( $shop_id != 6 and $shop_id != 19 and $shop_id != 20 and $shop_id != 21 )
*/		
		
		if ( $article_id > 0 )
		{
			
			// MailSend2
			$post_data = 						array();
			$post_data['API'] = 				'cms';
			$post_data['APIRequest'] = 			'MailSend2';
			$post_data['ToReceiver'] = 			$mail;
			$post_data['FromSender'] = 			$seller_mail;		
			$post_data['save'] = 				1;
			$post_data['site_id'] = 			$site_id;
			$post_data['user_id'] = 			$customer_id;
			$post_data['format'] = 				1;
			$post_data['order_id'] = 			$_POST['OrderID'];		
			$post_data['article_id'] = 			$article_id;
			$post_data['language_id'] = 		$language_id;
			$post_data['os_order_id'] = 		$_POST['OrderID'];
			$post_data['os_name'] = 			$Name;
			$post_data['os_buyer_user_id'] = 	$BuyerUserID;
			$post_data['os_items'] = 			serialize( $item );
			$post_data['os_country_code'] = 	$country_code;
			$post_data['os_tracking_id'] = 		$tracking_id;
			$post_data['os_seller_name'] = 		$seller_name;
			$post_data['os_seller_phone'] = 	$seller_phone;
			$post_data['os_seller_mail'] = 		$seller_mail;
			
			$postdata = http_build_query( $post_data );
			
			$response = soa2( $postdata, __FILE__, __LINE__ );
			
/*			
			// Kopie an mwosgien
			$post_data = 						array();
			$post_data['API'] = 				'cms';
			$post_data['APIRequest'] = 			'MailSend2';
			$post_data['ToReceiver'] = 			'mwosgien@mapco.de';
			$post_data['FromSender'] = 			$seller_mail;		
			$post_data['article_id'] = 			$article_id;
			$post_data['language_id'] = 		$language_id;
			$post_data['os_order_id'] = 		$_POST['OrderID'];
			$post_data['os_name'] = 			$Name;
			$post_data['os_buyer_user_id'] = 	$BuyerUserID;
			$post_data['os_items'] = 			serialize( $item );
			$post_data['os_country_code'] = 	$country_code;
			$post_data['os_tracking_id'] = 		$tracking_id;
			$post_data['os_seller_name'] = 		$seller_name;
			$post_data['os_seller_phone'] = 	$seller_phone;
			$post_data['os_seller_mail'] = 		$seller_mail;
			
			$postdata = http_build_query( $post_data );
			
			$response = soa2( $postdata, __FILE__, __LINE__ );
*/		
		}
	}
	elseif ( $_POST['mode'] == 'other' and $_POST['ToReceiver'] != '0' and $_POST['ToReceiver'] != '' )
	{
		
		//get mail-address
		$mail = $_POST['ToReceiver']; // auf $_POST umbauen
		if ( $mail == "" || $mail == "0" )
		{
			show_error( 9814, 7, __FILE__, __LINE__, "Keine email-Adresse gefunden." );
			exit;
		}
		
		// get user origin
		$user_origin = $_POST['origin'];
/*		
								// Absender-Email_adresse
								$sendermail = 'bestellung@mapco-shop.de';
		
								// get user origin
								$user_origin = $_POST['origin'];
		
								//Get Name
								$name = '';
								if ( ( $_POST['firstname'] != '' and $_POST['firstname'] != '-' ) or ( $_POST['lastname'] != '' and $_POST['lastname'] != '-' ) ) {
									if ( $_POST['firstname'] != '' and $_POST['firstname'] != '-' ) $name .= $_POST['firstname'];
									if ( $_POST['lastname'] != '' and $_POST['lastname'] != '-' ) {
										if ( $_POST['firstname'] != '' and $_POST['firstname'] != '-' ) $name .= ' ' . $_POST['lastname'];
										else $name .= $_POST['lastname'];
									}
									if ( ( $_POST['company'] != '' and $_POST['company'] != '-' ) or ( $_POST['company2'] != '' and $_POST['company2'] != '-' ) ) {
										$name .= ' (';
										if ( $_POST['company'] != '' and $_POST['company'] != '-' ) $name .= $_POST['company'];
										if ( $_POST['company2'] != '' and $_POST['company2'] != '-' ) {
											if ( $_POST['company'] != '' and $_POST['company'] != '-' ) $name .= ', ' . $_POST['company2'];
											else $name .= $_POST['company2'];	
										}
										$name .= ')';
									}
								}
								else {
									if ( $_POST['company'] != '' and $_POST['company'] != '-' ) $name .= $_POST['company'];
									if ( $_POST['company2'] != '' and $_POST['company2'] != '-' ) {
										if ( $_POST['company'] != '' and $_POST['company'] != '-' ) $name .= ', ' . $_POST['company2'];
										else $name .= $_POST['company2'];	
									}
								}
*/			
		//GET TRACKING-ID
		$tracking_id = $_POST['shipping_number']; // auf $_POST umbauen
		if ( $tracking_id == "" || $tracking_id == "0" )
		{
			show_error( 9843, 7, __FILE__, __LINE__, "Keine Tracking-Id gefunden." );
			exit;
		}
		
		// language_id
		$language_id = 1; 
		if ( $user_origin != "DE" and $user_origin != "CH" and $user_origin != "AT" and $user_origin != '' )
		{
			$language_id = 2;
		}		
/*		
								//COUNTRY-CODE FÜR TRACKING-LINK
								$country_code = 'de';
								if ( $user_origin != 'DE' and $user_origin != 'AT' and $user_origin != 'CH' and $user_origin != '' ) {
									$country_code = 'en';
								}
		
								// get article(s)
								$article_id = 	55432;
								$msgtext = 		"";
								$subject = 		"";
								
								if ( $user_origin != "DE" and $user_origin != "CH" and $user_origin != "AT" and $user_origin != '' ) {
									$post_data = 				array();
									$post_data["API"] = 		"cms";
									$post_data["APIRequest"] = 	"ArticleTranslationGet";
									$post_data["article_id"] = 	$article_id;
									$post_data["id_language"] = 2;
											
									$postdata = http_build_query( $post_data );
									
									$xml = soa2( $postdata );
									if ( $xml->Ack[0] != "Success" ) {
										show_error( 9815, 1, __FILE__, __LINE__, "Keine Übersetzung des Beitrags gefunden. article_id: " . $article_id );
									}
									else
										$article_id = $xml->article_id_trans[0];
								}
								
								$res_cms_articles = q( "SELECT introduction, article FROM cms_articles WHERE id_article=" . $article_id, $dbweb, __FILE__, __LINE__ );
								$cms_articles = 	mysqli_fetch_assoc( $res_cms_articles );
								$subject = 			$cms_articles["introduction"];
								$msgtext = 			$cms_articles["article"];	
		
								// build email
								//$mail_header = '<img style="margin:0px 0px 0px 0px; border:0; padding:0; float:right;" src="' . PATH . 'images/newsletter_header.jpg" alt="MAPCO" title="MAPCO">';
								
								//$msgtext = str_replace( '<!-- MAIL-HEADER -->', $mail_header, $msgtext );
								$msgtext = str_replace( '<!-- NAME -->', $name, $msgtext );
								//if ( strlen($name) > 0 ) $msgtext = cutout( $msgtext, '<!-- BUYER-START -->', '<!-- BUYER-END -->' );
								$msgtext = str_replace( '<!-- COUNTRY-CODE -->', $country_code, $msgtext );
								$msgtext = str_replace( '<!-- TRACKING-ID -->', $tracking_id, $msgtext );
								$msgtext = str_replace( '<!-- PATH -->', PATH, $msgtext );
			
//***************** mail versand **********************************************************
	
		//MailSend
		$post_data = 				array();
		$post_data["API"] = 		"cms";
		$post_data["APIRequest"] = 	"MailSend";
		$post_data["ToReceiver"] = 	$mail;	
		$post_data["FromSender"] = 	$sendermail;
		$post_data["Subject"] = 	$subject;
		$post_data["MsgText"] = 	$msgtext;
		
		$postdata = http_build_query( $post_data );
		
		$response = soa2( $postdata, __FILE__, __LINE__ );
	
//**************************** mail versand ende ********************************************
	
		// Kopie an mwosgien	
		$post_data = 				array();
		$post_data["API"] = 		"cms";
		$post_data["APIRequest"] = 	"MailSend";
		$post_data["ToReceiver"] = 	"mwosgien@mapco.de";	
		$post_data["FromSender"] = 	$sendermail;
		$post_data["Subject"] = 	$subject . " *****KOPIE*****";
		$post_data["MsgText"] = 	$msgtext . '<br /><br />Empfänger: ' . $mail . '<br />Tracking-Id: ' . $_POST['shipping_number'] . '<br />Firma 1: ' . $_POST['company'] . '<br />Firma 2: ' . $_POST['company2'] . '<br />Vorname: ' . $_POST['firstname'] . '<br />Nachname: ' . $_POST['lastname'] . '<br />Origin: ' . $_POST['origin'];
		
		$postdata = http_build_query( $post_data );
		
		$response = soa2( $postdata, __FILE__, __LINE__ );
*/



		// MailSend2
		$post_data = 						array();
		$post_data['API'] = 				'cms';
		$post_data['APIRequest'] = 			'MailSend2';
		$post_data['ToReceiver'] = 			$_POST['ToReceiver'];
		$post_data['FromSender'] = 			'bestellung@mapco-shop.de';
		$post_data['article_id'] = 			55432;
		$post_data['language_id'] = 		$language_id;
		$post_data['oso_origin'] = 			$_POST['origin'];
		$post_data['oso_shipping_number'] = $_POST['shipping_number'];
		$post_data['oso_firstname'] = 		$_POST['firstname'];
		$post_data['oso_lastname'] = 		$_POST['lastname'];
		$post_data['oso_company'] = 		$_POST['company'];
		$post_data['oso_company2'] = 		$_POST['company2'];
		
		$postdata = http_build_query( $post_data );
		
		$response = soa2( $postdata, __FILE__, __LINE__ );
/*		
		// Kopie mwosgien
		$post_data = 						array();
		$post_data['API'] = 				'cms';
		$post_data['APIRequest'] = 			'MailSend2';
		$post_data['ToReceiver'] =			'mwosgien@mapco.de'; // ändern
//		$post_data['ToReceiver'] = 			$_POST['ToReceiver'];
		$post_data['FromSender'] = 			'bestellung@mapco-shop.de' . '****** KOPIE *****';
		$post_data['article_id'] = 			55432;
		$post_data['language_id'] = 		$language_id;
		$post_data['oso_origin'] = 			$_POST['origin'];
		$post_data['oso_shipping_number'] = $_POST['shipping_number'];
		$post_data['oso_firstname'] = 		$_POST['firstname'];
		$post_data['oso_lastname'] = 		$_POST['lastname'];
		$post_data['oso_company'] = 		$_POST['company'];
		$post_data['oso_company2'] = 		$_POST['company2'];
		
		$postdata = http_build_query( $post_data );
		
		$response = soa2( $postdata, __FILE__, __LINE__ );
*/		
		// mail an mwosgien
		//mail( 'mwosgien@mapco.de', 'Nicht-Online Bestellung gesendet', 'Infotext' );
	}

?>