<?php

	/*
	*	SOA 2 SERVICE
	*/
	
	include("../functions/shop_get_prices.php");
	
	// is set $_SESSION['checkout_order_id'] ?
	$checkout_order_id = 0;
	if ( isset( $_SESSION['checkout_order_id'] ) )
	{
		$checkout_order_id = $_SESSION['checkout_order_id'];
	}
	
	// get_order_id
	if ( isset( $_POST['get_id_order'] ) and $_POST['get_id_order'] > 0 and isset( $_SESSION['id_user'] ) )
	{
		// check, if get_id_order is editable
		$res16 = q( "SELECT * FROM shop_orders WHERE id_order=" . $_POST['get_id_order'], $dbshop, __FILE__, __LINE__ );
		if ( mysqli_num_rows( $res16 ) == 1 )
		{
			$shop_orders_2 = mysqli_fetch_assoc( $res16 );
			if ( $shop_orders_2['status_id'] == 0 or $shop_orders_2['status_id'] == 1 )
			{
				if ( $checkout_order_id != $_POST['get_id_order'] )
				{
					// set new checkout-order & write items from order to shop_carts
					$checkout_order_id = $_POST['get_id_order'];
					
//					$_SESSION['checkout_order_id'] = $checkout_order_id;
					
					$res13 = q( "SELECT * FROM shop_carts WHERE order_id=" . $checkout_order_id, $dbshop, __FILE__, __LINE__ );
					if ( mysqli_num_rows( $res13 ) == 0 )
					{
						$res14 = q( "SELECT * FROM shop_orders_items WHERE order_id=". $checkout_order_id, $dbshop, __FILE__, __LINE__ );
						while ( $shop_orders_items = mysqli_fetch_assoc( $res14 ) )
						{
							// write items in shop_carts
							$data = 				array();
							$data['item_id'] = 		$shop_orders_items['item_id'];
							$data['amount'] = 		$shop_orders_items['amount'];
							$data['shop_id'] = 		$shop_orders_2['shop_id'];
							$data['session_id'] = 	session_id();
							$data['user_id'] = 		$_SESSION['id_user'];
							$data['order_id'] = 	$checkout_order_id;
							$data['lastmod'] = 		time();
							
							$result = q_insert( 'shop_carts', $data, $dbshop, __FILE__, __LINE__ );
						}
					}
				}
			}
		}
	}
	
	// are entries in shop_carts?
	if ( isset( $_SESSION['id_user'] ) )
	{
		$res = q( "SELECT * FROM shop_carts WHERE user_id=" . $_SESSION['id_user'] . " AND shop_id=" . $_SESSION['id_shop'] . " AND order_id=0", $dbshop, __FILE__, __LINE__ );
	}
	else
	{
		$res = q( "SELECT * FROM shop_carts WHERE session_id='" . session_id() . "' AND shop_id=" . $_SESSION['id_shop'] . " AND order_id=0", $dbshop, __FILE__, __LINE__ );
	}
	if ( mysqli_num_rows( $res ) > 0 )
	{
		if ( $checkout_order_id == 0 ) // if there is no checkout-order
		{	
			// add order
			$post_data = 					array();
			$post_data['API'] = 			'shop';
			$post_data['APIRequest'] = 		'OrderAdd';
			$post_data['mode'] = 			'shop';
			$post_data['shop_id'] = 		$_SESSION['id_shop'];
			$post_data['ordertype_id'] = 	1;
			$post_data['firstmod'] =		time();
			$post_data['lastmod'] = 		time();
			
			$post_data['order_note'] = 'checkout-test'; // später wieder raus - nur zum testen
			
			if ( isset( $_SESSION['id_user'] ) )
			{
				$post_data['customer_id'] = 	$_SESSION['id_user'];
				$post_data['firstmod_user'] = 	$_SESSION['id_user'];
				$post_data['lastmod_user'] = 	$_SESSION['id_user'];
				
				// mail, fax &c.
				$res2 = q( "SELECT * FROM shop_orders WHERE customer_id=" . $_SESSION['id_user'] . " AND shop_id=" . $_SESSION['id_shop'] . " ORDER BY id_order DESC", $dbshop, __FILE__, __LINE__ );
				if ( mysqli_num_rows( $res2 ) > 0 )
				{
					$shop_orders = mysqli_fetch_assoc( $res2 );
					$post_data['usermail'] = 	$shop_orders['usermail'];
					$post_data['userphone'] = 	$shop_orders['userphone'];
					$post_data['userfax'] = 	$shop_orders['userfax'];
					$post_data['usermobile'] = 	$shop_orders['usermobile'];
				}
				else
				{
					$res3 = q( "SELECT * FROM cms_users WHERE id_user=" . $_SESSION['id_user'], $dbweb, __FILE__, __LINE__ );
					if ( mysqli_num_rows( $res3 ) > 0 )
					{
						$cms_users = mysqli_fetch_assoc( $res3 );
						$post_data['usermail'] = $cms_users['usermail'];
					}
				}
				
				// bill-address
				$res4 = q( "SELECT * FROM shop_bill_adr WHERE user_id=" . $_SESSION['id_user'] . " AND standard=1 AND shop_id=" . $_SESSION['id_shop'], $dbshop, __FILE__, __LINE__ );
				if ( mysqli_num_rows( $res4 ) > 0 )
				{
					$shop_bill_adr = mysqli_fetch_assoc( $res4 );
					$post_data['bill_company'] = 	$shop_bill_adr['company'];
					$post_data['bill_gender'] = 	$shop_bill_adr['gender'];
					$post_data['bill_title'] = 		$shop_bill_adr['title'];
					$post_data['bill_firstname'] = 	$shop_bill_adr['firstname'];
					$post_data['bill_lastname'] = 	$shop_bill_adr['lastname'];
					$post_data['bill_zip'] = 		$shop_bill_adr['zip'];
					$post_data['bill_city'] = 		$shop_bill_adr['city'];
					$post_data['bill_street'] = 	$shop_bill_adr['street'];
					$post_data['bill_number'] = 	$shop_bill_adr['number'];
					$post_data['bill_additional'] = $shop_bill_adr['additional'];
					$post_data['bill_country'] = 	$shop_bill_adr['country'];
					// get country code
					$res5 = q( "SELECT * FROM shop_countries WHERE id_country=" . $shop_bill_adr['country_id'], $dbshop, __FILE__, __LINE__ );
					if ( mysqli_num_rows( $res5 ) == 1 )
					{
						$shop_countries = mysqli_fetch_assoc( $res5 );
						$post_data['bill_country_code'] = $shop_countries['country_code'];
					}
					$post_data['bill_adr_id'] = 	$shop_bill_adr['adr_id'];
				}
				else
				{
					// data from last order
					$res6 = q( "SELECT * FROM shop_orders WHERE customer_id=" . $_SESSION['id_user'] . " AND shop_id=" . $_SESSION['id_shop'] . " ORDER BY id_order DESC", $dbshop, __FILE__, __LINE__ );
					if ( mysqli_num_rows( $res6 ) > 0 )
					{
						$shop_orders = mysqli_fetch_assoc( $res6 );
						if ( $shop_orders['bill_adr_id'] > 0 )
						{
							$res7 = q( "SELECT * FROM shop_bill_adr WHERE adr_id=" . $shop_orders['bill_adr_id'], $dbshop, __FILE__, __LINE__ );
							if ( mysqli_num_rows( $res7 ) > 0 )
							{
								$shop_bill_adr = mysqli_fetch_assoc( $res7 );
								$post_data['bill_company'] = 	$shop_bill_adr['company'];
								$post_data['bill_gender'] = 	$shop_bill_adr['gender'];
								$post_data['bill_title'] = 		$shop_bill_adr['title'];
								$post_data['bill_firstname'] = 	$shop_bill_adr['firstname'];
								$post_data['bill_lastname'] = 	$shop_bill_adr['lastname'];
								$post_data['bill_zip'] = 		$shop_bill_adr['zip'];
								$post_data['bill_city'] = 		$shop_bill_adr['city'];
								$post_data['bill_street'] = 	$shop_bill_adr['street'];
								$post_data['bill_number'] = 	$shop_bill_adr['number'];
								$post_data['bill_additional'] = $shop_bill_adr['additional'];
								$post_data['bill_country'] = 	$shop_bill_adr['country'];
								// get country code
								$res5 = q( "SELECT * FROM shop_countries WHERE id_country=" . $shop_bill_adr['country_id'], $dbshop, __FILE__, __LINE__ );
								if ( mysqli_num_rows( $res5 ) == 1 )
								{
									$shop_countries = mysqli_fetch_assoc( $res5 );
									$post_data['bill_country_code'] = $shop_countries['country_code'];
								}
								$post_data['bill_adr_id'] = 	$shop_bill_adr['adr_id'];
							}
						}
					}
				}
				
				// ship-address
				$res8 = q( "SELECT * FROM shop_bill_adr WHERE user_id=" . $_SESSION['id_user'] . " AND standard_ship_adr=1 AND shop_id=" . $_SESSION['id_shop'], $dbshop, __FILE__, __LINE__ );
				if ( mysqli_num_rows( $res8 ) > 0 )
				{
					$shop_bill_adr = mysqli_fetch_assoc( $res8 );
					$post_data['ship_company'] = 	$shop_bill_adr['company'];
					$post_data['ship_gender'] = 	$shop_bill_adr['gender'];
					$post_data['ship_title'] = 		$shop_bill_adr['title'];
					$post_data['ship_firstname'] = 	$shop_bill_adr['firstname'];
					$post_data['ship_lastname'] = 	$shop_bill_adr['lastname'];
					$post_data['ship_zip'] = 		$shop_bill_adr['zip'];
					$post_data['ship_city'] = 		$shop_bill_adr['city'];
					$post_data['ship_street'] = 	$shop_bill_adr['street'];
					$post_data['ship_number'] = 	$shop_bill_adr['number'];
					$post_data['ship_additional'] = $shop_bill_adr['additional'];
					$post_data['ship_country'] = 	$shop_bill_adr['country'];
					// get country code
					$res9 = q( "SELECT * FROM shop_countries WHERE id_country=" . $shop_bill_adr['country_id'], $dbshop, __FILE__, __LINE__ );
					if ( mysqli_num_rows( $res9 ) == 1 )
					{
						$shop_countries = mysqli_fetch_assoc( $res9 );
						$post_data['ship_country_code'] = $shop_countries['country_code'];
					}
					$post_data['ship_adr_id'] = 	$shop_bill_adr['adr_id'];
				}
				else
				{
					// data from last order
					$res10 = q( "SELECT * FROM shop_orders WHERE customer_id=" . $_SESSION['id_user'] . " AND shop_id=" . $_SESSION['id_shop'] . " ORDER BY id_order DESC", $dbshop, __FILE__, __LINE__ );
					if ( mysqli_num_rows( $res10 ) > 0 )
					{
						$shop_orders = mysqli_fetch_assoc( $res10 );
						if ( $shop_orders['ship_adr_id'] > 0 )
						{
							$res11 = q( "SELECT * FROM shop_bill_adr WHERE adr_id=" . $shop_orders['ship_adr_id'], $dbshop, __FILE__, __LINE__ );
							if ( mysqli_num_rows( $res11 ) > 0 )
							{
								$shop_bill_adr = mysqli_fetch_assoc( $res11 );
								$post_data['ship_company'] = 	$shop_bill_adr['company'];
								$post_data['ship_gender'] = 	$shop_bill_adr['gender'];
								$post_data['ship_title'] = 		$shop_bill_adr['title'];
								$post_data['ship_firstname'] = 	$shop_bill_adr['firstname'];
								$post_data['ship_lastname'] = 	$shop_bill_adr['lastname'];
								$post_data['ship_zip'] = 		$shop_bill_adr['zip'];
								$post_data['ship_city'] = 		$shop_bill_adr['city'];
								$post_data['ship_street'] = 	$shop_bill_adr['street'];
								$post_data['ship_number'] = 	$shop_bill_adr['number'];
								$post_data['ship_additional'] = $shop_bill_adr['additional'];
								$post_data['ship_country'] = 	$shop_bill_adr['country'];
								// get country code
								$res12 = q( "SELECT * FROM shop_countries WHERE id_country=" . $shop_bill_adr['country_id'], $dbshop, __FILE__, __LINE__ );
								if ( mysqli_num_rows( $res12 ) == 1 )
								{
									$shop_countries = mysqli_fetch_assoc( $res12 );
									$post_data['ship_country_code'] = $shop_countries['country_code'];
								}
								$post_data['ship_adr_id'] = 	$shop_bill_adr['adr_id'];
							}
						}
					}
				}
			}
			
			$postdata = http_build_query( $post_data );
			
			$response = soa2( $postdata, __FILE__, __LINE__ );
			
			$checkout_order_id = (int)$response->id_order[0];
			
//			session_start();
//			$_SESSION['checkout_order_id'] = $checkout_order_id;
	
		}
		
		// syncronize order-items
		while ( $shop_carts = mysqli_fetch_assoc( $res ) )
		{
			$res15 = q( "SELECT * FROM shop_orders_items WHERE order_id=" . $checkout_order_id . " AND item_id=" . $shop_carts['item_id'], $dbshop, __FILE__, __LINE__ );
			if ( mysqli_num_rows( $res15 ) > 0 )
			{
				// add amounts
				$shop_orders_items_2 = 	mysqli_fetch_assoc( $res15 );
				
				$data = 				array();
				$data['amount'] = 		$shop_orders_items_2['amount'] + $shop_carts['amount'];
				
				$where = 				'WHERE id=' . $shop_orders_items_2['id'];
				
				$result = q_update( 'shop_orders_items', $data, $where, $dbshop, __FILE__, __LINE__ );
				
				// syncronize with shop_cart and delete
				$res17 = q( "SELECT * FROM shop_carts WHERE order_id=" . $checkout_order_id . " AND item_id=" . $shop_carts['item_id'], $dbshop, __FILE__, __LINE__ );
				if ( mysqli_num_rows( $res17 ) == 1 )
				{
					$shop_carts_2 = mysqli_fetch_assoc( $res17 );
					
					$data = 			array();
					$data['amount'] = 	$shop_carts['amount'] + $shop_carts_2['amount'];
					
					$where = 			'WHERE id_carts=' . $shop_carts_2['id_carts'];
					
					$result = 			q_update( 'shop_carts', $data, $where, $dbshop, __FILE__, __LINE__ );
					
					$result = 			q( "DELETE FROM shop_carts WHERE id_carts=" . $shop_carts['id_carts'], $dbshop, __FILE__, __LINE__ );
				}
			}
			else
			{
				// insert into shop_orders_items
				$price = get_prices( $shop_carts["item_id"], $shop_carts["amount"] );
				
				$data = 				array();
				$data["API"] = 			"shop";
				$data["APIRequest"] = 	"OrderItemAdd";
				$data["mode"] = 		"shop";

				if ( $shop_carts["item_id"] != "30781" and $shop_carts["item_id"] != "30702" )
				{
					$data["order_id"] = 			$checkout_order_id;
					$data["item_id"] = 				$shop_carts["item_id"];
					$data["amount"] = 				$shop_carts["amount"];
					$data["price"] = 				round( $price["total"], 2 );
					$data["netto"] = 				round( $price["net"], 2 );
					$data["collateral"] = 			round( $price["collateral_total"], 2 );
					$data["Currency_Code"] = 		'EUR';
					$data["exchange_rate_to_EUR"] = 1;
					$data["customer_vehicle_id"] = 	$shop_carts["customer_vehicle_id"];
				}
				elseif ( $shop_carts["item_id"] == "30781" or $shop_carts["item_id"] == "30702" )
				{
					$data["order_id"] = 			$checkout_order_id;
					$data["item_id"] = 				$shop_carts["item_id"];
					$data["amount"] = 				1;
					$data["price"] = 				0;
					$data["netto"] = 				0;
					$data["collateral"] = 			round( $price["collateral_total"], 2 );
					$data["Currency_Code"] = 		'EUR';
					$data["exchange_rate_to_EUR"] = 1;
					$data["customer_vehicle_id"] = 	0;
				}
				
				$response = soa2( $data, __FILE__, __LINE__ );
								
				if ( $response->Ack[0] != "Success" )
				{
					show_error( 9773, 7, __FILE__, __LINE__, print_r( $data ) );
					exit;
				}
				
				unset($response);
				
				// todo: set order_id in shop_carts
				$data = 			array();
				$data['order_id'] = $checkout_order_id;
				
				$where = 			'WHERE id_carts=' . $shop_carts['id_carts'];
				
				$result = 			q_update( 'shop_carts', $data, $where, $dbshop, __FILE__, __LINE__ );
			}	
		}			
	}
	
	session_start();
	$_SESSION['checkout_order_id'] = $checkout_order_id;
	
	// return-values
	$xml = '';
	$xml .= '<checkout_order_id>' . $checkout_order_id . '</checkout_order_id>' . "\n";
	
	echo $xml;

?>