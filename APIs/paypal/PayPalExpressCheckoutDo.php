<?php

	// SOA2 SERVICE

	$required = array();
	$required["order_id"] 		= "numericNN";
	$required["paypal_token"] 	= "textNN";
	$required["payerID"] 		= "textNN";
	check_man_params($required);

	require_once 'functions/PayPal_Functions.php';
	



	//GET ORDER DATA
	$postfield = array();
	$postfield["API"]				= "shop";
	$postfield["APIRequest"]		= "OrderDetailGet_neu_test";
	$postfield["OrderID"]			= $_POST["order_id"];
	
	$order = soa2($postfield, __FILE__, __LINE__, "obj");
	
	if ( (string)$order->Ack[0] != "Success" )
	{
		//show_error();
		echo "KEINE ORDER";
		exit;	
	}
	
	// shipping address
	$address = array();

	//DEFINE SHIPPING ADDRESS
	if ( (int)$order->Order[0]->ship_adr_id[0] != 0 )
	{

		$name = "";
		//DEFINE SHIP_TO NAME
		if ( (string)$order->Order[0]->ship_adr_company[0] != "" )
		{
			$name .= (string)$order->Order[0]->ship_adr_company[0];
		}

		//SET "," as separator between company and name
		if ( (string)$order->Order[0]->ship_adr_firstname[0] != "" && (string)$order->Order[0]->ship_adr_lastname[0] != "")
		{
			if ( $name != "")
			{
				$name .= ", ";
			}
		}
		
		if ( (string)$order->Order[0]->ship_adr_firstname[0] != "" )
		{
			$name .= (string)$order->Order[0]->ship_adr_firstname[0];
		}
		
		if ( (string)$order->Order[0]->ship_adr_lastname[0] != "" )
		{
			if ( (string)$order->Order[0]->ship_adr_firstname[0] !="")
			{
				$name .= " ";	
			}
			$name .= (string)$order->Order[0]->ship_adr_lastname[0];
		}
		
		$address["Name"] = $name;
		
		//STREET
		$address["Street1"] = (string)$order->Order[0]->ship_adr_street[0]." ".(string)$order->Order[0]->ship_adr_number[0];
		
		//ADDITIONAL
		$address["Street2"] = (string)$order->Order[0]->ship_adr_additional[0];
		
		//ZIP
		$address["ZIP"] = (string)$order->Order[0]->ship_adr_zip[0];

		//CITY
		$address["City"] = (string)$order->Order[0]->ship_adr_city[0];
		
		//PHONE
		$address["Phone"] = "";
		
		//COUNTRY
			//LÄNDERKÜRZEL BEZIEHEN
			$res_country = q("SELECT * FROM shop_countries WHERE id_country = ".(int)$order->Order[0]->ship_adr_country_id[0], $dbshop, __FILE__, __LINE__);
			if ( mysqli_num_rows( $res_country ) == 0 )
			{
				//show_error();
				echo "FEHLER LÄNDERKÜRZEL";
				exit;
			}
			else
			{
				$country = mysqli_fetch_array( $res_country );
			}
			
		$address["Country"] = $country["country_code"];
		
	}

	elseif ( (int)$order->Order[0]->bill_adr_id[0] != 0 )
	{

		$name = "";
		//DEFINE SHIP_TO NAME
		if ( (string)$order->Order[0]->bill_adr_company[0] != "" )
		{
			$name .= (string)$order->Order[0]->bill_adr_company[0];
		}

		//SET "," as separator between company and name
		if ( (string)$order->Order[0]->bill_adr_firstname[0] != "" && (string)$order->Order[0]->bill_adr_lastname[0] != "")
		{
			if ( $name != "")
			{
				$name .= ", ";
			}
		}
		
		if ( (string)$order->Order[0]->bill_adr_firstname[0] != "" )
		{
			$name .= (string)$order->Order[0]->bill_adr_firstname[0];
		}
		
		if ( (string)$order->Order[0]->bill_adr_lastname[0] != "" )
		{
			if ( (string)$order->Order[0]->bill_adr_firstname[0] !="")
			{
				$name .= " ";	
			}
			$name .= (string)$order->Order[0]->bill_adr_lastname[0];
		}
		
		$address["Name"] = $name;
		
		//STREET
		$address["Street1"] = (string)$order->Order[0]->bill_adr_street[0]." ".(string)$order->Order[0]->bill_adr_number[0];
		
		//ADDITIONAL
		$address["Street2"] = (string)$order->Order[0]->bill_adr_additional[0];
		
		//ZIP
		$address["ZIP"] = (string)$order->Order[0]->bill_adr_zip[0];

		//CITY
		$address["City"] = (string)$order->Order[0]->bill_adr_city[0];
		
		//PHONE
		$address["Phone"] = "";
		
		//COUNTRY
			//LÄNDERKÜRZEL BEZIEHEN
			$res_country = q("SELECT * FROM shop_countries WHERE id_country = ".(int)$order->Order[0]->bill_adr_country_id[0], $dbshop, __FILE__, __LINE__);
			if ( mysqli_num_rows( $res_country ) == 0 )
			{
				//show_error();
				echo "FEHLER LÄNDERKÜRZEL2";
				exit;
			}
			else
			{
				$country = mysqli_fetch_array( $res_country );
			}
			
		$address["Country"] = $country["country_code"];
		
	}
	
		
		$postfields = array();
		$postfields["TOKEN"]								= $_POST["paypal_token"];
		$postfields["PAYERID"]								= $_POST["payerID"];
		$postfields["ADDROVERRIDE"]							= 0; //ADRESSE wird nicht bei PayPal angezeigt (anzeigen = 1)
		$postfields["PAYMENTREQUEST_0_SHIPTONAME"]			= $address["Name"];
		$postfields["PAYMENTREQUEST_0_SHIPTOSTREET"]		= $address["Street1"];
		if ( $address["Street2"] != "" )
		{
			$postfields["PAYMENTREQUEST_0_SHIPTOSTREET2"]	= $address["Street2"];
		}
		$postfields["PAYMENTREQUEST_0_SHIPTOSTATE"]			= "";
		$postfields["PAYMENTREQUEST_0_SHIPTOZIP"]			= $address["ZIP"];
		$postfields["PAYMENTREQUEST_0_SHIPTOCITY"]			= $address["City"];
		$postfields["PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE"]	= $address["Country"];
		if ( $address["Phone"] != "" )
		{ 
			$postfields["PAYMENTREQUEST_0_SHIPTOPHONENUM"]	= $address["Phone"];
		}
	
	//ADD ITEMS
	$positionCount = 0;
	while ( isset($order->Order[0]->OrderItems[0]->Item[$positionCount]) )
	{
		
		if ( strlen( (string)$order->Order[0]->OrderItems[0]->Item[$positionCount]->OrderItemDescLong[0] ) > 30 )
		{
			$short_desc = substr( (string)$order->Order[0]->OrderItems[0]->Item[$positionCount]->OrderItemDescLong[0],0,30 )."...";
		}
		else
		{
			$short_desc = (string)$order->Order[0]->OrderItems[0]->Item[$positionCount]->OrderItemDescLong[0];
		}
		
		$item_title = (string)$order->Order[0]->OrderItems[0]->Item[$positionCount]->OrderItemDesc[0];
		
		if ((string)$order->Order[0]['type'] == "business" )
		{
			$itemprice = str_replace(",", ".", (string)$order->Order[0]->OrderItems[0]->Item[$positionCount]->orderItemPriceNetFC[0] );
		}
		else
		{
			$itemprice = str_replace(",", ".", (string)$order->Order[0]->OrderItems[0]->Item[$positionCount]->orderItemPriceGrossFC[0] );
		}
		
		$amount = (int)$order->Order[0]->OrderItems[0]->Item[$positionCount]->OrderItemAmount[0];
		
		$postfields["L_PAYMENTREQUEST_0_AMT".$positionCount]	= $itemprice;
		$postfields["L_PAYMENTREQUEST_0_QTY".$positionCount]	= $amount;	
		$postfields["L_PAYMENTREQUEST_0_NAME".$positionCount] 	= $item_title;
		$postfields["L_PAYMENTREQUEST_0_DESC".$positionCount] 	= $short_desc;	
		
		$positionCount++;
		
	}

		if ((string)$order->Order[0]['type'] == "business" )
		{
			$orderItemsTotalFC 			= str_replace(",", ".",$order->Order[0]->orderItemsTotalNetFC[0]);
			$shippingCostsFC 			= str_replace(",", ".",$order->Order[0]->shippingCostsNetFC[0]);
			$orderTotalTaxFC 			= str_replace(",", ".",$order->Order[0]->orderTotalTaxFC[0]);
			//$orderTotalFC 				= str_replace(",", ".",$order->Order[0]->orderTotalNetFC[0]);

		}
		else
		{
			$orderItemsTotalFC 			= str_replace(",", ".",$order->Order[0]->orderItemsTotalGrossFC[0]);
			$shippingCostsFC 			= str_replace(",", ".",$order->Order[0]->shippingCostsGrossFC[0]);
			$orderTotalTaxFC 			= str_replace(",", ".",$order->Order[0]->orderTotalTaxFC[0]);
			
		}
	$orderTotalFC 						= str_replace(",", ".",$order->Order[0]->orderTotalGrossFC[0]);
	$Currency_Code 						= (string)$order->Order[0]->Currency_Code[0];
	
	$usermail 							= (string)$order->Order[0]->usermail[0];
	
	$orderid 							= (int)$order->Order[0]->id_order[0];
	
	//GET SHOP-Title
	$shop_id 							= (int)$order->Order[0]->shop_id[0];
	$res_shop = q("SELECT * FROM shop_shops WHERE id_shop = ".$shop_id, $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows( $res_shop ) == 0 )
	{
		$shop_title = "";
	}
	else
	{
		$row_shop = mysqli_fetch_assoc( $res_shop );
		$shop_title = $row_shop["title"];
	}

	
	$postfields["PAYMENTREQUEST_0_ITEMAMT"]			= $orderItemsTotalFC;
	$postfields["PAYMENTREQUEST_0_SHIPPINGAMT"]		= $shippingCostsFC;
	
	if ((string)$order->Order[0]['type'] == "business" )
	{
		$postfields["PAYMENTREQUEST_0_TAXAMT"]			= $orderTotalTaxFC;
	}

	$postfields["PAYMENTREQUEST_0_AMT"]				= $orderTotalFC;
	$postfields["PAYMENTREQUEST_0_CURRENCYCODE"]	= $Currency_Code;
	
	$postfields["PAYMENTREQUEST_0_PAYMENTACTION"]	= "sale";
	$postfields["PAYMENTREQUEST_0_DESC"]			= $shop_title;
	$postfields["PAYMENTREQUEST_0_CUSTOM"]			= $orderid;
//	 $postfields["PAYMENTREQUEST_0_INVNUM"]			= $orderid;
	
	
	//CREATE PARAM ARRAY
	
	$callparams = array();
	$callparams["method"] 		= "DoExpressCheckoutPayment";
	$callparams["order_id"]		= $_POST["order_id"];
	$callparams["production"] 	= 0; //OPTIONAL
	$callparams["RequestData"] 	= $postfields;
	
	//print_r($callparams);

	$paypal_response = send_request( $callparams );
	

	if ( $paypal_response["Ack"] == "Success")
	{
		foreach ( $paypal_response["Response"] as $key => $value)
		{
			if ( is_numeric( $value ) )
			{
				echo "	<".$key.">".$value."</".$key.">\n";		
			}
			else
			{
				echo "	<".$key."><![CDATA[".$value."]]></".$key.">\n";		
			}
		}
	}
	else
	{
		$error = show_error($paypal_response["ErrorCode"], 14, __FILE__, __LINE__, $paypal_response["Errortext"], false);
		exit;
	}


?>