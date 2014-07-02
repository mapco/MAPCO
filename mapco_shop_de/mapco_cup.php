<?php
	include("config.php");
	include("templates/".TEMPLATE."/header.php");
	include("templates/".TEMPLATE."/cms_leftcolumn.php");
	
	echo '<div id="mid_column">';
	$query="SELECT * FROM mapco_cup ORDER BY time_min, time_sec, time_mil;";
	$results=q($query, $dbweb, __FILE__, __LINE__);
	if (mysqli_num_rows($results)>0)
	{
		echo '<div class="box_small">';
		echo '<h1>MAPCO Racing Cup</h1>';
		echo '<table class="hover">';
		echo '<tr>';
		echo '	<th>Platz</th>';
		echo '	<th>Name</th>';
		echo '	<th style="width:70px;">Zeit</th>';
		echo '</tr>';
		$i=0;
		while($row=mysqli_fetch_array($results))
		{
			$i++;
			echo '<tr>';
			echo '	<td>'.$i.'</td>';
			echo '	<td>'.$row["firstname"].' '.$row["lastname"].'</td>';
			echo '	<td>'.$row["time_min"].':'.$row["time_sec"].'´'.$row["time_mil"].'´´</td>';
			echo '</tr>';
		}
		echo '</table>';
		echo '</div>';
	}
	echo '</div>';

	include("templates/".TEMPLATE."/cms_rightcolumn.php");
	include("templates/".TEMPLATE."/footer.php");
?>