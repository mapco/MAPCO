<?php 

	/*
		Service setzt in der Order:
			AUF_ID und auf_id_date auf 0, 
			status auf 7 wenn order bezahlt (Completed) oder 1
			status_date auf Zahldatum oder firstmod
		einträge in shop_orders_auf_id werden gelöscht
	*/

	$required = array();
	$required['order_id'] = 'numericNN';
	check_man_params( $required );
	
	define('TABLE_SHOP_ORDERS', 'shop_orders');
	define('TABLE_SHOP_ORDERS_AUF_ID', 'shop_orders_auf_id'); 
	
	//GET ORDER 
	$res_order = q('SELECT * FROM '.TABLE_SHOP_ORDERS.' WHERE id_order = '.$_POST['order_id'], $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows( $res_order ) == 0 )
	{
		//show_error;
		exit;	
	}
	$order = mysqli_fetch_assoc( $res_order );
	
	//DEFINE NEW status
	if ( $order['Payments_TransactionState'] == 'Completed' )
	{
		$status_id = 7;
		$status_date = $order['Payments_TransactionStateDate'];
	}
	else
	{
		$status_id = 1;
		$status_date = $order['firstmod'];
	}
	
	$datafield = array();
	$datafield['AUF_ID'] 		= 0;
	$datafield['auf_id_date'] 	= 0;
	$datafield['status_id'] 	= $status_id;
	$datafield['status_date'] 	= $status_date;
	
	q_update(TABLE_SHOP_ORDERS, $datafield, 'WHERE id_order = '.$_POST['order_id'], $dbshop, __FILE__, __LINE__);
	
	//RESET TABLE AUF_IDS
	q('DELETE from '.TABLE_SHOP_ORDERS_AUF_ID.' WHERE order_id = '.$_POST['order_id'], $dbshop, __FILE__, __LINE__);