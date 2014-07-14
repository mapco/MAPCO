<?php
	include("config.php");
	$login_required=true;
	include("functions/mapco_baujahr.php");
	include("functions/cms_tl.php");

	$title="Mein Fuhrpark";
	include("templates/".TEMPLATE."/header.php");

	if ( $_GET["getvars1"]=="entfernen" and $_GET["getvars2"]!="" )
	{
		q("UPDATE shop_carfleet SET active=0, lastmod=".time().", lastmod_user=".$_SESSION["id_user"]." WHERE id = ".$_GET["getvars2"].";", $dbshop, __FILE__, __LINE__);
		echo '<div class="success">'.t("Fahrzeug erfolgreich aus dem Fuhrpark entfernt.").'</div>';
	}
	
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	//echo '<div id="mid_column">';
	echo '<div id="mid_right_column">';

	//PATH
	echo '<p>';
	echo '<a href="'.PATHLANG.'online-shop/mein-konto/">'.t("Mein Konto").'</a>';
	echo ' > '.t("Fuhrpark");
	echo '</p>';

	echo '<h1>'.t("Mein Fuhrpark").'</h1>';
	$results=q("SELECT * FROM shop_carfleet WHERE shop_id=".$_SESSION["id_shop"]." AND user_id='".$_SESSION["id_user"]."' AND active = 1;", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($results)>0)
	{
		echo '<table class="hover">';
		echo '<tr>';
		echo '	<th>'.t("Fahrzeug").'</th>';
		echo '	<th>'.t("Fahrzeugschein (zu_2 / zu_3)").'</th>';
		//echo '	<th>'.t("Baujahr").'</th>';
		echo '	<th>'.t("Erstzulassung").'</th>';
		echo '	<th>'.t("FIN").'</th>';
		echo '	<th>'.t("Leistung").'</th>';
		echo '	<th>'.t("Optionen").'</th>';
		echo '</tr>';
		while($row=mysqli_fetch_array($results))
		{
			$results2=q("SELECT * FROM vehicles_de WHERE id_vehicle=".$row["vehicle_id"]." LIMIT 1;", $dbshop, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			echo '<tr>';
			echo '	<td><a href="'.PATHLANG.tl(830, "alias").$row["id"].'/">'.$row2["BEZ1"].' '.$row2["BEZ2"].' '.$row2["BEZ3"].'</a></td>';
			if($row["kbanr"]!="")
			{
				echo '	<td>'.substr($row["kbanr"], 0, 4).' / '.substr($row["kbanr"], 4, 3).'</td>';
			}
			else
			{
				echo '	<td></td>';
			}
			if($row["date_built"]!=0)
			{
				echo '	<td>'.date("m/Y", ($row["date_built"])).'</td>';
			}
			else
			{
				echo '	<td></td>';
			}
			echo '	<td>'.$row["FIN"].'</td>';
			echo '	<td>'.number_format($row2["kW"]).t("kW").' ('.number_format($row2["PS"]).t("PS").')</td>';
			echo '	<td><a href="'.PATHLANG.'online-shop/mein-konto/fuhrpark/entfernen/'.$row["id"].'/">'.t("Entfernen").'</a></td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	else echo t("Der Fuhrpark ist leer.");
	echo '</div>';
	
	//include("templates/".TEMPLATE."/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>