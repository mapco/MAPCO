<?php

	//**********
	//	SOA 2
	//**********
	
	$xml = '';
	
	// get active paymenttypes
	$payment_types_active = array();
	$res = q( "SELECT * FROM shop_payment_types WHERE active=1", $dbshop, __FILE__, __LINE__ );
	while ( $shop_payment_types = mysqli_fetch_assoc( $res ) )
	{
		$payment_types_active[] = $shop_payment_types['id_paymenttype'];
	}
	
	// get active shippingtypes
	$shipping_types_active = array();
	$res5 = q( "SELECT * FROM shop_shipping_types WHERE active=1", $dbshop, __FILE__, __LINE__ );
	while ( $shop_shipping_types = mysqli_fetch_assoc( $res5 ) )
	{
		$shipping_types_active[] = $shop_shipping_types['id_shippingtype'];
	}
	
	// get shippable countries
	$res2 = q( "SELECT * FROM shop_countries", $dbshop, __FILE__, __LINE__ );
	while ( $shop_countries = mysqli_fetch_assoc( $res2 ) )
	{
		$res3 = q( "SELECT * FROM shop_payment WHERE shop_id=" . $_SESSION['id_shop'] . " AND active=1" . " AND country_id=" . $shop_countries['id_country'] . " AND paymenttype_id IN(" . implode( ',', $payment_types_active ) . ")", $dbshop, __FILE__, __LINE__ );
		while ( $shop_payment = mysqli_fetch_assoc( $res3 ) )
		{
			$res4 = q( "SELECT * FROM shop_shipping WHERE payment_id=" . $shop_payment['id_payment'] . " AND active=1 AND shippingtype_id IN (" . implode( ',', $shipping_types_active ) . ")", $dbshop, __FILE__, __LINE__ );
			if ( mysqli_num_rows( $res4 ) > 0 )
			{
				$xml .= '<shipping_country>' . "\n";
				$xml .= '	<country_id>' . $shop_countries['id_country'] . '</country_id>' . "\n";
				$xml .= '	<country>' . $shop_countries['country'] . '</country>' . "\n";
				$xml .= '</shipping_country>' . "\n";
				break 1;
			}
		}
	}
	
//	echo implode( ',', $payment_types_active );
	echo $xml;

?>