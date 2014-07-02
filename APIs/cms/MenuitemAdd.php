<?php
	
	check_man_params(array("menu_id" => "numericNN", "menuitem_id" => "numeric", "dynamic" => "numeric", "local" => "numeric", "link" => "text"));

	//check for existing file
	if( ( strpos($_POST["link"], "http://") === false and $_POST["local"]==0 and !file_exists("../../mapco_shop_de/".$_POST["link"]) )
		or ( $_POST["local"]==1 and !file_exists("../../mapco_shop_de/templates/".TEMPLATE."/".$_POST["link"]) ) )
	{
		show_error(9881, 1, __FILE__, __LINE__, print_r($_POST, true));
		exit;
	}

	//remove API and APIRequest from $data
	$data=$_POST;
	unset($data["API"]);
	unset($data["APIRequest"]);

	//remove menuitem_languages fields from $data
	$results=q("SELECT * FROM cms_languages;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		unset($data["title".$row["id_language"]]);
		unset($data["description".$row["id_language"]]);
		unset($data["alias".$row["id_language"]]);
		unset($data["meta_title".$row["id_language"]]);
		unset($data["meta_description".$row["id_language"]]);
		unset($data["meta_keywords".$row["id_language"]]);
	}
	
	//get ordering
	$results=q("SELECT id_menuitem FROM cms_menuitems WHERE menu_id=".$_POST["menu_id"]." AND menuitem_id=".$_POST["menuitem_id"].";", $dbweb, __FILE__, __LINE__);
	$data["ordering"]=mysqli_num_rows($results);

	//add to cms_menuitems
	$data["firstmod"]=time();
	$data["firstmod_user"]=$_SESSION["id_user"];
	$data["lastmod"]=time();
	$data["lastmod_user"]=$_SESSION["id_user"];
	q_insert("cms_menuitems", $data, $dbweb, __FILE__, __LINE__);
	$id_menuitem=mysqli_insert_id($dbweb);

	//add to cms_menuitems_languages
	$results=q("SELECT * FROM cms_languages;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if( $_POST["title".$row["id_language"]]!="" )
		{
			$data=array();
			$data["menuitem_id"]=$id_menuitem;
			$data["language_id"]=$row["id_language"];
			$data["title"]=$_POST["title".$row["id_language"]];
			$data["description"]=$_POST["description".$row["id_language"]];
			$data["alias"]=$_POST["alias".$row["id_language"]];
			$data["meta_title"]=$_POST["meta_title".$row["id_language"]];
			$data["meta_description"]=$_POST["meta_description".$row["id_language"]];
			$data["meta_keywords"]=$_POST["meta_keywords".$row["id_language"]];
			$data["firstmod"]=time();
			$data["firstmod_user"]=$_SESSION["id_user"];
			$data["lastmod"]=time();
			$data["lastmod_user"]=$_SESSION["id_user"];
			q_insert("cms_menuitems_languages", $data, $dbweb, __FILE__, __LINE__);
		}
	}
	
	//update tl arrays
	$data=array();
	$data["API"]="jobs";
	$data["APIRequest"]="update_tl";
	soa2($data, __FILE__, __LINE__, "xml");

	echo '<MenuitemID>'.$id_menuitem.'</MenuitemID>'."\n";

?>