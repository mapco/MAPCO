<?php
	
	check_man_params(array("id_menu" => "numericNN", "site_id" => "numeric", "title" => "text", "idtag" => "text"));

	//check for existing idtag
	$results=q("SELECT * FROM cms_menus WHERE idtag='".$_POST["idtag"]."'AND site_id=".$_POST['site_id'].";", $dbweb, __FILE__, __LINE__);
	if( mysqli_num_rows($results)>0 ) { show_error(9871, 1, __FILE__, __LINE__); exit; }

	//add menu
	$data=$_POST;
	unset($data["API"]);
	unset($data["APIRequest"]);
	unset($data["id_menu"]);
	$data["lastmod"]=time();
	$data["lastmod_user"]=$_SESSION["id_user"];
	q_update("cms_menus", $data, "WHERE id_menu=".$_POST["id_menu"].";", $dbweb, __FILE__, __LINE__);

?>