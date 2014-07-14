<?php
	function color($bestand)
	{
		if ($bestand==0) return(' style="color:#b30000;"');
		elseif ($bestand==3) return(' style="color:#dddd00;"');
		elseif ($bestand==2) return(' style="color:#00cc00;"');
		else return(' style="color:#008000;"');
	}

	function bestand($bestand)
	{
		if ($bestand==0) return('momentan nicht lieferbar');
		elseif ($bestand==3) return('<5 Artikel');
		elseif ($bestand==2) return('>5 Artikel');
		else return('>10 Artikel');
	}

	include_once("../config.php");

	echo '<table>';
	echo '	<tr>';
	echo '		<th>Standort</th>';
	echo '		<th>Lieferstatus</th>';
	echo '	</tr>';
	$results=q("SELECT * FROM lagerrc WHERE ARTNR='".$_GET["artnr"]."';", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		echo '	<tr>';
		echo '		<td>MAPCO RegionalCENTER '.str_replace("_", " ", $row["RCBEZ"]).'</td>';
		echo '		<td'.color($row["BESTAND"]).'>'.bestand($row["BESTAND"]).'</td>';
		echo '	</tr>';
	}
	echo '</table>';


/*
	echo '<table>';
	echo '	<tr>';
	echo '		<th>Standort</th>';
	echo '		<th>Lieferstatus</th>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Zentrallager Brück</td>';
	echo '		<td><span style="color:#008000;">sofort lieferbar</span></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>RegionalCENTER Borkheide</td>';
	echo '		<td><span style="color:#008000;">sofort lieferbar</span></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>RegionalCENTER Dresden</td>';
	echo '		<td><span style="color:#cccc00;">ca. 2-4 Tage Lieferzeit</span></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>RegionalCENTER Frankfurt</td>';
	echo '		<td><span style="color:#008000;">sofort lieferbar</span></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>RegionalCENTER Leipzig</td>';
	echo '		<td><span style="color:#008000;">sofort lieferbar</span></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>RegionalCENTER Magdeburg</td>';
	echo '		<td><span style="color:#cccc00;">ca. 2-4 Tage Lieferzeit</span></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>RegionalCENTER Neubrandenburg</td>';
	echo '		<td><span style="color:#008000;">sofort lieferbar</span></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>RegionalCENTER Sömmerda</td>';
	echo '		<td><span style="color:#008000;">sofort lieferbar</span></td>';
	echo '	</tr>';
	echo '	<tr>';
	echo '		<td>Shop Potsdam</td>';
	echo '		<td><span style="color:#b30000;">ca. 10+ Tage Lieferzeit</span></td>';
	echo '	</tr>';
	echo '</table>';
*/
?>