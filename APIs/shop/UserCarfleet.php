<?php
	include("../config.php");
	include("../functions/cms_t.php");

	if ( !isset($_POST["car"]) or $_POST["car"]=="" )
	{
		echo '<UserCarfleetResponse>'."\n";
		echo '	<Ack>Error</Ack>'."\n";
		echo '	<Error>'."\n";
		echo '		<Code>'.__LINE__.'</Code>'."\n";
		echo '		<shortMsg>Fahrzeug unbekannt.</shortMsg>'."\n";
		echo '		<longMsg>Es wurde keine gültige Fahrzeug ID übergeben.</longMsg>'."\n";
		echo '	</Error>'."\n";
		echo '</UserCarfleetResponse>'."\n";
		exit;
	}

	if ( $_POST["car"]>0 )
	{		
		q("UPDATE shop_carfleet SET active=0, lastmod=".time().", lastmod_user=".$_SESSION["id_user"]." WHERE id = ".$_POST["car"].";", $dbshop, __FILE__, __LINE__);
		echo '<script> show_status("'.t("Fahrzeug erfolgreich aus dem Fuhrpark entfernt").'."); </script>';
	}

	$results=q("SELECT * FROM shop_carfleet WHERE user_id='".$_SESSION["id_user"]."' AND active = 1;", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($results)>0)
	{
		echo '<table class="hover">';
		echo '<tr>';
		echo '	<th>'.t("Fahrzeug").'</th>';
		echo '	<th>'.t("KBA").'</th>';
		echo '	<th></th>';
		echo '</tr>';
		while($row=mysqli_fetch_array($results))
		{
			$results2=q("SELECT * FROM vehicles_de WHERE id_vehicle=".$row["vehicle_id"]." LIMIT 1;", $dbshop, __FILE__, __LINE__);
			$row2=mysqli_fetch_array($results2);
			echo '<tr>';
			echo '	<td><a href="'.PATHLANG.'vehicle_id-suche/'.$row["vehicle_id"].'/'.$row["kbanr"].'/">'.$row2["BEZ1"].' '.$row2["BEZ2"].' '.$row2["BEZ3"].'</a></td>';
			if($row["kbanr"]!="")
			{
				echo '	<td>'.substr($row["kbanr"], 0, 4).' <br /> '.substr($row["kbanr"], 4, 3).'</td>';
			}
			else
			{
				echo '	<td></td>';
			}
			echo '	<td><img src="'.PATH.'images/icons/16x16/remove.png" style="cursor:pointer; float:right;" onclick="carfleet_view('.$row["id"].');" alt="'.t("Entfernen").'" title="'.t("Entfernen").'" /></td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	else echo t("Der Fuhrpark ist leer").'.';

?>