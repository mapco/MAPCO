<?php

	//*************
	//	SOA 2
	//*************
	
	$required = array( 'OrderID' => 		'numericNN',
					   'customer_id' => 	'numericNN',
					   'addresstype' =>		'text' );		
					   
	check_man_params( $required );
	
	// get order-data	
	$res = q( "SELECT * FROM shop_orders WHERE id_order=" . $_POST['OrderID'] . ' AND customer_id=' . $_SESSION['id_user'], $dbshop, __FILE__, __LINE__ );
	if( mysqli_num_rows( $res ) == 0 )
	{
		show_error( 9762, 7, __FILE__, __LINE__, "OrderID:" . $_POST["OrderID"] );
		exit;
	}
	$shop_orders = mysqli_fetch_assoc( $res );

	// set order					   
	$postdata = 				array();
	$postdata['API'] = 			'shop';
	$postdata['APIRequest'] = 	'OrderAddressUpdate';
	
	$postdata['OrderID'] = 		$_POST['OrderID'];
	$postdata['customer_id'] = 	$_POST['customer_id'];
	$postdata['addresstype'] = 	$_POST['addresstype'];
	
	$postdata['usermail'] = 	$shop_orders['usermail'];
	$postdata['userphone'] = 	$shop_orders['userphone'];
	
	$postdata['company'] = 		'';
	$postdata['firstname'] = 	'';
	$postdata['lastname'] = 	'';
	$postdata['street'] = 		'';
	$postdata['number'] = 		'';
	$postdata['additional'] = 	'';
	$postdata['zip'] = 			'';
	$postdata['city'] = 		'';
	$postdata['country_id'] = 	1;
		
	$response = soa2( $postdata, __FILE__, __LINE__ );
?>