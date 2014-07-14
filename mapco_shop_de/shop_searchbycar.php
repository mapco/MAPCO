<?php
	include("config.php");
	include("functions/mapco_motorart.php");
	include("functions/mapco_baujahr.php");
	include("functions/shop_show_item.php");
	include("functions/shop_itemstatus.php");
	include("functions/mapco_get_titles.php");
	include("functions/cms_t.php");
	include("functions/cms_tl.php");
	

	if(($_SESSION["id_shop"]>8 and $_SESSION["id_shop"]<17) or $_SESSION["id_shop"]==18)
	{
		$login_required=true;	
	}
	
	//redirect OBSOLETE standard vehicle-id search
	if ( $_GET["id_menuitem"]==825 )
	{
		$link  = PATHLANG.tl(826, "alias");
		if( isset($_GET["getvars1"]) and $_GET["getvars1"]!="" ) $link .= $_GET["getvars1"]."/";
		if( isset($_GET["getvars2"]) and $_GET["getvars2"]!="" ) $link .= $_GET["getvars2"]."/";
		header("HTTP/1.1 301 Moved Permanently");
		header("location: ".$link);
		exit;
	}
	//redirect OBSOLETE kba vehicle search
	elseif ( $_GET["id_menuitem"]==829 )
	{
		$link  = PATHLANG.tl(828, "alias");
		if( isset($_GET["getvars1"]) and $_GET["getvars1"]!="" ) $link .= $_GET["getvars1"]."/";
		header("HTTP/1.1 301 Moved Permanently");
		header("location: ".$link);
		exit;
	}
	//standard vehicle-id search
	elseif ( $_GET["id_menuitem"]==826 )
	{
		$_GET["id_vehicle"]=$_GET["getvars1"];
		if( isset($_GET["getvars2"]) and $_GET["getvars2"]!="" ) $_GET["kbanr"]=$_GET["getvars2"];
	}
	//kba vehicle search
	elseif ( $_GET["id_menuitem"]==828 )
	{
		$_GET["kbanr"]=$_GET["getvars1"];
	}
	//carfleet search
	elseif ( $_GET["id_menuitem"]==830 )
	{
		$results=q("SELECT * FROM shop_carfleet WHERE id=".$_GET["getvars1"]." AND shop_id=".$_SESSION["id_shop"].";", $dbshop, __FILE__, __LINE__);
		if( mysqli_num_rows($results)>0 )
		{
			$row=mysqli_fetch_assoc($results);
			$_GET["id_vehicle"]=$row["vehicle_id"];
			$_GET["kbanr"]=$row["kbanr"];
		}
	}
	else
	{
		if ( isset($_GET["id_vehicle"]) and is_numeric($_GET["id_vehicle"]) )
		{
			header("HTTP/1.1 301 Moved Permanently");
			header("location: ".PATHLANG.tl(826, "alias").$_GET["id_vehicle"].'/');
			exit;
		}
	}

	//generate vehicle information
	if ( isset($_GET["ktypnr"]) and $_GET["ktypnr"]>0) 
	{
		$results=q("SELECT * FROM vehicles_".$_GET["lang"]." WHERE Exclude=0 AND KTypNr=".$_GET["ktypnr"].";", $dbshop, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		$_GET["id_vehicle"]=$row["id_vehicle"];	
	}

	elseif ($_GET["kbanr"]>0) 
	{
		$results=q("SELECT id_vehicle FROM t_121 AS a, vehicles_".$_GET["lang"]." AS b WHERE b.Exclude=0 AND a.KBANr='".$_GET["kbanr"]."' AND a.KTypNr=b.KTypNr LIMIT 1;", $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows($results)==0 )
		{
			include("templates/".TEMPLATE."/header.php");
			include("templates/".TEMPLATE."/cms_leftcolumn.php");
			echo '<div id="mid_column"><h1>Fehler</h1>Das Fahrzeug konnte nicht gefunden werden.</div>';
			include("templates/".TEMPLATE."/cms_rightcolumn.php");
			include("templates/".TEMPLATE."/footer.php");
			exit;
		}
		$row=mysqli_fetch_array($results);
		$_GET["id_vehicle"]=$row["id_vehicle"];
		$kba_nr=$_GET["kbanr"];	
	}
	
	//**************************************************
	elseif ($_GET["vehicle_id"]>0) 
	{
		/*$results=q("SELECT id_vehicle FROM t_121 AS a, vehicles_".$_GET["lang"]." AS b WHERE b.Exclude=0 AND a.KTypNr=b.KTypNr AND b.id_vehicle=".$_GET["vehicle_id"]." LIMIT 1;", $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows($results)==0 )
		{
			include("templates/".TEMPLATE."/header.php");
			include("templates/".TEMPLATE."/cms_leftcolumn.php");
			echo '<div id="mid_column"><h1>Fehler</h1>Das Fahrzeug konnte nicht gefunden werden.</div>';
			include("templates/".TEMPLATE."/cms_rightcolumn.php");
			include("templates/".TEMPLATE."/footer.php");
			exit;
		}
		$row=mysqli_fetch_array($results);
		$_GET["id_vehicle"]=$row["id_vehicle"];*/
		$_GET["id_vehicle"]=$_GET["vehicle_id"];
		$kba_nr=$_GET["kbanr2"];	
	}
	//**************************************************

	if ( is_numeric($_GET["id_vehicle"]) )
	{
		$results=q("SELECT * FROM vehicles_".$_GET["lang"]." WHERE id_vehicle=".$_GET["id_vehicle"].";", $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows($results)==0 )
		{
			include("templates/".TEMPLATE."/header.php");
			include("templates/".TEMPLATE."/cms_leftcolumn.php");
			echo '<div id="mid_column"><h1>Fehler</h1>Das Fahrzeug konnte nicht gefunden werden.</div>';
			include("templates/".TEMPLATE."/cms_rightcolumn.php");
			include("templates/".TEMPLATE."/footer.php");
			exit;
		}
		$row=mysqli_fetch_array($results);
		$ktyp_nr=$row["KTypNr"];
		$krit_nr=$row["KRITNR"];
		
		$results2=q("SELECT * FROM t_121 WHERE KTypNr=".$ktyp_nr.";", $dbshop, __FILE__, __LINE__);
		$row2=mysqli_fetch_array($results2);
		//if( !isset($kba_nr) or $kba_nr=="" ) $kba_nr=$row2["KBANr"];
		//************************************************************
		/*if( !isset($kba_nr) or $kba_nr=="" )
		{
			 $kba_nr="";
			 $vehicle_title=$row["BEZ1"].' '.$row["BEZ2"].' '.$row["BEZ3"];
		}
		else
		{
			$vehicle_title=$row["BEZ1"].' '.$row["BEZ2"].' '.$row["BEZ3"].' ('.$kba_nr.')';
		}*/
		if( !isset($kba_nr))
		{
			 $kba_nr=$row2["KBANr"];
			 if($kba_nr=="")
			 {
				 $vehicle_title=$row["BEZ1"].' '.$row["BEZ2"].' '.$row["BEZ3"];
			 }
			 else
			 {
				$vehicle_title=$row["BEZ1"].' '.$row["BEZ2"].' '.$row["BEZ3"].' ('.$kba_nr.')';
			 }
		}
		else if($kba_nr=="")
		{
			$vehicle_title=$row["BEZ1"].' '.$row["BEZ2"].' '.$row["BEZ3"];
		}
		else if (isset($kba_nr) and $kba_nr!="")
		{
			$vehicle_title=$row["BEZ1"].' '.$row["BEZ2"].' '.$row["BEZ3"].' ('.$kba_nr.')';
		}
		//**************************************************************
		$vehicle_Year=baujahr($row["BJvon"]).' - '.baujahr($row["BJbis"]);
		$vehicle_Power=number_format($row["kW"]).t("kW").' ('.number_format($row["PS"]).t("PS").')';
		$vehicle_CubicCapacity=number_format($row["ccmTech"]).'ccm';
		$vehicle_Motor=motorart($row["MotArt"]);
		$meta_title.=" ".$vehicle_title.' '.$vehicle_Power;
		$meta_description=$vehicle_title.' '.$vehicle_Power." ".$meta_description;

		include("templates/".TEMPLATE."/header.php");
		
		$_SESSION["ktypnr"]=$ktyp_nr;
		$_SESSION["kbanr"]=$kba_nr;

	}
	else
	{
		include("templates/".TEMPLATE."/header.php");
		include("templates/".TEMPLATE."/cms_leftcolumn.php");
		echo '<div id="mid_column"><h1>Fehler '.$_GET["id_vehicle"].'</h1>Die Fahrzeug-Identifikationsnummer ist ungültig.</div>';
		include("templates/".TEMPLATE."/cms_rightcolumn.php");
		include("templates/".TEMPLATE."/footer.php");
		exit;
	}

	//ajax
?>

        <script type="text/javascript">
         <!--
			function status(id)
			{
				var response=ajax('<?php echo PATH; ?>ajax/item_status.php?wert='+encodeURIComponent(id), false);
				var mpos=getMouseXY();
			    document.getElementById("popup").style.left=+(mpos[0]-300) + "px";
			    document.getElementById("popup").style.top=+(mpos[1]+20) + "px";
				document.getElementById("popup").style.visibility='visible';
				document.getElementById("popup").innerHTML=response;
            }
			function showhide_cat(id)
			{
				$("#"+id).toggle(500);
//				$("#cat"+id).src='';
			}

			function check_onEnter(id_item, vehicle_save)
			{
				if(!e) var e = event || window.event;
				if ((e.keyCode) == 13)  check_amount(id_item, vehicle_save);
			}
	
			function check_amount(id_item, vehicle_save)
			{
				var $amount=$("#article" + id_item).val();
				if (($amount%2) != 0) alert("<?php echo t("Bremsscheiben werden nur als Satz verkauft!"); ?>");
				else cart_add(id_item, vehicle_save);
			}

         //-->
        </script>


<?php
	if (!isset($_GET["id_category"])) $_GET["id_category"]=1;
	
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	echo '<div id="mid_right_column">';
	
	if ($_GET["id_vehicle"]!="")
	{
		echo '<h1>'.$vehicle_title.'</h1>';
		echo '<table class="hover">';
//		echo '<tr><th colspan="2">'.$vehicle_title.'</th></tr>';
		echo '<tr><td>'.t("Baujahr").'</td><td>'.$vehicle_Year.'</td></tr>';
		echo '<tr><td>'.t("Leistung").'</td><td>'.$vehicle_Power.'</td></tr>';
		echo '<tr><td>'.t("Hubraum").'</td><td>'.$vehicle_CubicCapacity.'</td></tr>';
		echo '<tr><td>'.t("Motorart").'</td><td>'.t($vehicle_Motor).'</td></tr>';
		if($_SESSION["id_user"]!=0)
		{
			$responseXml = post(PATH."soa2/", array("API" => "shop", "APIRequest" => "CarfleetAdd", "id_vehicle" => $_GET["id_vehicle"], "kbanr" => $kba_nr));
			$use_errors = libxml_use_internal_errors(true);
			try
			{
				$response = new SimpleXMLElement($responseXml);
			}
			catch(Exception $e)
			{
				echo '<div class="failure">'.t("Beim Eintragen des Fahrzeugs in den Fuhrpark ist ein Fehler aufgetreten.").'.</div>';
			}
			libxml_clear_errors();
			libxml_use_internal_errors($use_errors);
			$car_added=$response->car_added[0];
			if($car_added==1)
				echo '<div class="success">'.t("Das Fahrzeug wurde Ihrem Fuhrpark hinzugefügt").'.</div>';
			//echo '<tr><td colspan="2"><a style="float:right;" href="'.PATHLANG.'online-shop/fuhrpark/hinzufuegen/'.$_GET["id_vehicle"].'/'.$kba_nr.'/" />'.t("Zum Fuhrpark hinzufügen").'</a></td></tr>';
		}
		else
		{
			echo '<tr><td colspan="2"><span style="float:right; color:#909090;">'.t("Bitte melden Sie sich an um den Fuhrpark nutzen zu können").'!</span></td></tr>';
		}
		echo '</table>';
	}

	//categories
	$menuitem=array();
	$menuitem[0]="unsortiert";
	$results=q("SELECT * FROM cms_menuitems WHERE menu_id=5 AND menuitem_id>0;", $dbweb, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$menuitem[$row["id_menuitem"]]=$row["title"];
	}

	//get language_id
	$results=q("SELECT * FROM cms_languages WHERE code='".$_GET["lang"]."' LIMIT 1;", $dbweb, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$language_id=$row["id_language"];

//	$starttime=time()+microtime();

	//find items for car
	$i=0;
	$id_item=array();
	$art_nr=array();
	$title=array();
	$description=array();
	$item_cat=array();
	$category=array();
	$cat_count=array();
	$results=q("SELECT * FROM shop_items_vehicles WHERE vehicle_id=".$_GET["id_vehicle"]." AND language_id=".$language_id.";", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		$results2=q("SELECT * FROM shop_items WHERE id_item=".$row["item_id"]." AND active>0;", $dbshop, __FILE__, __LINE__);
		if ( mysqli_num_rows($results2)>0 )
		{
			$id_item[$i]=$row["item_id"];

			$row2=mysqli_fetch_array($results2);
			$art_nr[$i]=$row2["MPN"];
			$item_cat[$i]=$row2["menuitem_id"];
			$category[$i]=$menuitem[$row2["menuitem_id"]];
			if ( isset($catcount[$row2["menuitem_id"]]) ) $catcount[$row2["menuitem_id"]]++; else $catcount[$row2["menuitem_id"]]=1;

			$results3=q("SELECT * FROM shop_items_keywords WHERE GART=".$row2["GART"]." AND language_id=".$_SESSION["id_language"]." ORDER BY ordering;", $dbshop, __FILE__, __LINE__);
			$row3=mysqli_fetch_array($results3);
			$title[$i]='MAPCO '.$row2["MPN"].' '.$row3["keyword"];

			$results2=q("SELECT * FROM shop_items_".$_GET["lang"]." WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
//			$title[$i]=$row2["title"];
			$description[$i]=$row2["short_description"];
			if ( $row["criteria"]!="" ) $description[$i].='; <span style="color:#ff0000;">'.$row["criteria"].'</span>';

			//show replacements in criteria
			$results3=q("SELECT * FROM t_204 WHERE EArtNr='".$art_nr[$i]."';", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($results3)>0)
			{
				$row3=mysqli_fetch_array($results3);
				$description[$i].='; <span style="color:#ff0000;">'.t('Artikel wird künftig ersetzt durch:').$row3["ArtNr"].'</span>';
			}
				

			$i++;
		}
	}

	//debug infos
//	if ($_SESSION["id_user"]==21371)
//	{
//		print_r($art_nr);
//		exit;
//	}

	array_multisort($category, $title, $id_item, $description, $art_nr, $item_cat);

//	$stoptime=time()+microtime();
//	echo $time=number_format($stoptime-$starttime, 2);

	//show items by category
	echo '<h2>'.t("Ersatzteile für").' '.$vehicle_title.'</h2>';
	$categ='';
	$j=0;
	for($i=0; $i<sizeof($category); $i++)
	{
		if ($id_item[$i]!="")
		{
			if ($categ!=$category[$i])
			{
				if ($j>0) echo '</div>';
				$categ=$category[$i];
				echo '<div id="cat'.$i.'" onclick="showhide_cat('.$i.');" class="category">'.t($category[$i]).' ('.$catcount[$item_cat[$i]].')</div>';
				echo '<div style="display:none; float:left;" id="'.$i.'">';
			}
//			echo '<br style="clear:both;" />';
			if(isset($_SESSION["id_user"]) and $_SESSION["id_user"]>0)
				show_item($id_item[$i], $art_nr[$i], $title[$i], $description[$i], "", 1, $title[$i].' für '.$vehicle_title);
			else
				show_item($id_item[$i], $art_nr[$i], $title[$i], $description[$i], "", 0, $title[$i].' für '.$vehicle_title);
			$j++;
		}
	}
	echo '</div>';
	echo '</div>';

	include("templates/".TEMPLATE."/footer.php");
?>