<?php

	/*
	*	SOA2-Service
	*/
	
	include("../functions/shop_get_prices.php");
	include("../functions/shop_itemstatus.php");	
	include("../functions/mapco_gewerblich.php");
	include("../functions/cms_t2.php");
	
	/*
	*	optionale POST_Variablen:
	*		save				--> =1 -> save mail in conversations
	*			order_id
	*			site_id
	*		FromSender			--> email-Absenderadresse
	*		language_id			--> sucht nach der entsprechenden Artikel-Übersetzung: default: Original-Artikel
	*		subject				--> falls nicht der Betreff aus dem article verwendet werden soll
	*
	*		pw				--> password (password-request)
	*		user_token		--> usertoken für autologin-link (password-request)
	*
	*		user_name				--> (b2c-registration)
	*		user_mail				--> (b2c-registration)
	*
	*		b2b_reg_username			--> (b2b-registration)
	*		b2b_reg_company				--> (b2b-registration)
	*		b2b_reg_company_voice		--> (b2b-registration)
	*		b2b_reg_street				--> (b2b-registration)
	*		b2b_reg_zip					--> (b2b-registration)
	*		b2b_reg_city				--> (b2b-registration)
	*		b2b_reg_tel					--> (b2b-registration)
	*		b2b_reg_fax					--> (b2b-registration)
	*		b2b_reg_usermail			--> (b2b-registration)
	*		b2b_reg_tax_number			--> (b2b-registration)
	*		b2b_reg_filepath			--> (b2b-registration)
	*		b2b_reg_ship_adr			--> (b2b-registration)
	*		b2b_reg_ship_company		--> (b2b-registration)
	*		b2b_reg_ship_company_voice	--> (b2b-registration)
	*		b2b_reg_ship_street			--> (b2b-registration)
	*		b2b_reg_ship_zip			--> (b2b-registration)
	*		b2b_reg_ship_city			--> (b2b-registration)
	*		b2b_reg_ship_tel			--> (b2b-registration)
	*		b2b_reg_ship_fax			--> (b2b-registration)
	*
	*		occ_order_id					--> (order-confirmation-customer)
	*		occ_trustedshops_id				--> (order-confirmation-customer)
	*		occ_usermail					--> (order-confirmation-customer)		
	*		occ_domain						--> (order-confirmation-customer)
	*		occ_Payments_TransactionID		--> (order-confirmation-customer)
	*		occ_Payments_TransactionState	--> (order-confirmation-customer)
	*		occ_bill_adr_id					--> (order-confirmation-customer)
	*		occ_ship_adr_id					--> (order-confirmation-customer)
	*		occ_partner_id					--> (order-confirmation-customer)
	*		occ_shipping_details			--> (order-confirmation-customer)
	*		occ_shipping_details_memo		--> (order-confirmation-customer)
	*		occ_shipping_costs				--> (order-confirmation-customer)
	*		occ_gewerblich					--> (order-confirmation-customer)
	*		occ_shop_id						--> (order-confirmation-customer)
	*		occ_status_id					--> (order-confirmation-customer)
	*		occ_payments_type_id			--> (order-confirmation-customer)
	*		occ_withdrawal_article_id		--> (order-confirmation-customer)
	*
	*		os_order_id 			--> (order-sent-online)
	*		os_name					--> (order-sent-online)
	*		os_buyer_user_id		--> (order-sent-online)
	*		os_items (array)		--> (order-sent-online)
	*		os_country_code			--> (order-sent-online)
	*		os_tracking_id			--> (order-sent-online)
	*		os_seller_name			--> (order-sent-online)
	*		os_seller_phone			--> (order-sent-online)
	*		os_seller_mail			--> (order-sent-online)
	*
	*		oso_origin			--> (order-sent-nicht online)
	*		oso_shipping_number	--> (order-sent-nicht online)
	*		oso_firstname 		--> (order-sent-nicht online)
	*		oso_lastname		--> (order-sent-nicht online)
	*		oso_company			--> (order-sent-nicht online)
	*		oso_company2		--> (order-sent-nicht online)
	*
	*		fin_order_id			--> (fin-mail)
	*		fin_name				--> (fin-mail)
	*		fin_buyer_user_id		--> (fin-mail)
	*		fin_items (array)		--> (fin-mail)
	*		fin_company				--> (fin_mail)
	*		fin_firstname			--> (fin_mail)
	*		fin_lastname			--> (fin_mail)
	*		fin_street				--> (fin_mail)
	*		fin_additional			--> (fin_mail)
	*		fin_city				--> (fin_mail)
	*		fin_country				--> (fin_mail)
	*		fin_domain				--> (fin-mail)
	*		fin_token				--> (fin-mail)
	* 		fin_pay_data_show		--> (fin-mail)
	*		
	*		ocb_shop_city				--> (Bestellmeldung RCs Borkheide)
	*		ocb_shipping_details		--> (Bestellmeldung RCs Borkheide)
	*		ocb_shipping_costs			--> (Bestellmeldung RCs Borkheide)
	*		ocb_shop_id					--> (Bestellmeldung RCs Borkheide)
	*		ocb_firstmod				--> (Bestellmeldung RCs Borkheide)
	*		ocb_username				--> (Bestellmeldung RCs Borkheide)
	*		ocb_order_id				--> (Bestellmeldung RCs Borkheide)
	*		ocb_payments_transaction_id	--> (Bestellmeldung RCs Borkheide)
	*		ocb_ordernr					--> (Bestellmeldung RCs Borkheide)
	*		ocb_comment					--> (Bestellmeldung RCs Borkheide)
	*		ocb_usermail				--> (Bestellmeldung RCs Borkheide)
	*		ocb_userphone				--> (Bestellmeldung RCs Borkheide)
	*		ocb_userfax					--> (Bestellmeldung RCs Borkheide)
	*		ocb_usermobile				--> (Bestellmeldung RCs Borkheide)
	*		ocb_bill_adr_id				--> (Bestellmeldung RCs Borkheide)
	*		ocb_ship_adr_id				--> (Bestellmeldung RCs Borkheide)
	*		ocb_user_id					--> (Bestellmeldung RCs Borkheide)
	*		ocb_eu						--> (Bestellmeldung RCs Borkheide)
	*		ocb_bill_country_id			--> (Bestellmeldung RCs Borkheide)
	*
	*		ocs_shop_city					--> (Bestellmeldung Shop)
	*		ocs_shipping_details			--> (Bestellmeldung Shop)
	*		ocb_shipping_costs				--> (Bestellmeldung Shop)
	*		ocb_shop_id						--> (Bestellmeldung Shop)
	*		ocs_firstmod					--> (Bestellmeldung Shop)
	*		ocs_username					--> (Bestellmeldung Shop)
	*		ocs_order_id					--> (Bestellmeldung Shop)
	*		ocs_payments_transaction_id		--> (Bestellmeldung Shop)
	*		ocs_ordernr						--> (Bestellmeldung Shop)
	*		ocs_comment						--> (Bestellmeldung Shop)
	*		ocs_usermail					--> (Bestellmeldung Shop)
	*		ocs_userphone					--> (Bestellmeldung Shop)
	*		ocs_userfax						--> (Bestellmeldung Shop)
	*		ocs_usermobile					--> (Bestellmeldung Shop)
	*		ocs_bill_adr_id					--> (Bestellmeldung Shop)
	*		ocs_ship_adr_id					--> (Bestellmeldung Shop)
	*		ocs_user_id						--> (Bestellmeldung Shop)
	*		ocs_eu							--> (Bestellmeldung Shop)
	*		ocs_bill_country_id				--> (Bestellmeldung Shop)
	*/

	/*
	*	Variablen für den Email-Versand:
	*		$ToReceiver
	*		$FromSender
	*		$Subject
	*		$MsgText
	*	optional (für Anhänge):
	*		$IFile
	*		$IFileName
	*/
	
	$required = array( "ToReceiver" => "textNN",			// können auch mehrere, durch Komma getrennte; Emailadressen sein
					   "article_id" => "numericNN" );
					   
	check_man_params( $required );
	
	// for email saving
	if ( isset( $_POST[ 'save' ] ) and $_POST[ 'save' ] == 1 ) {
		
		$required = array( "format" => 	"numericNN",		// cms_articles 1=html 2=text
						   "user_id" => "numericNN" );
		
		check_man_params( $required );
		
		$order_id = 0;
		if ( isset( $_POST[ 'order_id' ] ) and $_POST[ 'order_id' ] > 0 ) {
			$order_id = $_POST[ 'order_id' ];
		}
	}
	
	// get article
	$article_id = $_POST[ 'article_id' ];
	
	if ( isset( $_POST[ 'language_id' ] ) )		// get article translation
	{
		$post_data = 				array();
		$post_data["API"] = 		"cms";
		$post_data["APIRequest"] = 	"ArticleTranslationGet";
		$post_data["article_id"] = 	$_POST[ 'article_id' ];
		$post_data["id_language"] = $_POST[ 'language_id' ];
				
		$postdata = http_build_query( $post_data );
		
		$xml = soa2( $postdata );
		if ( $xml->Ack[0] != "Success" )
		{
			show_error( 9815, 1, __FILE__, __LINE__, "Keine Übersetzung des Beitrags gefunden. article_id: " . $article_id );
		}
		else
			$article_id = $xml->article_id_trans[0];
	}
	
	// get language_code
	$language_code = 'de';
	if ( isset( $_POST[ 'language_id' ] ) )
	{
		$res = q( "SELECT * FROM cms_languages WHERE id_language=" . $_POST[ 'language_id' ], $dbweb, __FILE__, __LINE__ );
		if ( mysqli_num_rows( $res ) > 0 )
		{
			$cms_languages = mysqli_fetch_assoc( $res );
			$language_code = $cms_languages['code'];
		}
	}
	
	$res = q( "SELECT * FROM cms_articles WHERE id_article=" . $article_id, $dbweb, __FILE__, __LINE__ );
	if ( mysqli_num_rows( $res ) == 1 )
	{
		$cms_articles = mysqli_fetch_assoc( $res );
		 
		$Subject = $cms_articles[ 'introduction' ];		// subject
		$MsgText = $cms_articles[ 'article' ];			// message
	
	}
	else
	{
		show_error( 9855, 1, __FILE__, __LINE__, 'article_id: ' . $article_id );
		exit;
	}
	
	// get attachments
	$res2 = q( "SELECT * FROM cms_articles_files WHERE article_id=" . $_POST[ 'article_id' ], $dbweb, __FILE__, __LINE__ );
	if ( mysqli_num_rows( $res2 ) > 0 ) {
		$IFile = 		array();						// attachment path
		$IFileName = 	array();						// attachment filename
		$file_ids = 	array();
		while ( $cms_articles_files = mysqli_fetch_assoc( $res2 ) ) {
			$file_ids[] = $cms_articles_files[ 'file_id' ];
		}
		$res3 = q( "SELECT * FROM cms_files WHERE id_file IN (" . implode( ',', $file_ids ) . ")", $dbweb, __FILE__, __LINE__ );
		while ( $cms_files = mysqli_fetch_assoc( $res3 ) ) {
			$IFile[] = 		'../files/' . substr( $cms_files[ 'id_file' ], 0, 4 ) . '/' . $cms_files[ 'id_file' ] . '.' . $cms_files[ 'extension' ];
			$IFileName[] = 	$cms_files[ 'filename' ] . '.' . $cms_files[ 'extension' ];
		}
	}
	
	$ToReceiver = 	$_POST[ 'ToReceiver' ];				// receiver
	
	$FromSender = 	'';
	if ( isset( $_POST[ 'FromSender' ] ) )
	{
		$FromSender = $_POST[ 'FromSender' ];			// sender
	}
	
	if ( isset( $_POST[ 'subject' ] ) )
	{
		$Subject = $_POST[ 'subject' ];					// subject
	}
	
//****************************** replacements *********************************************
	
	// <!-- B2B_REG_CITY -->
	if ( strpos( $MsgText, '<!-- B2B_REG_CITY -->' ) ) {
		if ( isset( $_POST[ 'b2b_reg_city' ] ) ) {
			$MsgText = str_replace( '<!-- B2B_REG_CITY -->', $_POST[ 'b2b_reg_city' ], $MsgText );
		}
	}
	
	// <!-- B2B_REG_COMPANY -->
	if ( strpos( $MsgText, '<!-- B2B_REG_COMPANY -->' ) ) {
		if ( isset( $_POST[ 'b2b_reg_company' ] ) ) {
			$MsgText = str_replace( '<!-- B2B_REG_COMPANY -->', $_POST[ 'b2b_reg_company' ], $MsgText );
		}
	}
	
	// <!-- B2B_REG_COMPANY_VOICE -->
	if ( strpos( $MsgText, '<!-- B2B_REG_COMPANY_VOICE -->' ) ) {
		if ( isset( $_POST[ 'b2b_reg_company_voice' ] ) ) {
			$MsgText = str_replace( '<!-- B2B_REG_COMPANY_VOICE -->', $_POST[ 'b2b_reg_company_voice' ], $MsgText );
		}
	}
	
	// <!-- B2B_REG_FAX -->
	if ( strpos( $MsgText, '<!-- B2B_REG_FAX -->' ) ) {
		if ( isset( $_POST[ 'b2b_reg_fax' ] ) ) {
			$MsgText = str_replace( '<!-- B2B_REG_FAX -->', $_POST[ 'b2b_reg_fax' ], $MsgText );
		}
	}
	
	// <!-- B2B_REG_FILEPATH -->
	if ( strpos( $MsgText, '<!-- B2B_REG_FILEPATH -->' ) ) {
		if ( isset( $_POST[ 'b2b_reg_filepath' ] ) ) {
			$MsgText = str_replace( '<!-- B2B_REG_FILEPATH -->', $_POST[ 'b2b_reg_filepath' ], $MsgText );
		}
	}
	
	// <!-- B2B_REG_SHIP_CITY -->
	if ( strpos( $MsgText, '<!-- B2B_REG_SHIP_CITY -->' ) ) {
		if ( isset( $_POST[ 'b2b_reg_ship_city' ] ) ) {
			$MsgText = str_replace( '<!-- B2B_REG_SHIP_CITY -->', $_POST[ 'b2b_reg_ship_city' ], $MsgText );
		}
	}
	
	// <!-- B2B_REG_SHIP_COMPANY -->
	if ( strpos( $MsgText, '<!-- B2B_REG_SHIP_COMPANY -->' ) ) {
		if ( isset( $_POST[ 'b2b_reg_ship_company' ] ) ) {
			$MsgText = str_replace( '<!-- B2B_REG_SHIP_COMPANY -->', $_POST[ 'b2b_reg_ship_company' ], $MsgText );
		}
	}
	
	// <!-- B2B_REG_SHIP_COMPANY_VOICE -->
	if ( strpos( $MsgText, '<!-- B2B_REG_SHIP_COMPANY_VOICE -->' ) ) {
		if ( isset( $_POST[ 'b2b_reg_ship_company_voice' ] ) ) {
			$MsgText = str_replace( '<!-- B2B_REG_SHIP_COMPANY_VOICE -->', $_POST[ 'b2b_reg_ship_company_voice' ], $MsgText );
		}
	}
	
	// <!-- B2B_REG_SHIP_FAX -->
	if ( strpos( $MsgText, '<!-- B2B_REG_SHIP_FAX -->' ) ) {
		if ( isset( $_POST[ 'b2b_reg_ship_fax' ] ) ) {
			$MsgText = str_replace( '<!-- B2B_REG_SHIP_FAX -->', $_POST[ 'b2b_reg_ship_fax' ], $MsgText );
		}
	}
	
	// <!-- B2B_REG_SHIP_STREET -->
	if ( strpos( $MsgText, '<!-- B2B_REG_SHIP_STREET -->' ) ) {
		if ( isset( $_POST[ 'b2b_reg_ship_street' ] ) ) {
			$MsgText = str_replace( '<!-- B2B_REG_SHIP_STREET -->', $_POST[ 'b2b_reg_ship_street' ], $MsgText );
		}
	}
	
	// <!-- B2B_REG_SHIP_TEL -->
	if ( strpos( $MsgText, '<!-- B2B_REG_SHIP_TEL -->' ) ) {
		if ( isset( $_POST[ 'b2b_reg_ship_tel' ] ) ) {
			$MsgText = str_replace( '<!-- B2B_REG_SHIP_TEL -->', $_POST[ 'b2b_reg_ship_tel' ], $MsgText );
		}
	}

	// <!-- B2B_REG_SHIP_ZIP -->
	if ( strpos( $MsgText, '<!-- B2B_REG_SHIP_ZIP -->' ) ) {
		if ( isset( $_POST[ 'b2b_reg_ship_zip' ] ) ) {
			$MsgText = str_replace( '<!-- B2B_REG_SHIP_ZIP -->', $_POST[ 'b2b_reg_ship_zip' ], $MsgText );
		}
	}
	
	// <!-- B2B_REG_SHOP -->
	if ( strpos( $MsgText, '<!-- B2B_REG_SHOP -->' ) != false ) {
		$res = q( "SELECT * FROM shop_shops WHERE id_shop=" . $_SESSION[ 'id_shop' ], $dbshop, __FILE__, __LINE__ );
		if ( mysqli_num_rows( $res ) > 0 ) {
			$shop_shops = mysqli_fetch_assoc( $res );
			$MsgText = str_replace( '<!-- B2B_REG_SHOP -->', $shop_shops[ 'domain' ], $MsgText );
		}
	}
	
	// <!-- B2B_REG_STREET -->
	if ( strpos( $MsgText, '<!-- B2B_REG_STREET -->' ) ) {
		if ( isset( $_POST[ 'b2b_reg_street' ] ) ) {
			$MsgText = str_replace( '<!-- B2B_REG_STREET -->', $_POST[ 'b2b_reg_street' ], $MsgText );
		}
	}
	
	// <!-- B2B_REG_TAX_NUMBER -->
	if ( strpos( $MsgText, '<!-- B2B_REG_TAX_NUMBER -->' ) ) {
		if ( isset( $_POST[ 'b2b_reg_tax_number' ] ) ) {
			$MsgText = str_replace( '<!-- B2B_REG_TAX_NUMBER -->', $_POST[ 'b2b_reg_tax_number' ], $MsgText );
		}
	}
	
	// <!-- B2B_REG_TEL -->
	if ( strpos( $MsgText, '<!-- B2B_REG_TEL -->' ) ) {
		if ( isset( $_POST[ 'b2b_reg_tel' ] ) ) {
			$MsgText = str_replace( '<!-- B2B_REG_TEL -->', $_POST[ 'b2b_reg_tel' ], $MsgText );
		}
	}
	
	// <!-- B2B_REG_USERMAIL -->
	if ( strpos( $MsgText, '<!-- B2B_REG_USERMAIL -->' ) != false ) {
		if( isset( $_POST[ 'b2b_reg_usermail' ] ) ) {
			$MsgText = str_replace( '<!-- B2B_REG_USERMAIL -->', $_POST[ 'b2b_reg_usermail' ], $MsgText );
		}
	}
	
	// <!-- B2B_REG_USERNAME -->
	if ( strpos( $MsgText, '<!-- B2B_REG_USERNAME -->' ) != false ) {
		if( isset( $_POST[ 'b2b_reg_username' ] ) ) {
			$MsgText = str_replace( '<!-- B2B_REG_USERNAME -->', $_POST[ 'b2b_reg_username' ], $MsgText );
		}
	}
	
	// <!-- B2B_REG_ZIP -->
	if ( strpos( $MsgText, '<!-- B2B_REG_ZIP -->' ) != false ) {
		if( isset( $_POST[ 'b2b_reg_zip' ] ) ) {
			$MsgText = str_replace( '<!-- B2B_REG_ZIP -->', $_POST[ 'b2b_reg_zip' ], $MsgText );
		}
	}
	
	// <!-- FIN_ADDITIONAL -->
	if ( strpos( $MsgText, '<!-- FIN_ADDITIONAL -->' ) != false )
	{
		if ( isset( $_POST['fin_additional'] ) )
		{
			$MsgText = str_replace( '<!-- FIN_ADDITIONAL -->', $_POST['fin_additional'], $MsgText );
		}
	}
	
	// <!-- FIN_CITY -->
	if ( strpos( $MsgText, '<!-- FIN_CITY -->' ) != false )
	{
		if ( isset( $_POST['fin_city'] ) )
		{
			$MsgText = str_replace( '<!-- FIN_CITY -->', $_POST['fin_city'], $MsgText );
		}
	}
	
	// <!-- FIN_COMPANY -->
	if ( strpos( $MsgText, '<!-- FIN_COMPANY -->' ) != false )
	{
		if ( isset( $_POST['fin_company'] ) )
		{
			$MsgText = str_replace( '<!-- FIN_COMPANY -->', $_POST['fin_company'], $MsgText );
		}
	}
	
	// <!-- FIN_COUNTRY -->
	if ( strpos( $MsgText, '<!-- FIN_COUNTRY -->' ) != false )
	{
		if ( isset( $_POST['fin_country'] ) )
		{
			$MsgText = str_replace( '<!-- FIN_COUNTRY -->', $_POST['fin_country'], $MsgText );
		}
	}
	
	// <!-- FIN_DOMAIN -->
	if ( strpos( $MsgText, '<!-- FIN_DOMAIN -->' ) != false )
	{
		if ( isset( $_POST['fin_domain'] ) )
		{
			$MsgText = str_replace( '<!-- FIN_DOMAIN -->', $_POST['fin_domain'], $MsgText );
		}
	}
	
	// <!-- FIN_FIRSTNAME -->
	if ( strpos( $MsgText, '<!-- FIN_FIRSTNAME -->' ) != false )
	{
		if ( isset( $_POST['fin_firstname'] ) )
		{
			$MsgText = str_replace( '<!-- FIN_FIRSTNAME -->', $_POST['fin_firstname'], $MsgText );
		}
	}

		
	// <!-- FIN_ITEMS -->
	if ( strpos( $MsgText, '<!-- FIN_ITEMS -->' ) != false )
	{
		if ( isset( $_POST['fin_items'] ) )
		{
			$item = array();
			$item = unserialize( $_POST['fin_items'] );
			
			if ( sizeof( $item ) == 1 && $item[0] != "" )
			{
				$MsgText = str_replace( "<!-- FIN_ITEMS -->", $item[0], $MsgText );
			}
			elseif ( sizeof( $item ) > 1 && !in_array( "", $item ) )
			{
				$buf = "";
				for ( $i = 0; $i < sizeof( $item ); $i++ )
				{
					$buf .= '<b>' . $item[$i] . '</b><br />';
				}
				$MsgText = str_replace( "<!-- FIN_ITEMS -->", $buf, $MsgText );
			}
		}
	}
	
	// <!-- FIN_LASTNAME -->
	if ( strpos( $MsgText, '<!-- FIN_LASTNAME -->' ) != false )
	{
		if ( isset( $_POST['fin_lastname'] ) )
		{
			$MsgText = str_replace( '<!-- FIN_LASTNAME -->', $_POST['fin_lastname'], $MsgText );
		}
	}

	
	// <!-- FIN_NAME -->
	if ( strpos( $MsgText, '<!-- FIN_NAME -->' ) != false )
	{
		if ( isset( $_POST['fin_name'] ) and isset( $_POST['fin_buyer_user_id'] ) )
		{
			
			if ( trim( $_POST['fin_name'] ) == "" )
			{
				if ( $_POST['fin_buyer_user_id'] != "" )
				{
					$MsgText = str_replace( "<!-- FIN_NAME -->" , $_POST['fin_buyer_user_id'], $MsgText );
				}
			}
			else
			{
				if ( $_POST['fin_buyer_user_id'] != "" )
				{
					$_POST['fin_name'] = $_POST['fin_name'] . " (" . $_POST['fin_buyer_user_id'] . ")";
				}
				$MsgText = str_replace( "<!-- FIN_NAME -->", $_POST['fin_name'], $MsgText );	
			}
	
		}		
	}
	
	// <!-- FIN_ORDER_ID -->
	if ( strpos( $Subject, '<!-- FIN_ORDER_ID -->' ) != false )
	{
		if ( isset( $_POST['fin_order_id'] ) )
		{
			$Subject = str_replace( '<!-- FIN_ORDER_ID -->', $_POST['fin_order_id'], $Subject );
			$MsgText = str_replace( '<!-- FIN_ORDER_ID -->', $_POST['fin_order_id'], $MsgText );
		}
	}
	
	// <!-- FIN_STREET -->
	if ( strpos( $MsgText, '<!-- FIN_STREET -->' ) != false )
	{
		if ( isset( $_POST['fin_street'] ) )
		{
			$MsgText = str_replace( '<!-- FIN_STREET -->', $_POST['fin_street'], $MsgText );
		}
	}
	
	// <!-- FIN_TOKEN -->
	if ( strpos( $MsgText, '<!-- FIN_TOKEN -->' ) != false )
	{
		if ( isset( $_POST['fin_token'] ) )
		{
			$MsgText = str_replace( '<!-- FIN_TOKEN -->', $_POST['fin_token'], $MsgText );
		}
	}
	
	//<!-- OCB_ADDRESS -->
	if ( strpos( $MsgText, '<!-- OCB_ADDRESS -->' ) != false )
	{
		if ( isset( $_POST['ocb_bill_adr_id'] ) and isset( $_POST['ocb_ship_adr_id'] ) )
		{
			$text = 		'';
			$res = 			q( "SELECT * FROM shop_bill_adr WHERE adr_id=" . $_POST['ocb_bill_adr_id'] . ";", $dbshop, __FILE__, __LINE__ );
			$row_bill_adr = mysqli_fetch_array( $res );
			$bill_gender = 	'Herr';
			if ( $row_bill_adr["gender"] == 1 )
			{
				$bill_gender = 'Frau';
			}
			if ( $_POST['ocb_ship_adr_id'] > 0 and $_POST['ocb_ship_adr_id'] != $_POST['ocb_bill_adr_id'] )
			{
				$res = 			q( "SELECT *FROM shop_bill_adr WHERE adr_id=" . $_POST['ocb_ship_adr_id'] . ";", $dbshop, __FILE__, __LINE__ );
				$row_ship_adr = mysqli_fetch_array( $res );
				$ship_gender = 	'Herr';
				if ( $row_ship_adr["gender"] == 1 )
				{
					$ship_gender = 'Frau';
				}
			}
			
			$adr_title = 'Rechnungsanschrift';
			if ( $_POST['ocb_bill_adr_id'] == $_POST['ocb_ship_adr_id'] )
			{
				$adr_title = 'Rechnungs- und Lieferanschrift';
			}
				
			$text .= '<p><b>' . $adr_title . ':</b><br>';
			if ( $row_bill_adr["company"] != "" )
			{
				$text .= $row_bill_adr["company"] . '<br>';
			}
			$text .= $bill_gender . ' ' . $row_bill_adr["title"] . '<br>';
			$text .= $row_bill_adr["firstname"] . ' ' . $row_bill_adr["lastname"] . '<br>';
			$text .= $row_bill_adr["street"] . ' ' . $row_bill_adr["number"] . '<br>';
			$text .= $row_bill_adr["zip"] . ' ' . $row_bill_adr["city"] . '<br>';
			if ( $row_bill_adr["additional"] != "" )
			{
				$text .= $row_bill_adr["additional"] . '<br>';
			}
			$text .= $row_bill_adr["country"] . '<br>';
			$text .= '</p>';
			if ( $_POST['ocb_ship_adr_id'] > 0 and $_POST['ocb_ship_adr_id'] != $_POST['ocb_bill_adr_id'] )
			{
				$text .= '<p><b>' . 'Lieferanschrift' . ':</b><br>';
				if ( $row_ship_adr["company"] != "" )
				{
					$text .= $row_ship_adr["company"] . '<br>';
				}
				$text .= $ship_gender.' '.$row_ship_adr["title"].'<br>';
				$text .= $row_ship_adr["firstname"] . ' ' . $row_ship_adr["lastname"] . '<br>';
				$text .= $row_ship_adr["street"] . ' ' . $row_ship_adr["number"] . '<br>';
				$text .= $row_ship_adr["zip"] . ' ' . $row_ship_adr["city"] . '<br>';
				if ( $row_ship_adr["additional"] != "" )
				{
					$text .= $row_ship_adr["additional"] . '<br>';
				}
				$text .= $row_ship_adr["country"] . '<br>';
				$text .= '</p>';
			}
			
			$MsgText = str_replace( '<!-- OCB_ADDRESS -->', $text, $MsgText );
		}
	}
	
	// <!-- OCB_COPY -->
	if ( strpos( $Subject , '<!-- OCB_COPY -->' ) != false )
	{
		if ( isset( $_POST['ocb_shipping_details'] ) and isset( $_POST['ocb_shop_id'] ) )
		{
			$copy = '';
			if ( ( strpos( $_POST['ocb_shipping_details'], 'Lieferservice' ) !== false or strpos( $_POST['ocb_shipping_details'], 'Selbstabholung' ) !== false ) and ( $_POST['ocb_shop_id'] > 8 and $_POST['ocb_shop_id'] < 17 ) )
			{
				$copy = 'Kopie - ';
			}
			$Subject = str_replace( '<!-- OCB_COPY -->', $copy, $Subject );
		}
	}
	
	// <!-- OCB_DATA -->
	if ( strpos( $MsgText, '<!-- OCB_DATA -->' ) != false )
	{
		if ( isset( $_POST['ocb_firstmod'] ) and isset( $_POST['ocb_username'] ) and isset( $_POST['ocb_order_id'] ) and isset( $_POST['ocb_payments_transaction_id'] ) and isset( $_POST['ocb_ordernr'] ) and isset( $_POST['ocb_comment'] ) and isset( $_POST['ocb_usermail'] ) and isset( $_POST['ocb_userphone'] ) and isset( $_POST['ocb_userfax'] ) and isset( $_POST['ocb_usermobile'] ) )
		{
			$text = 	'';
			$text .= 	'Bestellzeit: ' . date( "d.m.Y H:i", $_POST['ocb_firstmod'] );
			$text .= '	<br />Kundennummer: ' . $_POST['ocb_username'];
			$text .= '	<br />Online-Shop-Bestellnummer: ' . $_POST['ocb_order_id'];
			if ( $_POST['ocb_payments_transaction_id'] != "" )
			{
				$text .= ' <br /><b>PAYMENTS-TRANSACTION ID: </b>' . $_POST['ocb_payments_transaction_id'];
			}
			if ( $_POST['ocb_ordernr'] != "" )
			{
				$text .= '	<br />Eigene Bestellnummer: ' . $_POST['ocb_ordernr'];
			}
			if ( $_POST['ocb_comment'] != "" )
			{
				$text .= '	<br /><br>Anmerkung:<br />' . nl2br( $_POST['ocb_comment'] );
			}
			$text .= '</p>';
			$text .= '<p><b>Kontaktdaten:</b><br>';
			$text .= 'E-Mail: ' . $_POST['ocb_usermail'];
			if ( $_POST['ocb_userphone'] != "" )
			{
				$text .= '<br>Telefon:' . $_POST['ocb_userphone'];
			}
			if ( $_POST['ocb_userfax'] != "" )
			{
				$text .= '<br>Telefax:' . $_POST['ocb_userfax'];
			}
			if ( $_POST['ocb_usermobile'] != "" )
			{
				$text .= '<br>Handy:' . $_POST['ocb_usermobile'];
			}
			$text .= '</p>';
			$MsgText = str_replace( '<!-- OCB_DATA -->', $text, $MsgText );
		}
	}
	
	// <!-- OCB_INFO -->
	if ( strpos( $MsgText, '<!-- OCB_INFO -->' ) != false )
	{
		if ( isset( $_POST['ocb_shipping_details'] ) and isset( $_POST['ocb_shop_id'] ) and isset( $_POST['ocb_shop_city'] ) )
		{
			$info = '';
			if ( ( strpos( $_POST['ocb_shipping_details'], 'Lieferservice' ) !== false or strpos( $_POST['ocb_shipping_details'], 'Selbstabholung' ) !== false ) and ( $_POST['ocb_shop_id'] > 8 and $_POST['ocb_shop_id'] < 17 ) )
			{
				$info = '<b style="color:red; font-size:14px;">Diese Bestellung wird durch das RC ' . $_POST['ocb_shop_city'] . ' bearbeitet!</b><br /><br />';
			}
			elseif ( $_POST['ocb_shop_id'] == 17 )
			{
				$info = '<b style="color:red; font-size:14px;">Diese Bestellung für das RC ' . $_POST['ocb_shop_city'] . ' erfassen!</b><br /><br />';
			}
			$MsgText = str_replace( '<!-- OCB_INFO -->', $info, $MsgText );
		}
	}
	
	// <!-- OCB_SHIPPINGTYPE -->
	if ( strpos( $MsgText, '<!-- OCB_SHIPPINGTYPE -->' ) != false )
	{
		if ( isset( $_POST['ocb_shipping_details'] ) and isset( $_POST['ocb_user_id'] ) and isset( $_POST['ocb_ordernr'] ) and isset( $_POST['ocb_comment'] ) and isset( $_POST['ocb_order_id'] ) )
		{
			$discount = 0;
			
			// get order-total_net
			$post_data = 				array();
			$post_data["API"] = 		"shop";
			$post_data["APIRequest"] = 	"OrderDetailGet_neu";
			$post_data["OrderID"] = 	$_POST["ocb_order_id"];
					
			$postdata = http_build_query( $post_data );
			
			$xml = soa2( $postdata );
			if ( $xml->Ack[0] != "Success" )
			{
				show_error( 9812, 7, __FILE__, __LINE__, "Keine Bestelldaten gefunden. OrderID: " . $_POST['ocb_order_id'] );
				exit;
			}
			
			$text = '';
			$pos = strpos( $_POST['ocb_shipping_details'] , ", " ) + 2;
			$text = '<p>';
			$text .= '<b>' . substr( $_POST['ocb_shipping_details'], $pos ) . '</b><br>';
			//$text .= '<b>' . number_format( (float)$xml->Order[0]->orderItemsTotalNet[0], 2 ) . ' € Netto-Warenwert</b><br>';
			$text .= '<b>' . (string)$xml->Order[0]->orderItemsTotalNet[0] . ' € Netto-Warenwert</b><br>';
			if ( gewerblich( $_POST['ocb_user_id'] ) )
			{
				$text .= '<b>Gewerbekunde</b>';
				if( $discount > 0 )
				{
					$text .= '<br>';
					$text .= '<b>ACHTUNG 4% (€ ' . number_format( $discount, 2 ) . ') ONLINE-RABATT BEACHTEN!!!</b>';					
				}
			}
			else
			{
				$text .= '<b>Privatkunde</b>';
			}
			$text .= '</p>';
			
			if ( $_POST['ocb_ordernr'] != "" )
			{
				$text .= '<p><b>Bitte eigene OrderNr. des Kunden übernehmen!!!</b></p>';
			}
			if ( $_POST['ocb_comment'] != "" )
			{
				$text .= '<p><b>Bitte die Anmerkung des Kunden zur Bestellung beachten!!!</b></p>';
			}
			
			$MsgText = str_replace( '<!-- OCB_SHIPPINGTYPE -->', $text, $MsgText );
		}
	}
	
	// <!-- OCB_SHOP -->
	if ( strpos( $Subject , '<!-- OCB_SHOP -->' ) != false )
	{
		if ( isset( $_POST['ocb_shipping_details'] ) and isset( $_POST['ocb_shop_id'] ) )
		{
			$city = '';
			if ( ( ( strpos( $_POST['ocb_shipping_details'], 'Lieferservice' ) !== false or strpos( $_POST['ocb_shipping_details'], 'Selbstabholung' ) !== false ) and ( $_POST['ocb_shop_id'] > 8 and $_POST['ocb_shop_id'] < 17 ) ) or $_POST['ocb_shop_id'] == 17 )
			{
				$city = $_POST['ocb_shop_city'];
			}
			$Subject = str_replace( '<!-- OCB_SHOP -->', $city, $Subject );
		}
	}
	
	// <!-- OCB_TABLE -->
	if ( strpos( $MsgText, '<!-- OCB_TABLE -->' ) != false )
	{
		if ( isset( $_POST['ocb_order_id'] ) and isset( $_POST['ocb_shop_id'] ) and isset( $_POST['ocb_shipping_details'] ) and isset( $_POST['ocb_shipping_costs'] ) and isset( $_POST['ocb_shipping_costs'] ) and isset( $_POST['ocb_user_id'] ) and isset( $_POST['ocb_eu'] ) and isset( $_POST['ocb_bill_country_id'] ) )
		{
			$text = '';
			$text .= '<table border="1" cellpadding="4">';
			$text .= '  <tr><th colspan="6">Bestellung</th></tr>';
			$text .= '  <tr>';
			$text .= '    <td>Artikel-Nr.</td>';
			$text .= '    <td>Menge</td>';
			$text .= '    <td>Bezeichnung</td>';
			$text .= '    <td>EK</td>';
			$text .= '    <td>Gesamt</td>';
			$text .= '    <td></td>';
			$text .= '  </tr>';
			
			$total = 			0;
			$totalpos = 		0;
			$collateral_count = 0;
			$collateral_sum = 	0;
			
			$results2 = q("SELECT a.price, a.netto, a.amount, a.collateral, c.title, b.MPN, b.id_item FROM shop_orders_items AS a, shop_items AS b, shop_items_de AS c WHERE a.order_id=" . $_POST['ocb_order_id'] . " AND a.item_id=b.id_item AND b.id_item=c.id_item;", $dbshop, __FILE__, __LINE__ );
			
			while ( $row2 = mysqli_fetch_array( $results2 ) )
			{
				//Barcodes erzeugen
				$post_data = 				array();
				$post_data["API"] = 		"shop";
				$post_data["APIRequest"] = 	"BarcodeCreate";
				$post_data["item_id"] = 	$row2["id_item"];
				$response = soa2( $post_data, __FILE__, __LINE__ );
				
				$text .= '  <tr>';
				$text .= '  <td>' . $row2["MPN"] . '</td>';
				$text .= '  <td>' . number_format( $row2["amount"], 0 ) . '</td>';
				$text .= '  <td>' . $row2["title"];
				if( $row2["collateral"] > 0 ) 
				{
					$text .= '<br />zzgl. ' . $row2["collateral"] . ' € Altteilpfand';
					$collateral_count = $collateral_count + $row2["amount"];
					$collateral_sum = 	$collateral_sum + ( number_format( $row2["collateral"] * $row2["amount"], 2 ) );
				}
				$text .= '  </td>';
				$text .= '  <td>' . number_format( $row2["price"], 2 ) . ' €</td>';
				$price = 		$row2["amount"] * $row2["price"];
				$total += 		$price;
				$totalpos += 	( $row2["amount"] * $row2["netto"] );
				$text .= '  <td>' . number_format( $price, 2 ) . ' €</td>';
				//if ($partner_id>0) 
		//		if($_SESSION["rcid"]!="" and $_SESSION["rcid"]!=9999 and $_SESSION["rcid"]>0)
				if( $_POST['ocb_shop_id'] > 8 and $_POST['ocb_shop_id'] < 17 )
				{
					$text .= '  <td><span style="float:right;">' . itemstatus_rc( $row2["id_item"], 1, $row2["amount"] ) . '</span></td>';					
				}
				else
				{
					$text .= '  <td><span style="float:right;">' . itemstatus( $row2["id_item"], 1, $row2["amount"] ) . '</span></td>';
				}
				$text .= '  </tr>';
			}
			
			//Versandkosten
			$text .= '  <tr>';
			$text .= '    <td colspan="4">' . $_POST['ocb_shipping_details'] . '</td><td colspan="2">' . number_format( $_POST['ocb_shipping_costs'], 2 ) . ' €</td>';
			$text .= '  </tr>';
			$total += $_POST['ocb_shipping_costs'];
					
			//Gesamt Netto
			if ( gewerblich( $_POST['ocb_user_id'] ) or ( !gewerblich( $_POST['ocb_user_id'] ) and $_POST['ocb_eu'] == 0 ) )
			{
				$discount = 0;
				$text .= '  <tr>';
				if ( isset( $_POST['ocb_bill_country_id'] ) and $_POST['ocb_bill_country_id'] > 1 )
				{
					$text .= '    <td colspan="4"><b>Gesamtpreis Netto</b></td><td colspan="2"><b>' . number_format( $total, 2 ) . ' €</b></td>';
				}
				else
				{
					$text .= '    <td colspan="4">Gesamtpreis Netto</td><td colspan="2">' . number_format( $total, 2 ) . ' €</td>';
				}
				$text .= '  </tr>';
				$ust = 		( UST/100 ) * $total;
				$total = 	( ( 100 + UST ) / 100 ) * $total;
			}
			else 
			{
				$discount = 0;
				$ust = 		$total / ( 100 + UST ) * UST;
			}
		
			if( $_POST['ocb_shop_id'] != 17 and ( ( !gewerblich( $_POST['ocb_user_id'] ) and $_POST['ocb_eu'] == 1 ) or !isset( $_POST['ocb_bill_country_id'] ) or ( isset( $_POST['ocb_bill_country_id'] ) and $_POST['ocb_bill_country_id'] == 1 ) ) )
			{
				$text .= '  <tr>';
				$text .= '    <td colspan="4">';
				if ( gewerblich( $_POST['ocb_user_id'] ) )
				{
					$text .= 'zzgl. '.UST.'% gesetzliche';
				}
				else
				{
					$text .= 'darin enthalten, '.UST.'%';  
				}
				$text .= ' Umsatzsteuer</td><td colspan="2">' . number_format( $ust, 2 ) . ' €</td>';
				$text .= '  </tr>';
			
				//Gesamt Brutto
				$text .= '  <tr>';
				$text .= '    <td colspan="4"><b>Gesamtpreis Brutto</b></td><td colspan="2"><b>' . number_format( $total, 2 ) . ' €</b></td>';
				$text .= '  </tr>';
			}
			
			$text .= '</table>';
			
			$MsgText = str_replace( '<!-- OCB_TABLE -->', $text, $MsgText );
		}
	}
	
	// <!-- OCC_ADDRESS -->
	if ( strpos( $MsgText, '<!-- OCC_ADDRESS -->' ) != false ) 
	{
		if ( isset( $_POST['occ_bill_adr_id'] ) and isset( $_POST['occ_ship_adr_id'] ) )
		{
			
			//addresses
			$res_bill_adr = q( "SELECT * FROM shop_bill_adr WHERE adr_id=" . $_POST['occ_bill_adr_id'], $dbshop, __FILE__, __LINE__ );
			$row_bill_adr = mysqli_fetch_array( $res_bill_adr );
			$bill_gender = t( "Herr" );
			if ( $row_bill_adr["gender"] == 1 ) $bill_gender = t( "Frau" );
			if ( $_POST['occ_ship_adr_id'] > 0 and $_POST['occ_ship_adr_id'] != $_POST['occ_bill_adr_id'] )
			{
				$res_ship_adr = q( "SELECT *FROM shop_bill_adr WHERE adr_id=" . $_POST['occ_ship_adr_id'], $dbshop, __FILE__, __LINE__ );
				$row_ship_adr = mysqli_fetch_array( $res_ship_adr );
				$ship_gender = t( "Herr" );
				if ( $row_ship_adr["gender"] == 1 ) $ship_gender = t( "Frau" );
			}
			
			$adr_title = t( "Rechnungsanschrift" );
			if ( $_POST['occ_bill_adr_id'] == $_POST['occ_ship_adr_id'] ) $adr_title = t( "Rechnungs- und Lieferanschrift" );
			
			$text = '<p><b>' . $adr_title . ':</b><br>';
			if ( $row_bill_adr["company"] != "" ) $text .= $row_bill_adr["company"] . '<br>';
			$text .= $bill_gender . ' ' . $row_bill_adr["title"] . '<br>';
			$text .= $row_bill_adr["firstname"] . ' ' . $row_bill_adr["lastname"] . '<br>';
			$text .= $row_bill_adr["street"] . ' ' . $row_bill_adr["number"] . '<br>';
			$text .= $row_bill_adr["zip"] . ' ' . $row_bill_adr["city"] . '<br>';
			if ( $row_bill_adr["additional"] != "" ) $text .= $row_bill_adr["additional"] . '<br>';
			$text .= $row_bill_adr["country"] . '<br>';
			$text .= '</p>';
			if ( $_POST['occ_ship_adr_id'] > 0 and $_POST['occ_ship_adr_id'] != $_POST['occ_bill_adr_id'] )
			{
				$text .= '<p><b>' . t( "Lieferanschrift" ) . ':</b><br>';
				if ( $row_ship_adr["company"] != "" ) $text .= $row_ship_adr["company"] . '<br>';
				$text .= $ship_gender . ' ' . $row_ship_adr["title"] . '<br>';
				$text .= $row_ship_adr["firstname"] . ' ' . $row_ship_adr["lastname"] . '<br>';
				$text .= $row_ship_adr["street"] . ' ' . $row_ship_adr["number"] . '<br>';
				$text .= $row_ship_adr["zip"] . ' ' . $row_ship_adr["city"] . '<br>';
				if ( $row_ship_adr["additional"] != "" ) $text .= $row_ship_adr["additional"] . '<br>';
				$text .= $row_ship_adr["country"] . '<br>';
				$text .= '</p>';
			}				
			$MsgText = str_replace( '<!-- OCC_ADDRESS -->', $text , $MsgText );
		}
	}
	
	// <!-- OCC_ADDRESS_17 -->
	if ( strpos( $MsgText, '<!-- OCC_ADDRESS_17 -->' ) != false )
	{
		if ( isset( $_POST['occ_bill_adr_id'] ) and isset( $_POST['occ_ship_adr_id'] ) )
		{
			//addresses
			$res_bill_adr = q( "SELECT * FROM shop_bill_adr WHERE adr_id=" . $_POST['occ_bill_adr_id'], $dbshop, __FILE__, __LINE__ );
			$row_bill_adr = mysqli_fetch_assoc( $res_bill_adr );
			$bill_gender = 	t( "Herr" );
			if ( $row_bill_adr["gender"] == 1 ) $bill_gender = t( "Frau" );
			if ( $_POST['occ_ship_adr_id'] > 0 and $_POST['occ_ship_adr_id'] != $_POST['occ_bill_adr_id'] )
			{
				$res_ship_adr = q( "SELECT *FROM shop_bill_adr WHERE adr_id=" . $_POST['occ_ship_adr_id'], $dbshop, __FILE__, __LINE__ );
				$row_ship_adr = mysqli_fetch_array( $res_ship_adr );
				$ship_gender = 	t( "Herr" );
				if ( $row_ship_adr["gender"] == 1 ) $ship_gender = t( "Frau" );
			}
			
			$adr_title = t( "Rechnungsanschrift" );
			if ( $_POST['occ_bill_adr_id'] == $_POST['occ_ship_adr_id'] ) $adr_title = t( "Rechnungs- und Lieferanschrift" );
			
			$text = '<p><b>' . $adr_title . ':</b> ';
			if ( $row_bill_adr["company"] != "") $text .= $row_bill_adr["company"] . ', ';
			$text .= $bill_gender . ' ' . $row_bill_adr["title"];
			$text .= $row_bill_adr["firstname"] . ' ' . $row_bill_adr["lastname"];
			$text .= ', ' . $row_bill_adr["street"] . ' ' . $row_bill_adr["number"];
			$text .= ', ' . $row_bill_adr["zip"] . ' ' . $row_bill_adr["city"];
			if ( $row_bill_adr["additional"] != "" ) $text .= ', ' . $row_bill_adr["additional"];
			$text .= ', ' . $row_bill_adr["country"] . '<br>';
			$text .= '</p>';
			if ( $_POST['occ_ship_adr_id'] > 0 and $_POST['occ_ship_adr_id'] != $_POST['occ_bill_adr_id'] )
			{
				$text .= '<p><b>' . t("Lieferanschrift") . ':</b> ';
				if ( $row_ship_adr["company"] != "") $text .= $row_ship_adr["company"] . ', ';
				$text .= $ship_gender . ' ' . $row_ship_adr["title"];
				$text .= $row_ship_adr["firstname"] . ' ' . $row_ship_adr["lastname"];
				$text .= ', ' . $row_ship_adr["street"] . ' ' . $row_ship_adr["number"];
				$text .= ', ' . $row_ship_adr["zip"] . ' ' . $row_ship_adr["city"];
				if ( $row_ship_adr["additional"] != "" ) $text .= ', ' . $row_ship_adr["additional"];
				$text .= ', ' . $row_ship_adr["country"] . '<br>';
				$text .= '</p>';
			}
			$MsgText = str_replace( '<!-- OCC_ADDRESS_17 -->', $text , $MsgText );
		}
	}
	
	// <!-- OCC_CANCEL -->
	if ( strpos( $MsgText, '<!-- OCC_CANCEL -->' ) != false )
	{
		if ( isset( $_POST['occ_withdrawal_article_id'] ) ) {
			$res = 			q( "SELECT * FROM cms_articles WHERE id_article=" . $_POST['occ_withdrawal_article_id'], $dbweb, __FILE__, __LINE__ );
			$cms_articles = mysqli_fetch_assoc( $res );
			$MsgText = str_replace( '<!-- OCC_CANCEL -->', $cms_articles['article'] , $MsgText );
		}
	}
	
	// <!-- OCC_DOMAIN -->
	if ( strpos( $MsgText, '<!-- OCC_DOMAIN -->' ) != false )
	{
		if ( isset( $_POST['occ_domain'] ) ) {
			$MsgText = str_replace( '<!-- OCC_DOMAIN -->', $_POST['occ_domain'] , $MsgText );
		}
	}
	
	// <!-- OCC_ORDER_ID -->
	if ( strpos( $MsgText, '<!-- OCC_ORDER_ID -->' ) != false )
	{
		if ( isset( $_POST['occ_order_id'] ) )
		{
			$MsgText = str_replace( '<!-- OCC_ORDER_ID -->', $_POST['occ_order_id'] , $MsgText );
		}
	}
	if ( strpos( $Subject, '<!-- OCC_ORDER_ID -->' ) != false )
	{
		if ( isset( $_POST['occ_order_id'] ) ) {
			$Subject = str_replace( '<!-- OCC_ORDER_ID -->', $_POST['occ_order_id'] , $Subject );
		}
	}
	
	// <!-- OCC_PAYCHECK -->
	if ( strpos( $MsgText, '<!-- OCC_PAYCHECK -->' ) != false ) 
	{
		if ( isset( $_POST['occ_Payments_TransactionID'] ) and isset( $_POST['occ_Payments_TransactionState'] ) ) {
			//CHECK AUF PAYPAL-Zahlung && ZAHLSTATUS
			if ( $_POST['occ_Payments_TransactionID'] != "" ) {
				
				if ( $_POST['occ_Payments_TransactionState'] == "Pending" ) {
					
					$MsgText = str_replace( '<!-- OCC_PAYCHECK -->', ' '.t("Der Versand erfolgt nachdem PayPal uns den Erhalt Ihrer Zahlung bestätigt hat."), $MsgText );
				}
			}
		}
	}
	
	// <!-- OCC_TABLE -->
	if ( strpos( $MsgText, '<!-- OCC_TABLE -->' ) != false )
	{
		if ( isset( $_POST['occ_order_id'] ) and isset( $_POST['occ_partner_id'] ) and isset( $_POST['occ_bill_adr_id'] ) and isset( $_POST['occ_shipping_details'] ) and isset( $_POST['occ_shipping_details_memo'] ) and isset( $_POST['occ_shipping_costs'] ) and isset( $_POST['occ_gewerblich'] ) and isset( $_POST['occ_shop_id'] ) ) 
		{
			//country data
			$res_bill_adr = q( "SELECT * FROM shop_bill_adr WHERE adr_id=" . $_POST['occ_bill_adr_id'], $dbshop, __FILE__, __LINE__ );
			$row_bill_adr = mysqli_fetch_array( $res_bill_adr );
	
			$eu = 1;
			$res = q( "SELECT * FROM shop_countries WHERE id_country=" . $row_bill_adr["country_id"], $dbshop, __FILE__, __LINE__ );
			if ( mysqli_num_rows( $res ) > 0 )
			{
				$shop_countries = mysqli_fetch_assoc( $res );
				$eu = $shop_countries["EU"];
			}
			
			//bill Tabelle
			$text = '<table border="1" cellpadding="4">';
			$text .= '  <tr><th colspan="6">' . t( "Bestellung" ) . '</th></tr>';
			$text .= '  <tr>';
			$text .= '    <td>' . t( "Artikel-Nr." ).'</td>';
			$text .= '    <td>' . t( "Menge" ) . '</td>';
			$text .= '    <td>' . t( "Bezeichnung" ) . '</td>';
			$text .= '    <td>' . t( "EK" ) . '</td>';
			$text .= '    <td>' . t( "Gesamt" ) . '</td>';
			$text .= '    <td></td>';
			$text .= '  </tr>';
			
			$results = q( "SELECT a.price, a.netto, a.amount, a.collateral, c.title, b.MPN, b.id_item FROM shop_orders_items AS a, shop_items AS b, shop_items_de AS c WHERE a.order_id=" . $_POST["occ_order_id"] . " AND a.item_id=b.id_item AND b.id_item=c.id_item;", $dbshop, __FILE__, __LINE__ );
			$total = 			0;
			$totalpos = 		0;
			$collateral_count = 0;
			$collateral_sum = 	0;
			while ( $row = mysqli_fetch_array( $results ) )
			{
				$text .= '  <tr>';
				$text .= '  <td>' . $row["MPN"] . '</td>';
				$text .= '  <td>' . number_format( $row["amount"], 0 ) . '</td>';
				$text .= '  <td>' . $row["title"];
				if ( $row["collateral"] > 0 ) 
				{
					$text .= '<br />zzgl. ' . $row["collateral"] . ' € ' . t( "Altteilpfand" ) . '';
					$collateral_count = $collateral_count + $row["amount"];
					$collateral_sum = $collateral_sum + ( number_format( $row["collateral"] * $row["amount"], 2 ) );
				}
				$text .= '  </td>';
				$text .= '  <td>' . number_format( $row["price"], 2 ) . ' €</td>';
				$price = $row["amount"] * $row["price"];
				$total += $price;
				$totalpos += ( $row["amount"] * $row["netto"] );
				$text .= '  <td>' . number_format( $price, 2 ) . ' €</td>';
				if ( $_POST['occ_partner_id'] > 0 ) 
				{
					$text .= '  <td><span style="float:right;">' . itemstatus_rc( $row["id_item"], 1, $row["amount"] ) . '</span></td>';					
				}
				else
				{
					$text .= '  <td><span style="float:right;">' . itemstatus( $row["id_item"], 1, $row["amount"] ) . '</span></td>';
				}
				$text .= '  </tr>';
			}

			//Versandkosten
			$text .= '  <tr>';
			$text .= '    <td colspan="4">' . $_POST["occ_shipping_details"] . '<br />' . $_POST["occ_shipping_details_memo"] . '</td>';
			$text .= '    <td colspan="2">' . number_format( $_POST["occ_shipping_costs"], 2 ) . ' €</td>';
			$text .= '  </tr>';
			$total += $_POST["occ_shipping_costs"];
					
			//Gesamt Netto
			if ( $_POST['occ_gewerblich'] or ( !$_POST['occ_gewerblich'] and $eu == 0 ) )
			{		
				$text .= '  <tr>';
				if ( isset( $row_bill_adr["country_id"] ) and $row_bill_adr["country_id"] > 1 )
					$text .= '    <td colspan="4"><b>' . t( "Gesamtpreis Netto" ) . '</b></td><td colspan="2"><b>' . number_format( $total, 2 ) . ' €</b></td>';
				else
					$text .= '    <td colspan="4">' . t( "Gesamtpreis Netto" ) . '</td><td colspan="2">' . number_format( $total, 2 ) . ' €</td>';
				$text .= '  </tr>';
				$ust = ( UST / 100 ) * $total;
				$total = ( ( 100 + UST ) / 100 ) * $total;
			}
			else 
			{
				$ust = $total / ( 100 + UST ) * UST;
			}
		
			if ( $_POST['occ_shop_id'] != 17 and ( ( !$_POST['occ_gewerblich'] and $eu == 1 ) or !isset( $row_bill_adr["country_id"] ) or (isset( $row_bill_adr["country_id"] ) and $row_bill_adr["country_id"] == 1 ) ) )
			{
				$text .= '  <tr>';
				$text .= '    <td colspan="4">';
				if ( $_POST['occ_gewerblich'] )
				{
					$text .= '' . t( "zzgl." ) . ' ' . UST . '% ' . t( "gesetzliche" ) . '';
				}
				else
				{
					$text .= '' . t( "darin enthalten" ) . ', ' . UST . '%';  
				}
				$text .= ' ' . t( "Umsatzsteuer" ) . '</td><td colspan="2">' . number_format( $ust, 2 ) . ' €</td>';
				$text .= '  </tr>';
			
				//Gesamt Brutto
				$text .= '  <tr>';
				$text .= '    <td colspan="4"><b>' .t( "Gesamtpreis Brutto" ) . '</b></td><td colspan="2"><b>' . number_format( $total, 2 ) . ' €</b></td>';
				$text .= '  </tr>';
			}
			
			$text .= '</table>';
						
			$MsgText = str_replace( '<!-- OCC_TABLE -->', $text , $MsgText );
		}
	}
	
	// <!-- OCC_TRUSTEDSHOPS_ID -->
	if ( strpos( $MsgText, '<!-- OCC_TRUSTEDSHOPS_ID -->' ) != false )
	{
		if ( isset( $_POST['occ_trustedshops_id'] ) ) {
			$MsgText = str_replace( '<!-- OCC_TRUSTEDSHOPS_ID -->', $_POST['occ_trustedshops_id'] , $MsgText );
		}
	}
	
	// <!-- OCC_USERMAIL -->
	if ( strpos( $MsgText, '<!-- OCC_USERMAIL -->' ) != false )
	{
		if ( isset( $_POST['occ_usermail'] ) ) {
			$MsgText = str_replace( '<!-- OCC_USERMAIL -->', $_POST['occ_usermail'] , $MsgText );
		}
	}
	
	// <!-- OCS_ADDRESS -->
	if ( strpos( $MsgText, '<!-- OCS_ADDRESS -->' ) != false )
	{
		if ( isset( $_POST['ocs_bill_adr_id'] ) and isset( $_POST['ocs_ship_adr_id'] ) )
		{
			$text = 		'';
			$res = 			q( "SELECT * FROM shop_bill_adr WHERE adr_id=" . $_POST['ocs_bill_adr_id'] . ";", $dbshop, __FILE__, __LINE__ );
			$row_bill_adr = mysqli_fetch_array( $res );
			$bill_gender = 	'Herr';
			if ( $row_bill_adr["gender"] == 1 )
			{
				$bill_gender = 'Frau';
			}
			if ( $_POST['ocs_ship_adr_id'] > 0 and $_POST['ocs_ship_adr_id'] != $_POST['ocs_bill_adr_id'] )
			{
				$res = 			q( "SELECT *FROM shop_bill_adr WHERE adr_id=" . $_POST['ocs_ship_adr_id'] . ";", $dbshop, __FILE__, __LINE__ );
				$row_ship_adr = mysqli_fetch_array( $res );
				$ship_gender = 	'Herr';
				if ( $row_ship_adr["gender"] == 1 )
				{
					$ship_gender = 'Frau';
				}
			}
			
			$adr_title = 'Rechnungsanschrift';
			if ( $_POST['ocs_bill_adr_id'] == $_POST['ocs_ship_adr_id'] )
			{
				$adr_title = 'Rechnungs- und Lieferanschrift';
			}
				
			$text .= '<p><b>' . $adr_title . ':</b><br>';
			if ( $row_bill_adr["company"] != "" )
			{
				$text .= $row_bill_adr["company"] . '<br>';
			}
			$text .= $bill_gender . ' ' . $row_bill_adr["title"] . '<br>';
			$text .= $row_bill_adr["firstname"] . ' ' . $row_bill_adr["lastname"] . '<br>';
			$text .= $row_bill_adr["street"] . ' ' . $row_bill_adr["number"] . '<br>';
			$text .= $row_bill_adr["zip"] . ' ' . $row_bill_adr["city"] . '<br>';
			if ( $row_bill_adr["additional"] != "" )
			{
				$text .= $row_bill_adr["additional"] . '<br>';
			}
			$text .= $row_bill_adr["country"] . '<br>';
			$text .= '</p>';
			if ( $_POST['ocs_ship_adr_id'] > 0 and $_POST['ocs_ship_adr_id'] != $_POST['ocs_bill_adr_id'] )
			{
				$text .= '<p><b>' . 'Lieferanschrift' . ':</b><br>';
				if ( $row_ship_adr["company"] != "" )
				{
					$text .= $row_ship_adr["company"] . '<br>';
				}
				$text .= $ship_gender.' '.$row_ship_adr["title"].'<br>';
				$text .= $row_ship_adr["firstname"] . ' ' . $row_ship_adr["lastname"] . '<br>';
				$text .= $row_ship_adr["street"] . ' ' . $row_ship_adr["number"] . '<br>';
				$text .= $row_ship_adr["zip"] . ' ' . $row_ship_adr["city"] . '<br>';
				if ( $row_ship_adr["additional"] != "" )
				{
					$text .= $row_ship_adr["additional"] . '<br>';
				}
				$text .= $row_ship_adr["country"] . '<br>';
				$text .= '</p>';
			}
			
			$MsgText = str_replace( '<!-- OCS_ADDRESS -->', $text, $MsgText );
		}
	}
	
	// <!-- OCS_ADDRESS_17 -->
	if ( strpos( $MsgText, '<!-- OCS_ADDRESS_17 -->' ) != false )
	{
		if ( isset( $_POST['ocs_bill_adr_id'] ) and isset( $_POST['ocs_ship_adr_id'] ) and isset( $_POST['ocs_usermail'] ) and isset( $_POST['ocs_userphone'] ) and isset( $_POST['ocs_usermobile'] ) )
		{
			$text = 		'';
			
			$res = 			q( "SELECT * FROM shop_bill_adr WHERE adr_id=" . $_POST['ocs_bill_adr_id'] . ";", $dbshop, __FILE__, __LINE__ );
			$row_bill_adr = mysqli_fetch_array( $res );
			if ( $_POST['ocs_ship_adr_id'] > 0 and $_POST['ocs_ship_adr_id'] != $_POST['ocs_bill_adr_id'] )
			{
				$res = 			q( "SELECT *FROM shop_bill_adr WHERE adr_id=" . $_POST['ocs_ship_adr_id'] . ";", $dbshop, __FILE__, __LINE__ );
				$row_ship_adr = mysqli_fetch_array( $res );
			}
			
			$adr_title = t( "Lieferanschrift", __FILE__, __LINE__, $language_code );
//			$text .= '<p><b>' . $adr_title . ':</b><br />';
			if ( $_POST['ocs_ship_adr_id'] == 0 )
			{
				$text .= '<table>';
				$text .= '<tr><td><b>' . $adr_title . ':</b></td><td></td></tr>';
				if ( $row_bill_adr["company"] != "" )
				{
					$text .= '<tr><td>' . t( "Firma", __FILE__, __LINE__, $language_code ) . ':</td><td>' . $row_bill_adr["company"] . '</td></tr>';
				}
				if ( $row_bill_adr["title"] != '' )
				{
					$text .= '<tr><td>' . t( "Name", __FILE__, __LINE__, $language_code ) . ':</td><td>' . $row_bill_adr["title"] . ' ' . $row_bill_adr["firstname"] . ' ' . $row_bill_adr["lastname"] . '</td></tr>';
				}
				else
				{
					$text .= '<tr>' . t( "Name", __FILE__, __LINE__, $language_code ) . ':<td></td><td>' . $row_bill_adr["firstname"] . ' ' . $row_bill_adr["lastname"] . '</td></tr>';
				}
				$text .= '<tr><td>' . t( "Adresse", __FILE__, __LINE__, $language_code ) . ':</td><td>' . $row_bill_adr["street"] . ' ' . $row_bill_adr["number"] . '</td></tr>';
				if ( $row_order["userphone"] != "" )
				{
					$text .= '<tr><td>' . t( "Telefon", __FILE__, __LINE__, $language_code ) . ':</td><td>' .$row_order["userphone"] . '</td></tr>';
				}
				$text .= '<tr><td>' . t( "PLZ-Ort-Land", __FILE__, __LINE__, $language_code ) . ':</td><td>' . $row_bill_adr["zip"] . '-' . $row_bill_adr["city"] . '-' . $row_bill_adr["country"] . '</td></tr>';
				$text .= '<tr><td>' . t( "Email", __FILE__, __LINE__, $language_code ) . ':</td><td>' . $_POST['ocs_usermail'] . '</td></tr>';
				if ( $_POST['ocs_userphone'] != '' )
				{
					$text .= '<tr><td>' . t( "Telefon", __FILE__, __LINE__, $language_code ) . ':</td><td>' . $_POST['ocs_userphone'] . '</td></tr>';
				}
				if ( $_POST['ocs_usermobile'] != '' )
				{
					$text .= '<tr><td>' . t( "Handy", __FILE__, __LINE__, $language_code ) . ':</td><td>' . $_POST['ocs_usermobile'] . '</td></tr>';
				}
				$text .= '</table>';
			}
			else
			{
				$text .= '<table>';
				$text .= '<tr><td><b>' . $adr_title . ':</b></td><td></td></tr>';
				if ( $row_ship_adr["company"] != "" )
				{
					$text .= '<tr><td>' . t( "Firma", __FILE__, __LINE__, $language_code ) . ':</td><td>' . $row_ship_adr["company"] . '</td></tr>';
				}
				if ( $row_ship_adr["title"] != '' )
				{
					$text .= '<tr><td>' . t( "Name", __FILE__, __LINE__, $language_code ) . ':</td><td>' . $row_ship_adr["title"] . ' ' . $row_ship_adr["firstname"] . ' ' . $row_ship_adr["lastname"] . '</td></tr>';
				}
				else
				{
					$text .= '<tr>' . t( "Name", __FILE__, __LINE__, $language_code ) . ':<td></td><td>' . $row_ship_adr["firstname"] . ' ' . $row_ship_adr["lastname"] . '</td></tr>';
				}
				$text .= '<tr><td>' . t( "Adresse", __FILE__, __LINE__, $language_code ) . ':</td><td>' . $row_ship_adr["street"] . ' ' . $row_ship_adr["number"] . '</td></tr>';
				if ( $row_order["userphone"] != "" )
				{
					$text .= '<tr><td>' . t( "Telefon", __FILE__, __LINE__, $language_code ) . ':</td><td>' . $row_order["userphone"] . '</td></tr>';
				}
				$text .= '<tr><td>' . t( "PLZ-Ort-Land", __FILE__, __LINE__, $language_code ) . ':</td><td>' . $row_ship_adr["zip"].'-' . $row_ship_adr["city"] . '-' . $row_ship_adr["country"] . '</td></tr>';
				$text .= '<tr><td>' . t( "Email", __FILE__, __LINE__, $language_code ) . ':</td><td>' . $_POST['ocs_usermail'] . '</td></tr>';
				if ( $_POST['ocs_userphone'] != '' )
				{
					$text .= '<tr><td>' . t( "Telefon", __FILE__, __LINE__, $language_code ) . ':</td><td>' . $_POST['ocs_userphone'] . '</td></tr>';
				}
				if ( $_POST['ocs_usermobile'] != '' )
				{
					$text .= '<tr><td>' . t( "Handy", __FILE__, __LINE__, $language_code ) . ':</td><td>' . $_POST['ocs_usermobile'] . '</td></tr>';
				}
				$text .= '</table>';
			}
			
			$MsgText = str_replace( '<!-- OCS_ADDRESS_17 -->', $text, $MsgText );
		}
	}
	
	
	// <!-- OCS_CITY -->
	if ( strpos( $Subject, '<!-- OCS_CITY -->' ) !== false )
	{
		if ( isset( $_POST['ocs_shop_city'] ) )
		{
			$Subject = str_replace( '<!-- OCS_CITY -->', $_POST['ocs_shop_city'], $Subject );
		}
	}
	
	// <!-- OCS_COPY -->
	if ( strpos( $Subject , '<!-- OCS_COPY -->' ) !== false )
	{
		if ( isset( $_POST['ocs_shipping_details'] ) )
		{
			$copy = '';
			if ( !( strpos( $_POST['ocs_shipping_details'], 'Lieferservice' ) !== false ) and !( strpos( $_POST['ocs_shipping_details'], 'Selbstabholung' ) !== false ) )
			{
				$copy = 'Kopie - ';
			}
			$Subject = str_replace( '<!-- OCS_COPY -->', $copy, $Subject );
		}
	}
	
	// <!-- OCS_DATA -->
	if ( strpos( $MsgText, '<!-- OCS_DATA -->' ) != false )
	{
		if ( isset( $_POST['ocs_firstmod'] ) and isset( $_POST['ocs_username'] ) and isset( $_POST['ocs_order_id'] ) and isset( $_POST['ocs_payments_transaction_id'] ) and isset( $_POST['ocs_ordernr'] ) and isset( $_POST['ocs_comment'] ) and isset( $_POST['ocs_usermail'] ) and isset( $_POST['ocs_userphone'] ) and isset( $_POST['ocs_userfax'] ) and isset( $_POST['ocs_usermobile'] ) )
		{
			$text = 	'';
			$text .= 	t('Bestellzeit', __FILE__, __LINE__, $language_code) . ': ' . date( "d.m.Y H:i", $_POST['ocs_firstmod'] );
			$text .= '	<br />' . t('Kundennummer', __FILE__, __LINE__, $language_code) . ': ' . $_POST['ocs_username'];
			$text .= '	<br />' . t('Online-Shop-Bestellnummer', __FILE__, __LINE__, $language_code) . ': ' . $_POST['ocs_order_id'];
			if ( $_POST['ocs_payments_transaction_id'] != "" )
			{
				$text .= ' <br /><b>' . t('PAYMENTS-TRANSACTION ID', __FILE__, __LINE__, $language_code) . ': </b>' . $_POST['ocs_payments_transaction_id'];
			}
			if ( $_POST['ocs_ordernr'] != "" )
			{
				$text .= '	<br />' . t('Eigene Bestellnummer', __FILE__, __LINE__, $language_code) . ': ' . $_POST['ocs_ordernr'];
			}
			if ( $_POST['ocs_comment'] != "" )
			{
				$text .= '	<br /><br>' . t('Anmerkung', __FILE__, __LINE__, $language_code) . ':<br />' . nl2br( $_POST['ocs_comment'] );
			}
			$text .= '</p>';
			$text .= '<p><b>' . t('Kontaktdaten', __FILE__, __LINE__, $language_code) . ':</b><br>';
			$text .= t('E-Mail', __FILE__, __LINE__, $language_code) . ': ' . $_POST['ocs_usermail'];
			if ( $_POST['ocs_userphone'] != "" )
			{
				$text .= '<br>' . t('Telefon', __FILE__, __LINE__, $language_code) . ': ' . $_POST['ocs_userphone'];
			}
			if ( $_POST['ocs_userfax'] != "" )
			{
				$text .= '<br>' . t('Telefax', __FILE__, __LINE__, $language_code) . ': ' . $_POST['ocs_userfax'];
			}
			if ( $_POST['ocs_usermobile'] != "" )
			{
				$text .= '<br>' . t('Handy', __FILE__, __LINE__, $language_code) . ': ' . $_POST['ocs_usermobile'];
			}
			$text .= '</p>';
			$MsgText = str_replace( '<!-- OCS_DATA -->', $text, $MsgText );
		}
	}
	
	// <!-- OCS_DATA_17 -->
	if ( strpos( $MsgText, '<!-- OCS_DATA_17 -->' ) !== false )
	{
		if ( isset( $_POST['ocs_firstmod'] ) )
		{
			$text = '';
			$text .= ' <table>';
			$text .= '	<tr>';
			$text .= '		<td style="padding-right: 10px">' . t( "Bestellzeit", __FILE__, __LINE__, $language_code ) . ': '.date("d.m.Y H:i", $_POST["ocs_firstmod"]).'<br />' . t( "Kundennummer", __FILE__, __LINE__, $language_code ) . ': ' . $_POST['ocs_username'] . '<br />' . t( "Online-Shop-Bestellnummer", __FILE__, __LINE__, $language_code ) . ': ' . $_POST['ocs_order_id'] . '</td>';
			$text .= '		<td><img src="' . PATH . 'templates/mapco.de/images/header_logo.jpg"></td>';
			$text .= '	</tr>';
			$text .= '</table><br /><br />';
			
			$MsgText = str_replace( '<!-- OCS_DATA_17 -->', $text, $MsgText );
		}
	}
	
	// <!-- OCS_INFO -->
	if ( strpos( $MsgText , '<!-- OCS_INFO -->' ) !== false )
	{
		if ( isset( $_POST['ocs_shipping_details'] ) )
		{
			$info = '';
			if ( !( strpos( $_POST['ocs_shipping_details'], 'Lieferservice' ) !== false ) and !( strpos( $_POST['ocs_shipping_details'], 'Selbstabholung' ) !== false ) )
			{
				$info = '<b style="color:red; font-size:14px;">'.t("Diese Bestellung wird von Borkheide bearbeitet", __FILE__, __LINE__, $language_code).'!</b><br /><br />';
			}
			$MsgText = str_replace( '<!-- OCS_INFO -->', $info, $MsgText );
		}
	}
	
	// <!-- OCS_SHIPPINGTYPE -->
	if ( strpos( $MsgText, '<!-- OCS_SHIPPINGTYPE -->' ) != false )
	{
		if ( isset( $_POST['ocs_shipping_details'] ) and isset( $_POST['ocs_user_id'] ) and isset( $_POST['ocs_ordernr'] ) and isset( $_POST['ocs_comment'] ) and isset( $_POST['ocs_order_id'] ) )
		{
			$discount = 0;
			
			// get order-total_net
			$post_data = 				array();
			$post_data["API"] = 		"shop";
			$post_data["APIRequest"] = 	"OrderDetailGet_neu";
			$post_data["OrderID"] = 	$_POST["ocs_order_id"];
					
			$postdata = http_build_query( $post_data );
			
			$xml = soa2( $postdata );
			if ( $xml->Ack[0] != "Success" )
			{
				show_error( 9812, 7, __FILE__, __LINE__, "Keine Bestelldaten gefunden. OrderID: " . $_POST['ocs_order_id'] );
				exit;
			}
			
			$text = '';
			$pos = strpos( $_POST['ocs_shipping_details'] , ", " ) + 2;
			$text = '<p>';
			$text .= '<b>' . substr( $_POST['ocs_shipping_details'], $pos ) . '</b><br>';
			//$text .= '<b>' . number_format( (float)$xml->Order[0]->orderItemsTotalNet[0], 2 ) . ' € Netto-Warenwert</b><br>';
			$text .= '<b>' . (string)$xml->Order[0]->orderItemsTotalNet[0] . ' € ' . t( 'Netto-Warenwert', __FILE__, __LINE__, $language_code ) . '</b><br>';
			if ( gewerblich( $_POST['ocs_user_id'] ) )
			{
				$text .= '<b>' . t( 'Gewerbekunde', __FILE__, __LINE__, $language_code ) . '</b>';
				if( $discount > 0 )
				{
					$text .= '<br>';
					$text .= '<b>' . t( 'ACHTUNG', __FILE__, __LINE__, $language_code ) . ' 4% (€ ' . number_format( $discount, 2 ) . ') ' . t( 'ONLINE-RABATT BEACHTEN', __FILE__, __LINE__, $language_code ) . '!!!</b>';					
				}
			}
			else
			{
				$text .= '<b>' . t( 'Privatkunde', __FILE__, __LINE__, $language_code ) . '</b>';
			}
			$text .= '</p>';
			
			if ( $_POST['ocs_ordernr'] != "" )
			{
				$text .= '<p><b>' . t( 'Bitte eigene OrderNr. des Kunden übernehmen', __FILE__, __LINE__, $language_code ) . '!!!</b></p>';
			}
			if ( $_POST['ocs_comment'] != "" )
			{
				$text .= '<p><b>' . t( 'Bitte die Anmerkung des Kunden zur Bestellung beachten', __FILE__, __LINE__, $language_code ) . '!!!</b></p>';
			}
			
			$MsgText = str_replace( '<!-- OCS_SHIPPINGTYPE -->', $text, $MsgText );
		}
	}
	
	// <!-- OCS_TABLE -->
	if ( strpos( $MsgText, '<!-- OCS_TABLE -->' ) != false )
	{
		if ( isset( $_POST['ocs_order_id'] ) and isset( $_POST['ocs_shop_id'] ) and isset( $_POST['ocs_shipping_details'] ) and isset( $_POST['ocs_shipping_costs'] ) and isset( $_POST['ocs_shipping_costs'] ) and isset( $_POST['ocs_user_id'] ) and isset( $_POST['ocs_eu'] ) and isset( $_POST['ocs_bill_country_id'] ) )
		{
			$text = '';
			$text .= '<table border="1" cellpadding="4">';
			$text .= '  <tr><th colspan="6">Bestellung</th></tr>';
			$text .= '  <tr>';
			$text .= '    <td>' . t( 'Artikel-Nr.', __FILE__, __LINE__, $language_code ) . '</td>';
			$text .= '    <td>' . t( 'Menge', __FILE__, __LINE__, $language_code ) . '</td>';
			$text .= '    <td>' . t( 'Bezeichnung', __FILE__, __LINE__, $language_code ) . '</td>';
			$text .= '    <td>' . t( 'EK', __FILE__, __LINE__, $language_code ) . '</td>';
			$text .= '    <td>' . t( 'Gesamt', __FILE__, __LINE__, $language_code ) . '</td>';
			$text .= '    <td></td>';
			$text .= '  </tr>';
			
			$total = 			0;
			$totalpos = 		0;
			$collateral_count = 0;
			$collateral_sum = 	0;
			
			$results2 = q("SELECT a.price, a.netto, a.amount, a.collateral, c.title, b.MPN, b.id_item FROM shop_orders_items AS a, shop_items AS b, shop_items_de AS c WHERE a.order_id=" . $_POST['ocs_order_id'] . " AND a.item_id=b.id_item AND b.id_item=c.id_item;", $dbshop, __FILE__, __LINE__ );
			
			while ( $row2 = mysqli_fetch_array( $results2 ) )
			{
				//Barcodes erzeugen
				$post_data = 				array();
				$post_data["API"] = 		"shop";
				$post_data["APIRequest"] = 	"BarcodeCreate";
				$post_data["item_id"] = 	$row2["id_item"];
				$response = soa2( $post_data, __FILE__, __LINE__ );
				
				$text .= '  <tr>';
				$text .= '  <td>' . $row2["MPN"] . '</td>';
				$text .= '  <td>' . number_format( $row2["amount"], 0 ) . '</td>';
				$text .= '  <td>' . $row2["title"];
				if( $row2["collateral"] > 0 ) 
				{
					$text .= '<br />' . t( 'zzgl.', __FILE__, __LINE__, $language_code ) . ' ' . $row2["collateral"] . ' € ' . t( 'Altteilpfand', __FILE__, __LINE__, $language_code ) . '';
					$collateral_count = $collateral_count + $row2["amount"];
					$collateral_sum = 	$collateral_sum + ( number_format( $row2["collateral"] * $row2["amount"], 2 ) );
				}
				$text .= '  </td>';
				$text .= '  <td>' . number_format( $row2["price"], 2 ) . ' €</td>';
				$price = 		$row2["amount"] * $row2["price"];
				$total += 		$price;
				$totalpos += 	( $row2["amount"] * $row2["netto"] );
				$text .= '  <td>' . number_format( $price, 2 ) . ' €</td>';
				//if ($partner_id>0) 
		//		if($_SESSION["rcid"]!="" and $_SESSION["rcid"]!=9999 and $_SESSION["rcid"]>0)
				if( $_POST['ocs_shop_id'] > 8 and $_POST['ocs_shop_id'] < 17 )
				{
					$text .= '  <td><span style="float:right;">' . itemstatus_rc( $row2["id_item"], 1, $row2["amount"] ) . '</span></td>';					
				}
				else
				{
					$text .= '  <td><span style="float:right;">' . itemstatus( $row2["id_item"], 1, $row2["amount"] ) . '</span></td>';
				}
				$text .= '  </tr>';
			}
			
			//Versandkosten
			$text .= '  <tr>';
			$text .= '    <td colspan="4">' . $_POST['ocs_shipping_details'] . '</td><td colspan="2">' . number_format( $_POST['ocs_shipping_costs'], 2 ) . ' €</td>';
			$text .= '  </tr>';
			$total += $_POST['ocs_shipping_costs'];
					
			//Gesamt Netto
			if ( gewerblich( $_POST['ocs_user_id'] ) or ( !gewerblich( $_POST['ocs_user_id'] ) and $_POST['ocs_eu'] == 0 ) )
			{
				$discount = 0;
				$text .= '  <tr>';
				if ( isset( $_POST['ocs_bill_country_id'] ) and $_POST['ocs_bill_country_id'] > 1 )
				{
					$text .= '    <td colspan="4"><b>' . t( 'Gesamtpreis Netto', __FILE__, __LINE__, $language_code ) . '</b></td><td colspan="2"><b>' . number_format( $total, 2 ) . ' €</b></td>';
				}
				else
				{
					$text .= '    <td colspan="4">' . t( 'Gesamtpreis Netto', __FILE__, __LINE__, $language_code ) . '</td><td colspan="2">' . number_format( $total, 2 ) . ' €</td>';
				}
				$text .= '  </tr>';
				$ust = 		( UST/100 ) * $total;
				$total = 	( ( 100 + UST ) / 100 ) * $total;
			}
			else 
			{
				$discount = 0;
				$ust = 		$total / ( 100 + UST ) * UST;
			}
		
			if( $_POST['ocs_shop_id'] != 17 and ( ( !gewerblich( $_POST['ocs_user_id'] ) and $_POST['ocs_eu'] == 1 ) or !isset( $_POST['ocs_bill_country_id'] ) or ( isset( $_POST['ocs_bill_country_id'] ) and $_POST['ocs_bill_country_id'] == 1 ) ) )
			{
				$text .= '  <tr>';
				$text .= '    <td colspan="4">';
				if ( gewerblich( $_POST['ocs_user_id'] ) )
				{
					$text .= t( 'zzgl.', __FILE__, __LINE__, $language_code ) . ' '.UST.'% ' . t( 'gesetzliche', __FILE__, __LINE__, $language_code ) . '';
				}
				else
				{
					$text .= t( 'darin enthalten', __FILE__, __LINE__, $language_code ) . ', '.UST.'%';  
				}
				$text .= ' ' . t( 'Umsatzsteuer', __FILE__, __LINE__, $language_code ) . '</td><td colspan="2">' . number_format( $ust, 2 ) . ' €</td>';
				$text .= '  </tr>';
			
				//Gesamt Brutto
				$text .= '  <tr>';
				$text .= '    <td colspan="4"><b>' . t( 'Gesamtpreis Brutto', __FILE__, __LINE__, $language_code ) . '</b></td><td colspan="2"><b>' . number_format( $total, 2 ) . ' €</b></td>';
				$text .= '  </tr>';
			}
			
			$text .= '</table>';
			
			$MsgText = str_replace( '<!-- OCS_TABLE -->', $text, $MsgText );
		}
	}
	
	// <!-- OCS_TABLE_17 -->
	if ( strpos( $MsgText, '<!-- OCS_TABLE_17 -->' ) != false )
	{
		if ( isset( $_POST['ocs_order_id'] ) and isset( $_POST['ocs_shop_id'] ) and isset( $_POST['ocs_shipping_details'] ) and isset( $_POST['ocs_shipping_costs'] ) and isset( $_POST['ocs_shipping_costs'] ) and isset( $_POST['ocs_user_id'] ) and isset( $_POST['ocs_eu'] ) and isset( $_POST['ocs_bill_country_id'] ) )
		{
			$text = '';
			$text .= '<table border="1" cellpadding="4">';
			$text .= '  <tr><th colspan="7">' . t( "Bestellung", __FILE__, __LINE__, $language_code ) . '</th></tr>';
			$text .= '  <tr>';
			$text .= '    <td>PLT</td>';
			$text .= '    <td>ART</td>';
			$text .= '    <td>PZ</td>';
			$text .= '    <td>DESCRIZIONE</td>';
			$text .= '    <td>BCD</td>';
			$text .= '    <td>EK</td>';
			$text .= '    <td>TOTALE</td>';
			$text .= '  </tr>';
			
			$results2 = q( "SELECT a.price, a.netto, a.amount, a.collateral, c.title, b.MPN, b.id_item, b.EAN, d.pallet FROM shop_orders_items AS a, shop_items AS b, shop_items_it AS c LEFT JOIN mapco_gls_roma AS d ON ( c.id_item = d.item_id ) WHERE a.order_id=" . $_POST['ocs_order_id'] . " AND a.item_id=b.id_item AND b.id_item=c.id_item ORDER BY d.pallet;", $dbshop, __FILE__, __LINE__ );
			$amountsum = 		0;
			$total = 			0;
			$collateral_count = 0;
			$collateral_sum = 	0;
			while ( $row2 = mysqli_fetch_array( $results2 ) )
			{
				//Barcodes erzeugen
				$item_id_barcode = $row2["id_item"];
				
				$post_data = 				array();
				$post_data["API"] = 		"shop";
				$post_data["APIRequest"] = 	"BarcodeCreate";
				$post_data["item_id"] = 	$item_id_barcode;
				$response = soa2( $post_data, __FILE__, __LINE__ );
				
				$text .= '  <tr>';
				$text .= '  <td>' . $row2["pallet"] . '</td>';
				$text .= '  <td>' . $row2["MPN"] . '</td>';
				$amountsum = $amountsum + $row2["amount"];
				$text .= '  <td>' . number_format( $row2["amount"], 0, ",", "" ) . '</td>';
				$text .= '  <td>' . $row2["title"];
				if ( $row2["collateral"] > 0 ) 
				{
					$text .= '<br />zzgl. ' . $row2["collateral"] . ' ' . t( "€ Altteilpfand", __FILE__, __LINE__, $language_code ) . '';
					$collateral_count = $collateral_count + $row2["amount"];
					$collateral_sum = $collateral_sum + ( number_format( $row2["collateral"] * $row2["amount"], 2, ",", "" ) );
				}
				$text .= '  </td>';
				$text .= '  <td><img src="' . PATH . 'images/barcodes/' . $row2["EAN"] . '.png"></td>';
				$text .= '  <td>' . number_format( $row2["price"], 2, ",", "" ) . ' €</td>';
				$price = $row2["amount"] * $row2["price"];
				$total += $price;
				$text .= '  <td>' . number_format( $price, 2, ",", "" ) . ' €</td>';	
			}
			
			//Versandkosten
			$text .= '  <tr>';
			$text .= '	   <td style="border-right: none"></td>';
			$text .= '	   <td style="border-right: none; border-left: none;"></td>';
			$text .= '	   <td style="border-left: none">' . number_format( $amountsum, 0, ",", "" ) . '</td>';
			$text .= '    <td colspan="3">' . $_POST['ocs_shipping_details'] . '</td><td>' . number_format( $row_order["shipping_costs"], 2, ",", "" ) . ' €</td>';
			$text .= '  </tr>';
			$total += $_POST['ocs_shipping_costs'];
					
			//Gesamt Netto
			if ( gewerblich( $_POST['ocs_user_id'] ) )
			{
				$discount = 0;
				$text .= '  <tr>';
				$text .= '    <td colspan="6"><b>' . t( "Gesamtpreis Netto", __FILE__, __LINE__, $language_code ) . '</b></td><td><b>' . number_format( $total, 2, ",", "" ) . ' €</b></td>';
				$text .= '  </tr>';
				//$ust=(UST/100)*$total;
				//$total=((100+UST)/100)*$total;
			}
			else 
			{
				/*$discount = 0;
				$ust=$total/(100+UST)*UST;
				
				$text9 .= '  <tr>';
				$text9 .= '    <td colspan="5">';
				$text9 .= ''.t("darin enthalten").', '.UST.'%';  
				$text9 .= ' '.t("Umsatzsteuer").'</td><td colspan="2">'.number_format($ust, 2, ",", "").' Euro</td>';
				$text9 .= '  </tr>';
			
				//Gesamt Brutto
				$text9 .= '  <tr>';
				$text9 .= '    <td colspan="5"><b>'.t("Gesamtpreis Brutto").'</b></td><td colspan="2"><b>'.number_format($total, 2, ",", "").' Euro</b></td>';
				$text9 .= '  </tr>';*/
				
				$discount = 0;
				$ust = $total / ( 100 + UST ) * UST;
				
				$total = $total - $ust;
				
				/*$text9 .= '  <tr>';
				$text9 .= '    <td colspan="5">';
				$text9 .= ''.t("darin enthalten").', '.UST.'%';  
				$text9 .= ' '.t("Umsatzsteuer").'</td><td colspan="2">'.number_format($ust, 2, ",", "").' Euro</td>';
				$text9 .= '  </tr>';*/
			
				//Gesamt Brutto
				$text .= '  <tr>';
				$text .= '    <td colspan="6"><b>' . t( "Gesamtpreis Netto", __FILE__, __LINE__, $language_code ) . '</b></td><td><b>' . number_format( $total, 2, ",", "" ) . ' €</b></td>';
				$text .= '  </tr>';
			}
	
			$text .= '</table>';
			
			$MsgText = str_replace( '<!-- OCS_TABLE_17 -->', $text, $MsgText );
		}
	}
	// <!-- OS_COUNTRY_CODE -->
	if ( strpos( $MsgText, '<!-- OS_COUNTRY_CODE -->' ) != false )
	{
		if ( isset( $_POST['os_country_code'] ) )
		{
			$MsgText = str_replace( "<!-- OS_COUNTRY_CODE -->", $_POST['os_country_code'], $MsgText );
		}
	}
	
	// <!-- OS_ITEMS -->
	if ( strpos( $MsgText, '<!-- OS_ITEMS -->' ) )
	{
		if ( isset( $_POST['os_items'] ) )
		{
			$item = array();
			$item = unserialize( $_POST['os_items'] );
			
			if ( sizeof( $item ) == 1 && $item[0] != "" )
			{
				$MsgText = str_replace( "<!-- OS_ITEMS -->", $item[0], $MsgText );
			}
			elseif ( sizeof( $item ) > 1 && !in_array( "", $item ) )
			{
				$buf = "";
				for ( $i = 0; $i < sizeof( $item ); $i++ )
				{
					$buf .= '<b>' . $item[$i] . '</b><br />';
				}
				$MsgText = str_replace( "<!-- OS_ITEMS -->", $buf, $MsgText );
			}
		}
	}
	
	// <!-- OS_NAME -->
	if ( strpos( $MsgText, '<!-- OS_NAME -->' ) != false )
	{
		if ( isset( $_POST['os_name'] ) and isset( $_POST['os_buyer_user_id'] ) )
		{
			
			if ( trim( $_POST['os_name'] ) == "" )
			{
				if ( $_POST['os_buyer_user_id'] != "" )
				{
					$MsgText = str_replace( "<!-- OS_NAME -->" , $_POST['os_buyer_user_id'], $MsgText );
				}
			}
			else
			{
				if ( $_POST['os_buyer_user_id'] != "" )
				{
					$_POST['os_name'] = $_POST['os_name'] . " (" . $_POST['os_buyer_user_id'] . ")";
				}
				$MsgText = str_replace( "<!-- OS_NAME -->", $_POST['os_name'], $MsgText );	
			}
	
		}		
	}
	
	// <!-- OS_ORDER_ID -->
	if ( strpos( $Subject, '<!-- OS_ORDER_ID -->' ) != false )
	{
		if ( isset( $_POST['os_order_id'] ) )
		{
			$Subject = str_replace( '<!-- OS_ORDER_ID -->', $_POST['os_order_id'], $Subject );
		}
	}
	
	// <!-- OS_SELLER_MAIL -->
	if ( strpos( $MsgText, '<!-- OS_SELLER_MAIL -->' ) != false )
	{
		if ( isset( $_POST['os_seller_mail'] ) )
		{
			$MsgText = str_replace( '<!-- OS_SELLER_MAIL -->', $_POST['os_seller_mail'], $MsgText );
		}
	}
	
	// <!-- OS_SELLER_NAME -->
	if ( strpos( $MsgText, '<!-- OS_SELLER_NAME -->' ) != false )
	{
		if ( isset( $_POST['os_seller_name'] ) )
		{
			$MsgText = str_replace( '<!-- OS_SELLER_NAME -->', $_POST['os_seller_name'], $MsgText );
		}
	}
	
	// <!-- OS_SELLER_PHONE -->
	if ( strpos( $MsgText, '<!-- OS_SELLER_PHONE -->' ) != false )
	{
		if ( isset( $_POST['os_seller_phone'] ) )
		{
			$MsgText = str_replace( '<!-- OS_SELLER_PHONE -->', $_POST['os_seller_phone'], $MsgText );
		}
	}

	// <!-- OS_TRACKING_ID -->
	if ( strpos( $MsgText, '<!-- OS_TRACKING_ID -->' ) != false )
	{
		if ( isset( $_POST['os_tracking_id'] ) )
		{
			$MsgText = str_replace( "<!-- OS_TRACKING_ID -->", $_POST['os_tracking_id'], $MsgText );
		}
	}
	
	// <!-- OSO_COUNTRY_CODE -->
	if ( strpos( $MsgText, '<!-- OSO_COUNTRY_CODE -->' ) != false )
	{
		if ( isset( $_POST['oso_origin'] ) )
		{
			$country_code = 'de';
			if ( $_POST['oso_origin'] != 'DE' and $_POST['oso_origin'] != 'AT' and $_POST['oso_origin'] != 'CH' and $_POST['oso_origin'] != '' )
			{
				$country_code = 'en';
			}
			$MsgText = str_replace( "<!-- OSO_COUNTRY_CODE -->", $country_code, $MsgText );
		}
	}
	
	// <!-- OSO_NAME -->
	if ( strpos( $MsgText, '<!-- OSO_NAME -->' ) )
	{
		if ( isset( $_POST['oso_firstname'] ) and isset( $_POST['oso_lastname'] ) and isset( $_POST['oso_company'] ) and isset( $_POST['oso_company2'] ) )
		{
			$name = '';
			if ( ( $_POST['oso_firstname'] != '' and $_POST['oso_firstname'] != '-' ) or ( $_POST['oso_lastname'] != '' and $_POST['oso_lastname'] != '-' ) ) {
				if ( $_POST['oso_firstname'] != '' and $_POST['oso_firstname'] != '-' ) $name .= $_POST['oso_firstname'];
				if ( $_POST['oso_lastname'] != '' and $_POST['oso_lastname'] != '-' ) {
					if ( $_POST['oso_firstname'] != '' and $_POST['oso_firstname'] != '-' ) $name .= ' ' . $_POST['oso_lastname'];
					else $name .= $_POST['oso_lastname'];
				}
				if ( ( $_POST['oso_company'] != '' and $_POST['oso_company'] != '-' ) or ( $_POST['oso_company2'] != '' and $_POST['oso_company2'] != '-' ) ) {
					$name .= ' (';
					if ( $_POST['oso_company'] != '' and $_POST['oso_company'] != '-' ) $name .= $_POST['oso_company'];
					if ( $_POST['oso_company2'] != '' and $_POST['oso_company2'] != '-' ) {
						if ( $_POST['oso_company'] != '' and $_POST['oso_company'] != '-' ) $name .= ', ' . $_POST['oso_company2'];
						else $name .= $_POST['oso_company2'];	
					}
					$name .= ')';
				}
			}
			else {
				if ( $_POST['oso_company'] != '' and $_POST['oso_company'] != '-' ) $name .= $_POST['oso_company'];
				if ( $_POST['oso_company2'] != '' and $_POST['oso_company2'] != '-' ) {
					if ( $_POST['oso_company'] != '' and $_POST['oso_company'] != '-' ) $name .= ', ' . $_POST['oso_company2'];
					else $name .= $_POST['oso_company2'];	
				}
			}
			$MsgText = str_replace( "<!-- OSO_NAME -->", $name, $MsgText );
		}
	}
	
	// <!-- OSO_TRACKING_ID -->
	if ( strpos( $MsgText, '<!-- OSO_TRACKING_ID -->' ) != false )
	{
		if ( isset( $_POST['oso_shipping_number'] ) )
		{
			$MsgText = str_replace( "<!-- OSO_TRACKING_ID -->", $_POST['oso_shipping_number'], $MsgText );
		}
	}
	
	// <!-- PATH -->
	if ( strpos( $MsgText, '<!-- PATH -->' ) != false )
	{
		$MsgText = str_replace( '<!-- PATH -->', PATH, $MsgText );
	}
	
	// <!-- PW -->
	if ( strpos( $MsgText, '<!-- PW -->' ) != false )
	{
		if ( isset( $_POST[ 'pw' ] ) )
		{
			$MsgText = str_replace( '<!-- PW -->', $_POST[ 'pw' ], $MsgText );
		}
	}
	
	// <!-- PW_LINK -->
	if ( strpos( $MsgText, '<!-- PW_LINK -->' ) != false )
	{
		if ( isset( $_POST[ 'user_token' ] ) )
		{
			$MsgText = str_replace( '<!-- PW_LINK -->', PATHLANG . 'autologin/' . $_POST[ 'user_token' ] . '/neues-passwort/', $MsgText );
		}
	}
	
	// <!-- USERMAIL -->
	if ( strpos( $MsgText, '<!-- USERMAIL -->' ) != false )
	{
		if ( isset( $_POST[ 'user_mail' ] ) )
		{
			$MsgText = str_replace( '<!-- USERMAIL -->', $_POST[ 'user_mail' ], $MsgText );
		}
	}
	
	// <!-- USERNAME -->
	if ( strpos( $MsgText, '<!-- USERNAME -->' ) != false )
	{
		if ( isset( $_POST[ 'user_name' ] ) )
		{
			$MsgText = str_replace( '<!-- USERNAME -->', $_POST[ 'user_name' ], $MsgText );
		}
	}

//****************************** end of replacements ************************************	



//****************************** cutouts ************************************************

	// B2B_REG_SHIP_ADR
	if ( strpos( $MsgText, '<!-- B2B_REG_SHIP_ADR_START -->' ) != false )
	{
		if ( !isset( $_POST[ 'b2b_reg_ship_adr' ] ) or ( isset( $_POST[ 'b2b_reg_ship_adr' ] ) and $_POST[ 'b2b_reg_ship_adr' ] == 0 ) ) {
			$MsgText = cutout( $MsgText, "<!-- B2B_REG_SHIP_ADR_START -->", "<!-- B2B_REG_SHIP_ADR_END -->" );
		}
	}
	
	// FIN_PAYDATA
	if ( strpos( $MsgText, '<!-- FIN_PAYDATA_START -->' ) != false )
	{
		if ( isset( $_POST['fin_pay_data_show'] ) and $_POST['fin_pay_data_show'] == 0 )
		{
			$MsgText = cutout( $MsgText, "<!-- FIN_PAYDATA_START -->", "<!-- FIN_PAYDATA_END -->" );
		}
	}
	
	// FIN_THANKS_1
	if ( strpos( $MsgText, '<!-- FIN_THANKS_1_START -->' ) != false )
	{
		if ( isset( $_POST['fin_items'] ) )
		{
			$item = array();
			$item = unserialize( $_POST['fin_items'] );
			
			if ( sizeof( $item ) == 1 && $item[0] != "" )
			{
			}
			elseif ( sizeof( $item ) > 1 && !in_array( "", $item ) )
			{
				$MsgText = cutout( $MsgText, "<!-- FIN_THANKS_1_START -->", "<!-- FIN_THANKS_1_END -->" );
			}
			else
			{
				$MsgText = cutout( $MsgText, "<!-- FIN_THANKS_1_START -->", "<!-- FIN_THANKS_1_END -->" );
			}
		}
	}
	
	// FIN_THANKS_2
	if ( strpos( $MsgText, '<!-- FIN_THANKS_2_START -->' ) != false )
	{
		if ( isset( $_POST['fin_items'] ) )
		{
			$item = array();
			$item = unserialize( $_POST['fin_items'] );
			
			if ( sizeof( $item ) == 1 && $item[0] != "" )
			{
				$MsgText = cutout( $MsgText, "<!-- FIN_THANKS_2_START -->", "<!-- FIN_THANKS_2_END -->" );
			}
			elseif ( sizeof($item) > 1 && !in_array( "", $item ) )
			{
			}
			else
			{
				$MsgText = cutout( $MsgText, "<!-- FIN_THANKS_2_START -->", "<!-- FIN_THANKS_2_END -->" );
			}
		}
	}
	
	// FIN_THANKS_3
	if ( strpos( $MsgText, '<!-- FIN_THANKS_3_START -->' ) != false )
	{
		if ( isset( $_POST['fin_items'] ) )
		{
			$item = array();
			$item = unserialize( $_POST['fin_items'] );
			
			if ( sizeof( $item ) == 1 && $item[0] != "" )
			{
				$MsgText = cutout( $MsgText, "<!-- FIN_THANKS_3_START -->", "<!-- FIN_THANKS_3_END -->" );
			}
			elseif ( sizeof( $item ) > 1 && !in_array( "", $item ) )
			{
				$MsgText = cutout( $MsgText, "<!-- FIN_THANKS_3_START -->", "<!-- FIN_THANKS_3_END -->" );
			}
		}
	}
	
	// OCC_PAYDATA
	if ( strpos( $MsgText, '<!-- OCC_PAYDATA_START -->' ) != false )
	{
		if ( isset( $_POST['occ_status_id'] ) and isset( $_POST['occ_payments_type_id'] ) and ( $_POST['occ_status_id'] == 7 or ( $_POST['occ_payments_type_id'] > 2 and $_POST['occ_payments_type_id'] < 7 ) ) )
		{
			$MsgText = cutout( $MsgText, "<!-- OCC_PAYDATA_START -->", "<!-- OCC_PAYDATA_END -->" );
		}
	}
	
	// OS_BUYER
	if ( strpos( $MsgText, '<!-- OS_BUYER_START -->' ) != false )
	{
		if ( isset( $_POST['os_name'] ) and isset( $_POST['os_buyer_user_id'] ) and ( $_POST['os_name'] != '' or $_POST['os_buyer_user_id'] != '' ) )
		{
			$MsgText = cutout( $MsgText, '<!-- OS_BUYER_START -->', '<!-- OS_BUYER_END -->' );
		}
	}
	
	// OS_THANKS_1
	if ( strpos( $MsgText, '<!-- OS_THANKS_1_START -->' ) != false )
	{
		if ( isset( $_POST['os_items'] ) )
		{
			$item = array();
			$item = unserialize( $_POST['os_items'] );
			
			if ( sizeof( $item ) == 1 && $item[0] != "" )
			{
			}
			elseif ( sizeof( $item ) > 1 && !in_array( "", $item ) )
			{
				$MsgText = cutout( $MsgText, "<!-- OS_THANKS_1_START -->", "<!-- OS_THANKS_1_END -->" );
			}
			else
			{
				$MsgText = cutout( $MsgText, "<!-- OS_THANKS_1_START -->", "<!-- OS_THANKS_1_END -->" );
			}
		}
	}
	
	// OS_THANKS_2
	if ( strpos( $MsgText, '<!-- OS_THANKS_2_START -->' ) != false )
	{
		if ( isset( $_POST['os_items'] ) )
		{
			$item = array();
			$item = unserialize( $_POST['os_items'] );
			
			if ( sizeof( $item ) == 1 && $item[0] != "" )
			{
				$MsgText = cutout( $MsgText, "<!-- OS_THANKS_2_START -->", "<!-- OS_THANKS_2_END -->" );
			}
			elseif ( sizeof($item) > 1 && !in_array( "", $item ) )
			{
			}
			else
			{
				$MsgText = cutout( $MsgText, "<!-- OS_THANKS_2_START -->", "<!-- OS_THANKS_2_END -->" );
			}
		}
	}
	
	// OS_THANKS_3
	if ( strpos( $MsgText, '<!-- OS_THANKS_3_START -->' ) != false )
	{
		if ( isset( $_POST['os_items'] ) )
		{
			$item = array();
			$item = unserialize( $_POST['os_items'] );
			
			if ( sizeof( $item ) == 1 && $item[0] != "" )
			{
				$MsgText = cutout( $MsgText, "<!-- OS_THANKS_3_START -->", "<!-- OS_THANKS_3_END -->" );
			}
			elseif ( sizeof( $item ) > 1 && !in_array( "", $item ) )
			{
				$MsgText = cutout( $MsgText, "<!-- OS_THANKS_3_START -->", "<!-- OS_THANKS_3_END -->" );
			}
		}
	}

//****************************** end of cutouts *****************************************



//****************************** send mail **********************************************

	define( "XNL", "\r\n" ) ; // CONSTANT Newline CR
	
	if ( !function_exists( "TextEncode" ) ) {
		//
		// build attachment as text conforming RFC2045 (76 char per line, end with \r\n)
		//
		function TextEncode( $FileName )
		{
			if ( is_readable( $FileName ) ) {
				$fp = 		fopen( $FileName, "rb" );
				$cont = 	fread( $fp, filesize ( $FileName ) );
				$contents = base64_encode( $cont );
				$len = 		strlen( $contents );
				$str = 		"" ;
				
				while( $len > 0 ) {
					if ( $len >= 76 ) {
						$str .= 	substr( $contents, 0, 76 ).XNL ;
						$contents = substr( $contents, 76 ) ;
						$len = 		$len - 76 ;
					} else {
						$str .= 	$contents.XNL;
						$contents = "";
						$len = 		0;
					}
				}
				fclose( $fp );
			} else {
				$str = "File " . $FileName . " not found";
			}
			return $str;
		}
	}
							
	if ( !isset( $IFile ) ) {
		$IFile = "none";
	}
	
	if( !isset( $IFileName ) ) {
		$IFileName="none";
	}
		
	$mime_boundary = 	"--==================_846811060==_";
	$mimetype = 		"application/octet-stream";
	
	// check for array (multiple attachments)
	if ( !is_array( $IFile ) ) {
		$File[0] = 		$IFile;
		$FileName[0] = 	$IFileName;
	} else {
		for ( $i = 0; $i < count( $IFile ); $i++ ) {
			$File[ $i ] = 	$IFile[ $i ];
			$FileName[ $i ] = $IFileName[ $i ];
		}
	}
	
	$attCount = count($File);
	
	// check if there is really an attachment
	$attExists = FALSE ;                                    
	for ( $i = 0; $i < $attCount; $i++ ) {
		if ( $File[ $i ] != "none" ) {
			$attExists = TRUE;
		}
	}
	
	// build header for text
	$txtheaders  = "From: " . $FromSender . "\n";              
	$txtheaders .= "Reply-To: " . $FromSender . "\n";
	$txtheaders .= "X-Mailer: PHP\n";
	$txtheaders .= "X-Sender: " . $FromSender . "\n";
	
	// is there an attachment
	if ( $attExists ) {
		// build header for attachment
		$attheaders  = "MIME-version: 1.0\n";
		$attheaders .= 'Content-type: multipart/mixed; boundary="' . $mime_boundary . '"' . "\n";
		$attheaders .= "Content-transfer-encoding: 7BIT\n";
		$attheaders .= "X-attachments: ";
		$firstAtt = TRUE;
		for ( $i = 0; $i < $attCount; $i++ ) {
			if ( $File[$i] != "none" ) {
				if ( $firstAtt ) {
					$firstAtt = FALSE;
				} else {
					$attheaders .= ",";
				}
				$attheaders .= $FileName[ $i ];
			}
		}
		$attheaders .= ";\n\n";
		
		// build attachment itself
		$attach = "";
		for ( $i = 0; $i < $attCount; $i++ ) {
			if ( $File[ $i ] != "none" ) {
				$attach  .= "--" . $mime_boundary . "\n";
				$attach  .= "Content-type:" . $mimetype . '; name="' . $FileName[ $i ] . '";' . "\n";
				$attach  .= "Content-Transfer-Encoding: base64\n" ;
				$attach  .= 'Content-disposition: attachment; filename="' . $FileName[ $i ] . '"' . "\n\n";
				$attach  .= TextEncode( $File[ $i ] ) . "\n";
			}
		}
		// build message itself
		$message  = "--" . $mime_boundary . "\n";
		$message .= 'MIME-Version: 1.0' ."\n";
		$message .= 'Content-Transfer-Encoding: 8bit'. "\n";
		$message .= 'Content-Type: text/html; charset=utf-8' . "\n\n";
		$message .= $MsgText . "\n";
	} else {                                                // no attachment
		$txtheaders .= 'MIME-Version: 1.0' . "\n";
		$txtheaders .= 'Content-Transfer-Encoding: 8bit' . "\n";
		$txtheaders .= 'Content-Type: text/html; charset=utf-8' . "\n\n";
		$attheaders = "";
		$attach  = "";
		$message = $MsgText . "\n";                         // send text only
	}
	
	// send email
	$mail_status = 'test';
	$mail_status = mail( $ToReceiver, $Subject, $message.$attach, $txtheaders . $attheaders ) ;

//	mail("pm@mapco.eu", $Subject." - ".$ToReceiver, $message.$attach, $txtheaders.$attheaders) ;
	
	$xml = '';
	$xml .= '<mail_status><![CDATA[' . $mail_status . ']]></mail_status>' . "\n";
	echo $xml;

//****************************** end of send mail ***************************************



//****************************** save mail **********************************************

	if ( isset( $_POST[ 'save' ] ) and $_POST[ 'save' ] == 1 ) {
		
		//save email in cms_articles
		$post_data[ "API" ] = 			"cms";
		$post_data[ "APIRequest" ] = 	"ArticleAdd";
		$post_data[ "title" ] = 		$Subject;
		$post_data[ "article" ] = 		$MsgText;	
		$post_data[ "format" ] = 		$_POST['format'];
		if ( isset( $_POST[ 'site_id' ] ) ) {
			$post_data[ 'site_id' ] = 	$_POST[ 'site_id' ];
		}
		
		$postdata = http_build_query( $post_data );
		
		$response = soa2( $postdata, __FILE__, __LINE__ );	
		$article_id = (int)$response->article_id[ 0 ];
		
		
		//save conversation in crm_conversations
		$post_data=array();
		$post_data["API"]="crm";
		$post_data["APIRequest"]="ConversationAdd";
		$post_data["user_id"]=$_POST[ 'user_id' ];
		$post_data["order_id"]=$order_id;
		$post_data["article_id"]=$article_id;
		$post_data["type_id"]=1;
		$post_data["con_from"]=$FromSender;
		$post_data["con_to"]=$ToReceiver;
		
		$postdata=http_build_query($post_data);
		
		$response=soa2($postdata, __FILE__, __LINE__);
	
		
		//save article label in cms_articles_labels
		$post_data=array();
		$post_data["API"]="cms";
		$post_data["APIRequest"]="ArticleLabelAdd";
		$post_data["article_id"]=$article_id;
		$post_data["label_id"]=21;
		
		$postdata=http_build_query($post_data);
		
		$response=soa2($postdata, __FILE__, __LINE__);

	} // end of: if ( isset( $_POST[ 'save' ] ) and $_POST[ 'save' ] == 1 )

//****************************** end of save mail ***************************************

?>