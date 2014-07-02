<?php
	//Listen-ID fehlt.
	if ( !isset($_POST["id_list"]) ) { show_error(9849, 7, __FILE__, __LINE__); exit; }
	//Listenprofil-ID fehlt.
	if ( !isset($_POST["id_listprofile"]) ) { show_error(9852, 7, __FILE__, __LINE__); exit; }
	
	//remove old profile
	q("DELETE FROM shop_lists_fields WHERE list_id=".$_POST["id_list"].";", $dbshop, __FILE__, __LINE__);
	
	//add new profile
	$results=q("SELECT * FROM shop_lists_profiles_fields WHERE listprofile_id=".$_POST["id_listprofile"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_assoc($results) )
	{
		unset($row["id"]);
		unset($row["listprofile_id"]);
		$row["list_id"]=$_POST["id_list"];
		q_insert("shop_lists_fields", $row, $dbshop, __FILE__, __LINE__);
	}
	
?>