<?php
	
	check_man_params(array("id_menuitem" => "numericNN"));

	//remove sub menuitems
	$results=q("SELECT * FROM cms_menuitems WHERE menuitem_id=".$_POST["id_menuitem"].";", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$postdata=array();
		$postdata["API"]="cms";
		$postdata["APIRequest"]="MenuitemRemove";
		$postdata["id_menuitem"]=$row["id_menuitem"];
		post(PATH."soa2/", $postdata);
	}
	//remove from cms_menuitems_languages
	q("DELETE FROM cms_menuitems_languages WHERE menuitem_id=".$_POST["id_menuitem"].";", $dbweb, __FILE__, __LINE__);
	//remove from cms_menuitems
	q("DELETE FROM cms_menuitems WHERE id_menuitem=".$_POST["id_menuitem"].";", $dbweb, __FILE__, __LINE__);

?>