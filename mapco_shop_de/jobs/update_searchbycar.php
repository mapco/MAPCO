<?php
	include("../config.php");
	
	//read all names
	$names=array();
	$results=q("SELECT LBezNr, Bez FROM t_012 WHERE SprachNr=1;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$names[$row["LBezNr"]]=$row["Bez"];
	}

	//read number of parts by KTypNr
	$parts=array();
	$cars=array();
	$results=q("SELECT ArtNr, KritWert, KritNr FROM t_400 WHERE KritNr=2 OR KritNr=16;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$cars[$row["KritWert"]]=$row["KritWert"];
		$parts[$row["KritWert"]][sizeof($parts[$row["KritWert"]])]=$row["ArtNr"];
		if ($row["KritNr"]==16)
		{
			$transporter[$row["KritWert"]]=1;
		}
		else $transporter[$row["KritWert"]]=0;
	}


	//read KBA numbers
	$kbanr=array();
	$i=0;
	$results=q("SELECT KTypNr, KBANr FROM t_121;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		$kbanr[$row["KTypNr"]][sizeof($kbanr[$row["KTypNr"]])]=$row["KBANr"];
		$i++;
	}

	//write all types
	echo 'Typangaben schreiben ... ';
	$results=q("TRUNCATE TABLE shop_cars;", $dbshop, __FILE__, __LINE__);
	$results=q("SELECT * FROM vehicle_models AS a, t_120 AS b WHERE a.KModNr=b.KModNr;", $dbshop, __FILE__, __LINE__);
	$j=0;
	while($row=mysqli_fetch_array($results))
	{
		//only vehicles with mapco parts available
		if (isset($cars[$row["KTypNr"]]))
		{
			//multiple KBANr for one KTypNr
			if (isset($kbanr[$row["KTypNr"]]))
			{
				for($i=0; $i<sizeof($kbanr[$row["KTypNr"]]); $i++)
				{
					if ($i>0) $j++;
					q("INSERT INTO shop_cars (manufacturer, model, KTypNr, KBANr, transporter, parts) VALUES('".$manufacturers[$row["KHerNr"]]."', '".$models[$row["KHerNr"]]."', '".$row["KTypNr"]."', '".$kbanr[$row["KTypNr"]][$i]."', '".$transporter[$row["KTypNr"]]."', '".sizeof($parts[$row["KTypNr"]])."');", $dbshop, __FILE__, __LINE__);
				}
			}
			else
			{
				q("INSERT INTO shop_cars (manufacturer, model, KTypNr, KBANr, transporter, parts) VALUES('".$manufacturers[$row["KHerNr"]]."', '".$models[$row["KHerNr"]]."', '".$row["KTypNr"]."', '', '".$transporter[$row["KTypNr"]]."', '".sizeof($parts[$row["KTypNr"]])."');", $dbshop, __FILE__, __LINE__);
			}
		}
	}
	echo 'fertig!<br />';
	echo $j;
?>