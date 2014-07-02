<?php

	// SOA2 SERVICE

	$required = array();
	$required["paypal_token"] 	= "textNN";
	$required["order_id"] 		= "numericNN";
	check_man_params($required);

	require_once 'functions/PayPal_Functions.php';


	$postfields = array();
	$postfields["TOKEN"] 		= $_POST["paypal_token"];

	$callparams = array();
	$callparams["method"] 		= "GetExpressCheckoutDetails";
	$callparams["order_id"]		= $_POST["order_id"];
	$callparams["production"]	= 0; //OPTIONAL
	$callparams["RequestData"] 	= $postfields;

	$paypal_response = send_request( $callparams );
//	print_r( $paypal_response );
	
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
	

?>