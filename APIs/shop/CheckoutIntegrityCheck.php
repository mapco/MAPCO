<?php

	/*
	*	SOA2-SERVICE
	*/
	
	$required = array( 'checkout_order_id' => 'numericNN' );
	
	check_man_params( $required );
	
	$res = q( "SELECT * FROM shop_orders WHERE id_order=" . $_POST['checkout_order_id'], $dbshop, __FILE__, __LINE__ );
	if ( mysqli_num_rows( $res ) == 0 )
	{
		show_error( 9900, 7, __FILE__, __LINE__, 'Checkout-Order_ID: ' . $_POST['checkout_order_id'] );
		exit;
	}
	$shop_orders = mysqli_fetch_assoc( $res );
	
	$checkout_adr_edit = 		1;
	$checkout_payment_edit = 	1;
	$checkout_shipping_edit = 	1;
 	
	if ( $shop_orders['bill_adr_id'] > 0 )
	{
		$checkout_adr_edit = 0;
	}
	
	if ( $checkout_adr_edit == 0 and $shop_orders['payments_type_id'] > 0 )
	{
		$adr_id = 0;
		if ( $shop_orders['ship_adr_id'] > 0 )
		{
			$adr_id = $shop_orders['ship_adr_id'];
		}
		else
		{
			$adr_id = $shop_orders['bill_adr_id'];
		}
		
		$res2 = 			q( "SELECT * FROM shop_bill_adr WHERE adr_id=" . $adr_id, $dbshop, __FILE__, __LINE__ );
		$shop_bill_adr = 	mysqli_fetch_assoc( $res2 );
		$country_id = 		$shop_bill_adr['country_id'];
		
		$res3 = q( "SELECT * FROM shop_payment WHERE shop_id=" . $shop_orders['shop_id'] . " AND country_id=" . $country_id . " AND paymenttype_id=" . $shop_orders['payments_type_id'], $dbshop, __FILE__, __LINE__ );
		if ( mysqli_num_rows( $res3 ) > 0 )
		{
			$checkout_payment_edit = 0;
		}
	}
	
	if ( $checkout_adr_edit == 0 and $checkout_payment_edit == 0 and $shop_orders['shipping_type_id'] > 0 )
	{
		$shop_payment = mysqli_fetch_assoc( $res3 );
		
		$res4 = q( "SELECT * FROM shop_shipping WHERE payment_id=" . $shop_payment['id_payment'], $dbshop, __FILE__, __LINE__ );
		
		while ( $shop_shipping = mysqli_fetch_assoc( $res4 ) )
		{
			if ( $shop_orders['shipping_type_id'] == $shop_shipping['shippingtype_id'] and $shop_orders['shipping_net'] == $shop_shipping['price'] and strpos( $shop_orders['shipping_details'], $shop_shipping['shipping'] ) !== false )
			{
				$checkout_shipping_edit = 0;
			}
		}
	}
	
	$xml = '';
	
	$xml .= '<checkout_adr_edit>' . $checkout_adr_edit . '</checkout_adr_edit>' . "\n";
	$xml .= '<checkout_payment_edit>' . $checkout_payment_edit . '</checkout_payment_edit>' . "\n";
	$xml .= '<checkout_shipping_edit>' . $checkout_shipping_edit . '</checkout_shipping_edit>' . "\n";
	
	echo $xml;
	
?>