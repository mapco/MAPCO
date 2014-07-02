<?php
	
	check_man_params(array("id_menu" => "numericNN"));

	//check for existing menu
	$results=q("SELECT * FROM cms_menus WHERE id_menu='".$_POST["id_menu"]."';", $dbweb, __FILE__, __LINE__);
	if( mysqli_num_rows($results)==0 ) { show_error(9872, 1, __FILE__, __LINE__); exit; }

	//remove menu
	q("DELETE FROM cms_menus WHERE id_menu=".$_POST["id_menu"].";", $dbweb, __FILE__, __LINE__);

?>