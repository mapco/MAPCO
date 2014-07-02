<?php
	include("config.php");
	$login_required=true;
	include("functions/mapco_baujahr.php");

	$title="Mein Fuhrpark";
	include("templates/".TEMPLATE."/header.php");

	//if (isset($_GET["hinzufuegen"]) and $_GET["hinzufuegen"]!="" and isset($_GET["kba"]) and $_GET["kba"]!="")
	if (isset($_GET["hinzufuegen"]) and $_GET["hinzufuegen"]!="")
	{
		if(!isset($_GET["kba"]) or $_GET["kba"]=="")
		{
			$kba = "";
		}
		else
		{
			$kba = $_GET["kba"];
		}
		/*$results=q("SELECT * FROM shop_carfleet WHERE vehicle_id=".$_GET["hinzufuegen"]." AND user_id='".$_SESSION["id_user"]."' LIMIT 1;", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($results)>0) echo '<div class="failure">Fahrzeug befindet sich bereits im Fuhrpark.</div>';
		else
		{*/
		q("INSERT INTO shop_carfleet (user_id, vehicle_id, kbanr, active, firstmod, firstmod_user, lastmod, lastmod_user) VALUES(".$_SESSION["id_user"].", ".$_GET["hinzufuegen"].", '".$kba."', 1, ".time().", ".$_SESSION["id_user"].", ".time().", ".$_SESSION["id_user"].");", $dbshop, __FILE__, __LINE__);
		echo '<div class="success">Fahrzeug erfolgreich zum Fuhrpark hinzugefügt.</div>';
		//}
	}
	
	if (isset($_GET["entfernen"]) and $_GET["entfernen"]!="")
	{
		//q("DELETE FROM shop_carfleet WHERE id='".$_GET["entfernen"]."';", $dbshop, __FILE__, __LINE__);
		q("UPDATE shop_carfleet SET active=0, lastmod=".time().", lastmod_user=".$_SESSION["id_user"]." WHERE id = ".$_GET["entfernen"].";", $dbshop, __FILE__, __LINE__);
		echo '<div class="success">Fahrzeug erfolgreich aus dem Fuhrpark entfernt.</div>';
	}
	
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	//echo '<div id="mid_column">';
	echo '<div id="mid_right_column">';

	//PATH
	echo '<p>';
	echo '<a href="'.PATHLANG.'online-shop/mein-konto/">Mein Konto</a>';
	echo ' > Fuhrpark';
	echo '</p>';

	echo '<h1>Mein Fuhrpark</h1>';
	$results=q("SELECT * FROM shop_carfleet WHERE user_id='".$_SESSION["id_user"]."' AND active = 1;", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($results)>0)
	{
		echo '<table class="hover">';
		echo '<tr>';
		echo '	<th>Fahrzeug</th>';
		echo '	<th>Fahrzeugschein (zu_2 / zu_3)</th>';
		//echo '	<th>Baujahr</th>';
		echo '	<th>Erstzulassung</th>';
		echo '	<th>FIN</th>';
		echo '	<th>Leistung</th>';
		echo '	<th>Optionen</th>';
		echo '</tr>';
		while($row=mysqli_fetch_array($results))
		{
			/*$results2=q("SELECT * FROM t_121 AS a, vehicles_de AS b WHERE b.Exclude=0 AND a.KBANr='".$row["kbanr"]."' AND a.KTypNr=b.KTypNr LIMIT 1;", $dbshop, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			echo '<tr>';
			//$tmp=date("d.m.Y", timestamp)
			echo '	<td><a href="'.PATHLANG.'kba-suche/'.$row["kbanr"].'/">'.$row2["BEZ1"].' '.$row2["BEZ2"].'</a></td>';
			echo '	<td>'.substr($row["kbanr"], 0, 4).' / '.substr($row["kbanr"], 4, 3).'</td>';
			echo '	<td>'.baujahr($row2["BJvon"]).' - '.baujahr($row2["BJbis"]).'</td>';
			echo '	<td>mmmmmmmmmmmmmmmmm</td>';
			echo '	<td>'.number_format($row2["kW"]).'kW ('.number_format($row2["PS"]).'PS)</td>';
			echo '	<td><a href="'.PATHLANG.'online-shop/fuhrpark/entfernen/'.$row["id"].'/">Entfernen</a></td>';*/
			$results2=q("SELECT * FROM vehicles_de WHERE id_vehicle=".$row["vehicle_id"]." LIMIT 1;", $dbshop, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			echo '<tr>';
			//echo '	<td><a href="'.PATHLANG.'kba-suche/'.$row["kbanr"].'/">'.$row2["BEZ1"].' '.$row2["BEZ2"].' '.$row2["BEZ3"].'</a></td>';
			echo '	<td><a href="'.PATHLANG.'vehicle_id-suche/'.$row["vehicle_id"].'/'.$row["kbanr"].'/">'.$row2["BEZ1"].' '.$row2["BEZ2"].' '.$row2["BEZ3"].'</a></td>';
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
			echo '	<td>'.number_format($row2["kW"]).'kW ('.number_format($row2["PS"]).'PS)</td>';
			echo '	<td><a href="'.PATHLANG.'online-shop/fuhrpark/entfernen/'.$row["id"].'/">Entfernen</a></td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	else echo 'Der Fuhrpark ist leer.';
	echo '</div>';
	
	//include("templates/".TEMPLATE."/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>