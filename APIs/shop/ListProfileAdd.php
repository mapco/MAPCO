<?php
	//Listen-ID fehlt.
	if ( !isset($_POST["id_list"]) ) { show_error(9849, 7, __FILE__, __LINE__, print_r($_POST, true)); exit; }
	//Titel fehlt.
	if ( !isset($_POST["title"]) ) { show_error(9850, 7, __FILE__, __LINE__, print_r($_POST, true)); exit; }
	//Title known
	$results=q("SELECT id_listprofile FROM shop_lists_profiles WHERE title='".$_POST["title"]."';", $dbshop, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)>0 ) { show_error(9851, 7, __FILE__, __LINE__, print_r($_POST, true)); exit; }
	
	//add profile
	$data=array();
	$data["title"]=$_POST["title"];
	$data["firstmod"]=time();
	$data["firstmod_user"]=$_SESSION["id_user"];
	$data["lastmod"]=time();
	$data["lastmod_user"]=$_SESSION["id_user"];
	q_insert("shop_lists_profiles", $data, $dbshop, __FILE__, __LINE__);
	$id_profile=mysqli_insert_id($dbshop);
	
	//copy fields and values
	$results=q("SELECT * FROM shop_lists_fields WHERE list_id=".$_POST["id_list"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_assoc($results) )
	{
		unset($row["id"]);
		unset($row["list_id"]);
		$row["listprofile_id"]=$id_profile;
		q_insert("shop_lists_profiles_fields", $row, $dbshop, __FILE__, __LINE__);
	}
	
	echo '<ProfileID>'.$id_profile.'</ProfileID>'."\n";

?>