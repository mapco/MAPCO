<?php
	include("config.php");
	$login_required=true;
	include("templates/".TEMPLATE_BACKEND."/header.php");


	//CREATE TABLE shop_lists
	q("	
		CREATE TABLE IF NOT EXISTS `shop_lists` (
		  `id_list` int(11) NOT NULL AUTO_INCREMENT,
		  `title` tinytext NOT NULL,
		  `firstmod` int(11) NOT NULL,
		  `firstmod_user` int(11) NOT NULL,
		  `lastmod` int(11) NOT NULL,
		  `lastmod_user` int(11) NOT NULL,
		  PRIMARY KEY (`id_list`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
	", $dbshop, __FILE__, __LINE__);

	//CREATE TABLE shop_lists
	q("	
		CREATE TABLE IF NOT EXISTS `shop_lists_items` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `list_id` int(11) NOT NULL,
		  `item_id` int(11) NOT NULL,
		  `firstmod` int(11) NOT NULL,
		  `firstmod_user` int(11) NOT NULL,
		  `lastmod` int(11) NOT NULL,
		  `lastmod_user` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
	", $dbshop, __FILE__, __LINE__);

	//CREATE TABLE shop_carts
	q("	
		CREATE TABLE IF NOT EXISTS `shop_carts` (
		  `id_carts` int(11) NOT NULL AUTO_INCREMENT,
		  `item_id` int(11) NOT NULL,
		  `amount` int(11) NOT NULL,
		  `session_id` tinytext NOT NULL,
		  PRIMARY KEY (`id_carts`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
	", $dbshop, __FILE__, __LINE__);


	//CREATE TABLE shop_orders
	q("
		CREATE TABLE IF NOT EXISTS `shop_orders` (
		  `id_order` int(11) NOT NULL AUTO_INCREMENT,
		  `status_id` int(11) NOT NULL,
		  `customer_id` int(11) NOT NULL,
		  `ordernr` tinytext NOT NULL,
		  `comment` text NOT NULL,
		  `usermail` tinytext NOT NULL,
		  `userphone` tinytext NOT NULL,
		  `userfax` tinytext NOT NULL,
		  `usermobile` tinytext NOT NULL,
		  `bill_company` tinytext NOT NULL,
		  `bill_gender` tinytext NOT NULL,
		  `bill_title` tinytext NOT NULL,
		  `bill_firstname` tinytext NOT NULL,
		  `bill_lastname` tinytext NOT NULL,
		  `bill_zip` tinytext NOT NULL,
		  `bill_city` tinytext NOT NULL,
		  `bill_street` tinytext NOT NULL,
		  `bill_number` tinytext NOT NULL,
		  `bill_additional` tinytext NOT NULL,
		  `bill_country` tinytext NOT NULL,
		  `ship_company` tinytext NOT NULL,
		  `ship_gender` tinytext NOT NULL,
		  `ship_title` tinytext NOT NULL,
		  `ship_firstname` tinytext NOT NULL,
		  `ship_lastname` tinytext NOT NULL,
		  `ship_zip` tinytext NOT NULL,
		  `ship_city` tinytext NOT NULL,
		  `ship_street` tinytext NOT NULL,
		  `ship_number` tinytext NOT NULL,
		  `ship_additional` tinytext NOT NULL,
		  `ship_country` tinytext NOT NULL,
		  `shipping_costs` float NOT NULL,
		  `shipping_details` tinytext NOT NULL,
		  `firstmod` int(11) NOT NULL,
		  `firstmod_user` int(11) NOT NULL,
		  `lastmod` int(11) NOT NULL,
		  `lastmod_user` int(11) NOT NULL,
		  PRIMARY KEY (`id_order`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1655088 ;
	", $dbshop, __FILE__, __LINE__);


	//CREATE TABLE shop_orders_items
	q("
		CREATE TABLE IF NOT EXISTS `shop_orders_items` (
		  `id` int(11) NOT NULL auto_increment,
		  `order_id` int(11) NOT NULL,
		  `item_id` int(11) NOT NULL,
		  `amount` int(11) NOT NULL,
		  `price` double NOT NULL,
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
	", $dbshop, __FILE__, __LINE__);


	//CREATE TABLE shop_payment
	q("
		CREATE TABLE IF NOT EXISTS `shop_payment` (
		  `id_payment` int(11) NOT NULL AUTO_INCREMENT,
		  `title` tinytext NOT NULL,
		  `memo` text NOT NULL,
		  `ordering` int(11) NOT NULL,
		  `firstmod` int(11) NOT NULL,
		  `firstmod_user_id` int(11) NOT NULL,
		  `lastmod` int(11) NOT NULL,
		  `lastmod_user_id` int(11) NOT NULL,
		  PRIMARY KEY (`id_payment`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
	", $dbshop, __FILE__, __LINE__);


	//CREATE TABLE shop_price_research
	q("
		CREATE TABLE IF NOT EXISTS `shop_price_research` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `item_id` int(11) NOT NULL,
		  `price` double NOT NULL,
		  `shipping` double NOT NULL,
		  `seller` tinytext NOT NULL,
		  `EbayID` bigint(20) NOT NULL,
		  `firstmod` int(11) NOT NULL,
		  `firstmod_user` int(11) NOT NULL,
		  `lastmod` int(11) NOT NULL,
		  `lastmod_user` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
	", $dbshop, __FILE__, __LINE__);


	//CREATE TABLE shop_shipping
	q("
		CREATE TABLE IF NOT EXISTS `shop_shipping` (
		  `id_shipping` int(11) NOT NULL AUTO_INCREMENT,
		  `title` tinytext NOT NULL,
		  `memo` text NOT NULL,
		  `price` float NOT NULL,
		  `payment_id` int(11) NOT NULL,
		  `ordering` int(11) NOT NULL,
		  `firstmod` int(11) NOT NULL,
		  `firstmod_user_id` int(11) NOT NULL,
		  `lastmod` int(11) NOT NULL,
		  `lastmod_user_id` int(11) NOT NULL,
		  PRIMARY KEY (`id_shipping`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
	", $dbshop, __FILE__, __LINE__);


	//CREATE TABLE shop_shipping
	q("
		CREATE TABLE IF NOT EXISTS `shop_carts` (
		  `id_carts` int(11) NOT NULL AUTO_INCREMENT,
		  `item_id` int(11) NOT NULL,
		  `amount` int(11) NOT NULL,
		  `session_id` tinytext NOT NULL,
		  PRIMARY KEY (`id_carts`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
	", $dbshop, __FILE__, __LINE__);


	//CREATE TABLE shop_shipping
	q("
		CREATE TABLE IF NOT EXISTS `shop_items` (
		  `id_item` int(11) NOT NULL AUTO_INCREMENT,
		  `price` float NOT NULL,
		  `menuitem_id` int(11) NOT NULL,
		  `firstmod` int(11) NOT NULL,
		  `lastmod` int(11) NOT NULL,
		  PRIMARY KEY (`id_item`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1543875;
	", $dbshop, __FILE__, __LINE__);
	q("
		CREATE TABLE IF NOT EXISTS `shop_items_de` (
		  `id_item` int(11) NOT NULL AUTO_INCREMENT,
		  `title` tinytext NOT NULL,
		  `short_description` text NOT NULL,
		  `description` text NOT NULL,
		  PRIMARY KEY (`id_item`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1543875;
	", $dbshop, __FILE__, __LINE__);


	//CREATE TABLE shop_shipping
	q("
		CREATE TABLE IF NOT EXISTS `shop_offers` (
		  `id_offer` int(11) NOT NULL AUTO_INCREMENT,
		  `item_id` int(11) NOT NULL,
		  `percent` double NOT NULL,
		  `from` int(11) NOT NULL,
		  `until` int(11) NOT NULL,
		  `firstmod` int(11) NOT NULL,
		  `firstmod_user` int(11) NOT NULL,
		  `lastmod` int(11) NOT NULL DEFAULT '0',
		  `lastmod_user` int(11) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id_offer`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
	", $dbshop, __FILE__, __LINE__);


	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > Online-Shop';
	echo '</p>';

	echo '<h1>Schnellzugriff</h1>';
	echo '<ul class="quickaccess">';
	show_tree(2, true);
	echo '</ul>';

	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>
