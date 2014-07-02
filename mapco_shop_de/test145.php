<?php

	/*****************************************************************
	 * IMPORTS SHOP_CARFLEET FROM OLD SHOPS TO THE CLONE SHOP SYSTEM *
	 *****************************************************************/

	include("config.php");
//	if( $_SESSION["id_user"]!=21371 ) exit;
	$db=$dblenkung24;
	$id_shop=7;

	//import shop_carfleet
	$user=array();
	$results=q("SELECT id_user, new_user_id FROM cms_users;", $db, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$user[$row["id_user"]]=$row["new_user_id"];
	}
	$results=q("SELECT * FROM shop_carfleet WHERE new_id=0;", $db, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		//remove sequential keys
		for($i=0; $i<sizeof($row); $i++)
		{
			if( isset($row[$i]) ) unset($row[$i]);
		}
		//remove unknown keys
		$old_id=$row["id"];
		unset($row["id"]);
		unset($row["new_id"]);
		//convert to new format
		$row["user_id"]=$user[$row["user_id"]];
		//add new columns
		$row["shop_id"]=$id_shop;
		$row["lastmod"]=time();
		
		q_insert("shop_carfleet", $row, $dbshop, __FILE__, __LINE__);
		$new_id=mysqli_insert_id($dbshop);
		q("UPDATE shop_carfleet SET new_id=".$new_id." WHERE id=".$old_id.";", $db, __FILE__, __LINE__);
		echo '<br />Fahrzeug '.$old_id.' erfolgreich nach '.$new_id.' importiert.';
	}
?>