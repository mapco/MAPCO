<?php
	include("config.php");

	$menu=array();
	$results=q("SELECT * FROM cms_menuitems WHERE menuitem_id!=0;", $dbap, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		q("INSERT INTO cms_menuitems (link, alias, title, description, ordering, icon, menu_id, menuitem_id, hide, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('".mysqli_real_escape_string($dbweb, $row["link"])."', '".mysqli_real_escape_string($dbweb, $row["alias"])."', '".mysqli_real_escape_string($dbweb, $row["title"])."', '".mysqli_real_escape_string($dbweb, $row["description"])."', ".$row["ordering"].", '".mysqli_real_escape_string($dbweb, $row["icon"])."', ".$row["menu_id"].", ".$row["menuitem_id"].", '".mysqli_real_escape_string($dbweb, $row["hide"])."', ".$row["firstmod"].", ".$row["firstmod_user"].", ".$row["lastmod"].", ".$row["lastmod_user"].");", $dbweb, __FILE__, __LINE__);
		$id_menuitem=mysqli_insert_id($dbweb);
		q("UPDATE cms_menuitems SET id_menuitem=".$id_menuitem." WHERE id_menuitem=".$row["id_menuitem"].";", $dbap, __FILE__, __LINE__);
//		q("UPDATE cms_menuitems SET menuitem_id=".$id_menuitem." WHERE menuitem_id=".$row["id_menuitem"].";", $dbap, __FILE__, __LINE__);
	}
	
?>