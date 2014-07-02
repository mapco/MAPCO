<?php
	include("config.php");
	include("functions/mapco_motorart.php");
	include("functions/mapco_baujahr.php");
	include("functions/shop_show_item.php");
	include("functions/shop_itemstatus.php");
	include("functions/mapco_get_titles.php");
	
	//language check
	if ( !isset($_GET["lang"]) ) $_GET["lang"]="de";
	
	//redirect old URLs	
	if ( strpos($_SERVER['REQUEST_URI'], "shop_searchbycar.php") )
	{
		if ( isset($_GET["id_vehicle"]) and is_numeric($_GET["id_vehicle"]) )
		{
			header("HTTP/1.1 301 Moved Permanently");
			header("location: ".PATHLANG.'fahrzeugsuche/'.$_GET["id_vehicle"].'/');
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
		if( !isset($kba_nr) or $kba_nr=="" ) $kba_nr=$row2["KBANr"];

		$vehicle_title=$row["BEZ1"].' '.$row["BEZ2"].' '.$row["BEZ3"].' ('.$kba_nr.')';
		$vehicle_Year=baujahr($row["BJvon"]).' - '.baujahr($row["BJbis"]);
		$vehicle_Power=number_format($row["kW"]).'kW ('.number_format($row["PS"]).'PS)';
		$vehicle_CubicCapacity=number_format($row["ccmTech"]).'ccm';
		$vehicle_Motor=motorart($row["MotArt"]);
		$title='Autoteile für '.$vehicle_title.' günstig online kaufen';

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
		echo '<tr><td>'.t("Motorart").'</td><td>'.$vehicle_Motor.'</td></tr>';
		if($_SESSION["id_user"]!=0)
		{
			echo '<tr><td colspan="2"><a style="float:right;" href="'.PATHLANG.'online-shop/fuhrpark/hinzufuegen/'.$_GET["id_vehicle"].'/'.$kba_nr.'/" />'.t("Zum Fuhrpark hinzufügen").'</a></td></tr>';
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

			$results2=q("SELECT * FROM shop_items_".$_GET["lang"]." WHERE id_item=".$row["item_id"].";", $dbshop, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			$title[$i]=$row2["title"];
			$description[$i]=$row2["short_description"];
			if ( $row["criteria"]!="" ) $description[$i].='; <span style="color:#ff0000;">'.$row["criteria"].'</span>';

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
	echo '<h2>Ersatzteile für '.$vehicle_title.'</h2>';
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
				echo '<div id="cat'.$i.'" onclick="showhide_cat('.$i.');" class="category">'.t($category[$i], __FILE__, __LINE__).' ('.$catcount[$item_cat[$i]].')</div>';
				echo '<div style="display:none; float:left;" id="'.$i.'">';
			}
//			echo '<br style="clear:both;" />';
			show_item($id_item[$i], $art_nr[$i], $title[$i].' für '.$vehicle_title, $description[$i]);
			$j++;
		}
	}
	echo '</div>';
	echo '</div>';

	include("templates/".TEMPLATE."/footer.php");
?>