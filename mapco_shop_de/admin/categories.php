<?php
	include("config.php");
	$title="Kategorie-Editor | Online-Shop";
	$right_column=true;
	include("templates/".TEMPLATE."/header.php");

	echo '<table>';
	echo '	<tr>';
	echo '		<th>Titel</th>';
	echo '		<th>Sortierung</th>';
	echo '		<th>Optionen</th>';
	echo '	</tr>';
	$results=q("SELECT * FROM shop_categories ORDER BY title;", $dbshop, __FILE__, __LINE__);
	while($row=mysqli_fetch_array($results))
	{
		echo '<tr>';
		echo '	<td>'.$row["title"].'</td>';
		echo '	<td>'.$row[""].'</td>';
		echo '	<td>'.$row["title"].'</td>';
		echo '</tr>';
	}
	echo '</table>';

	include("templates/".TEMPLATE."/footer.php");
?>