<?php
	include("config.php");
	
	//Häufungen der KritNr in t_400 und t_210
	/*$results=q("SELECT KritNr,KritWert, count(KritNr) AS krit FROM t_400 GROUP BY KritNr ORDER BY krit DESC;", $dbshop, __FILE__, __LINE__);
	echo '<table>';
	echo '<tr>';
	echo '<th>KritNr t_400</th>';
	echo '<th>KritWert t_400</th>';
	echo '<th>Anzahl</th>';
	echo '<th>BezNr t_050</th>';
	echo '<th>Bez t_052</th>';
	echo '</tr>';
	while($row=mysql_fetch_array($results))
	{
		$results2=q("SELECT * FROM t_050 WHERE KritNr=".$row["KritNr"].";", $dbshop, __FILE__, __LINE__);
		$row2=mysql_fetch_array($results2);
		$results3=q("SELECT * FROM t_030 WHERE BezNr=".$row2["BezNr"].";", $dbshop, __FILE__, __LINE__);
		$row3=mysql_fetch_array($results3);
		echo '<tr>';
		echo '<td>'.$row["KritNr"].'</td>';
		echo '<td>'.$row["KritWert"].'</td>';
		echo '<td>'.$row["krit"].'</td>';
		echo '<td>'.$row2["BezNr"].'</td>';
		echo '<td>'.$row3["Bez"].'</td>';
		echo '</tr>';
	}
	echo '</table>';
	
	$results=q("SELECT KritNr,KritVal, count(KritNr) AS krit FROM t_210 GROUP BY KritNr ORDER BY krit DESC;", $dbshop, __FILE__, __LINE__);
	echo '<table>';
	echo '<tr>';
	echo '<th>KritNr t_210</th>';
	echo '<th>KritWert t_210</th>';
	echo '<th>Anzahl</th>';
	echo '<th>BezNr t_050</th>';
	echo '<th>Bez t_052</th>';
	echo '</tr>';
	while($row=mysql_fetch_array($results))
	{
		$results2=q("SELECT * FROM t_050 WHERE KritNr=".$row["KritNr"].";", $dbshop, __FILE__, __LINE__);
		$row2=mysql_fetch_array($results2);
		$results3=q("SELECT * FROM t_030 WHERE BezNr=".$row2["BezNr"].";", $dbshop, __FILE__, __LINE__);
		$row3=mysql_fetch_array($results3);
		echo '<tr>';
		echo '<td>'.$row["KritNr"].'</td>';
		echo '<td>'.$row["KritVal"].'</td>';
		echo '<td>'.$row["krit"].'</td>';
		echo '<td>'.$row2["BezNr"].'</td>';
		echo '<td>'.$row3["Bez"].'</td>';
		echo '</tr>';
	}
	echo '</table>';*/
	
	
	
	/*$results=q("SELECT KritNr,KritWert, SortNr, ArtNr FROM t_400 WHERE KritNr>0002;", $dbshop, __FILE__, __LINE__);
	echo '<table>';
	echo '<tr>';
	echo '<th></th>';
	echo '<th>ArtNr t_400</th>';
	echo '<th>KritNr t_400</th>';
	echo '<th>KritWert t_400</th>';
	echo '<th>SortNr t_400</th>';
	echo '<th>Kriterium</th>';
	echo '<th>Wert</th>';
	echo '</tr>';
	$cnt=0;
	while($row=mysql_fetch_array($results))
	{
		$cnt++;
		$results2=q("SELECT BezNr, Typ, TabNr FROM t_050 WHERE KritNr='".$row["KritNr"]."';", $dbshop, __FILE__, __LINE__);
		$row2=mysql_fetch_array($results2);
		if ($row2["Typ"]=="K")
		{
			if (is_numeric($row["KritWert"]))
			{
				$results4=q("SELECT BezNr FROM t_052 WHERE TabNr=".$row2["TabNr"]." AND Schl=".$row["KritWert"].";", $dbshop, __FILE__, __LINE__);
			}
			else
			{
				$results4=q("SELECT BezNr FROM t_052 WHERE TabNr=".$row2["TabNr"]." AND Schl='".$row["KritWert"]."';", $dbshop, __FILE__, __LINE__);
			}
			$row4=mysql_fetch_array($results4);
			$results4=q("SELECT Bez FROM t_030 WHERE BezNr=".$row4["BezNr"].";", $dbshop, __FILE__, __LINE__);
			$row4=mysql_fetch_array($results4);
			$wert=$row4["Bez"];
		}
		else 
		{
			if ($row["KritNr"] == 20 or $row["KritNr"] == 21)
			{
				$wert=substr($row["KritWert"], -2, 2).'/'.substr($row["KritWert"], 0, 4);
			}
			else $wert=$row["KritWert"];
		}
		$results2=q("SELECT Bez FROM t_030 WHERE BezNr=".$row2["BezNr"].";", $dbshop, __FILE__, __LINE__);
		$row2=mysql_fetch_array($results2);
		//$bez=iconv("windows-1252", "utf-8", $row2["Bez"]);
		$bez=$row2["Bez"];
		
		echo '<tr>';
		echo '<td>'.$cnt.'</td>';
		echo '<td>'.$row["ArtNr"].'</td>';
		echo '<td>'.$row["KritNr"].'</td>';
		echo '<td>'.$row["KritWert"].'</td>';
		echo '<td>'.$row["SortNr"].'</td>';
		echo '<td>'.$bez.'</td>';
		echo '<td>'.$wert.'</td>';
		echo '</tr>';
	}
	echo '</table>';*/
	
	//$results=q("SELECT * FROM t_400 GROUP BY KritNr ORDER BY KritNr;", $dbshop, __FILE__, __LINE__);
	$results=q("SELECT * FROM t_400 WHERE KritNr='0649' GROUP BY KritWert;", $dbshop, __FILE__, __LINE__);
	echo '<table>';
	echo '<tr>';
	echo '<th></th>';
	echo '<th>ArtNr t_400</th>';
	echo '<th>KritNr t_400</th>';
	echo '<th>KritWert t_400</th>';
	echo '<th>SortNr t_400</th>';
	echo '<th>Kriterium</th>';
	echo '<th>Wert</th>';
	echo '</tr>';
	$cnt=0;
	while($row=mysql_fetch_array($results))
	{
		$cnt++;
		$results2=q("SELECT BezNr, Typ, TabNr FROM t_050 WHERE KritNr='".$row["KritNr"]."';", $dbshop, __FILE__, __LINE__);
		$row2=mysql_fetch_array($results2);
		if ($row2["Typ"]=="K")
		{
			if (is_numeric($row["KritWert"]))
			{
				$results4=q("SELECT BezNr FROM t_052 WHERE TabNr=".$row2["TabNr"]." AND Schl=".$row["KritWert"].";", $dbshop, __FILE__, __LINE__);
			}
			else
			{
				$results4=q("SELECT BezNr FROM t_052 WHERE TabNr=".$row2["TabNr"]." AND Schl='".$row["KritWert"]."';", $dbshop, __FILE__, __LINE__);
			}
			$row4=mysql_fetch_array($results4);
			$results4=q("SELECT Bez FROM t_030 WHERE BezNr=".$row4["BezNr"].";", $dbshop, __FILE__, __LINE__);
			$row4=mysql_fetch_array($results4);
			$wert=$row4["Bez"];
		}
		else 
		{
			if ($row["KritNr"] == 20 or $row["KritNr"] == 21)
			{
				$wert=substr($row["KritWert"], -2, 2).'/'.substr($row["KritWert"], 0, 4);
			}
			else $wert=$row["KritWert"];
		}
		$results2=q("SELECT Bez FROM t_030 WHERE BezNr=".$row2["BezNr"].";", $dbshop, __FILE__, __LINE__);
		$row2=mysql_fetch_array($results2);
		//$bez=iconv("windows-1252", "utf-8", $row2["Bez"]);
		$bez=$row2["Bez"];
		
		echo '<tr>';
		echo '<td>'.$cnt.'</td>';
		echo '<td>'.$row["ArtNr"].'</td>';
		echo '<td>'.$row["KritNr"].'</td>';
		echo '<td>'.$row["KritWert"].'</td>';
		echo '<td>'.$row["SortNr"].'</td>';
		echo '<td>'.$bez.'</td>';
		echo '<td>'.$wert.'</td>';
		echo '</tr>';
	}
	echo '</table>';
?>