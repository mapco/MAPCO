<?php

	/*
	*	SOA2-SERVICE
	*/
	
	include( '../functions/shop_get_prices.php' );
	
	if ( isset( $_SESSION['checkout_order_id'] ) and isset( $_SESSION['id_user'] ) )
	{
		$res2 = q( "SELECT * FROM shop_orders WHERE id_order=" . $_SESSION['checkout_order_id'], $dbshop, __FILE__, __LINE__ );
		if ( mysqli_num_rows( $res2 ) == 1 )
		{
			$shop_orders = mysqli_fetch_assoc( $res2 );
			if ( $shop_orders['status_id'] == 0 and $shop_orders['ordertype_id'] != 5 )
			{
				$res = q( "SELECT * FROM shop_orders_items WHERE order_id=" . $_SESSION['checkout_order_id'], $dbshop, __FILE__, __LINE__ );
				while ( $shop_orders_items = mysqli_fetch_assoc( $res ) )
				{
					if ( $shop_orders_items['item_id'] != 28093 )
					{
						$price = get_prices( $shop_orders_items["item_id"], $shop_orders_items["amount"] );
						
						$data = array();
						$data['price'] = round( $price["total"], 2 );
						$data['netto'] = round( $price["net"], 2 );
						
						$where = 'WHERE id=' . $shop_orders_items['id'];
						
						$result = q_update( 'shop_orders_items', $data, $where, $dbshop, __FILE__, __LINE__ );
					}
				}
			}
		}
	}

?>