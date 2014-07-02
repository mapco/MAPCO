<?php

	include("../functions/cms_t.php");

	if (isset($_POST["view"]) && $_POST["view"]=="gart")
	{
		$results=q("SELECT GART FROM shop_items GROUP BY GART ORDER BY GART;", $dbshop, __FILE__, __LINE__);
		while ($row=mysqli_fetch_array($results))
		{
			$GART[$row["GART"]*1]=$row["GART"]*1;
		}

		//unset empty GART		
		unset($GART[00000]);

		//get tecdoc names for every GART needed		
		$BezNr=array();
		$results=q("SELECT * FROM t_320 WHERE GenArtNr IN (".implode(", ", $GART).");", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{ 
			$GART[$row["GenArtNr"]*1]=$row["BezNr"];
			$GART_BgNr[$row["GenArtNr"]*1]=$row["BgNr"];
			$BezNr[$row["BezNr"]]=$row["BezNr"];
		}
		
		//Bezeichnungen
		$results=q("SELECT Bez, BezNr FROM t_030 WHERE SprachNr=1;", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )
		{
			$GART_Bez[$row["BezNr"]]=$row["Bez"];
		}

		//Baugruppen-Bezeichnungen
		$BgNr=array();
		$results=q("SELECT * FROM t_324 GROUP BY BgNr;", $dbshop, __FILE__, __LINE__);
		while( $row=mysqli_fetch_array($results) )	{ $BgNr_Bez[$row["BgNr"]]=$row["BezNr"];}
		
		//GART-ID mit Bezeichnung direkt verknüpfen
		while (list($key, $val) = each ($GART))
		{
			$Gart_Names[$key]=$GART_Bez[$val].' ('.$GART_Bez[$BgNr_Bez[$GART_BgNr[$key]]].')';
		}
		
		//Einträge zu GART ermitteln
		//Meta-Daten
		$res=q("SELECT * FROM shop_items_descriptions WHERE language_id=1;", $dbshop, __FILE__, __LINE__);
		while ($row=mysqli_fetch_array($res))
		{
			$Gart_desc[$row["GART"]]=1;
		}
		//Artikelkeywords
		$res=q("SELECT * FROM shop_items_keywords WHERE language_id=1;", $dbshop, __FILE__, __LINE__);
		while ($row=mysqli_fetch_array($res))
		{
			if (isset($Gart_keywords[$row["GART"]])) $Gart_keywords[$row["GART"]]++; else $Gart_keywords[$row["GART"]]=1;
		}
		//Artikelansichten
		$res=q("SELECT * FROM cms_views_gart;", $dbweb, __FILE__, __LINE__);
		while ($row=mysqli_fetch_array($res))
		{
			if (isset($Gart_view[$row["GART"]])) $Gart_view[$row["GART"]]++; else $Gart_view[$row["GART"]]=1;
		}
		
		
		$OK=asort($Gart_Names);
		
		echo '<ul id="gart_head" class="orderlist" style="width:580px; cursor:default; float:left;">';
		echo '	<li class="header" style="width:580px; height:27px; float:left;">Generische Artikel';
			if ($_SESSION["userrole_id"]==1) echo '<img src="'.PATH.'images/icons/24x24/database.png" style="cursor:pointer" alt="Update Synonyme aus TecDoc" title="Update Synonyme aus TecDoc" onclick="GartKeywordTecDocUpdate();"/>';
		echo '	</li>';
		echo '</ul>';
		
		echo '<div style="width:600px; height:700px; overflow:auto; float:left;">';
		echo '<ul id="gart" class="orderlist" style="width:410px;">';

			$rowcount=0;
			while (list($key, $val) = each ($Gart_Names))
			{
				$rowcount++;
				echo '<li style="width:560px;  cursor:default">';
				echo '	<div style="width:20px">';
				echo 		$rowcount;
				echo '	</div>';
				echo '	<div style="width:30px">';
				echo 		$key;
				echo '	</div>';
				echo '	<a href="javascript:setGartLabel('.$key.');" id="GartLabel'.$key.'" class="GartLabel">';
				echo 		$val;
//				echo ' ('.$GART_Bez[$BgNr_Bez[$GART_BgNr[$key]]].')';
				echo '	</a>';
				echo '	<div style="float:right; width:20px; margin:0px; padding:0px">';
					if (isset($Gart_view[$key]) and $Gart_view[$key]>0) echo '<img src="'.PATH.'images/icons/16x16/accept.png" style="cursor:help" alt="Es ist/sind '.$Gart_view[$key].' Artikelansicht(en) zu diesem generischen Artikel definiert" title="Es ist/sind '.$Gart_view[$key].' Artikelansicht(en) zu diesem generischen Artikel definiert"/>';
					else echo '<img src="'.PATH.'images/icons/16x16/remove.png" style="cursor:help" alt="Es ist keine Artikelansicht zu diesem generischen Artikel definiert" title="Es ist keine Artikelansicht zu diesem generischen Artikel definiert"/>';
				echo '</div>';
				echo '	<div style="float:right; width:20px; margin:0px; padding:0px">';
					if ( isset($Gart_keywords[$key]) and $Gart_keywords[$key]>0) echo '<img src="'.PATH.'images/icons/16x16/accept.png" style="cursor:help" alt="Es ist/sind '.$Gart_keywords[$key].' deutsches Schlüsselwort/deutsche Schlüsselwörter zu diesem generischen Artikel vorhanden" title="Es ist/sind '.$Gart_keywords[$key].' deutsches Schlüsselwort/deutsche Schlüsselwörter zu diesem generischen Artikel vorhanden"/>';
					else echo '<img src="'.PATH.'images/icons/16x16/remove.png" style="cursor:help" alt="Es sind keine deutschen Schlüsselwörter zu diesem generischen Artikel vorhanden" title="Es sind keine deutschen Schlüsselwörter zu diesem generischen Artikel vorhanden"/>';
				echo '</div>';
				echo '	<div style="float:right; width:20px; margin:0px; padding:0px">';
					if (isset($Gart_desc[$key]) and $Gart_desc[$key]==1) echo '<img src="'.PATH.'images/icons/16x16/accept.png" style="cursor:help" alt="Es sind deutsche Meta-Daten zu diesem generischen Artikel vorhanden" title="Es sind deutsche Meta-Daten zu diesem generischen Artikel vorhanden"/>';
					else echo '<img src="'.PATH.'images/icons/16x16/remove.png" style="cursor:help" alt="Es sind keine deutschen Meta-Daten zu diesem generischen Artikel vorhanden" title="Es sind keine deutschen Meta-Daten zu diesem generischen Artikel vorhanden"/>';
				echo '</div>';
				echo '</li>';
			}
		echo '</ul>';
		echo '<div>';

	}
	
//__________________________________________________________________________________________________________________________
	
	if (isset($_POST["view"]) && $_POST["view"]=="detail")
	{
		if (isset($_POST["gart"]) && $_POST["gart"]!="")
		{
			echo '<div style="display:block; float:left;">';
			echo '<p><div style="display:inline; float:left;">';
			echo '<a id="tab_description" href="javascript:view_detail(\'description\');" class="tab">Meta-Daten</a>';
			echo '<a id="tab_keywords" href="javascript:GART_view=\'keywords\'; view(\'detail\', '.$_POST["gart"].');" class="tab">Synonyme</a>';
			echo '<a id="tab_image_views" href="javascript:view_detail(\'image_views\');" class="tab">Ansichten der Artikel (Artikelbilder)</a>';
			echo '<a id="tab_duty_numbers" href="javascript:view_detail(\'duty_numbers\');" class="tab">Zolltarifnummer</a>';
			echo '</div></p>';
			echo '<br style="clear:both;" />';

			//GET GART-BEZ
			$results=q("SELECT b.Bez FROM t_320 as a, t_030 as b WHERE a.GenArtNr='".$_POST["gart"]."' AND a.BezNr=b.BezNr AND b.SprachNr = 1;", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($results)>0)
			{
				$row=mysqli_fetch_array($results);
				$GartBez=$row["Bez"];
			}
			else {$GartBez="";}

			//DETAILVIEW DESCRIPTION
			echo '<table id="detail_description" class="detailView" style="display:none; width:700px">';
			echo '<tr>';
			echo '	<th>Generischer Artikel: '.$GartBez;
			echo '	<img src="'.PATH.'images/icons/24x24/add.png" alt="'.t("Neue Meta-Daten hinzufügen").'" title="'.t("Neue Meta-Daten hinzufügen").'" style="float:right; cursor:pointer;" onclick="GartAddDescription('.$_POST["gart"].', \''.$GartBez.'\');" />';
			echo '	</th>';
			echo '</tr>';

			//GET SYSTEM LANGUAGES
			$language=array();
			$res_lang=q("SELECT * FROM cms_languages ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
			while ($row_lang=mysqli_fetch_array($res_lang)) $language[$row_lang["id_language"]]=$row_lang["language"];

			$res=q("SELECT * FROM shop_items_descriptions where GART = '".$_POST["gart"]."';", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res) == 0)
			{
				echo '<tr><td><b>Zu diesem generischen Artikel wurden noch keine Meta-Daten hinterlegt</b></td></tr>';
			}
			else
			{

				//GET DESCRIPTIONS (Languages)
				echo '<tr>';
				echo '	<td><b>Die Metadaten liegen in folgenden Sprachen vor: <br />';
				$init_lang="";
				while ($row=mysqli_fetch_array($res))
				{
				
					$description[$row["language_id"]]=$row["description"];
					echo '<span class="label">';
					echo '	<a alt="Metadaten anzeigen" title="Metadaten anzeigen" href="javascript:show_description('.$row["GART"].', '.$row["language_id"].')" id="description_label_lang_'.$row["language_id"].'" class="description_label_lang">'.$language[$row["language_id"]].'</a>';
					echo '</span>';
				}
				echo '	</td>';
				echo '</tr>';
			
				echo '<tr>';
				echo '	<td>';
				echo '		<span style="font-weight:bold;">Meta-Keywords: </span>';
				echo '<img src="'.PATH.'images/icons/24x24/remove.png" alt="'.t("Meta-Daten löschen").'" title="'.t("Meta-Daten löschen").'" style="float:right; cursor:pointer;" onclick="GartDeleteDescription('.$_POST["gart"].', \''.$GartBez.'\');" /><img src="'.PATH.'images/icons/24x24/notes_edit.png" alt="'.t("Meta-Daten bearbeiten").'" title="'.t("Meta-Daten bearbeiten").'" style="float:right; cursor:pointer;" onclick="GartUpdateDescription('.$_POST["gart"].', \''.$GartBez.'\');" />';
				echo ' <br style="clear:both">';
				echo '	<textarea class="gart_keywords" cols="83" rows="5" disabled="disabled"></textarea>';
				echo '	<span style="font-weight:bold;">Meta-Description: </span>';
				echo '	<textarea class="gart_description" cols="83" rows="20" disabled="disabled"></textarea>';
				echo '	</<td>';
				echo '</tr>';
			}
			echo '</table>';
			
			//DETAILVIEW KEYWORDS
			echo '<table id="detail_keywords" class="detailView" style="width:700px; display:none;">';
			echo '<tr class="unsortable">';
			echo '	<th colspan="2">Generischer Artikel '.$GartBez.'</th>';
			echo '	<th><img src="'.PATH.'images/icons/24x24/add.png" alt="'.t("Neues Schlüsselwort hinzufügen").'" title="'.t("Neues Schlüsselwort hinzufügen").'" style="float:right; cursor:pointer;" onclick="GartAddKeyword('.$_POST["gart"].', \''.$GartBez.'\');" /></th>';
			echo '</tr>';
	
			echo '<tr>';
			echo '	<td colspan="3">';
			echo'		<b>Die Synonyme liegen in folgenden Sprachen vor:</b><br />';
			$results=q("SELECT * FROM cms_languages ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
			while ($row=mysqli_fetch_array($results))
			{
				echo '<span class="label">';
				echo '	<a alt="Metadaten anzeigen" title="Metadaten anzeigen" href="javascript:language_code='.$row["id_language"].'; view(\'detail\', '.$_POST["gart"].');" class="description_label_lang">'.$language[$row["id_language"]].'</a>';
				echo '</span>';
			}
			echo '	</td>';
			echo '</tr>';

			$res=q("SELECT * FROM shop_items_keywords where GART=".$_POST["gart"]." AND language_id=".$_POST["id_language"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($res) == 0)
			{
				echo '<tr class="unsortable">';
				echo '	<td style="width:700px;" colspan="3"><b>Zu diesem generischen Artikel wurden noch keine Synonyme hinterlegt</b></td>';
				echo '</tr>';

			}
			else
			{
				//GET SYSTEM LANGUAGES
				$language=array();
				$res_lang=q("SELECT * FROM cms_languages ORDER BY ordering;", $dbweb, __FILE__, __LINE__);
				while ($row_lang=mysqli_fetch_array($res_lang)) $language[$row_lang["id_language"]]=$row_lang["language"];


				while ($row=mysqli_fetch_array($res))
				{
					echo '<tr id="keywordid'.$row["id_keyword"].'">'	;
					echo '	<td style="width:20px; cursor:move">'.$row["ordering"].'</td>';
					echo '	<td  style="width:600px;">';
					echo '		<table>';
					echo '			<tr>';
					echo '				<td style="width:99px;">'.$language[$row["language_id"]].'</td>';
					echo '				<td style="width:499px;">'.$row["keyword"].'</td>';
					echo '			</tr>';
					echo '		</table>';
					echo '	</td>';
					echo '	<td class="unsortable">';
					echo '		<img src="'.PATH.'images/icons/24x24/remove.png" alt="'.t("Schlüsselwort löschen").'" title="'.t("Schlüsselwort löschen").'" style="float:right; cursor:pointer;" onclick="GartDeleteKeyword('.$row["id"].', '.$_POST["gart"].');" />';
					echo '		<img src="'.PATH.'images/icons/24x24/edit.png" alt="'.t("Schlüsselwort bearbeiten").'" title="'.t("Schlüsselwort bearbeiten").'" style="float:right; cursor:pointer;" onclick="GartUpdateKeyword('.$row["id"].', \''.$row["keyword"].'\', '.$_POST["gart"].');" /><br />';
					echo '	</td>';
					echo '</tr>';
				}
			}
			echo '</table>';



			//DETAILVIEW IMAGEVIEWS
			echo '<table id="detail_image_views" class="detailView" style="width:700px; display:none;">';
			echo '<tr class="unsortable">';
			echo '	<th colspan="2" style="width:500px">Generischer Artikel '.$GartBez.'</th>';
			echo '	<th><img src="'.PATH.'images/icons/24x24/add.png" alt="'.t("Neue Artikelansicht hinzufügen").'" title="'.t("Neue Artikelansicht hinzufügen").'" style="float:right; cursor:pointer;" onclick="GartAddImageView('.$_POST["gart"].', \''.$GartBez.'\');" /></th>';
			echo '</tr>';
	
			$res=q("SELECT 
					a.id_view, a.ordering, a.article_id,
					b.title, b.introduction
					FROM 
					cms_views_gart as a,
					cms_articles as b
					WHERE
					a.GART = '".$_POST["gart"]."' AND a.article_id = b.id_article
					ORDER BY a.ordering;",
					$dbweb , __FILE__, __LINE__);
			if (mysqli_num_rows($res) == 0)
			{
				echo '<tr class="unsortable">';
				echo '	<td style="width:700px;" colspan="3"><b><b>Zu diesem generischen Artikel wurden noch keine Artikelansichten hinterlegt</b></td>';
				echo '</tr>';

			}
			else
			{
				while ($row=mysqli_fetch_array($res))
				{
					echo '<tr id="imageviewid'.$row["id_view"].'">';
					echo '	<td style="width:30px">'.$row["ordering"].'</td>';
					echo '	<td style="width:570px" class="unsortable"><b>Titel: </b><a href="'.PATH.'backend_cms_article_editor.php?lang='.$_GET["lang"].'&id_article='.$row["article_id"].'" target="blank">'.$row["title"].'</a><br />';
					echo '	------------------------------------------------------------------------------------------------- <br />';
					echo '<b>Bildbeschriftung: </b>'.$row["introduction"].'</td>';
					echo '	<td class="unsortable"><img src="'.PATH.'images/icons/24x24/remove.png" alt="'.t("Artikelansicht löschen").'" title="'.t("Artikelansicht löschen").'" style="float:right; cursor:pointer;" onclick="GartDeleteImageView('.$row["id_view"].','.$_POST["gart"].', \''.$GartBez.'\');" /><img src="'.PATH.'images/icons/24x24/edit.png" alt="'.t("Artikelansicht bearbeiten").'" title="'.t("Artikelansicht bearbeiten").'" style="float:right; cursor:pointer;" onclick="GartUpdateImageView('.$row["id_view"].', \''.$GartBez.'\');" /></td>';
					echo '</tr>';
				}
			}
			echo '</table>';
			
			
			
			//DETAILVIEW ZOLLTARIFNUMMERN
			echo '<table id="detail_duty_numbers" class="detailView" style="width:700px; display:none;">';
			echo '<tr class="unsortable">';
			echo '	<th style="width:500px">Generischer Artikel '.$GartBez.'</th>';
			echo '	<th><img src="'.PATH.'images/icons/24x24/add.png" alt="'.t("Neue Zolltarifnummer hinzufügen").'" title="'.t("Neue Zolltarifnummer hinzufügen").'" style="float:right; cursor:pointer;" onclick="GartAddDutyNumber('.$_POST["gart"].', \''.$GartBez.'\');" /></th>';
			echo '</tr>';
	
			$res=q("SELECT * FROM shop_items_duty_numbers WHERE GART = ".$_POST["gart"].";",$dbshop , __FILE__, __LINE__);
			if (mysqli_num_rows($res) == 0)
			{
				echo '<tr class="unsortable">';
				echo '	<td style="width:700px;" colspan="2"><b><b>Zu diesem generischen Artikel wurden noch keine Zolltarifnummer hinterlegt</b></td>';
				echo '</tr>';

			}
			else
			{
				while ($row=mysqli_fetch_array($res))
				{
					echo '<tr id="dutynumberid'.$row["id"].'">';
					echo '	<td class="unsortable" style="width:650px"><b>Zolltarifnummer: </b>'.$row["duty_number"].'</td>';
					echo '	<td class="unsortable" style="width:100px"><img src="'.PATH.'images/icons/24x24/remove.png" alt="'.t("Zolltarifnummer löschen").'" title="'.t("Zolltarifnummer löschen").'" style="float:right; cursor:pointer;" onclick="GartDeleteDutyNumber('.$row["id"].','.$_POST["gart"].', \''.$GartBez.'\');" /><img src="'.PATH.'images/icons/24x24/edit.png" alt="'.t("Zolltarifnummer bearbeiten").'" title="'.t("Zolltarifnummer bearbeiten").'" style="float:right; cursor:pointer;" onclick="GartUpdateDutyNumber('.$row["id"].','.$_POST["gart"].', \''.$GartBez.'\');" /></td>';
					echo '</tr>';
				}
			}
			echo '</table>';

		}
		echo '</div>';

	}

?>