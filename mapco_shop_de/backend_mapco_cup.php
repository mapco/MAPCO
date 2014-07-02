<?php
	include("config.php");
	include("templates/".TEMPLATE_BACKEND."/header.php");

	//PATH
	echo '<p>';
	echo '<a href="backend_index.php">Backend</a>';
	echo ' > <a href="backend_cms_index.php">Content Management</a>';
	echo ' > MAPCO Racing Cup';
	echo '</p>';
	
	
	//VIEW
	echo '<h1>MAPCO Racing Cup</h1>';
	echo '<a href="backend_mapco_cup_editor.php">Neuen Teilnehmer anlegen</a>';
	
	$query="SELECT * FROM mapco_cup ORDER BY firstname, middlename, lastname;";
	$results=q($query, $dbweb, __FILE__, __LINE__);
	if (mysqli_num_rows($results)>0)
	{
		echo '<table>';
		echo '<tr>';
		echo '	<th>Name</th>';
		echo '	<th>Zeit</th>';
		echo '</tr>';
		while($row=mysqli_fetch_array($results))
		{
			echo '<tr>';
			echo '	<td><a href="backend_mapco_cup_editor.php?id_user='.$row["id_user"].'">'.$row["firstname"].' '.$row["lastname"].'</a></td>';
			echo '	<td>'.$row["time_min"].':'.$row["time_sec"].'´'.$row["time_mil"].'´´</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	
	include("templates/".TEMPLATE_BACKEND."/footer.php");
?>