<?php
	include("config.php");
	include("templates/".TEMPLATE."/header.php");
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	include("functions/cms_t.php");


	function color_zentrallager($bestand)
	{
		if ($bestand==1) return('#008000');
		elseif ($bestand==2) return('#000080');
		else return('#800000');
	}
	function bestand_zentrallager($bestand)
	{
		if ($bestand==1) return(t("sofort lieferbar"));
		elseif ($bestand==2) return(t("Liefertermin auf Anfrage"));
		else return(t("z.Z nicht lieferbar"));
	}

	function color_zentrallager_rc($bestand)
	{
		if ($bestand==1) return('#008000');
		elseif ($bestand==2) return('#000080');
		else return('#800000');
	}
	function bestand_zentrallager_rc($bestand)
	{
		if ($bestand==1) return(t("1 Werktag Lieferzeit"));
		elseif ($bestand==2) return(t("Liefertermin auf Anfrage"));
		else return(t("z.Z nicht lieferbar"));
	}

	function color($bestand)
	{
		if ($bestand==0) return('#800000');
		elseif ($bestand==3) return('#000080');
		elseif ($bestand==2) return('#000080');
		else return('#008000');
	}
	function bestand($bestand)
	{
		if ($bestand==0) return(t("z.Z. nicht lieferbar"));
		elseif ($bestand==3) return('< 5 '.t("Artikel").' '.t("vorrätig"));
		elseif ($bestand==2) return('> 5 '.t("Artikel").' '.t("vorrätig"));
		else return('> 10 '.t("Artikel").' '.t("vorrätig"));
	}

	//check for errors
	if ( !isset($_GET["id_item"]) )
	{
		echo '<div id="mid_column"><h1>Fehler</h1>Es konnte keine Shop-Artikelnummer gefunden werden.</div>';
		include("templates/".TEMPLATE."/cms_rightcolumn.php");
		include("templates/".TEMPLATE."/footer.php");
		exit;
	}
	if ( !is_numeric($_GET["id_item"]) )
	{
		echo '<div id="mid_column"><h1>Fehler</h1>Es konnte keine numerische Shop-Artikelnummer gefunden werden.</div>';
		include("templates/".TEMPLATE."/cms_rightcolumn.php");
		include("templates/".TEMPLATE."/footer.php");
		exit;
	}

	//find artnr
	$results=q("SELECT * FROM shop_items AS a, shop_items_".$_GET["lang"]." AS b WHERE a.id_item=".$_GET["id_item"]." AND b.id_item=a.id_item;", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	$artnr=$row["MPN"];
	
	
	echo '<div id="mid_column">';
	echo '<h1>'.$row["title"].'</h1>';
	echo '<table class="hover">';
	echo '	<tr>';
	echo '		<th>'.t("Standort").'</th>';
	echo '		<th>'.t("Lieferstatus").'</th>';
	echo '	</tr>';
	
	//Zentrallager
	$results=q("SELECT * FROM lager WHERE ArtNr='".$artnr."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
	$row=mysqli_fetch_array($results);
	echo '	<tr>';
	echo '		<td>Zentrallager</td>';
	if($_SESSION["rcid"]!=9999 and $_SESSION["rcid"]>0)
	{
		echo '		<td style="color:'.color_zentrallager_rc($row["Bestand"]).'">'.bestand_zentrallager_rc($row["Bestand"]).'</td>';
	}
	else
	{
		echo '		<td style="color:'.color_zentrallager($row["Bestand"]).'">'.bestand_zentrallager($row["Bestand"]).'</td>';
	}
	echo '	</tr>';

	//RegionalCENTER
	if($_SESSION["rcid"]!=9999 and $_SESSION["rcid"]>0)
	{
		$results=q("SELECT * FROM lagerrc WHERE ARTNR='".$artnr."' AND RCNR='".$_SESSION["rcid"]."';", $dbshop, __FILE__, __LINE__);
	}
	else
	{
		$results=q("SELECT * FROM lagerrc WHERE ARTNR='".$artnr."' AND RCNR BETWEEN '39' AND '99';", $dbshop, __FILE__, __LINE__);
	}
	while($row=mysqli_fetch_array($results))
	{
		echo '	<tr>';
		echo '		<td>MAPCO RegionalCENTER '.str_replace("_", " ", $row["RCBEZ"]).'</td>';
		echo '		<td><span style="color:'.color($row["BESTAND"]).';">'.bestand($row["BESTAND"]).'</span></td>';
		echo '	</tr>';
	}


	echo '</table>';
	echo '<a href="javascript:history.back()">'.t("Zurück").'</a>';
	echo '</div>';

	include("templates/".TEMPLATE."/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>