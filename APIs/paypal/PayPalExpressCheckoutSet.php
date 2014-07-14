<?php

	// SOA2 SERVICE

	$required = array();
	$required["order_id"] 	= "numericNN";
	check_man_params($required);

	require_once 'functions/PayPal_Functions.php';
	require_once '../../mapco_shop_de/functions/cms_tl.php';


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
	//$postfields["PAYMENTREQUEST_0_INVNUM"]			= $orderid;
	
	$postfields["REQCONFIRMSHIPPING"]				= 0; //PayPal Confirmed Shipping Address Required
	$postfields["PAYMENTREQUEST_0_PAYMENTREASON"]	= "none"; //NO specific type (Other Value only: "Refund")
	$postfields["ALLOWNOTE"]						= 1; // BUYER CAN GIVE NOTE TO SELLER
	
	$postfields["EMAIL"]							= $usermail;
	// Locale of pages displayed by PayPal during Express Checkout.
	$postfields["LOCALECODE"]						= $address["Country"];

	//RETURN LINKS
	//$postfields["RETURNURL"]						= PATHLANG."online-shop/warenkorb/payment/";
		$postfields["RETURNURL"]						= PATHLANG.tl( 847, 'alias' )."paypal/success";
	//$postfields["CANCELURL"]						= PATHLANG."online-shop/warenkorb/abort/";
		$postfields["CANCELURL"]						= PATHLANG.tl( 847, 'alias' )."paypal/cancel";
	
	//CREATE PARAM ARRAY
	
/*	
	print_r($postfields);
	exit;
*/
	$callparams = array();
	$callparams["method"]		 = "SetExpressCheckout";
	$callparams["order_id"]		 = $_POST["order_id"];
	$callparams["production"]	 = 0; //OPTIONAL
	$callparams["RequestData"]	 = $postfields;
	
	$paypal_response = send_request( $callparams );

	//FOR PAYPAL URL
	$callparams["production"]	= 0; //OPTIONAL
	$callparams["shop_id"]	 	= (int)$order->Order[0]->shop_id[0];

	$account_data = accountDataGet($callparams);
	
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
		echo " <paypal_url><![CDATA[".$account_data["PAYPAL_URL"].$paypal_response["Response"]["TOKEN"]."]]></paypal_url>\n";
	}
	else
	{
		$error = show_error($paypal_response["ErrorCode"], 14, __FILE__, __LINE__, $paypal_response["Errortext"], false);
		exit;
	}
	
//	echo "https://www.sandbox.paypal.com/webscr&amp;cmd=_express-checkout&amp;token=".$paypal_response["Response"]["TOKEN"];


/*

class Authentification
{
	public $Username = null;
	public $Password = null;
	public $Signature = null;
	//public $Subject = null;

	public function __construct($Username, $Password, $Signature)
  	{
		$this->Username = $Username;
		$this->Password = $Password;
		$this->Signature = $Signature;
		//$this->Subject = $Subject;
	}

}

	//$client = new SoapClient("https://www.sandbox.paypal.com/wsdl/PayPalSvc.wsdl", array( 'soap_version' => SOAP_1_1 ));
	$client = new SoapClient("https://www.paypal.com/wsdl/PayPalSvc.wsdl", array( 'soap_version' => SOAP_1_1, 'cache_wsdl' =>  WSDL_CACHE_NONE, "trace" => 1 ));
	//$client = new SoapClient("https://www.paypal.com/wsdl/PayPalSvc.wsdl"); 



	//$auth = new Authentification(API_USERNAME, API_PASSWORD, API_SIGNATURE);
	
	$cred = array( 'Username' => 'nputzi_1357220940_biz_api1.mapco.de',
               'Password' => '1357220955',
               'Signature' => 'AFcWxV21C7fd0v3bYYYRCpSSRl31ARvCmT4HK0jBsx4MSoIadIdvMIuo' );


	$Credentials = new stdClass();
	$Credentials->Credentials = new SoapVar( $cred, SOAP_ENC_OBJECT, 'Credentials' );
	
	$header = new SoapHeader('https://api-3t.sandbox.paypal.com/2.0/','RequesterCredentials', $cred, false);
//	var_dump($header);
	
	//$client->__setSoapHeaders($header);
	
		
	
	$headers = new SoapVar( $Credentials,
							SOAP_ENC_OBJECT,
							'Credentials',
							'urn:ebay:apis:eBLBaseComponents' );
	
//	$client->__setSoapHeaders( new SoapHeader( 'urn:ebay:api:PayPalAPI',
	//										   'RequesterCredentials',
		//									   $headers ));
		//$client->__setSoapHeaders($header);
//	var_dump($client);

$args = array( 'Version' => '71.0', 'ReturnAllCurrencies' => '1' );

$GetBalanceRequest = new stdClass();
$GetBalanceRequest->GetBalanceRequest = new SoapVar( $args,
                                                     SOAP_ENC_OBJECT,
                                                     'GetBalanceRequestType',
                                                     'urn:ebay:api:PayPalAPI' );

//	echo $client->GetBalance($GetBalanceRequest);
$params = new SoapVar( $GetBalanceRequest, SOAP_ENC_OBJECT, 'GetBalanceRequest' );
	$xml = ' <?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
 xmlns:ns1="urn:ebay:apis:eBLBaseComponents" xmlns:ns2="urn:ebay:api:PayPalAPI"
 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
 <SOAP-ENV:Header>
  <ns2:RequesterCredentials>
   <ns1:Credentials xsi:type="Credentials">
    <Username>'.API_USERNAME.'</Username>
    <Password>'.API_PASSWORD.'</Password>
    <Signature>'.API_SIGNATURE.'</Signature>
   </ns1:Credentials>
  </ns2:RequesterCredentials>
 </SOAP-ENV:Header>
 <SOAP-ENV:Body>
  <ns2:GetBalanceReq xsi:type="GetBalanceRequest">
   <GetBalanceRequest xsi:type="ns2:GetBalanceRequestType">
    <ns1:Version>71.0</ns1:Version>
    <ns2:ReturnAllCurrencies>1</ns2:ReturnAllCurrencies>
   </GetBalanceRequest>
  </ns2:GetBalanceReq>
 </SOAP-ENV:Body>
</SOAP-ENV:Envelope> ';
//echo $xml;

try
{
		$response = $client->GetBalance($GetBalanceRequest);

	//$result = $client->GetBalance($xml);
}
catch(Exception $e)
{
	var_dump($e);
}

echo 'Balance is: ', $result->Balance->_, $result->Balance->currencyID;
exit;
*/

?>