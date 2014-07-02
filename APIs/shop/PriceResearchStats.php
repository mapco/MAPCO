<?php
	$user=array();
	$stats=array();
	$stats_week=array();
	$results=q("SELECT * FROM shop_price_research;", $dbshop, __FILE__, __LINE__);
	while( $row=mysqli_fetch_array($results) )
	{
		if ( isset($stats[$row["firstmod_user"]]) )
		{
			$stats[$row["firstmod_user"]]++;
			if ( ($row["firstmod"]+(7*24*3600))>time() ) $stats_week[$row["firstmod_user"]]++;
		}
		else
		{
			$user[$row["firstmod_user"]]=$row["firstmod_user"];
			$stats[$row["firstmod_user"]]=1;
			if ( ($row["firstmod"]+(7*24*3600))>time() ) $stats_week[$row["firstmod_user"]]=1;
			else $stats_week[$row["firstmod_user"]]=0;
		}
	}
	
	array_multisort($stats_week, SORT_DESC, $user, $stats);
	
	echo '<table>';
		echo '<tr>';
		echo '	<th>Mitarbeiter</th>';
		echo '	<th>Letzten 7 Tage</th>';
		echo '	<th>Gesamt</th>';
		echo '</tr>';
	for($i=0; $i<sizeof($stats); $i++)
	{
		echo '<tr>';
		$results=q("SELECT * FROm cms_users WHERE id_user=".$user[$i].";", $dbweb, __FILE__, __LINE__);
		$row=mysqli_fetch_array($results);
		echo '	<td>'.$row["username"].'</td>';
		echo '	<td>'.$stats_week[$i].'</td>';
		echo '	<td>'.$stats[$i].'</td>';
		echo '</tr>';
	}
	echo '</table>';

?>