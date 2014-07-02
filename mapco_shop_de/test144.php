<?php

	/****************************************************************
	 * IMPORTS ORDERS_ITEMS FROM OLD SHOPS TO THE CLONE SHOP SYSTEM *
	 ****************************************************************/

	include("config.php");
//	if( $_SESSION["id_user"]!=21371 ) exit;
	$db=$dblenkung24;
	$id_shop=7;

	//import shop_carts
	$user=array();
	$results=q("SELECT id_user, new_user_id FROM cms_users;", $db, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$user[$row["id_user"]]=$row["new_user_id"];
	}
	$results=q("SELECT * FROM shop_carts WHERE new_id_carts=0 AND user_id>0;", $db, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		//remove sequential keys
		for($i=0; $i<sizeof($row); $i++)
		{
			if( isset($row[$i]) ) unset($row[$i]);
		}
		//remove unknown keys
		$old_id_carts=$row["id_carts"];
		unset($row["id_carts"]);
		unset($row["new_id_carts"]);
		unset($row["session_id"]);
		//convert to new format
		$row["user_id"]=$user[$row["user_id"]];
		//add new columns
		$row["shop_id"]=$id_shop;
		$row["lastmod"]=time();
		
		q_insert("shop_carts", $row, $dbshop, __FILE__, __LINE__);
		$new_id_carts=mysqli_insert_id($dbshop);
		q("UPDATE shop_carts SET new_id_carts=".$new_id_carts." WHERE id_carts=".$old_id_carts.";", $db, __FILE__, __LINE__);
		echo '<br />Warenkorbzeile '.$old_id_carts.' erfolgreich nach '.$new_id_carts.' importiert.';
	}
?>