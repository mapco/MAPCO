<?php
	include("config.php");


	//cms_articles
	q("	
		CREATE TABLE IF NOT EXISTS `cms_articles` (
		  `id_article` int(11) NOT NULL AUTO_INCREMENT,
		  `language_id` int(11) NOT NULL DEFAULT '1',
		  `article_id` int(11) NOT NULL DEFAULT '0',
		  `title` tinytext CHARACTER SET utf8 NOT NULL,
		  `introduction` text CHARACTER SET utf8 NOT NULL,
		  `article` text CHARACTER SET utf8 NOT NULL,
		  `published` tinyint(1) NOT NULL,
		  `format` int(11) NOT NULL DEFAULT '1',
		  `ordering` int(11) NOT NULL,
		  `newsletter` tinyint(1) NOT NULL DEFAULT '0',
		  `firstmod` int(11) NOT NULL,
		  `firstmod_user` int(11) NOT NULL,
		  `lastmod` int(11) NOT NULL,
		  `lastmod_user` int(11) NOT NULL,
		  PRIMARY KEY (`id_article`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
	", $dbweb, __FILE__, __LINE__);


	//cms_articles_files
	q("
		CREATE TABLE IF NOT EXISTS `cms_articles_files` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `article_id` int(11) NOT NULL,
		  `file_id` int(11) NOT NULL,
		  `ordering` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
	", $dbweb, __FILE__, __LINE__);


	//cms_articles_images
	q("
		CREATE TABLE IF NOT EXISTS `cms_articles_images` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `article_id` int(11) NOT NULL,
		  `file_id` int(11) NOT NULL,
		  `original_id` int(11) NOT NULL,
		  `imageformat_id` int(11) NOT NULL,
		  `ordering` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=250 ;
	", $dbweb, __FILE__, __LINE__);


	//cms_articles_labels
	q("
		CREATE TABLE IF NOT EXISTS `cms_articles_labels` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `article_id` int(11) NOT NULL,
		  `label_id` int(11) NOT NULL,
		  `ordering` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
	", $dbweb, __FILE__, __LINE__);


	//cms_articles_videos
	q("
		CREATE TABLE IF NOT EXISTS `cms_articles_videos` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `article_id` int(11) NOT NULL,
		  `file_id` int(11) NOT NULL,
		  `ordering` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
	", $dbweb, __FILE__, __LINE__);


	//cms_contacts
	q("CREATE TABLE IF NOT EXISTS `cms_contacts` (
		  `id_contact` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `idLocation` int(10) unsigned NOT NULL,
		  `idBrand` int(10) unsigned DEFAULT NULL,
		  `idDepartment` int(10) unsigned NOT NULL,
		  `department_id` int(11) NOT NULL,
		  `position` varchar(255) NOT NULL,
		  `firstname` varchar(128) NOT NULL,
		  `lastname` varchar(128) NOT NULL,
		  `phone` varchar(50) NOT NULL,
		  `mobile` varchar(50) NOT NULL,
		  `fax` varchar(50) NOT NULL,
		  `mail` varchar(128) NOT NULL,
		  `gender` char(1) NOT NULL,
		  `ordering` int(10) unsigned NOT NULL,
		  `active` tinyint(1) unsigned NOT NULL,
		  `booDelete` tinyint(1) unsigned NOT NULL,
		  PRIMARY KEY (`id_contact`),
		  KEY `idLocation` (`idLocation`),
		  KEY `idDepartment` (`idDepartment`),
		  KEY `idBrand` (`idBrand`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;", $dbweb, __FILE__, __LINE__);


	//cms_contacts_departments
	q("CREATE TABLE IF NOT EXISTS `cms_contacts_departments` (
		  `id_department` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `department` tinytext NOT NULL,
		  `location_id` int(11) NOT NULL,
		  `ordering` int(11) NOT NULL,
		  PRIMARY KEY (`id_department`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;", $dbweb, __FILE__, __LINE__);


	//cms_contacts_locations
	q("CREATE TABLE IF NOT EXISTS `cms_contacts_locations` (
		  `id_location` int(11) NOT NULL AUTO_INCREMENT,
		  `location` tinytext NOT NULL,
		  `ordering` int(11) NOT NULL,
		  PRIMARY KEY (`id_location`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
	", $dbweb, __FILE__, __LINE__);


	//cms_imageformats
	q("
		CREATE TABLE IF NOT EXISTS `cms_imageformats` (
		  `id_imageformat` int(11) NOT NULL AUTO_INCREMENT,
		  `title` tinytext NOT NULL,
		  `imageprofile_id` int(11) NOT NULL,
		  `width` int(11) NOT NULL,
		  `height` int(11) NOT NULL,
		  `aoe` tinyint(1) NOT NULL,
		  `zc` tinyint(1) NOT NULL,
		  `ordering` int(11) NOT NULL,
		  PRIMARY KEY (`id_imageformat`)
		) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
	", $dbweb, __FILE__, __LINE__);
	$results=q("SELECT * FROM cms_imageformats LIMIT 1;", $dbweb, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		q("
			INSERT INTO `cms_imageformats` (`id_imageformat`, `title`, `imageprofile_id`, `width`, `height`, `aoe`, `zc`) VALUES
			(1, 'Thumbnail', 1, 160, 120, 1, 1),
			(2, 'Bild klein', 1, 500, 375, 1, 1),
			(3, 'Bild groß', 1, 800, 600, 1, 0),
			(4, 'Breitbild', 2, 940, 350, 1, 1);
		", $dbweb, __FILE__, __LINE__);
	}


	//cms_imageprofiles
	q("
		CREATE TABLE IF NOT EXISTS `cms_imageprofiles` (
		  `id_imageprofile` int(11) NOT NULL AUTO_INCREMENT,
		  `title` tinytext NOT NULL,
		  `description` text NOT NULL,
		  `ordering` int(11) NOT NULL,
		  PRIMARY KEY (`id_imageprofile`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
	", $dbweb, __FILE__, __LINE__);
	$results=q("SELECT * FROM cms_imageprofiles LIMIT 1;", $dbweb, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		q("
			INSERT INTO `cms_imageprofiles` (`id_imageprofile`, `title`, `description`) VALUES
			(1, 'News', 'Fotos für Newsbeiträge'),
			(2, 'Diaschau', 'Fotos in der Diaschau auf der Startseite.');
		", $dbweb, __FILE__, __LINE__);
	}


	//cms_files
	q("
		CREATE TABLE IF NOT EXISTS `cms_files` (
		  `id_file` int(11) NOT NULL AUTO_INCREMENT,
		  `filename` tinytext CHARACTER SET utf8 NOT NULL,
		  `extension` tinytext CHARACTER SET utf8 NOT NULL,
		  `filesize` int(11) NOT NULL,
		  `description` text CHARACTER SET utf8 NOT NULL,
		  `original_id` int(11) NOT NULL,
		  `firstmod` int(11) NOT NULL,
		  `firstmod_user` int(11) NOT NULL,
		  `lastmod` int(11) NOT NULL,
		  `lastmod_user` int(11) NOT NULL,
		  PRIMARY KEY (`id_file`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000000;
	", $dbweb, __FILE__, __LINE__);


	//cms_labels
	q("
		CREATE TABLE IF NOT EXISTS `cms_labels` (
		  `id_label` int(11) NOT NULL AUTO_INCREMENT,
		  `label` tinytext CHARACTER SET utf8 NOT NULL,
		  `ordering` int(11) NOT NULL,
		  `firstmod` int(11) NOT NULL,
		  `firstmod_user` int(11) NOT NULL,
		  `lastmod` int(11) NOT NULL,
		  `lastmod_user` int(11) NOT NULL,
		  PRIMARY KEY (`id_label`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
	", $dbweb, __FILE__, __LINE__);
	$results=q("SELECT * FROM cms_labels LIMIT 1;", $dbweb, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		q("
			REPLACE INTO `cms_labels` (`id_label`, `label`, `firstmod`, `firstmod_user`, `lastmod`, `lastmod_user`) VALUES
			(3, 'Startseite', 1293447866, 1, 1293447866, 1),
			(5, 'Newsletter', 1307520288, 1, 1307520288, 1),
			(6, 'Pressemitteilung', 1315215372, 1, 1315215372, 1),
			(7, 'Pressebericht', 1315215383, 1, 1315215383, 1);
		", $dbweb, __FILE__, __LINE__);
	}


	//cms_languages
	q("
		CREATE TABLE IF NOT EXISTS `cms_languages` (
		  `id_language` int(11) NOT NULL AUTO_INCREMENT,
		  `language` tinytext NOT NULL,
		  `code` tinytext NOT NULL,
		  `active` tinyint(1) NOT NULL DEFAULT '0',
		  `ordering` int(11) NOT NULL,
		  `language_id` int(11) NOT NULL DEFAULT '2',
		  `firstmod` int(11) NOT NULL,
		  `firstmod_user` int(11) NOT NULL,
		  `lastmod` int(11) NOT NULL,
		  `lastmod_user` int(11) NOT NULL,
		  PRIMARY KEY (`id_language`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
	", $dbweb, __FILE__, __LINE__);
	$results=q("SELECT * FROM cms_languages LIMIT 1;", $dbweb, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		q("
			REPLACE INTO `cms_languages` (`id_language`, `language`, `code`, `active`, `ordering`, `language_id`, `firstmod`, `firstmod_user`, `lastmod`, `lastmod_user`) VALUES
			(1, 'Deutsch', 'de', 1, 1, 2, 1294820247, 1, 1294820247, 1),
			(2, 'Englisch', 'en', 1, 2, 2, 1294820491, 1, 1294820491, 1),
			(3, 'Russisch', 'ru', 1, 3, 2, 1294820781, 1, 1294820781, 1),
			(4, 'Polnisch', 'pl', 1, 4, 2, 1294820802, 1, 1294820802, 1),
			(5, 'Italienisch', 'it', 1, 5, 2, 1294821047, 1, 1294821047, 1),
			(6, 'Französisch', 'fr', 1, 6, 2, 1294821073, 1, 1294821073, 1),
			(7, 'Spanisch', 'es', 1, 7, 2, 1294821124, 1, 1294821124, 1),
			(8, 'Chinesisch', 'zh', 1, 8, 2, 1294821141, 1, 1294821141, 1);
		", $dbweb, __FILE__, __LINE__);
	}


	//cms_menuitems
	q("
		CREATE TABLE IF NOT EXISTS `cms_menuitems` (
		  `id_menuitem` int(11) NOT NULL AUTO_INCREMENT,
		  `link` tinytext NOT NULL,
		  `alias` tinytext NOT NULL,
		  `title` tinytext NOT NULL,
		  `description` text NOT NULL,
		  `ordering` int(11) NOT NULL,
		  `icon` tinytext NOT NULL,
		  `menu_id` int(11) NOT NULL,
		  `menuitem_id` int(11) NOT NULL,
		  `firstmod` int(11) NOT NULL,
		  `firstmod_user` int(11) NOT NULL,
		  `lastmod` int(11) NOT NULL,
		  `lastmod_user` int(11) NOT NULL,
		  PRIMARY KEY (`id_menuitem`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
	", $dbweb, __FILE__, __LINE__);
	$results=q("SELECT * FROM cms_menuitems LIMIT 1;", $dbweb, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		q("
			REPLACE INTO `cms_menuitems` (`id_menuitem`, `link`, `title`, `description`, `ordering`, `icon`, `menu_id`, `menuitem_id`, `firstmod`, `firstmod_user`, `lastmod`, `lastmod_user`) VALUES
			(1, 'backend_cms_index.php', 'Inhalte', 'Content Management System', 2, 'images/icons/64x64/image_multi.png', 2, 0, 1295966297, 1, 1296058591, 1),
			(2, 'backend_shop_index.php', 'Shop', 'Online-Shop', 3, 'images/icons/64x64/shopping_cart.png', 2, 0, 1295966764, 1, 1296059977, 1),
			(3, 'backend_mm_index.php', 'Waren', 'Warenwirtschaft', 4, 'images/icons/64x64/note_edit.png', 2, 0, 1295966800, 1, 1296059931, 1),
			(4, 'backend_hr_index.php', 'Personal', 'Personalverwaltung', 5, 'images/icons/64x64/users.png', 2, 0, 1295966922, 1, 1296059923, 1),
			(5, 'backend_tm_index.php', 'Zeit', 'Zeitmanagement', 6, 'images/icons/64x64/clock.png', 2, 0, 1295966946, 1, 1296059913, 1),
			(6, 'backend_cms_articles.php', 'Beiträge', 'Beitragsübersicht anzeigen', 1, 'images/icons/64x64/page.png', 2, 1, 1295966991, 1, 1296049654, 1),
			(7, 'backend_cms_files.php', 'Dateien', 'Dateiübersicht anzeigen', 3, 'images/icons/64x64/image.png', 2, 1, 1295967030, 1, 1296049698, 1),
			(8, 'backend_cms_labels.php', 'Stichworte', 'Stichworte', 2, 'images/icons/64x64/note_edit.png', 2, 1, 1295981379, 1, 1296049713, 1),
			(9, 'backend_cms_menus.php', 'Menüs', 'Menüs', 4, 'images/icons/64x64/applications.png', 2, 1, 1295981416, 1, 1296049729, 1),
			(10, 'backend_cms_languages.php', 'Sprachen', 'Sprachen', 5, 'images/icons/64x64/user_comments.png', 2, 1, 1295981467, 1, 1296049745, 1),
			(11, 'backend_cms_translations.php', 'Übersetzungen', 'Übersetzungen', 6, 'images/icons/64x64/comments.png', 2, 1, 1295981487, 1, 1296049761, 1),
			(12, 'backend_cms_users.php', 'Benutzer', 'Benutzerprofile', 7, 'images/icons/64x64/users.png', 2, 1, 1295981517, 1, 1296049767, 1),
			(13, 'backend_cms_userroles.php', 'Benutzerrollen', 'Benutzerrollen', 8, 'images/icons/64x64/community_users.png', 2, 1, 1295981537, 1, 1296049775, 1),
			(14, 'backend_cms_userimport.php', 'Benutzer importieren', 'Benutzer importieren', 9, 'images/icons/64x64/user_add.png', 2, 1, 1295981637, 1, 1296049787, 1),
			(15, 'backend_shop_items.php', 'Artikel', 'Artikelübersicht', 1, 'images/icons/64x64/shopping_cart_accept.png', 2, 2, 1295981796, 1, 1296049866, 1),
			(16, 'backend_shop_offers.php', 'Angebote', 'Angebotsübersicht', 2, 'images/icons/64x64/shopping_cart_favorite.png', 2, 2, 1295981857, 1, 1296058390, 1),
			(17, 'backend_shop_orders.php', 'Bestellungen', 'Bestellübersicht', 4, 'images/icons/64x64/note_edit.png', 2, 2, 1295981907, 1, 1296058064, 1),
			(18, 'backend_shop_payment.php', 'Zahlung & Versand', 'Zahlungs- und Versandmöglichkeiten', 5, 'images/icons/64x64/shipping-8-icon.png', 2, 2, 1295981960, 1, 1296058068, 1),
			(19, 'backend_mm_stocks.php', 'Warenbestände', 'Warenübersicht', 1, 'images/icons/64x64/chart.png', 2, 3, 1295982012, 1, 1296049917, 1),
			(20, 'backend_mm_arrival.php', 'Wareneingang', 'Wareneingang', 2, 'images/icons/64x64/home_next.png', 2, 3, 1295982046, 1, 1296049928, 1),
			(21, 'backend_mm_suppliers.php', 'Lieferanten', 'Lieferantenübersicht', 3, 'images/icons/64x64/she_user.png', 2, 3, 1295982106, 1, 1296049943, 1),
			(22, 'backend_mm_warehouses.php', 'Lager', 'Lagerübersicht', 4, 'images/icons/64x64/home.png', 2, 3, 1295982163, 1, 1296049950, 1),
			(23, 'backend_mm_import.php', 'Warenimport', 'Warenimport', 5, 'images/icons/64x64/note_add.png', 2, 3, 1295982198, 1, 1296049961, 1),
			(24, 'backend_hr_employees.php', 'Mitarbeiter', 'Mitarbeiterübersicht', 1, 'images/icons/64x64/users.png', 2, 4, 1295982304, 1, 1296049974, 1),
			(25, 'backend_hr_charts.php', 'Auswertung', 'Auswertung Verkaufsprovisionen', 2, 'images/icons/64x64/chart.png', 2, 4, 1295982362, 1, 1296049982, 1),
			(26, 'backend_tm_todo.php', 'Aufgaben', 'Aufgabenübersicht', 1, 'images/icons/64x64/notes_edit.png', 2, 5, 1295982424, 1, 1296050032, 1),
			(27, 'backend_tm_calendar.php', 'Kalender', 'Terminkalender', 2, 'images/icons/64x64/calendar_date.png', 2, 5, 1295982710, 1, 1296050055, 1),
			(57, 'backend_index.php', 'Backend', 'Startseite und Favoriten', 1, 'images/icons/64x64/favorite.png', 2, 0, 1296055498, 1, 1296059962, 1),
			(58, 'index.php', 'Frontend', 'Zurück zur Webseite', 7, 'images/icons/64x64/right.png', 2, 0, 1296055705, 1, 1296059899, 1),
			(59, 'backend_shop_myoffers.php', 'Meine Angebote', 'Meine Angebote', 3, 'images/icons/64x64/shopping_cart_favorite.png', 2, 2, 1296057819, 1, 1296057819, 1),
			(60, 'backend_cms_library.php', 'Fotoarchiv', 'Zum Fotoarchiv', 10, 'images/icons/64x64/image_multi.png', 2, 1, 1296217331, 1, 1296217662, 1),
			(61, 'backend_hr_employee_import.php', 'Mitarbeiter importieren', 'Mitarbeiter importieren', 3, 'images/icons/64x64/user_add.png', 2, 4, 1296655366, 1, 1296655386, 1),
			(111, 'backend_shop_tecdoc_import.php', 'TecDoc-Import', 'TecDoc-Import', 6, 'images/icons/64x64/note_add.png', 2, 2, 1296835075, 1, 1296835085, 1),
			(112, 'backend_shop_stats.php', 'Statistiken', 'Statistiken', 7, 'images/icons/64x64/chart.png', 2, 2, 1297698863, 1, 1297698882, 1),
			(113, 'backend_shop_partners.php', 'Partnerprogramme', 'Anlegen und Auswerten von Partnerprogrammen', 8, 'images/icons/64x64/community_users.png', 2, 2, 1298969975, 22054, 1298980176, 1),
			(159, 'backend_mapco_replacements.php', 'Ersetzungen', 'Ersetzungseditor für Titelgenerierung', 9, 'images/icons/64x64/search_add.png', 2, 2, 1309264276, 1, 1309264850, 1),
			(160, 'backend_mapco_gart_export.php', 'Exportkategorien', 'eBay- und Amazon-Kategorien', 10, 'images/icons/64x64/page_swap.png', 2, 2, 1311665221, 1, 1311665221, 1);
		", $dbweb, __FILE__, __LINE__);
	}


	//cms_menus
	q("
		CREATE TABLE IF NOT EXISTS `cms_menus` (
		  `id_menu` int(11) NOT NULL AUTO_INCREMENT,
		  `title` tinytext NOT NULL,
		  `description` text NOT NULL,
		  `idtag` tinytext NOT NULL,
		  `ordering` int(11) NOT NULL,
		  `firstmod` int(11) NOT NULL,
		  `firstmod_user` int(11) NOT NULL,
		  `lastmod` int(11) NOT NULL,
		  `lastmod_user` int(11) NOT NULL,
		  PRIMARY KEY (`id_menu`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
	", $dbweb, __FILE__, __LINE__);
	$results=q("SELECT * FROM cms_menus LIMIT 1;", $dbweb, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		q("
			REPLACE INTO `cms_menus` (`id_menu`, `title`, `description`, `idtag`, `firstmod`, `firstmod_user`, `lastmod`, `lastmod_user`) VALUES
			(2, 'Backendmenü', 'Menü im Backend der Webseite.', 'backendmenu', 1295951265, 1, 1295952961, 1),
			(3, 'Hauptmenü', 'Das Hauptmenü der Webseite.', 'mainmenu', 1295951356, 1, 1295951356, 1);
		", $dbweb, __FILE__, __LINE__);
	}


	//cms_translations
	q("
		CREATE TABLE IF NOT EXISTS `cms_translations` (
		  `id_translation` int(11) NOT NULL AUTO_INCREMENT,
		  `de` text COLLATE utf8_bin NOT NULL,
		  `en` text COLLATE utf8_bin NOT NULL,
		  `ru` text COLLATE utf8_bin NOT NULL,
		  `fr` text COLLATE utf8_bin NOT NULL,
		  `it` text COLLATE utf8_bin NOT NULL,
		  `zh` text COLLATE utf8_bin NOT NULL,
		  `pl` text COLLATE utf8_bin NOT NULL,
		  `es` text COLLATE utf8_bin NOT NULL,
		  PRIMARY KEY (`id_translation`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;
	", $dbweb, __FILE__, __LINE__);


	//cms_users
	q("CREATE TABLE IF NOT EXISTS `cms_users` (
		  `id_user` int(11) NOT NULL AUTO_INCREMENT,
		  `username` tinytext NOT NULL,
		  `usermail` tinytext NOT NULL,
		  `password` tinytext NOT NULL,
		  `userrole_id` int(11) NOT NULL,
		  `language_id` int(11) NOT NULL,
		  `lastlogin` int(11) NOT NULL,
		  `lastvisit` int(11) NOT NULL,
		  `session_id` tinytext NOT NULL,
		  `active` tinyint(1) NOT NULL DEFAULT '1',
		  `partner_id` int(11) NOT NULL,
		  `newsletter` tinyint(1) NOT NULL DEFAULT '0',
		  `newsletter_id` int(11) NOT NULL,
		  `firstmod` int(11) NOT NULL,
		  `firstmod_user` int(11) NOT NULL,
		  `lastmod` int(11) NOT NULL,
		  `lastmod_user` int(11) NOT NULL,
		  PRIMARY KEY (`id_user`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;", $dbweb, __FILE__, __LINE__);
	$results=q("SELECT * FROM cms_users LIMIT 1;", $dbweb, __FILE__, __LINE__);
	if (mysqli_num_rows($results)==0)
	{
		q("
			INSERT INTO `cms_users` (`id_user`, `username`, `usermail`, `password`, `userrole_id`, `language_id`, `lastlogin`, `lastvisit`, `session_id`, `active`, `partner_id`, `newsletter`, `newsletter_id`, `firstmod`, `firstmod_user`, `lastmod`, `lastmod_user`) VALUES
			(1, 'admin', 'admin@abc.de', 'admin', 1, 1, 1320941686, 1320941687, '21465d2014bf4fa514f6db603005490b', 1, 0, 1, 161, 1291390502, 1, 1295001220, 1);
		", $dbweb, __FILE__, __LINE__);
	}


	//cms_userroles
	q("
		CREATE TABLE IF NOT EXISTS `cms_userroles` (
		  `id_userrole` int(11) NOT NULL AUTO_INCREMENT,
		  `userrole` tinytext NOT NULL,
		  PRIMARY KEY (`id_userrole`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
	", $dbweb, __FILE__, __LINE__);
	$results=q("SELECT * FROM cms_userroles LIMIT 1;", $dbweb, __FILE__, __LINE__);
	q("
		REPLACE INTO `cms_userroles` (`id_userrole`, `userrole`) VALUES
		(1, 'Administrator'),
		(2, 'Benutzer'),
		(6, 'Gast')
	", $dbweb, __FILE__, __LINE__);


	//cms_userroles_scripts
	q("
		CREATE TABLE IF NOT EXISTS `cms_userroles_scripts` (
		  `id` int(11) NOT NULL auto_increment,
		  `userrole_id` int(11) NOT NULL,
		  `script` tinytext NOT NULL,
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
	", $dbweb, __FILE__, __LINE__);
	$results=q("SELECT * FROM cms_userroles_scripts LIMIT 1;", $dbweb, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		q("
			REPLACE INTO `cms_userroles_scripts` (`id`, `userrole_id`, `script`) VALUES
			(153, 1, 'company_history.php'),
			(73, 6, 'user.php'),
			(152, 1, 'company.php'),
			(151, 1, 'backend_users.php'),
			(150, 1, 'backend_userroles.php'),
			(149, 1, 'backend_userrole_editor.php'),
			(148, 1, 'backend_user_editor.php'),
			(147, 1, 'backend_shop_payment.php'),
			(146, 1, 'backend_shop_orders.php'),
			(145, 1, 'backend_shop_order.php'),
			(175, 1, 'shop_cart.php'),
			(176, 1, 'shop_catalogues.php'),
			(173, 1, 'search.php'),
			(174, 1, 'shop.php'),
			(171, 1, 'response_test.php'),
			(172, 1, 'response_test2.php'),
			(169, 1, 'quality.php'),
			(170, 1, 'register.php'),
			(140, 1, 'backend_shop_offers.php'),
			(139, 1, 'backend_shop_myoffers.php'),
			(138, 1, 'backend_shop_items.php'),
			(137, 1, 'backend_shop_item_editor.php'),
			(136, 1, 'backend_shop_index.php'),
			(135, 1, 'backend_mm_stocks.php'),
			(134, 1, 'backend_mm_index.php'),
			(133, 1, 'backend_mm_import.php'),
			(132, 1, 'backend_mm_arrival.php'),
			(131, 1, 'backend_index.php'),
			(130, 1, 'backend_hr_index.php'),
			(129, 1, 'backend_cms_users.php'),
			(128, 1, 'backend_cms_userroles.php'),
			(127, 1, 'backend_cms_userrole_editor.php'),
			(126, 1, 'backend_cms_userimport.php'),
			(125, 1, 'backend_cms_user_editor.php'),
			(124, 1, 'backend_cms_translations_export.php'),
			(123, 1, 'backend_cms_translations.php'),
			(122, 1, 'backend_cms_menus.php'),
			(121, 1, 'backend_cms_library.php'),
			(120, 1, 'backend_cms_languages.php'),
			(119, 1, 'backend_cms_language_editor.php'),
			(118, 1, 'backend_cms_labels.php'),
			(117, 1, 'backend_cms_label_editor.php'),
			(116, 1, 'backend_cms_index.php'),
			(115, 1, 'backend_cms_files.php'),
			(113, 1, 'backend_cms_articles.php'),
			(74, 6, 'user_carfleet.php'),
			(75, 6, 'user_index.php'),
			(76, 6, 'shop.php'),
			(77, 6, 'shop_cart.php'),
			(78, 6, 'shop_catalogues.php'),
			(79, 6, 'shop_item.php'),
			(80, 6, 'shop_login.php'),
			(81, 6, 'shop_logout.php'),
			(84, 6, 'shop_register.php'),
			(85, 6, 'shop_search.php'),
			(86, 6, 'shop_searchbycar.php'),
			(87, 6, 'shop_status.php'),
			(88, 6, 'shop_user.php'),
			(89, 6, 'shop_user_orders.php'),
			(91, 6, 'company.php'),
			(92, 6, 'company_history.php'),
			(93, 6, 'company_jobs.php'),
			(94, 6, 'company_philosophy.php'),
			(95, 6, 'company_staff.php'),
			(96, 6, 'config.php'),
			(97, 6, 'contact.php'),
			(99, 6, 'gtct.php'),
			(100, 6, 'gtct2.php'),
			(101, 6, 'help.php'),
			(102, 6, 'hps.php'),
			(103, 6, 'imprint.php'),
			(104, 6, 'index.php'),
			(105, 6, 'password.php'),
			(106, 6, 'products.php'),
			(107, 6, 'quality.php'),
			(108, 6, 'register.php'),
			(112, 1, 'backend_cms_article_editor.php'),
			(114, 1, 'backend_cms_file_editor.php'),
			(154, 1, 'company_jobs.php'),
			(155, 1, 'company_philosophy.php'),
			(156, 1, 'company_staff.php'),
			(157, 1, 'config.php'),
			(158, 1, 'contact.php'),
			(159, 1, 'gtct.php'),
			(160, 1, 'gtct2.php'),
			(161, 1, 'help.php'),
			(162, 1, 'hps.php'),
			(163, 1, 'imprint.php'),
			(164, 1, 'index.php'),
			(165, 1, 'login.php'),
			(166, 1, 'logout.php'),
			(167, 1, 'password.php'),
			(168, 1, 'products.php'),
			(177, 1, 'shop_category.php'),
			(178, 1, 'shop_item.php'),
			(179, 1, 'shop_login.php'),
			(180, 1, 'shop_logout.php'),
			(183, 1, 'shop_register.php'),
			(184, 1, 'shop_search.php'),
			(185, 1, 'shop_searchbycar.php'),
			(186, 1, 'shop_status.php'),
			(187, 1, 'shop_user.php'),
			(188, 1, 'shop_user_orders.php'),
			(189, 1, 'shopitem.php'),
			(191, 1, 'translations.php'),
			(192, 1, 'user.php'),
			(193, 1, 'user_carfleet.php'),
			(194, 1, 'user_index.php'),
			(261, 1, 'backend_hr_employees.php'),
			(260, 1, 'backend_hr_employee_editor.php'),
			(374, 1, 'backend_hr_employee_import.php'),
			(375, 1, 'backend_shop_tecdoc_import.php'),
			(743, 1, 'backend_mapco_gart_export.php'),
			(747, 6, 'company_reasons.php'),
			(767, 1, 'newsletter_archive.php'),
			(766, 1, 'news_archive.php'),
			(576, 1, 'backend_shop_items_ebayexport.php'),
			(577, 1, 'backend_shop_stats.php'),
			(621, 1, 'backend_shop_partners.php'),
			(685, 1, 'backend_mapco_cup.php'),
			(741, 1, 'backend_mapco_replacements.php'),
			(745, 1, 'company_reasons.php'),
			(768, 1, 'newsletter_subscription.php'),
			(769, 1, 'newsletter_web.php'),
			(774, 6, 'news_archive.php'),
			(775, 6, 'newsletter_archive.php'),
			(776, 6, 'newsletter_subscription.php'),
			(777, 6, 'newsletter_web.php'),
			(820, 1, 'press_releases.php'),
			(821, 1, 'press_reports.php'),
			(822, 1, 'backend_cms_press.php'),
			(823, 6, 'press_releases.php'),
			(824, 6, 'press_reports.php'),
			(825, 1, 'index.php');
		", $dbweb, __FILE__, __LINE__);
	}


	include("templates/".TEMPLATE_BACKEND."/header.php");
	
	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > Content Management';
	echo '</p>';

	echo '<h1>Schnellzugriff</h1>';
	echo '<ul class="quickaccess">';
	show_tree(1, true);
	echo '</ul>';

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
