<?php
	include("../config.php");
	
	//Synchronisiere shop_carparts mit fahrz
	$results=q("SELECT f_ID FROM fahrz;", $dbshop, __FILE__, __LINE__);
	$results2=q("SELECT f_ID FROM shop_carparts;", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($results)!=mysqli_num_rows($results2))
	{
		while($row=mysqli_fetch_array($results))
		{
			$results2=q("SELECT f_ID FROM shop_carparts WHERE f_ID=".$row["f_ID"].";", $dbshop, __FILE__, __LINE__);
			if (mysqli_num_rows($results2)==0)
			{
				q("INSERT INTO shop_carparts (f_ID, parts, lastmod) VALUES('".$row["f_ID"]."', '0', '0');", $dbshop, __FILE__, __LINE__);
				echo $row["f_ID"].' synchronisiert.<br />';
			}
		}
	}
	else echo '<p>Fahrzeuglisten sind synchron.</p>';

	//Aktualisiere Zeilen in shop_carparts, die älter als 1 Tag sind
	$results=q("SELECT fahrz.f_ID, KTypNr FROM fahrz, shop_carparts WHERE fahrz.f_ID=shop_carparts.f_ID AND lastmod<".(time()-24*3600).";", $dbshop, __FILE__, __LINE__);
	if (mysqli_num_rows($results)==0) echo '<p>Alle Zeilen aktuell.</p>';
	else
	{
		while($row=mysqli_fetch_array($results))
		{
			$results2=q("SELECT * FROM t_400 WHERE (KritNr=2 AND KritWert='".$row["KTypNr"]."') OR (KritNr=16 AND KritWert='".$row["KTypNr"]."');", $dbshop, __FILE__, __LINE__);
			$parts=mysqli_num_rows($results2);
			$results2=q("SELECT * FROM t_210 WHERE (KritNr=2 AND KritVal='".$row["KTypNr"]."') OR (KritNr=16 AND KritVal='".$row["KTypNr"]."');", $dbshop, __FILE__, __LINE__);
			$parts+=mysqli_num_rows($results2);
			q("UPDATE shop_carparts SET parts='".$parts."', lastmod='".time()."' WHERE f_ID='".$row["f_ID"]."';", $dbshop, __FILE__, __LINE__);
			echo $row["f_ID"].' ('.$row["KTypNr"].') mit '.$parts.' Teilen aktualisiert.<br />';
		}
	}
?>