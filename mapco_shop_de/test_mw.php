<?php

	include( "config.php" );
	include( "templates/" . TEMPLATE_BACKEND . "/header.php" );
	
	//$post_data = array();
	//API
	//$post_data[ 'API' ] = 'cms';
	//$post_data[ 'API' ] = 'crm';
	//$post_data[ 'API' ] = 'shop';
	
	//$post_data[ 'APIRequest' ] = 'CustomerExistCheck';
	//$post_data[ 'APIRequest' ] = 'MailOrderSeller2';
	//$post_data[ 'APIRequest' ] = 'MailSend2';
	//$post_data[ 'APIRequest' ] = 'MailRegisterConfirmation';
	//$post_data[ 'APIRequest' ] = 'MailOrderSent';
	
	//DATA
	//$post_data[ 'save' ] = 1;
	//$post_data[ 'format' ] = 1;
	//$post_data[ 'user_id' ] = 49352;
	//$post_data[ 'user_mail' ] = 'mwosgien@zmapco.de';
	//$post_data[ 'order_id' ] = 22222222;
	//$post_data[ 'ToReceiver' ] = 'mwosgien@mapco.de';
	//$post_data[ 'article_id' ] = 55432; // OrderSent-template
	//$post_data[ 'article_id' ] = 70464;
	//$post_data[ 'FromSender' ] = 'os-test@test.de';
	//$post_data[ 'shipping_number' ] = 1234567890;
	//$post_data[ 'company' ] = 'Firma 1';
	//$post_data[ 'company2' ] = 'Firma 2';
	//$post_data[ 'firstname' ] = 'Mojo';
	//$post_data[ 'lastname' ] = 'Nixon';
	//$post_data[ 'origin' ] = 'CN';
	//$post_data[ 'mode' ] = 'other';
	
	$post_data = 								array();
	$post_data['API'] = 						'cms';
	$post_data['APIRequest'] = 					'MailSend2';
	$post_data['ToReceiver'] = 					'mwosgien@mapco.de'; // ändern
	$post_data['article_id'] = 					126927;
	$post_data['FromSender'] = 					'MAPCO-Shop-****NEU**** <bestellung@mapco-shop.de>'; // ändern
	$post_data['ocb_shop_city'] = 				$city;
	$post_data['ocb_shipping_details'] = 		'ssLieferservice';
	$post_data['ocb_shipping_costs'] = 			5.96;
	$post_data['ocb_shop_id'] = 				12;
	$post_data['ocb_firstmod'] = 				1397054551;
	$post_data['ocb_username'] = 				'MA1232456';
	$post_data['ocb_order_id'] = 				1812552;
	$post_data['ocb_payments_transaction_id'] = 65656575757333;
	$post_data['ocb_ordernr'] = 				9876543;
	$post_data['ocb_comment'] = 				'Dies ist ein Kommentar';
	$post_data['ocb_usermail'] = 				'nn@nn.nn';
	$post_data['ocb_userphone'] = 				'01234/1234';
	$post_data['ocb_userfax'] = 				'01234/1234-5';
	$post_data['ocb_usermobile'] = 				'0160/96429013';
	$post_data['ocb_shop_city'] = 				'Freiburg';
	$post_data['ocb_bill_adr_id'] = 			3000;
	$post_data['ocb_ship_adr_id'] = 			3001;
	$post_data['ocb_user_id'] = 				49352;
	$post_data['ocb_eu'] = 						0;
	$post_data['ocb_bill_country_id'] = 		2;	
	
	$postdata = http_build_query( $post_data );
	
//	$response = soa2( $postdata, __FILE__, __LINE__ );
	
	//$post_data[ 'username' ] = 'mwosgien';
	//$post_data[ 'usermail' ] = 'mwosgien@mapco.de';
	//$post_data[ 'language_id' ] = 2;
/*	
	$post_data[ 'mode' ] = 							'b2b';
	$post_data[ 'b2b_reg_username' ] = 				'mwosgien';
	$post_data[ 'b2b_reg_company' ] = 				'Software Wosgien';
	$post_data[ 'b2b_reg_company_voice' ] = 		'Assistent';
	$post_data[ 'b2b_reg_street' ] = 				'Wiesenburger Str. 30';
	$post_data[ 'b2b_reg_zip' ] = 					'14806';
	$post_data[ 'b2b_reg_city' ] = 					'Belzig';
	$post_data[ 'b2b_reg_tel' ] = 					'033841/555-55';
	$post_data[ 'b2b_reg_fax' ] = 					'033841/555-56';
	$post_data[ 'b2b_reg_usermail' ] = 				'mwosgien@mapco.de';
	$post_data[ 'b2b_reg_tax_number' ] = 			'1223456789456';
	$post_data[ 'b2b_reg_filepath' ] = 				PATH . 'files/1000/1000123.jpg';
	$post_data[ 'b2b_reg_ship_adr' ] = 				1;
	$post_data[ 'b2b_reg_ship_company' ] = 			'Lieferfirma';
	$post_data[ 'b2b_reg_ship_company_voice' ] = 	'Liefer-Assistent';
	$post_data[ 'b2b_reg_ship_street' ] = 			'Liefer-Strasse';
	$post_data[ 'b2b_reg_ship_zip' ] = 				'Liefer-12345';
	$post_data[ 'b2b_reg_ship_city' ] = 			'Liefer-Stadt';
	$post_data[ 'b2b_reg_ship_tel' ] = 				'Liefer-tel12345';
	$post_data[ 'b2b_reg_ship_fax' ] = 				'Liefer_fax-123456';
*/
	//AUFRUF
	//$postdata = http_build_query( $post_data );
	
	//echo $request = soa2( $post_data, __FILE__, __LINE__, 'xml' );
	echo "<br />" . PATH . '<br />';
	echo ' <div style="border: 1px solid; border-color: red; border-radius: 20px; text-align: center;"><p>test</p></div>';
	
	include( "templates/" . TEMPLATE_BACKEND . "/footer.php" );

?>