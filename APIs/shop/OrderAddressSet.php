<?php

	//*************
	//	SOA 2
	//*************
	
	$required = array( 'OrderID' => 		'numericNN',
					   'customer_id' => 	'numericNN',
					   'addresstype' =>		'text',				// bill, ship, both
					   'adr_id' =>			'numericNN' );		
					   
	check_man_params( $required );
	
	// get order-data	
	$res = q( "SELECT * FROM shop_orders WHERE id_order=" . $_POST['OrderID'] . ' AND customer_id=' . $_SESSION['id_user'], $dbshop, __FILE__, __LINE__ );
	if( mysqli_num_rows( $res ) == 0 )
	{
		show_error( 9762, 7, __FILE__, __LINE__, "OrderID:" . $_POST["OrderID"] );
		exit;
	}
	$shop_orders = mysqli_fetch_assoc( $res );
	
	// get address-data
	$res2 = q( "SELECT * FROM shop_bill_adr WHERE adr_id=" . $_POST['adr_id'], $dbshop, __FILE__, __LINE__ );
	if( mysqli_num_rows( $res ) == 0 )
	{
		show_error( 11365, 7, __FILE__, __LINE__, 'adr_id: ' . $_POST['adr_id'] );
		exit;
	}
	$shop_bill_adr = mysqli_fetch_assoc( $res2 );
	
	// set order					   
	$postdata = 				array();
	$postdata['API'] = 			'shop';
	$postdata['APIRequest'] = 	'OrderAddressUpdate';
	
	$postdata['OrderID'] = 		$_POST['OrderID'];
	$postdata['customer_id'] = 	$_POST['customer_id'];
	$postdata['addresstype'] = 	$_POST['addresstype'];
	
	$postdata['usermail'] = 	$shop_orders['usermail'];
	$postdata['userphone'] = 	$shop_orders['userphone'];
	
	$postdata['company'] = 		$shop_bill_adr['company'];
	$postdata['firstname'] = 	$shop_bill_adr['firstname'];
	$postdata['lastname'] = 	$shop_bill_adr['lastname'];
	$postdata['street'] = 		$shop_bill_adr['street'];
	$postdata['number'] = 		$shop_bill_adr['number'];
	$postdata['additional'] = 	$shop_bill_adr['additional'];
	$postdata['zip'] = 			$shop_bill_adr['zip'];
	$postdata['city'] = 		$shop_bill_adr['city'];
	$postdata['country_id'] = 	$shop_bill_adr['country_id'];
		
	$response = soa2( $postdata, __FILE__, __LINE__ );
?>