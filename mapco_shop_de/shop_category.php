<?php
	include("config.php");
	include("functions/shop_show_item.php");
	include("functions/shop_itemstatus.php");
	include("functions/mapco_gewerblich.php");
	include("functions/mapco_get_titles.php");	
	include("functions/mapco_motorart.php");
	include("functions/mapco_baujahr.php");
	include("functions/cms_url_encode.php");
	include("functions/cms_t.php");
	include("functions/cms_tl.php");
	
	if( $_SESSION["id_shop"] == 18 )
	{
		$login_required=true;	
	}

	if ( !isset($_GET["id_menuitem"]) )
	{
		echo 'Es wurde keine Kategorie angegeben.';
		exit;
	}
	
	if ( !($_GET["id_menuitem"]>0) )
	{
		echo 'Es wurde keine gültige Kategorie angegeben.';
		exit;
	}
	
	if( $_GET["page"]==1 )
	{
		header("HTTP/1.1 301 Moved Permanently");
		header("location: ".PATHLANG.$_GET["url"] );
		exit;
	}

	//show category as title and h1
	$results=q("SELECT * FROM cms_menuitems WHERE id_menuitem=".$_GET["id_menuitem"].";", $dbweb, __FILE__, __LINE__);
	if ( mysqli_num_rows($results)==0 )
	{
		echo '<CategoryViewResponse>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Kategorie nicht gefunden.</shortMsg>'."\n";
		echo '		<longMsg>Es muss eine gültige Kategorie übergeben werden, damit eine Kategorie angezeigt werden kann.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</CategoryViewResponse>'."\n";
		exit;
	}
	$row=mysqli_fetch_array($results);
	$h1=tl($row["id_menuitem"], "title");
	$menuitem_id=$row["menuitem_id"];
	//meta title with pages for SEO (no duplicate title)
	if ( !isset($_GET["page"]) ) $_GET["page"]=1;
	if( $_GET["page"]!=1 ) $meta_title.=' ('.$_GET["page"].')';
	if( $_GET["page"]>1 ) $meta_index="NOINDEX,FOLLOW";
	//SEO: dynamic meta-title and meta-description
	if( $meta_title=="" ) $meta_title=$h1." für das Auto | MAPCO";
	if( $meta_description=="" ) $meta_description="MAPCO hat tolle Angebote in ".$h1." für das Auto. Bei MAPCO einkaufen!";
	include("templates/".TEMPLATE."/header.php");
	include("templates/".TEMPLATE."/cms_leftcolumn_shop.php");
	echo '<div id="mid_right_column">';

	//PATH
	echo '<p>';
	echo '<a href="'.PATHLANG.tl(820, "alias").'" title="'.tl(820, "description").'" >'.tl(820, "title").'</a>';
	echo ' > <a href="'.PATHLANG.tl($menuitem_id, "alias").'" title="'.tl($menuitem_id, "description").'">'.tl($menuitem_id, "title").'</a>';
	echo ' > '.tl($_GET["id_menuitem"], "title");
	echo '</p>';

	//h1 with page for SEO (no duplicate title)
	echo '<h1>MAPCO '.$h1;
	if( $_GET["page"]!=1 ) echo ' ('.$_GET["page"].')';
	echo '</h1>';

	//sub categories
	$results=q("SELECT * FROM cms_menuitems WHERE menuitem_id=".$_GET["id_menuitem"]." AND menu_id=5 ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
	if (mysqli_num_rows($results)>0)
	{
		echo '<h3>'.t("Artikelgruppen").'</h3>';
		show_tree($_GET["id_menuitem"], false);
	}

	//results
	$results=q("SELECT * FROM shop_items WHERE menuitem_id='".mysqli_real_escape_string($dbshop, $_GET["id_menuitem"])."' AND active>0 ORDER BY MPN;", $dbshop, __FILE__, __LINE__);
	if( mysqli_num_rows($results)>0 )
	{
		$pages=ceil(mysqli_num_rows($results) / 20);
		$min=($_GET["page"]-1)*20+1;
		$max=$_GET["page"]*20;
	
	
		//show video if available
		$garts=array();
		$results2=q("SELECT GART FROM shop_items WHERE menuitem_id='".mysqli_real_escape_string($dbshop, $_GET["id_menuitem"])."' GROUP BY GART;", $dbshop, __FILE__, __LINE__);
		while( $row2=mysqli_fetch_array($results2) )
		{
			if( $row2["GART"]!=0 ) $garts[]=$row2["GART"]*1;
		}
		if( sizeof($garts)>0 )
		{
			$results2=q("SELECT * FROM mapco_gart_export WHERE GART IN (".implode(", ", $garts).");", $dbshop, __FILE__, __LINE__);
			while($row2=mysqli_fetch_array($results2))
			{
				if($row2["youtube"]!="")
				{
					echo '
						</p>
						<iframe style="margin:0px 0px 0px 100px;" id="ytplayer" type="text/html" width="546" height="307"
						src="https://www.youtube.com/embed/'.$row2["youtube"].'?rel=0"
						frameborder="0" allowfullscreen></iframe>
						<br style="clear:both;" />
						<p>
					';
					break;
				}
			}
		}
	
	
		echo '<h2>MAPCO '.$h1.' ('.$_GET["page"].' / '.$pages.')</h2>';

		//show page menu
		echo '<div style="width:100%; margin:5px 0px;">';
		echo '<div style="float:left; width:12%; text-align:left; margin:5px 0px;">';
		$link=PATHLANG.$_GET["url"];
		if( ($_GET["page"]-1)!=1 ) $link.='?page='.($_GET["page"]-1);
		if ($_GET["page"]>1) echo '<a href="'.$link.'" />'.t("Seite zurück").'</a>';
		else echo '<span style="color:lightgrey;">'.t("Seite zurück").'</span>';
		echo '</div>';
		echo '<div style="float:left; width:76%; text-align:center; margin:5px 0px;">';
		for($i=1; $i<=$pages; $i++)
		{
			if ( $_GET["page"]==$i ) $style=' style="font-weight:bold;"'; else $style='';
			$link=PATHLANG.$_GET["url"];
			if( $i!=1 ) $link.='?page='.$i;
			echo '<a'.$style.' href="'.str_replace(" ", "%20", $link).'" />'.$i.'</a>';
			echo '&nbsp; &nbsp;';
		}
		echo '</div>';
		echo '<div style="float:left; width:12%; text-align:right; margin:5px 0px;">';
		$link=PATHLANG.$_GET["url"];
		if( ($_GET["page"]+1)!=1 ) $link.='?page='.($_GET["page"]+1);
		if ($_GET["page"]<$pages) echo '<a href="'.$link.'" />'.t("nächste Seite").'</a>';
		else echo '<span style="color:lightgrey;">'.t("nächste Seite").'</span>';
		echo '</div>';
		echo '</div>';
	
	
		//show items
		$i=0;
		while( $row=mysqli_fetch_array($results) )
		{
			$results2=q("SELECT * FROM shop_items_".$_GET["lang"]." WHERE id_item=".$row["id_item"].";", $dbshop, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			$show=true;
			if ( isset($names) )
			{
				for($i=0; $i<sizeof($names); $i++)
				{
					if ( isset($_GET[str_replace(" ", "_", $names[$i])]) )
					{
						$show = strpos($row2["short_description"], $names[$i].": ".$_GET[str_replace(" ", "_", $names[$i])]);
					}
				}
			}
			if ($show!==false)
			{
				$i++;
				if ($i>=$min and $i<=$max) show_item($row["id_item"], $row["MPN"], $row2["title"], $row2["short_description"]);
			}
		}

		//show page menu
		echo '<div style="width:100%; margin:5px 0px;">';
		echo '<div style="float:left; width:12%; text-align:left; margin:5px 0px;">';
		$link=PATHLANG.$_GET["url"];
		if( ($_GET["page"]-1)!=1 ) $link.='?page='.($_GET["page"]-1);
		if ($_GET["page"]>1) echo '<a href="'.$link.'" />'.t("Seite zurück").'</a>';
		else echo '<span style="color:lightgrey;">'.t("Seite zurück").'</span>';
		echo '</div>';
		echo '<div style="float:left; width:76%; text-align:center; margin:5px 0px;">';
		for($i=1; $i<=$pages; $i++)
		{
			if ( $_GET["page"]==$i ) $style=' style="font-weight:bold;"'; else $style='';
			$link=PATHLANG.$_GET["url"];
			if( $i!=1 ) $link.='?page='.$i;
			echo '<a'.$style.' href="'.str_replace(" ", "%20", $link).'" />'.$i.'</a>';
			echo '&nbsp; &nbsp;';
		}
		echo '</div>';
		echo '<div style="float:left; width:12%; text-align:right; margin:5px 0px;">';
		$link=PATHLANG.$_GET["url"];
		if( ($_GET["page"]+1)!=1 ) $link.='?page='.($_GET["page"]+1);
		if ($_GET["page"]<$pages) echo '<a href="'.$link.'" />'.t("nächste Seite").'</a>';
		else echo '<span style="color:lightgrey;">'.t("nächste Seite").'</span>';
		echo '</div>';
		echo '</div>';
	}

	echo '</div>';
	include("templates/".TEMPLATE."/footer.php");
?>