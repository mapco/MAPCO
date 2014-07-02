<?php
	$title="Online-Shop";
	$right_column=true;
	include("templates/".TEMPLATE."/header.php");


	$results=q("SELECT * FROM shop_items WHERE ORDER BY RAND() LIMIT 6;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		echo '<tr>';
		
		//Compare
		echo '	<td style="background:#e8e8e6; vertical-align:middle;"><input type="checkbox" /></td>';
		
		
		//Image
		echo '<td style="width:100px;">';
		echo '	<div style="width:100px; height:100px; margin:2px; border:1px solid #cecece; padding:0; background:#ffffff; text-align:center; display:inline; float:left;">';
		echo '		<img src="http://www.mapco.de/fotos/abbildungen/web/'.$row["MPN"].'a.jpg" />';
		echo '	</div>';
		echo '</td>';		
		
		//title and description
		echo '<td><h1>'.$row["title"].'</h1><br style="clear:both;" /><br />'.$row["short_description"].'</td>';
		
		//price
		echo '<td style="text-align:center;">';
		echo '	<span style="width:100px; font-size:16px; font-weight:bold; font-style:italic; color:#fc7204;">€ '.number_format($row["price"], 2).'</span>';
		echo '</td>';
		echo '</tr>';
		
		/*
		echo '<div class="shopitem">';
		echo '<div class="compare"><input type="checkbox" /></div>';
		echo '	<div style="width:250px; height:130px; margin:2px; border:1px solid #cecece; padding:0; background:#ffffff; display:inline; float:left;"><img src="../fotos/abbildungen/web/'.$row["artnr"].'a.jpg" /></div>';
		echo '	<h1>'.$row["title"].' ('.$row["artnr"].')</h1>';
		echo '</div>';
		*/
	}
	echo '</table>';

	include("templates/".TEMPLATE."/footer.php");
?>