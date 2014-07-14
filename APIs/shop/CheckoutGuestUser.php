<?php

	/*
	*	SOA 2
	*/
	
	include("../functions/cms_createPassword.php");
	
	$required = array( 'usermail' => 	'textNN',
					   'firstname' => 	'text',
					   'lastname' => 	'text',
					   'street' => 		'text',
					   'number' => 		'text',
					   'zip' => 		'text',
					   'city' => 		'text',
					   'country_id' => 	'text' );
	
	check_man_params( $required );
	
	
	$checkout_user_id = 0;
	
	// guest-user already exist?
	$res = q( "SELECT * FROM cms_users AS a, cms_users_sites as b  WHERE a.guest=1 AND a.active=1 AND a.usermail='" . $_POST['usermail'] . "' AND a.id_user=b.user_id AND site_id=" . $_SESSION['id_site'], $dbweb, __FILE__, __LINE__ );
	if ( mysqli_num_rows( $res ) > 0 )
	{
		$cms_users = mysqli_fetch_assoc( $res );
		
		$checkout_user_id = $cms_users['id_user'];
		$_SESSION['checkout_user_id'] = $checkout_user_id;		
	}
	else
	{
		// create new guest-user
		$salt = createPassword( 32 );
		$pw = 	createPassword( 20 );
		$pw = 	md5( $pw );
		$pw = 	md5( $pw . $salt );
		$pw = 	md5( $pw . PEPPER );
		
		$data = 				array();
		$data['username'] = 	'guest_' . $_POST['usermail'];
		$data['usermail'] = 	$_POST['usermail'];
		$data['firstname'] = 	$_POST['firstname'];
		$data['lastname'] = 	$_POST['lastname'];
		$data['password'] = 	mysqli_real_escape_string( $dbweb, $pw );
		$data['user_token'] = 	mysqli_real_escape_string( $dbweb, createPassword( 50 ) );
		$data['user_salt'] = 	mysqli_real_escape_string( $dbweb, $salt );
		$data['userrole_id'] = 	5;
		$data['guest'] = 		1;
		$data['language_id'] = 	1;
		$data['active'] = 		1;
		$data['firstmod'] = 	time();
		$data['lastmod'] = 		time();
		
		$response = q_insert( 'cms_users', $data, $dbweb, __FILE__, __LINE__ );
		
		// cms_users_sites
		$checkout_user_id = mysqli_insert_id( $dbweb );
		
		$data = 					array();
		$data['user_id'] = 			$checkout_user_id;
		$data['site_id'] = 			$_SESSION['id_site'];
		$data['firstmod'] = 		time();
		$data['firstmod_user'] = 	$checkout_user_id;
		$data['lastmod'] = 			time();
		$data['lastmod_user'] = 	$checkout_user_id;
		
		$response = q_insert( 'cms_users_sites', $data, $dbweb, __FILE__, __LINE__ );
		
	}
	
	echo '<checkout_user_id>' . $checkout_user_id . '</checkout_user_id>' . "\n";
	
	// set guest, customer_id in shop_orders
	$data = 				array();
	$data['customer_id'] = 	$checkout_user_id;
	$data['guest'] = 		1;
	
	$where = 'WHERE id_order=' . $_SESSION['checkout_order_id'];
	
	$res = q_update( 'shop_orders', $data, $where, $dbshop, __FILE__, __LINE__ );
	
	// set bill_adr_id
	$post_data = 				array();
	$post_data['API'] = 		"shop";
	$post_data['APIRequest'] = 	"OrderAddressUpdate";

	$post_data['addresstype'] = 'bill';
	$post_data['OrderID'] = 	$_SESSION['checkout_order_id'];
	$post_data['customer_id'] = $checkout_user_id;
	$post_data['company'] = 	'';
	$post_data['gender'] = 		'';
	$post_data['title'] = 		'';
	$post_data['firstname'] = 	$_POST['firstname'];
	$post_data['lastname'] = 	$_POST['lastname'];
	$post_data['street'] = 		$_POST['street'];
	$post_data['number'] = 		$_POST['number'];
	$post_data['additional'] = 	'';
	$post_data['zip'] = 		$_POST['zip'];
	$post_data['city'] = 		$_POST['city'];
	$post_data['country_id'] = 	$_POST['country_id'];
	
	$response = soa2( $post_data, __FILE__, __LINE__ );
	
?>