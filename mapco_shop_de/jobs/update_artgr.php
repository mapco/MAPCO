<?php
	include("../config.php");
	
	//create shop_menuitems_artgr
	q("
		CREATE TABLE IF NOT EXISTS `shop_menuitems_artgr` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `menuitem_id` int(11) NOT NULL,
		  `artgr` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
		", $dbshop, __FILE__, __LINE__);

	//Oberkategorien
	$main=array("Antrieb / Lagerung", "Bremse", "Fahrwerk / Lenkung", "Motor / Filter", "Zubehör");
	
	//Unterkategorien
	$sub=array(16 => 0,
				 17 => 0,
				 18 => 0,
				 26 => 0,
				 76 => 0,
				 77 => 0,
				 1 => 1,
				 2 => 1,
				 3 => 1,
				 4 => 1,
				 5 => 1,
				 6 => 1,
				 8 => 1,
				 9 => 1,
				 15 => 1,
				 35 => 1,
				 47 => 1,
				 56 => 1,
				 86 => 1,
				 19 => 2,
				 20 => 2,
				 27 => 2,
				 29 => 2,
				 33 => 2,
				 34 => 2,
				 70 => 2,
				 95 => 2,
				 10 => 3,
				 13 => 3,
				 21 => 3,
				 22 => 3,
				 23 => 3,
				 30 => 3,
				 31 => 3,
				 37 => 3,
				 42 => 3,
				 60 => 3,
				 73 => 3,
				 80 => 3,
				 82 => 3,
				 90 => 3,
				 91 => 3,
				 93 => 3,
				 103 => 4,
				 104 => 4);


	//read out shopmenu ID
	$results=q("SELECT * FROM cms_menus WHERE idtag='shopmenu';", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$id_menu=$row["id_menu"];
	if ($id_menu=="")
	{
		echo '<p>Shopmenü konnte nicht gefunden werden.</p>';
		exit();
	}
	
	//read out mapco categories
	$artgr=array();
	$results=q("SELECT * FROM t_200 GROUP BY ARTGR;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$artgr[sizeof($artgr)]=(int)$row["ARTGR"];
	}

	//read out shopmenu menuitems
	$menuitem=array();
	$results=q("SELECT * FROM cms_menuitems WHERE menu_id=".$id_menu.";", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$menuitem[$row["id_menuitem"]]=$row["id_menuitem"];
	}
	
	//read out categories
	$mid=array();
	$results=q("SELECT * FROM shop_menuitems_artgr;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$mid[$row["artgr"]]=$row["menuitem_id"];
	}

	//create main menuitems
	for ($i=0; $i<sizeof($main); $i++)
	{
		$results=q("SELECT * FROM cms_menuitems WHERE title='".$main[$i]."' AND menu_id=".$id_menu.";", $dbweb, __FILE__, __LINE__);
		if (mysqli_num_rows($results)==0)
		{
			q("INSERT INTO cms_menuitems (link, title, description, ordering, icon, menu_id, menuitem_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('shop.php', '".$main[$i]."', '".$main[$i]."', 0, '', ".$id_menu.", 0, ".time().", 0, ".time().", 0);", $dbweb, __FILE__, __LINE__);
		}
	}

	//read out main menuitems
	$mainmenuitem=array();
	$results=q("SELECT * FROM cms_menuitems WHERE menu_id=".$id_menu." AND menuitem_id=0;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$mainmenuitem[$row["title"]]=$row["id_menuitem"];
	}

	//create sub menuitems
	for($i=0; $i<sizeof($artgr); $i++)
//	for($i=0; $i<2; $i++)
	{
		//mail new artgr
		if (!isset($sub[$artgr[$i]]))
		{
			$header = 'From: Jens Habermann <jhabermann@mapco.de>' . "\r\n" .
			'Reply-To: Jens Habermann <jhabermann@mapco.de>' . "\r\n" .
			'X-Mailer: PHP/' . phpversion();
			mail("developer@mapco.de", "Fehlende Artikelgruppe", "ARTGR missing: '.$artgr[$i].", $header);
			echo 'Fehlende Artikelgruppe gemeldet!<br />';
		}
		//create menuitem
		if (!isset($mid[$artgr[$i]]))
		{
			q("INSERT INTO cms_menuitems (link, title, description, ordering, icon, menu_id, menuitem_id, firstmod, firstmod_user, lastmod, lastmod_user) VALUES('shop.php', '".$artgr[$i]."', '".$artgr[$i]."', 0, '', ".$id_menu.", ".$mainmenuitem[$main[$sub[$artgr[$i]]]].", ".time().", 0, ".time().", 0);", $dbweb, __FILE__, __LINE__);
			$id_menuitem=mysqli_insert_id($dbweb);
			q("INSERT INTO shop_menuitems_artgr (menuitem_id, artgr) VALUES(".$id_menuitem.", ".$artgr[$i].");", $dbshop, __FILE__, __LINE__);
			echo 'Neue Artikelgruppe angelegt!<br />';
		}
	}
	echo 'Aktualisierung der Artikelgruppen erfolgreich abgeschlossen!<br />';
?>