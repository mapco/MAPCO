<?php
	
	check_man_params(array("site_id" => "numeric", "title" => "text", "idtag" => "text"));

	//check for existing idtag
	$results=q("SELECT * FROM cms_menus WHERE idtag='".$_POST["idtag"]."' AND site_id=".$_POST['site_id'].";", $dbweb, __FILE__, __LINE__);
	if( mysqli_num_rows($results)>0 ) { show_error(9871, 1, __FILE__, __LINE__); exit; }

	//add menu
	$data=$_POST;
	unset($data["API"]);
	unset($data["APIRequest"]);
	$data["firstmod"]=time();
	$data["firstmod_user"]=$_SESSION["id_user"];
	$data["lastmod"]=time();
	$data["lastmod_user"]=$_SESSION["id_user"];
	q_insert("cms_menus", $data, $dbweb, __FILE__, __LINE__);
	$id_menu=mysqli_insert_id($dbweb);
	
	echo '<MenuID>'.$id_menu.'</MenuID>'."\n";

?>