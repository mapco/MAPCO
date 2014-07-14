<?php

	/*
	*	SOA2-SERVICE
	*/
	
	include("../functions/mapco_gewerblich.php");

	$insert = false;
	
	$res_users = q("SELECT id_user, usermail FROM cms_users LIMIT 500;", $dbweb, __FILE__, __LINE__);
	while ($row_users = mysqli_fetch_assoc($res_users))
	{
		$res_customer = q( $select_crm_customer."SELECT id_crm_customer, user_id FROM crm_customers WHERE user_id=".$row_users[ 'user_id' ]." OR mail='".$row_users['usermail']."' LIMIT 1;", $dbweb, __FILE__, __LINE__ );
		if ( mysqli_num_rows( $res_customer ) > 0 )
		{
			$row_customer = mysqli_fetch_assoc($res_customer);
			if ( $row_customer['user_id'] == 0 )
			{
				$data = array();
				$data[ 'user_id' ] = $_POST[ 'user_id' ];
				q_update( 'crm_customers', $data, $where, $dbweb, __FILE__, __LINE__ );
			}
		}
		else
		{
			// aus shop_orders
			$res_order = q( "SELECT DISTINCT customer_id FROM shop_orders WHERE usermail=" . $row_users[ 'usermail' ] . " ORDER BY id_order DESC", $dbshop, __FILE__, __LINE__ );
			if ( mysqli_num_rows( $res_order ) == 1 )
			{
				$row_order = mysqli_fetch_assoc($res_order);
				$data[ 'user_id' ] = $row_order[ 'customer_id' ];
				q_update( 'crm_customers', $data, $where, $dbweb, __FILE__, __LINE__ );
			}			
		}
	}

		{
			$insert = true;
		}
		
		if ( $insert == true )
		{
			// aus shop_orders
			$res = q( "SELECT userphone, usermobile, userfax FROM shop_orders WHERE customer_id=" . $_POST[ 'user_id' ] . " ORDER BY id_order DESC", $dbshop, __FILE__, __LINE__ );
			if ( mysqli_num_rows( $res ) >0 ) {
				$shop_orders = mysqli_fetch_assoc( $res );
				
				$where ="WHERE ";
				if ($shop_orders[ 'userphone' ] != '')
				{
					$data[ 'phone' ] = 	$shop_orders[ 'userphone' ]; 
					$where .= "phone='" . $data[ 'phone' ] . "'";
					$res = q( $select_crm_customer." ".$where, $dbweb, __FILE__, __LINE__ );
				}
				if ($shop_orders[ 'usermobile' ] != '')
				{
					$data[ 'mobile' ] = $shop_orders[ 'usermobile' ];
					$where .= "WHERE mobile='" . $data[ 'mobile' ] . "'";
					$res = q( $select_crm_customer." ".$where, $dbweb, __FILE__, __LINE__ );
				}
				if ($shop_orders[ 'userfax' ] != '')
				{
					$data[ 'fax' ] = $shop_orders[ 'userfax' ];
					$where .= "WHERE fax='" . $data[ 'fax' ] . "'";
					$res = q( $select_crm_customer." ".$where, $dbweb, __FILE__, __LINE__ );
				}

				$where = "WHERE  OR mobile='" . $data[ 'mobile' ] . "' OR fax='" . $data[ 'fax' ] . "'";
				$res = q( "SELECT id_crm_customer FROM crm_customers ".$where, $dbweb, __FILE__, __LINE__ );
				if ( mysqli_num_rows( $res2 ) > 0 )
				{
					while ($row = mysqli_fetch_assoc($res))
					{ 
						$entrie[] = $row; 
					}
					q_update( 'crm_customers', $data, $where, $dbweb, __FILE__, __LINE__ );
				}
				else
				{
					
				}
			}
			$insert = true;
		}
	}
	else
	{
		while ($row = mysqli_fetch_assoc($res)) { $entrie[] = $row; }
	}
	 
	if ( $insert ) {
		$data = array();
		$data[ 'user_id' ] = $_POST[ 'user_id' ];
		$res3 = q( "SELECT * FROM cms_users WHERE id_user=" . $_POST[ 'user_id' ], $dbweb, __FILE__, __LINE__ );
		if ( mysqli_num_rows( $res3 ) > 0 ) {
			$cms_users = mysqli_fetch_assoc( $res3 );
			
			// Daten sammeln
			// aus cms_users 
			$data[ 'company' ] = $cms_users[ 'name' ];
			$data[ 'name' ] = trim( $cms_users[ 'firstname' ] . ' ' . $cms_users[ 'lastname' ] );
			if ( $data[ 'name' ] == '' ) {
				$data[ 'name' ] = $cms_users[ 'name' ];
			}
			if ( $_POST[ 'user_mail' ] == '' or $_POST[ 'user_mail' ] == 'Invalid Request' ) {
				$data[ 'mail' ] = $cms_users[ 'usermail' ];
			} else {
				$data[ 'mail' ] = $_POST[ 'user_mail' ];
			}
			// aus shop_bill_adr
			$res4 = q( "SELECT * FROM shop_bill_adr WHERE user_id=" . $_POST[ 'user_id' ] . " AND standard=1", $dbshop, __FILE__, __LINE__ );
			if ( mysqli_num_rows( $res4 ) >0 ) {
				$shop_bill_adr = mysqli_fetch_assoc( $res4 );
				if ( $shop_bill_adr[ 'company' ] != '' ) {
					$data[ 'company' ] = $shop_bill_adr[ 'company' ];
				}
				if ( $shop_bill_adr[ 'firstname' ] != '' or $shop_bill_adr[ 'lastname' ] != '' ) {
					$data[ 'name' ] = 	trim( $shop_bill_adr[ 'firstname' ] . ' ' . $shop_bill_adr[ 'lastname' ] );
				}
				$data[ 'street1' ] = 	trim( $shop_bill_adr[ 'street' ] . ' ' . $shop_bill_adr[ 'number' ] );
				$data[ 'zip' ] = 		$shop_bill_adr[ 'zip' ];
				$data[ 'city' ] = 		$shop_bill_adr[ 'city' ];
				$data[ 'country' ] = 	$shop_bill_adr[ 'country' ];
			}
			// aus shop_orders
			$res5 = q( "SELECT * FROM shop_orders WHERE customer_id=" . $_POST[ 'user_id' ] . " ORDER BY id_order DESC", $dbshop, __FILE__, __LINE__ );
			if ( mysqli_num_rows( $res5 ) >0 ) {
				$shop_orders = mysqli_fetch_assoc( $res5 );
				if ( $data[ 'mail' ] == '' ) {
					$data[ 'mail' ] = $shop_orders[ 'usermail' ];
				}
				$data[ 'phone' ] = 	$shop_orders[ 'userphone' ];
				$data[ 'mobile' ] = $shop_orders[ 'usermobile' ];
				$data[ 'fax' ] = 	$shop_orders[ 'userfax' ];
			}
			// andere
			if ( gewerblich( $_POST[ 'user_id' ] ) ) {
				$data[ 'gewerblich' ] = 1;
			} else {
				$data[ 'gewerblich' ] = 0;
			}
			$data[ 'firstmod' ] = 		time();
			$data[ 'firstmod_user' ] = 	1;
			$data[ 'lastmod' ] = 		time();
			$data[ 'lastmod_user' ] = 	1;
			
			$response = q_insert( 'crm_customers', $data, $dbweb, __FILE__, __LINE__ );					
		}
	}
	 
?>