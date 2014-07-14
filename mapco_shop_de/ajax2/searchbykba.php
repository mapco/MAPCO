<?php
	include("../config.php");
	include("../functions/mapco_baujahr.php");
	if ($_GET["zu_2"]!="" and $_GET["zu_3"]!="")
	{
		$results=q("SELECT * FROM t_121 WHERE KBANr='".$_GET["zu_2"].$_GET["zu_3"]."';", $dbshop, __FILE__, __LINE__);
		if (mysqli_num_rows($results)==1)
		{		
			$row=mysqli_fetch_array($results);
			echo $row["KBANr"];
		}
		else
		{
			echo '<table class="hover" style="position:absolute; left:50%; top:50%; width:700px; height:150px; margin-left:-350px; margin-top:-75px; background:#ffffff;">'."\n";
			echo '<tr>';
			echo '<th>Fahrzeug</th>';
			echo '<th>Baujahr</th>';
			echo '<th>Leistung</th>';
			echo '<th>Hubraum</th>';
			echo '</tr>';
			
			while ($row=mysqli_fetch_array($results))
			{
				$results2=q("SELECT * FROM vehicles_de WHERE Exclude=0 AND KTypNr=".$row["KTypNr"].";", $dbshop, __FILE__, __LINE__);
				$row2=mysqli_fetch_array($results2);
				echo '<tr>';
				echo '<td><a href="'.PATH.'shop_searchbycar.php?kbanr='.$row["KBANr"].'&ktypnr='.$row["KTypNr"].'&lang='.$_GET["lang"].'">'.$row2["BEZ1"].' '.$row2["BEZ2"].' '.$row2["BEZ3"].'</a></td>';
				echo '<td>'.baujahr($row2["BJvon"]).' - '.baujahr($row2["BJbis"]).'</td>';
				echo '<td>'.number_format($row2["kW"]).'KW ('.number_format($row2["PS"]).'PS)</td>';
				echo '<td>'.number_format($row2["ccmTech"]).'ccm</td>';
				echo '</tr>';
			}
			echo '</table>';
			
		}
	}
?>