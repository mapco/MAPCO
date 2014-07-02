<?php

include("config.php");

function show_tree($parent_id)
{
	$results2=q("SELECT * FROM t_301 AS a, t_030 AS b WHERE a.TreeTypeNr=1 AND a.BezNr=b.BezNr AND b.SprachNr=1 AND a.Node_Parent_ID=".$parent_id." ORDER BY a.SortNr", $dbshop, __FILE__, __LINE__);
	if(mysqli_num_rows($results2)>0)
	{
		while($row2=mysqli_fetch_array($results2))
		{
			echo $row2["Node_ID"].' - '.$row2["Bez"].' ('.$row2["Stufe"].')';
			show_tree($row2["Node_ID"]);	
		}
	}
}

$results=q("SELECT * FROM t_301 AS a, t_030 AS b WHERE a.TreeTypeNr=1 AND a.BezNr=b.BezNr AND b.SprachNr=1 AND a.Node_Parent_ID=0 ORDER BY a.SortNr", $dbshop, __FILE__, __LINE__);
while($row=mysqli_fetch_array($results))
{
	echo $row["Node_ID"].' - '.$row["Bez"].' ('.$row["Stufe"].')';
	show_tree($row["Node_ID"]);	
}

?>