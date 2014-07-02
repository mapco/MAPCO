<?php
	include("config.php");

	$menu=array();
	$results=q("SELECT * FROM cms_menus;", $dbap, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		q("INSERT INTO cms_menus (title, description, idtag, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".mysqli_real_escape_string($dbweb, $row["title"])."', '".mysqli_real_escape_string($dbweb, $row["description"])."', '".mysqli_real_escape_string($dbweb, $row["idtag"])."', ".$row["firstmod"].", ".$row["firstmod_user"].", ".$row["lastmod"].", ".$row["lastmod_user"].");", $dbweb, __FILE__, __LINE__);
		$id_menu=mysqli_insert_id($dbweb);
		q("UPDATE cms_menus SET id_menu=".$id_menu." WHERE id_menu=".$row["id_menu"].";", $dbap, __FILE__, __LINE__);
		q("UPDATE cms_menuitems SET menu_id=".$id_menu." WHERE menu_id=".$row["id_menu"].";", $dbap, __FILE__, __LINE__);
	}
	
?>