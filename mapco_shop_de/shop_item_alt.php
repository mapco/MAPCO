<?php
	include("config.php");
	include("functions/cms_remove_element.php");
	include("functions/mapco_get_titles.php");
	include("functions/shop_itemstatus.php");
	include("functions/shop_get_prices.php");
	include("functions/mapco_cutout.php");	
	include("functions/mapco_hide_price.php");	

	if ( !is_numeric($_GET["id_item"]) )
	{
		header("HTTP/1.0 404 Not Found");
		exit;
	}

	$results=q("SELECT * FROM shop_items WHERE id_item='".$_GET["id_item"]."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
	if ( mysql_num_rows($results)==0 )
	{
		include("templates/".TEMPLATE."/header.php");
		include("modules/cms_leftcolumn.php");
		echo '<div id="mid_right_column">';
		echo '<p>Artikel nicht gefunden.</p>';
		echo '</div>';
		include("templates/".TEMPLATE."/footer.php");
		exit;
	}
	$row=mysql_fetch_array($results);
	$shop_items=$row;
	$_GET["id_category"]=$row["category_id"];

	//header
	$right_column=true;
	include("templates/".TEMPLATE."/header.php");

	include("modules/cms_leftcolumn.php");
	echo '<div id="mid_right_column">';

?>

    <script type="text/javascript">

		function ebay_update_ma(id_item)
		{
			$.post("<?php echo PATH; ?>modules/backend_ebay_auction_actions.php", { action:"item_submit", id_item:id_item, pricelist_id:16815, id_account:1 }, function(data) { show_status(data); } );
		}

		function ebay_update_ap(id_item)
		{
			$.post("<?php echo PATH; ?>modules/backend_ebay_auction_actions.php", { action:"item_submit", id_item:id_item, pricelist_id:18209, id_account:2 }, function(data) { show_status(data); } );
		}

		function tecdoc_update(id_item)
		{
			$.post("<?php echo PATH; ?>soa/", { API:"shop", Action:"ItemUpdate", id_item:id_item }, function(data) { show_status(data); } )
		}
	
	</script>
	
<?php

	//PATH
	$results=q("SELECT * FROM shop_items AS a, shop_items_".$_GET["lang"]." AS b WHERE a.id_item='".$_GET["id_item"]."' AND a.id_item=b.id_item;", $dbshop, __FILE__, __LINE__);
	$row=mysql_fetch_array($results);
	$results2=q("SELECT alias, title FROM cms_menuitems WHERE id_menuitem='".$row["menuitem_id"]."';", $dbweb, __FILE__, __LINE__);
	$row2=mysql_fetch_array($results2);
	echo '<p>';
	echo '<a href="'.PATH.$_GET["lang"].'/online-shop/">Online-Shop</a>';
	echo ' > <a href="'.PATH.$_GET["lang"].'/'.$row2["alias"].'">'.$row2["title"].'</a>';
	echo ' > '.$row["title"];
	echo '</p>';


	echo '<form method="post">';
	if ($_SESSION["id_user"]==28642 or $_SESSION["id_user"]==21371 or $_SESSION["id_user"]==22733 or $_SESSION["id_user"]==22659 or $_SESSION["id_user"]==22044)
	{
		echo '<input class="formbutton" type="button" value="TecDoc-Update" onclick="tecdoc_update('.$_GET["id_item"].');" />';
	}
	if ($_SESSION["id_user"]==22044)
	{
		echo '<input class="formbutton" type="button" value="Ebay-Update MAPCO" onclick="ebay_update_ma('.$_GET["id_item"].');" />';
		echo '<input class="formbutton" type="button" value="Ebay-Update AP" onclick="ebay_update_ap('.$_GET["id_item"].');" />';
	}
	echo '</form>';
	
	//Hotline-Banner
	if ($_GET["lang"]=="de")
	{
		echo '<img src="'.PATH.'images/hotline_banner.png" alt="'.t("Haben Sie technische Fragen oder sind sich nicht sicher ob der Artikel bei Ihrem Fahrzeug passt? Dann rufen Sie uns an!").'" title="'.t("Haben Sie technische Fragen oder sind sich nicht sicher ob der Artikel bei Ihrem Fahrzeug passt? Dann rufen Sie uns an!").'"/>';
	}

	//title
//	$results=q("SELECT * FROM shop_items AS a, shop_items_".$_GET["lang"]." AS b WHERE a.id_item='".$_GET["id_item"]."' AND a.id_item=b.id_item;", $dbshop, __FILE__, __LINE__);
//	$row=mysql_fetch_array($results);
	$artnr=$row["MPN"];
	echo '<h1>'.$row["title"].'</h1>';
	$title=$row["title"];

	//price
	$hide_price=hide_price($_SESSION["id_user"]);
	
	echo '<div style="width:200px; display:inline; float:right;">';
	$price = get_prices($row["id_item"]);
	if ($price["discount"]>0)
	{
		echo '	<span style="width:100px; font-size:10px; font-weight:bold; font-style:italic; color:#ff0000;">';
		echo 'AKTIONSPREIS!';
		echo '	</span><br />';
		echo '	<span style="width:100px; font-size:30px; font-weight:bold; font-style:italic; color:#ff0000;">';	
	}
	else
	{
		echo '	<span style="width:100px; font-size:10px; font-weight:bold; color:#000000;">';
		echo t("Ihr Preis").':';
		echo '	</span><br />';
		echo '	<span style="width:100px; font-size:24px; font-weight:bold; font-style:italic; color:#000000;">';	
	}
	if ($price["total"]<9000)
	{
		if ($hide_price)
		{
			echo '<span id="hide_price"';
			echo 'onmouseover="this.innerHTML = \'		€ '.number_format($price["total"], 2).'\'"';

			if ($price["brutto"]>0)
			{
				echo 'onmouseout="this.innerHTML = \'€ '.number_format($price["brutto"], 2).'\'">';
				echo '€ '.number_format($price["brutto"], 2);
			}
			else 
			{
				echo 'onmouseout="this.innerHTML = \''.t("Preis auf Anfrage").'\'">';
				echo t("Preis auf Anfrage");
			}

//			echo 'onmouseout="this.innerHTML = \'		€ '.number_format($price["brutto"], 2).'\'">';
//			echo '		€ '.number_format($price["brutto"], 2);
			echo '</span>';
		}
		else echo '		€ '.number_format($price["total"], 2);
	}
	else 
	{
		echo '<span style="width:100px; font-size:18px; font-weight:bold; color:#000000;">';	
		echo 'Preis auf Anfrage';
		echo '</span>';	
	}
	echo '	</span>';
	if ($price["collateral_total"]>0)
	{
		echo '<span style="font-size:10px; font-weight:bold; color:#ff0000;">';
		echo '<br />zzgl. € '.number_format($price["collateral_total"], 2).' '.t("Altteilpfand");
		echo '</span>';
	}
	echo '<span style="font-size:12px; color:#ff0000;">';
	if (isset($price["season_price"]))
	{
		 echo '	<br />€ '.number_format($price["season_price"][0], 2).' '.t("ab").' '.$price["season_amount"][0].' Stück<br />';
	}
	echo '</span>';	
	echo '<span style="font-size:10px;">';
	if ($price["total"]==$price["gross"]) echo '	<br />'.t("inkl. Mehrwertsteuer").' ('.$price["VAT"].'%)';
	echo '	<br /><a href="'.PATH.'shipping_costs.php?lang=de&id_menuitem=169" target="_blank">'.t("zzgl. Versandkosten").'</a></span>';
	if ($price["brutto"]>0 and $price["brutto"]>$price["total"])
	{
		echo '<br />';
		echo '<span style="font-size:10px;">';
		echo '<br />'.t("unverbindl. Preisempfehlung").' € '.number_format($price["brutto"], 2);
		echo '</span>';
		if ($price["total"]==$price["net"])
		{
			echo '<span style="font-size:16px; font-weight:bold;">';
			echo '<br />'.t("Ihr Rabatt").':&nbsp&nbsp&nbsp'.number_format($price["percent"], 1).' %<br />';
			echo '</span>';
		}
		else echo '<br />'; 

	}
	if ($price["total"]<9000)
	{
		echo '	<br /><input id="article'.$row["id_item"].'" type="text" size="1" value="1" onkeyup="cart_add_enter('.$row["id_item"].')" />';
		echo '	<input type="button" onclick="javascript:cart_add('.$row["id_item"].');" value="'.t("In den Warenkorb").'" name="form_button" />';
	}
	if($_SESSION["rcid"]!=9999 and $_SESSION["rcid"]>0)
	{
		echo '<br />'.itemstatus_rc($_GET["id_item"]);
	}
	else
	{
		echo '<br />'.itemstatus($_GET["id_item"]);
	}
	
	//GOOGLE +1 BUTTON	
	echo '<br /><br /><br />';
	echo '<div class="g-plusone" data-size="medium"></div>';
	//FACEBOOK LIKE BUTTON	
	echo '<br /><br />';
	echo '<div class="fb-like" data-send="false" data-layout="button_count" data-width="100" data-show-faces="false"></div>';
	echo '<br /><br />';
	echo '</div>';
	
	
	//small images
	$results3=q("SELECT * FROM cms_articles_images WHERE article_id=".$shop_items["article_id"]." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
	if ( mysql_num_rows($results3)>1 )
	{
		echo '<div style="width:500px; margin:2px; border:1px solid #cccccc; padding:0px; float:left;">';
		while($row3=mysql_fetch_array($results3))
		{
			$results2=q("SELECT * FROM cms_files WHERE original_id='".$row3["file_id"]."' AND imageformat_id=8 LIMIT 1;", $dbweb, __FILE__, __LINE__);
			$row2=mysql_fetch_array($results2);
	//		echo '!'.$row2["id_file"].'!<br />';
			$filename='files/'.floor(bcdiv($row2["id_file"], 1000)).'/'.$row2["id_file"].'.'.$row2["extension"];
			if ( !file_exists($filename) ) $filename='images/library/rahmen-bild-folgt.jpg';
			echo '<img style="width:100px; margin:2px; border:0; padding:0;" src="'.PATH.$filename.'" alt="'.$title.'" title="'.$title.'" onmouseover="document.getElementById(\'bigimage\').src=this.src" />';
		}
		echo '</div>';
	}
	//big image
	echo '<div style="width:500px; margin:2px; border:1px solid #cccccc; padding:0px; float:left;">';
	$results3=q("SELECT * FROM cms_articles_images WHERE article_id=".$shop_items["article_id"]." ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
	if (mysql_num_rows($results3)>0)
	{
		$row3=mysql_fetch_array($results3);
		$results2=q("SELECT * FROM cms_files WHERE original_id='".$row3["file_id"]."' AND imageformat_id=8 LIMIT 1;", $dbweb, __FILE__, __LINE__);
		$row2=mysql_fetch_array($results2);
		$filename='files/'.floor(bcdiv($row2["id_file"], 1000)).'/'.$row2["id_file"].'.'.$row2["extension"];
		if ( !file_exists($filename) ) $filename='images/library/rahmen-bild-folgt.jpg';
		echo '	<img style="width:500px;" src="'.PATH.$filename.'" alt="'.$title.'" title="'.$title.'" id="bigimage" />';
	}
	else
	{
//		echo '	<img style="width:500px;" src="images/artikel/00000.jpg" alt="'.$title.'" title="'.$title.'" />';
		echo '	<img style="width:500px;" src="'.PATH.'images/library/rahmen-bild-folgt.jpg" alt="'.$title.'" title="'.$title.'" />';
	}
	echo '</div>';
	
	
	//Der-Schrauber-Hilfetexte
	$results3=q("SELECT * FROM cms_articles_shopitems WHERE item_id=".$_GET["id_item"].";", $dbweb, __FILE__, __LINE__);
	while( $row3=mysql_fetch_array($results3) )
	{
		$results2=q("SELECT * FROM cms_articles WHERE id_article=".$row3["article_id"].";", $dbweb, __FILE__, __LINE__);
		if ( mysql_num_rows($results2)>0 )
		{
			$row2=mysql_fetch_array($results2);
			if ( $row2["published"]>0 )
			{
				echo '<br style="clear:both;" />';
				echo '<br style="clear:both;" />';
				echo '<div style="border:2px solid #bb3712; padding:5px;">';
				echo '<h2>Der Schrauber hilft!</h2>';
				echo '<img src="http://www.mapco.de/images/schrauber.png" alt="Der Schrauber" style="width:300px; margin:0px 5px 3px 0px; float:left;" title="Der Schrauber">';
				echo nl2br($row2["article"]);
				echo '</div>';
				echo '<br style="clear:both;" />';
			}
		}
	}
	
	//description
	$description = $row["description"];
//	$description = cutout($description, 'OE START -->', '<!-- OE STOP');
	$description = cutout($description, 'OEM START -->', '<!-- OEM STOP');
//	$description = cutout($description, 'OETXT START -->', '<!-- OETXT STOP');
	echo $description;

	//lastmod
	$results=q("SELECT lastmod FROM shop_items WHERE id_item='".$_GET["id_item"]."';", $dbshop, __FILE__, __LINE__);
	$row=mysql_fetch_array($results);
	echo '<br style="clear:both;" />'.t("Letzte Aktualisierung").': '.date("d.m.Y H:i", $row["lastmod"]);

	echo '</div>';

	include("templates/".TEMPLATE."/footer.php");
?>