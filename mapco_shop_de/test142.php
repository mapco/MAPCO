<?php

	/**********************************************************
	 * IMPORTS ORDERS FROM OLD SHOPS TO THE CLONE SHOP SYSTEM *
	 **********************************************************/

	include("config.php");
//	if( $_SESSION["id_user"]!=21371 ) exit;
	$db=$dblenkung24;
	$id_shop=7;

	$user=array();
	$results=q("SELECT id_user, new_user_id FROM cms_users;", $db, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$user[$row["id_user"]]=$row["new_user_id"];
	}

	//import shop_orders
	$results=q("SELECT * FROM shop_orders WHERE new_order_id=0;", $db, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		//remove sequential keys
		for($i=0; $i<sizeof($row); $i++)
		{
			if( isset($row[$i]) ) unset($row[$i]);
		}
		//remove unknown keys
		$old_id_order=$row["id_order"];
		unset($row["id_order"]);
		unset($row["new_order_id"]);
		unset($row["status"]);
		//convert to new format
		$row["customer_id"]=$user[$row["customer_id"]];
		if( $id_shop==7 )
		{
			$row["Payments_TransactionID"]=$row["PayPal_TransactionID"];
			unset($row["PayPal_TransactionID"]);
			$row["Payments_TransactionState"]=$row["PayPal_TransactionState"];
			unset($row["PayPal_TransactionState"]);
			$row["Payments_TransactionStateDate"]=$row["PayPalTransactionStateDate"];
			unset($row["PayPalTransactionStateDate"]);
		}
		//add new columns
		$row["shop_id"]=$id_shop;
		$row["ordertype_id"]=1;
		$row["status_id"]=3;
		$row["status_date"]=$row["lastmod"];
		$row["bill_country_code"]="DE";
		$row["ship_country_code"]="DE";
		if( $row["Payments_TransactionID"]!="" ) $row["payments_type_id"]=4;
		else $row["payments_type_id"]=2;
		$row["shipping_type_id"]=3;
		$row["firstmod_user"]=$user[$row["firstmod_user"]];
		$row["Currency_Code"]="EUR";
		//update lastmod data
		$row["lastmod"]=time();
		$row["lastmod_user"]=21371;
		
		q_insert("shop_orders", $row, $dbshop, __FILE__, __LINE__);
		$new_order_id=mysqli_insert_id($dbshop);
		q("UPDATE shop_orders SET new_order_id=".$new_order_id." WHERE id_order=".$old_id_order.";", $db, __FILE__, __LINE__);
		echo '<br />Bestellung '.$row["id_order"].' erfolgreich nach '.$new_order_id.' importiert.';
	}
?>