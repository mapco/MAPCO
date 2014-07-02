<?php
	$starttime = time()+microtime();
	
	require_once("../functions/cms_t2.php");

	//list and itemgroups view
	echo '<br style="clear:both;" />';
	echo '<div style="width:100%; float:left;">';
	if ( $_POST["id_menuitem"]>0 ) $style=''; else $style=' style="font-weight:bold;"';
	echo '<a'.$style.' class="tab" href="javascript:show_lists();" id="tab_lists">'.t("Listen", __FILE__, __LINE__).'</a>';
	if ( $_POST["id_menuitem"]>0 ) $style=' style="font-weight:bold;"'; else $style='';
	echo '<a'.$style.' class="tab" href="javascript:show_itemgroups();" id="tab_itemgroups">Artikelgruppen</a>';
	echo '<img alt="Liste hinzufügen" onclick="list_add();" src="'.PATH.'images/icons/24x24/add.png" style="cursor:pointer;" title="Liste hinzufügen" />';
	echo '<br style="clear:both;" />';

	//lists
	echo '<div style="width:332px; float:left;">';
	$results=q("SELECT * FROM shop_listtypes ORDER BY ordering;", $dbshop, __FILE__, __LINE__);	
	while( $row=mysqli_fetch_array($results) )
	{
/*
		echo '<li class="header">';
		echo '	<div style="width:236px; text-align:left;">'.t("öffentliche Listen", __FILE__, __LINE__).'</div>';
		echo '	<div style="width:24px; float:right;">';
		echo '<div style="width:60px;height:24px; float:right;">';
	//		echo '<span id="list_add_old_button" title="Liste hinzufügen"></span>';
		echo '<img alt="Liste hinzufügen" onclick="list_add();" src="'.PATH.'images/icons/24x24/note_add.png" style="float:right;" title="Liste hinzufügen" />';
		echo '</div>';
		echo '	</div>';
		echo '</li>';
*/

		if( $row["id_listtype"]==2 )
		{
			$results2=q("SELECT * FROM shop_lists WHERE firstmod_user=".$_SESSION["id_user"]." AND listtype_id=".$row["id_listtype"]." ORDER BY title;", $dbshop, __FILE__, __LINE__);
		}
		else
		{
			$results2=q("SELECT * FROM shop_lists WHERE listtype_id=".$row["id_listtype"]." ORDER BY title;", $dbshop, __FILE__, __LINE__);
		}
		echo '<div style="width:332px; color:#000000; background-color:#cccccc; font-weight:bold; float:left;">';
		echo '	<img id="lists'.$row["id_listtype"].'_image" src="'.PATH.'images/icons/24x24/right.png" onclick="lists_showhide(\'#lists'.$row["id_listtype"].'\');" style="cursor:pointer; padding:5px;" />';
		echo '<span style="padding:5px;">'.t($row["title"]).' ('.mysqli_num_rows($results2).')</span>';
		echo '</div>';

		echo '<ul class="orderlist" id="lists'.$row["id_listtype"].'" style="width:332px; margin:0px 5px 5px 5px; display:none;">';
		while( $row2=mysqli_fetch_array($results2) )
		{
			echo '<li>';
			echo '	<div style="width:200px; text-align:left;">';
			if ($_POST["id_list"]==$row2["id_list"]) $style=' style="font-weight:bold; text-align:left;"'; else $style=' style="text-align:left;"';
			echo '		<a'.$style.' href="javascript:id_list='.$row2["id_list"].'; id_listtype='.$row["id_listtype"].'; view();">'.$row2["title"].'</a>';
//			echo '		<a'.$style.' href="javascript:id_list='.$row2["id_list"].'; id_menuitem=-1; view();">'.$row2["title"].'</a>';
			echo '	</div>';
			echo '	<div style="width:80px; float:right;">';
			if ( $_SESSION["userrole_id"]==1 or $_SESSION["id_user"]==$row2["firstmod_user"])
			{
				//Aktionszeitraum setzen
				//if($_SESSION["id_user"]==49352)
				{
					if($row["id_listtype"]==7)
					{
						$offer_pic='shopping_cart_favorite.png';
						$res=q("SELECT * FROM shop_offers WHERE list_id=".$row2["id_list"].";", $dbshop, __FILE__, __LINE__);
						if(mysqli_num_rows($res)>0)
						{
							$shop_offers=mysqli_fetch_assoc($res);
							if($shop_offers["offer_start"]<time() and $shop_offers["offer_end"]>time())
								$offer_pic='shopping_cart_accept.png';
						}
						
						echo '		<img src="'.PATH.'images/icons/24x24/'.$offer_pic.'" id="offer_icon_'.$row2["id_list"].'" onclick="offer_timerange_set('.$row2["id_list"].')" alt="Aktionszeitraum setzen" title="Aktionszeitraum setzen" />';
					}
				}
				//Liste löschen
				echo '		<img src="'.PATH.'images/icons/24x24/remove.png" onclick="list_remove('.$row2["id_list"].');" alt="Liste löschen" title="Liste löschen" />';
				//Liste bearbeiten
				echo '		<img src="'.PATH.'images/icons/24x24/edit.png" onclick="list_edit('.$row2["id_list"].', \''.addslashes(stripslashes($row2["title"])).'\', '.$row2["private"].');" alt="Liste bearbeiten" title="Liste bearbeiten" />';
			}
			echo '	</div>';
			echo '</li>';
		}
		echo '</ul>';
	}
	echo '</div>';

	echo '<div id="view_list_box" style="margin:0px 0px 0px 10px; overflow-x:auto; float:left;">';
	echo '	<div id="view_list_header" style="font-weight:bold; font-size:14px;"></div>';
	echo '	<br style="clear:both;" />';
	echo '	<div id="view_filters" style="float:left;"></div>';
	echo '	<br style="clear:both;" />';
	echo '	<div id="view_list" style="float:left;"></div>';
	echo '</div>';
	exit;
























//	if ( $_POST["id_menuitem"]>0 ) $display='none'; else $display='block';


	//itemgroups
	if ( $_POST["id_menuitem"]>0 ) $display='block'; else $display='none';
	echo '<ul class="orderlist" id="itemgroups" style="width:312px; margin:0px 5px 5px 5px; display:'.$display.';">';
	echo '<li class="header">';
	echo '	<div style="width:280px; text-align:left;">Artikelgruppen</div>';
	echo '</li>';
	$results=q("SELECT * FROM cms_menuitems WHERE menu_id=5 AND NOT menuitem_id=0 ORDER BY title;", $dbweb, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		echo '<li>';
		echo '	<div style="width:200px; text-align:left;">';
		if ($_POST["id_menuitem"]==$row["id_menuitem"]) $style=' style="font-weight:bold;"'; else $style='';
		echo '	<a'.$style.' href="javascript:id_list=-1; id_menuitem='.$row["id_menuitem"].'; view();">'.$row["title"].'</a>';
		echo '	</div>';
		echo '	<div style="width:60px; float:right;">';
		if ( $_SESSION["userrole_id"]==1 or $_SESSION["userrole_id"]==3 )
		{
			echo '		<img src="'.PATH.'images/icons/24x24/edit.png" />';
		}
		echo '	</div>';
		echo '</li>';
	}
	echo '</ul>';
	echo '</div>';
	
	//get items
	$Items=array();
	$i=0;
	if ( isset($_POST["id_list"]) and $_POST["id_list"]>0 )
	{
		$results=q("SELECT * FROM shop_lists_items WHERE list_id=".$_POST["id_list"].";", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			$Items["id"][$i]=$row["id"];
			$Items["id_item"][$i]=$row["item_id"];
			$results2=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			$Items["active"][$i]=$row2["active"];
			$Items["id_menuitem"][$i]=$row2["menuitem_id"];
			$Items["ArtNr"][$i]=$row2["MPN"];
			$Items["article_id"][$i]=$row2["article_id"];
			$Items["kfz_bestand"][$i]=$row2["KFZ_BESTAND_TECDOC"];
			$i++;
		}
	}
	//filter id_menuitem
	elseif( isset($_POST["id_menuitem"]) and $_POST["id_menuitem"]>0 )
	{
		if ( isset($_POST["id_menuitem"]) and $_POST["id_menuitem"]>0 )
		{
			$query="SELECT * FROM shop_items WHERE menuitem_id=".$_POST["id_menuitem"]." AND active>0 ORDER BY MPN;";
		}
		else
		{
			$query="SELECT * FROM shop_items WHERE active>0 ORDER BY MPN LIMIT 5;";
		}
		$results=q($query, $dbshop, __FILE__, __LINE__);
		$i=0;
		while($row=mysqli_fetch_array($results))
		{
			$Items["id"][$i]=$row["id_item"];
			$Items["id_item"][$i]=$row["id_item"];
			$Items["active"][$i]=$row["active"];
			$Items["id_menuitem"][$i]=$row["menuitem_id"];
			$Items["ArtNr"][$i]=$row["MPN"];
			$Items["article_id"][$i]=$row["article_id"];
			$Items["kfz_bestand"][$i]=$row["KFZ_BESTAND_TECDOC"];
			$i++;
		}
	}
	else
//	if ( sizeof($Items)==0 )
	{
		echo '<div style="width:600px; margin:10px; border:1px solid #ccc; padding:10px; display:inline; float:left;">';
		echo '	<p>Bitte wählen Sie eine Liste oder Artikelgruppe aus.</p>';
		echo '</div>';
		exit;
	}

	if ( isset($_POST["id_list"]) and $_POST["id_list"]>0 )
	{
		echo '<div style=" width:80%; overflow-x:scroll; float:left;">';
		echo '<div id="view_filters" style="margin:5px; float:left;"></div>';
		echo '<br style="clear:both;" />';
		echo '<div id="view_list" style="float:left;"></div>';
		echo '</div>';
		exit;
	}


	//SELECTION
	$artgr=array();
	$artgr_title=array();
	echo '<div style="float:left;">';
	echo '<table style="width:600px;">';
	echo '	<tr><th colspan="2">'.t("Suchfunktion", __FILE__, __LINE__).'</th></tr>';
	echo '	<tr>';
	echo '		<td>'.t("Lieferstatus", __FILE__, __LINE__).'</td>';
	echo '		<td>';
	echo '			<select id="filter" onchange="view()">';
	if ( $_POST["filter"]==4 ) $selected=' selected="selected"'; else $selected='';
	echo '				<option'.$selected.' value="4">alle anzeigen</option>';
	if ( $_POST["filter"]==1 ) $selected=' selected="selected"'; else $selected='';
	echo '				<option'.$selected.' value="1">nur sofort lieferbare anzeigen</option>';
	if ( $_POST["filter"]==2 ) $selected=' selected="selected"'; else $selected='';
	echo '				<option'.$selected.' value="2">nur z.Z. nicht lieferbare anzeigen</option>';
	if ( $_POST["filter"]==0 ) $selected=' selected="selected"'; else $selected='';
	echo '				<option'.$selected.' value="0">nur nicht lieferbare anzeigen</option>';
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>'.t("Abbildungen", __FILE__, __LINE__).'</td>';
	echo '		<td>';
	echo '			<select id="fotostatus" onchange="view()">';
	if ( $_POST["fotostatus"]==0 ) $selected=' selected="selected"'; else $selected='';
	echo '				<option'.$selected.' value="0">Alle anzeigen</option>';
	if ( $_POST["fotostatus"]==1 ) $selected=' selected="selected"'; else $selected='';
	echo '				<option'.$selected.' value="1">nur mit Fotos anzeigen</option>';
	if ( $_POST["fotostatus"]==2 ) $selected=' selected="selected"'; else $selected='';
	echo '				<option'.$selected.' value="2">nur ohne Fotos anzeigen</option>';
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>'.t("Preisrecherche", __FILE__, __LINE__).'</td>';
	echo '		<td>';
	echo '			<select id="pricestatus" onchange="view()">';
	if ( $_POST["pricestatus"]==0 ) $selected=' selected="selected"'; else $selected='';
	echo '				<option'.$selected.' value="0">Alle anzeigen</option>';
	if ( $_POST["pricestatus"]==1 ) $selected=' selected="selected"'; else $selected='';
	echo '				<option'.$selected.' value="1">0 recherchiert</option>';
	if ( $_POST["pricestatus"]==2 ) $selected=' selected="selected"'; else $selected='';
	echo '				<option'.$selected.' value="2">0-2 recherchiert</option>';
	if ( $_POST["pricestatus"]==3 ) $selected=' selected="selected"'; else $selected='';
	echo '				<option'.$selected.' value="3">1-2 recherchiert</option>';
	if ( $_POST["pricestatus"]==4 ) $selected=' selected="selected"'; else $selected='';
	echo '				<option'.$selected.' value="4">3+ recherchiert</option>';
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>'.t("Preisaktualität", __FILE__, __LINE__).'</td>';
	echo '		<td>';
	echo '			<select id="pricesuggestion" onchange="view()">';
	if ( $_POST["pricesuggestion"]==0 ) $selected=' selected="selected"'; else $selected='';
	echo '				<option'.$selected.' value="0">Alle anzeigen</option>';
	if ( $_POST["pricesuggestion"]==1 ) $selected=' selected="selected"'; else $selected='';
	echo '				<option'.$selected.' value="1">Preisvorschlag fehlt</option>';
	if ( $_POST["pricesuggestion"]==2 ) $selected=' selected="selected"'; else $selected='';
	echo '				<option'.$selected.' value="2">Preis aktuell</option>';
	echo '			</select>';
	echo '		</td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Suchtext</td>';
	echo '		<td>';
	echo '			<input id="needle" type="text" value="'.$_POST["needle"].'" />';
	echo '			<input type="button" value="Suchen" onclick="view();" />';
	if ( isset($_POST["needle_negate"]) and $_POST["needle_negate"]=="checked" ) $checked=' checked="checked"'; else $checked='';
	echo '			<input'.$checked.' id="needle_negate" type="checkbox"  /> Suche umkehren';
	echo '		</td>';
	echo '	</tr>';
	echo '</table>';


	
	
	//get title
	for($i=0; $i<sizeof($Items["id_item"]); $i++)
	{
		$results=q("SELECT * FROM shop_items_".$_POST["lang"]." WHERE id_item=".$Items["id_item"][$i].";", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
//		$Items["title"][$i]=$row["title"];
		$Items["title"][$i]=substr($row["title"], 0, strrpos($row["title"], "("));
	}
	$max=sizeof($Items["id_item"]);

	//filter availability
	$Items["availability"]=array();
	for($i=0; $i<$max; $i++)
	{
		$results=q("SELECT * FROM lager WHERE ArtNr='".$Items["ArtNr"][$i]."';", $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows($results)==0 )
		{
/*
			echo '<ItemsViewResponse>'."\n";
			echo '	<Error>'."\n";
			echo '		<Code>'.__LINE__.'</Code>'."\n";
			echo '		<shortMsg>ArtNr='.$Items["ArtNr"][$i].' existiert nicht in lager-Tabelle.</shortMsg>'."\n";
			echo '		<longMsg>Es konnte kein Lagerstatus für den Artikel mit der Nummer '.$Items["ArtNr"][$i].' abgerufen werden.</longMsg>'."\n";
			echo '	</Error>'."\n";
			echo '</ItemsViewResponse>'."\n";
*/
			$row["Bestand"]=0;
		}
		else $row=mysqli_fetch_array($results);
		if ($_POST["filter"]==0 and $row["Bestand"]!=0)
		{
			unset($Items["id"][$i]);
			unset($Items["id_item"][$i]);
			unset($Items["active"][$i]);
			unset($Items["id_menuitem"][$i]);
			unset($Items["kfz_bestand"][$i]);
			unset($Items["ArtNr"][$i]);
			unset($Items["title"][$i]);
			unset($Items["article_id"][$i]);
			unset($Items["article_id"][$i]);
		}
		elseif ($_POST["filter"]==1 and $row["Bestand"]!=1)
		{
			unset($Items["id"][$i]);
			unset($Items["id_item"][$i]);
			unset($Items["active"][$i]);
			unset($Items["id_menuitem"][$i]);
			unset($Items["kfz_bestand"][$i]);
			unset($Items["ArtNr"][$i]);
			unset($Items["title"][$i]);
			unset($Items["article_id"][$i]);
			unset($Items["article_id"][$i]);
		}
		elseif ($_POST["filter"]==2 and $row["Bestand"]!=2)
		{
			unset($Items["id"][$i]);
			unset($Items["id_item"][$i]);
			unset($Items["active"][$i]);
			unset($Items["id_menuitem"][$i]);
			unset($Items["kfz_bestand"][$i]);
			unset($Items["ArtNr"][$i]);
			unset($Items["title"][$i]);
			unset($Items["article_id"][$i]);
			unset($Items["article_id"][$i]);
		}
		else $Items["availability"][$i]=$row["ISTBESTAND"]+$row["MOCOMBESTAND"];
	}

	//filter fotos
	$Items["fotos"]=array();
	for($i=0; $i<$max; $i++)
	{
		if ( isset($Items["id_item"][$i]) )
		{
			$results=q("SELECT * FROM cms_articles_images WHERE article_id=".$Items["article_id"][$i].";", $dbweb, __FILE__, __LINE__);
			$fotos=mysqli_num_rows($results);
			if ($_POST["fotostatus"]==1 and $fotos<1)
			{
				unset($Items["id"][$i]);
				unset($Items["id_item"][$i]);
				unset($Items["active"][$i]);
				unset($Items["id_menuitem"][$i]);
				unset($Items["kfz_bestand"][$i]);
				unset($Items["ArtNr"][$i]);
				unset($Items["title"][$i]);
				unset($Items["availability"][$i]);
				unset($Items["article_id"][$i]);
			}
			elseif ($_POST["fotostatus"]==2 and $fotos>0)
			{
				unset($Items["id"][$i]);
				unset($Items["id_item"][$i]);
				unset($Items["active"][$i]);
				unset($Items["id_menuitem"][$i]);
				unset($Items["kfz_bestand"][$i]);
				unset($Items["ArtNr"][$i]);
				unset($Items["title"][$i]);
				unset($Items["availability"][$i]);
				unset($Items["article_id"][$i]);
			}
			else $Items["fotos"][$i]=$fotos;
		}
	}
	
	//filter prices
	$Items["prices"]=array();
	for($i=0; $i<$max; $i++)
	{
		if ( isset($Items["id_item"][$i]) )
		{
			$results=q("SELECT * FROM shop_price_research WHERE item_id=".$Items["id_item"][$i].";", $dbshop, __FILE__, __LINE__);
			$prices=mysqli_num_rows($results);
			if ($_POST["pricestatus"]==1 and $prices!=0)
			{
				unset($Items["id"][$i]);
				unset($Items["id_item"][$i]);
				unset($Items["active"][$i]);
				unset($Items["id_menuitem"][$i]);
				unset($Items["kfz_bestand"][$i]);
				unset($Items["ArtNr"][$i]);
				unset($Items["title"][$i]);
				unset($Items["availability"][$i]);
				unset($Items["fotos"][$i]);
				unset($Items["article_id"][$i]);
			}
			elseif ($_POST["pricestatus"]==2 and ($prices<0 or $prices>2) )
			{
				unset($Items["id"][$i]);
				unset($Items["id_item"][$i]);
				unset($Items["active"][$i]);
				unset($Items["id_menuitem"][$i]);
				unset($Items["kfz_bestand"][$i]);
				unset($Items["ArtNr"][$i]);
				unset($Items["title"][$i]);
				unset($Items["availability"][$i]);
				unset($Items["fotos"][$i]);
				unset($Items["article_id"][$i]);
			}
			elseif ($_POST["pricestatus"]==3 and ($prices<1 or $prices>2) )
			{
				unset($Items["id"][$i]);
				unset($Items["id_item"][$i]);
				unset($Items["active"][$i]);
				unset($Items["id_menuitem"][$i]);
				unset($Items["kfz_bestand"][$i]);
				unset($Items["ArtNr"][$i]);
				unset($Items["title"][$i]);
				unset($Items["availability"][$i]);
				unset($Items["fotos"][$i]);
				unset($Items["article_id"][$i]);
			}
			elseif ($_POST["pricestatus"]==4 and $prices<3 )
			{
				unset($Items["id"][$i]);
				unset($Items["id_item"][$i]);
				unset($Items["active"][$i]);
				unset($Items["id_menuitem"][$i]);
				unset($Items["kfz_bestand"][$i]);
				unset($Items["ArtNr"][$i]);
				unset($Items["title"][$i]);
				unset($Items["availability"][$i]);
				unset($Items["fotos"][$i]);
				unset($Items["article_id"][$i]);
			}
			else $Items["prices"][$i]=$prices;
		}
	}
	
	//filter price suggestions
	$Items["pricesuggestion"]=array();
	for($i=0; $i<$max; $i++)
	{
		if ( isset($Items["id_item"][$i]) )
		{
			$results=q("SELECT * FROM shop_price_suggestions WHERE item_id=".$Items["id_item"][$i]." AND lastmod>".(time()-3600*24*90).";", $dbshop, __FILE__, __LINE__);
			$pricesuggestion=mysqli_num_rows($results);
			if ( $_POST["pricesuggestion"]==1 and $pricesuggestion>0)
			{
				unset($Items["id"][$i]);
				unset($Items["id_item"][$i]);
				unset($Items["active"][$i]);
				unset($Items["id_menuitem"][$i]);
				unset($Items["kfz_bestand"][$i]);
				unset($Items["ArtNr"][$i]);
				unset($Items["title"][$i]);
				unset($Items["availability"][$i]);
				unset($Items["fotos"][$i]);
				unset($Items["prices"][$i]);
				unset($Items["article_id"][$i]);
			}
			elseif ( $_POST["pricesuggestion"]==2 and $pricesuggestion==0)
			{
				unset($Items["id"][$i]);
				unset($Items["id_item"][$i]);
				unset($Items["active"][$i]);
				unset($Items["id_menuitem"][$i]);
				unset($Items["kfz_bestand"][$i]);
				unset($Items["ArtNr"][$i]);
				unset($Items["title"][$i]);
				unset($Items["availability"][$i]);
				unset($Items["fotos"][$i]);
				unset($Items["prices"][$i]);
				unset($Items["article_id"][$i]);
			}
			else $Items["pricesuggestion"][$i]=$pricesuggestion;
		}
	}

	//filter text
	if ( isset($_POST["needle"]) and $_POST["needle"]!="" )
	{
		for($i=0; $i<$max; $i++)
		{
			if ( isset($Items["id_item"][$i]) )
			{
				$check=false;
				if (strpos($Items["title"][$i], $_POST["needle"]) === false and strpos($Items["ArtNr"][$i], $_POST["needle"]) === false) $check=true;
				if ( $_POST["needle_negate"]=="checked" ) $check=!$check;
				if ( $check )
				{
					unset($Items["id"][$i]);
					unset($Items["id_item"][$i]);
					unset($Items["active"][$i]);
					unset($Items["id_menuitem"][$i]);
					unset($Items["kfz_bestand"][$i]);
					unset($Items["ArtNr"][$i]);
					unset($Items["title"][$i]);
					unset($Items["availability"][$i]);
					unset($Items["fotos"][$i]);
					unset($Items["prices"][$i]);
					unset($Items["article_id"][$i]);
					unset($Items["pricesuggestion"][$i]);
				}
			}
		}
	}



	//sort items
	if ( sizeof($Items["id_item"])>0 )
	{
		array_multisort($Items["title"], $Items["id"], $Items["id_item"], $Items["active"], $Items["ArtNr"], $Items["id_menuitem"], $Items["availability"], $Items["fotos"], $Items["prices"], $Items["pricesuggestion"], $Items["article_id"], $Items["kfz_bestand"]);
	}
	//build response
echo '<div>';
	//echo '<div style="width:825px; margin:0px;">';
	echo '<br style="clear:both;" />';
	echo '<div style="height:30px"></div>';
		echo '<form name="itemform">';
		echo '<table id="myTable" class="tablesorter hover" style="width:825px; float:left; margin:0px; padding:0px; margin-bottom:0px; table-layout:fixed;">';
		echo '<colgroup><col width="28"><col width="35"><col width="80"><col width="209"><col width="59"><col width="44"><col width="74"><col width="59"><col width="59"><col width="134"></colgroup>';
		echo '<thead>';
		echo '<tr id="TestBox" style=" height:33px; position:absolute;top:330px;">';
		echo '	<td style="width:20px;"><input id="selectall" type="checkbox" onclick="checkAll();" /></td>';
		echo '	<th style="width:30px;">Nr.</th>';
		echo '	<th style="width:75px;">ArtNr</th>';
		echo '	<th style="width:210px;">Titel</th>';
		echo '	<th style="width:55px;">Lager<br />Bestand</th>';
		echo '	<th style="width:40px;">Fotos</th>';
		echo '	<th style="width:65px;">KFZ-<br />Bestand</th>';
		echo '	<th style="width:55px;">Recher-<br />chiert</th>';
		echo '	<th style="width:55px;">Preis aktuell?</th>';
		echo '	<td style="width:130px;">';
		//pdf export
//		echo '<img style="margin:0px 5px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/pdf.png" alt="Artikel exportieren" title="PDF exportieren" onclick="pdf_export();" />';
		//ebay submit
		if ($_SESSION["userrole_id"]==1 or $_SESSION["userrole_id"]==4)
		{
			echo '<img style="margin:0px 2px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/up.png" alt="Zu eBay übertragen" title="Zu eBay übertragen" onclick="items_submit_options();" />';
			echo '<img style="margin:0px 2px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/shopping_cart_up.png" alt="Artikel zu Promotion bei eBay hinzufügen" title="Artikel zu Promotion bei eBay hinzufügen" onclick="promotion_items_update();" />';
		}
		//export overview
		echo '<img style="margin:0px 2px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/down.png" alt="Exportmenü öffnen" title="Exportmenü öffnen" onclick="export_overview();" />';
		//items update
		echo '<img style="margin:0px 2px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/repeat.png" alt="Artikeldaten aktualisieren" title="Artikeldaten aktualisieren" onclick="items_update();" />';
		//list items add
		if ( isset($_POST["id_list"]) and $_POST["id_list"]>0 )
		{
			echo '<img style="margin:0px 2px 0px 0px; border:0; padding:0; cursor:pointer; float:right;" src="images/icons/24x24/add.png" alt="Artikel zu Liste hinzufügen" title="Artikel zu Liste hinzufügen" onclick="list_items_add();" />';
		}
		echo '	</td>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		for($i=0; $i<sizeof($Items["id_item"]); $i++)
		{
			echo '<tr>';
			//Checkbox
			echo '	<td><input name="item_id[]" type="checkbox" value="'.$Items["id_item"][$i].'" onmousedown="select_all_from_here(this.value);" oncontextmenu="select_all_from_here(this.value);" /></td>';
			//Zähler
			echo '	<td>'.($i+1).'</td>';
			//Artikelnummer
			echo '	<td>';
			if ( $Items["active"][$i]==0 ) $style=' style="text-decoration:line-through;"'; else $style='';
			echo '		<a'.$style.' href="'.PATH.'online-shop/autoteile/'.$Items["id_item"][$i].'/" target="_blank">'.$Items["ArtNr"][$i].'</a>';
			echo '	</td>';
			//Titel
			echo '	<td>'.$Items["title"][$i].'</td>';
			//Lieferstatus
			$itemstatuscolor=array("#b30000", "#008000", "#d88918");
			if ($Items["availability"][$i]==0) { $status=0;}
			elseif ($Items["availability"][$i]<10) { $status=2;}
			else { $status=1;}
			echo '	<td><span style="color:'.$itemstatuscolor[$status].'">'.$Items["availability"][$i].'</span></td>';
			//Fotos
			if ($Items["fotos"][$i]>1) $status=1;
			elseif ($Items["fotos"][$i]>0) $status=2;
			else $status=0;
			echo '	<td>';
			echo '		<span style="font-weight:bold; color:'.$itemstatuscolor[$status].'">';
			echo $Items["fotos"][$i];
			echo '		</span>';
			echo '	</td>';
			//KFZ-Bestand
			if ($Items["kfz_bestand"][$i]>200000) $status=1;
			elseif ($Items["kfz_bestand"][$i]>30000) $status=2;
			else $status=0;
			echo '	<td>';
			echo '		<span style="font-weight:bold; color:'.$itemstatuscolor[$status].'">';
			echo $Items["kfz_bestand"][$i];
			echo '		</span>';
			echo '	</td>';
			//Preisrecherche
			if ($Items["prices"][$i]>2) $status=1;
			elseif ($Items["prices"][$i]>0) $status=2;
			else $status=0;
			echo '	<td>';
			echo '		<span style="font-weight:bold; color:'.$itemstatuscolor[$status].'">';
			echo $Items["prices"][$i];
			echo '		</span>';
			echo '	</td>';
			//Preis aktuell?
			if ( $Items["pricesuggestion"][$i]==0 ) $style=' style="color:#b30000;"'; else $style=' style="color:#008000;"';
			echo '	<td '.$style.'>'.$Items["pricesuggestion"][$i].'</td>';
			echo '	<td>';
			//Artikel löschen
			echo '	<img src="'.PATH.'images/icons/24x24/remove.png" style="cursor:pointer; float:right;" onclick="item_remove('.$_POST["id_list"].', '.$Items["id"][$i].');" alt="Artikel löschen" title="Artikel löschen" />';
			if ( $_SESSION["userrole_id"]==1 or $_SESSION["userrole_id"]==3 or $_SESSION["userrole_id"]==4 or $_SESSION["userrole_id"]==7 or $_SESSION["userrole_id"]==15 )
			{
				//Preisrecherche
				echo '		<img src="'.PATH.'images/icons/24x24/search.png" alt="Preisrecherche" style="cursor:pointer; float:right;" title="Preisrecherche" onclick="price_research('.$Items["id_item"][$i].', \''.addslashes($Items["title"][$i]).'\');" />';
				//Shopartikel-Beschreibung bearbeiten
				echo '<a href="'.PATH.'backend_cms_article_editor.php?id_article='.$Items["article_id"][$i].'" target="_blank" title="Shopartikel bearbeiten"><img src="'.PATH.'images/icons/24x24/page_edit.png" alt="Shopartikel-Beschreibung bearbeiten" title="Shopartikel-Beschreibung bearbeiten" /></a>';
				//Shopartikel bearbeiten
				echo '<a href="'.PATH.'backend_shop_item_editor.php?id_item='.$Items["id_item"][$i].'" target="_blank" title="Shopartikel bearbeiten"><img src="'.PATH.'images/icons/24x24/edit.png" alt="Shopartikel bearbeiten" title="Shopartikel bearbeiten" /></a>';
			}
			echo '	</td>';
			echo '</tr>';
		}
		echo '</tbody>';
		echo '</table>';
//	echo '</div>';
echo '</div>';

	echo '</form>';
	echo '</div>';


	//performance
	$stoptime = time()+microtime();
	$time = $stoptime-$starttime;
//	echo 'Seitenaufbau: '.number_format($time,2).'s<br />';
?>

<script type='text/javascript'> 
document.onscroll = function () { 
var pos = window.pageYOffset; 
if (pos > 330) 
document.getElementById('TestBox').style.top = pos + 'px'; 
else 
document.getElementById('TestBox').style.top = '330px'; 
} 
</script> 
